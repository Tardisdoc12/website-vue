<?php
/*
* Les fonctions nécessaires dans plusieurs fichiers
*/

if (!defined('ABSPATH')) exit;

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
        'login' => 'login',
        'calendar' => 'calendar',
        'global_compte' => 'global_compte',
        'adherent' => 'adherent',
        'connexion' => 'connexion',
        'espace_profil' => 'espace_profil',
        'gestions_comptes' => 'gestions_comptes',
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