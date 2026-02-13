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

function vue_shortcode($atts, $content, $tag) {

    $plugin_url  = plugin_dir_url(dirname(__FILE__)) . 'dist/';
    $plugin_path = plugin_dir_path(dirname(__FILE__)) . 'dist/';

    $deps = [];
    if (wp_script_is('elementor-frontend', 'registered')) {
        $deps[] = 'elementor-frontend';
    }

    // Charger le CSS global (une seule fois)
    if (!wp_style_is('vue-plugin-style', 'enqueued') && file_exists($plugin_path . 'style.css')) {
        wp_enqueue_style(
            'vue-plugin-style',
            $plugin_url . 'style.css',
            [],
            filemtime($plugin_path . 'style.css')
        );
    }

    // Charger le JS principal (une seule fois)
    if (!wp_script_is('vue-plugin-main', 'enqueued') && file_exists($plugin_path . 'main.js')) {

        wp_enqueue_script(
            'vue-plugin-main',
            $plugin_url . 'main.js',
            $deps,
            filemtime($plugin_path . 'main.js'),
            true
        );

        wp_script_add_data('vue-plugin-main', 'type', 'module');

        wp_localize_script('vue-plugin-main', 'vueAppData', [
            'nonce' => wp_create_nonce('wp_rest'),
        ]);
    }

    // Retourne un conteneur générique
    return '<div class="vue-app" data-component="' . esc_attr(ucfirst($tag)) . '"></div>';
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