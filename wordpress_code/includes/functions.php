<?php
/*
* Les fonctions nécessaires dans plusieurs fichiers
*/

if (!defined('ABSPATH')) exit;

require_once __DIR__ . '/../objects/jwt_generator.php';

add_filter('safe_style_css', function($styles) {
    $styles[] = 'color';
    $styles[] = 'background-color';
    $styles[] = 'text-align';
    return $styles;
});

function monplugin_sanitize_rich_text($html) {
    // Utiliser HTMLPurifier via DOMDocument natif PHP
    $dom = new DOMDocument();
    @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    
    $allowed_tags = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'span', 'ul', 'ol', 'li', 'a'];
    $allowed_attrs = ['style', 'href', 'target', 'rel', 'class'];
    
    $xpath = new DOMXPath($dom);
    
    // Supprimer les balises non autorisées
    foreach ($xpath->query('//*') as $node) {
        if (!in_array(strtolower($node->nodeName), $allowed_tags)) {
            $node->parentNode->replaceChild($dom->createTextNode($node->textContent), $node);
            continue;
        }
        
        // Supprimer les attributs non autorisés
        $attrs_to_remove = [];
        foreach ($node->attributes as $attr) {
            if (!in_array($attr->name, $allowed_attrs)) {
                $attrs_to_remove[] = $attr->name;
            }
        }
        foreach ($attrs_to_remove as $attr) {
            $node->removeAttribute($attr);
        }
        
        // Valider le style : n'autoriser que color, background-color, text-align
        if ($node->hasAttribute('style')) {
            $style = $node->getAttribute('style');
            $safe_style = '';
            
            foreach (explode(';', $style) as $declaration) {
                $declaration = trim($declaration);
                if (empty($declaration)) continue;
                
                if (preg_match('/^(color|background-color|text-align)\s*:\s*(.+)$/i', $declaration, $m)) {
                    $prop  = strtolower(trim($m[1]));
                    $value = trim($m[2]);
                    
                    // Valider la valeur : hex, rgb(), rgba(), ou nom de couleur
                    if (preg_match('/^(#[0-9a-fA-F]{3,8}|rgb\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*\)|rgba\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*,\s*[\d.]+\s*\)|[a-zA-Z]+|left|center|right)$/', $value)) {
                        $safe_style .= $prop . ': ' . $value . '; ';
                    }
                }
            }
            
            if (!empty(trim($safe_style))) {
                $node->setAttribute('style', trim($safe_style));
            } else {
                $node->removeAttribute('style');
            }
        }
    }
    
    $result = $dom->saveHTML();
    
    // saveHTML ajoute parfois des wrappers, on les retire
    $result = preg_replace('/^<!DOCTYPE.+?>/i', '', $result);
    $result = trim($result);
    
    return $result;
}

//----------------------------------------------------------------------------

function monplugin_verify_csrf(WP_REST_Request $request) {
    // Vérification via X-WP-Nonce (classique WordPress)
    $nonce = $request->get_header('X-WP-Nonce');
    if ($nonce && wp_verify_nonce($nonce, 'wp_rest')) {
        return true;
    }

    // Vérification via JWT
    $auth_header = $request->get_header('Authorization');
    if ($auth_header && preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
        $token = $matches[1];

        $verified_payload = AssoSimpleJWT::verify($token);

        if (!$verified_payload) {
            return new WP_Error(
                'invalid_jwt_token',
                'JWT token invalide ou expiré.',
                ['status' => 403]
            );
        }

        // Le payload doit contenir un wp_user_id valide
        if (empty($verified_payload['wp_user_id'])) {
            return new WP_Error(
                'invalid_jwt_token',
                'JWT token invalide : utilisateur manquant.',
                ['status' => 403]
            );
        }

        $user_id = absint($verified_payload['wp_user_id']);
        $user    = get_userdata($user_id);

        // Vérifie que l'utilisateur existe bien en base
        if (!$user) {
            return new WP_Error(
                'invalid_jwt_token',
                'JWT token invalide : utilisateur introuvable.',
                ['status' => 403]
            );
        }

        // Optionnel mais recommandé : authentifier réellement l'utilisateur
        // pour que current_user_can(), get_current_user_id(), etc. fonctionnent
        wp_set_current_user($user_id);

        return true;
    }

    // Aucun token fourni
    return new WP_Error(
        'missing_auth',
        'Aucun CSRF token ou JWT token fourni.',
        ['status' => 403]
    );
}

//------------------------------------------------------------------------------
/**
 * Déclaration des shortcodes
 */
function get_vue_shortcodes() {
    return [
        // shortcode => Files vue
        'calendar'         => 'calendar',
        'account'          => 'global_compte',
        'form_adhesion'    => 'adherent',
        'test'             => 'test_list',
        'event-page'       => 'event_page_proper',
        'reinitialisation' => 'reset_password_page',
        'render_payement'  => 'payement_render'
    ];
}


//------------------------------------------------------------------------------

add_action('wp_print_scripts', function () {
    if (!is_singular('event')) {
        return;
    }

    global $wp_scripts;

    foreach ($wp_scripts->queue as $handle) {
        if (
            strpos($handle, 'element-pack') !== false ||
            strpos($handle, 'bdt-uikit') !== false
        ) {
            wp_dequeue_script($handle);
            wp_deregister_script($handle);
        }
    }
}, 0);

//------------------------------------------------------------------------------

/**
 * Remplace les balises {{xxx}} dans un template par leurs vraies valeurs.
 *
 * @param string $template Le texte contenant des balises {{xxx}}
 * @param array  $data      Tableau associatif ['xxx' => 'valeur']
 * @return string Le texte avec les balises remplacées
 */
function monplugin_render_email_template($template, $data = []) {
    if (empty($template)) return '';

    return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', function($matches) use ($data) {
        $tag = $matches[1];
        return isset($data[$tag]) ? $data[$tag] : $matches[0]; // si la balise n'a pas de valeur, on la laisse telle quelle
    }, $template);
}

//------------------------------------------------------------------------------
// End of File
//------------------------------------------------------------------------------