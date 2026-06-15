<?php
/*
* Les fonctions nécessaires dans plusieurs fichiers
*/

if (!defined('ABSPATH')) exit;

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

        // Vérifier le token via le hook du plugin JWT
        $user = apply_filters('jwt_auth_token_before_dispatch', $token);

        if ($user && !is_wp_error($user)) {
            return true;
        }

        return new WP_Error(
            'invalid_jwt_token',
            'JWT token invalide ou expiré.',
            ['status' => 403]
        );
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
// End of File
//------------------------------------------------------------------------------