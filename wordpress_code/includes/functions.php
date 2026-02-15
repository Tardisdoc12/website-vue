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

function enqueue_vue_scripts() {
    global $vue_requested_modules;

    if (empty($vue_requested_modules)) {
        return; // Aucun shortcode sur la page
    }

    $plugin_url  = plugin_dir_url(__DIR__);
    $plugin_path = plugin_dir_path(__DIR__);

    // Path vers le manifest
    $manifest_path = $plugin_path . 'manifest.json';

    if (!file_exists($manifest_path)) {
        error_log('Vue manifest not found at: ' . $manifest_path);
        return '<!-- Vue manifest not found -->';
    }

    $manifest = json_decode(file_get_contents($manifest_path), true);

    if (!$manifest || !is_array($manifest)) {
        error_log('Vue manifest invalid: ' . json_last_error_msg());
        return;
    }

    $css_file = "dist/" . $manifest['style.css']['file'] ?? null;
    $js_file  = "dist/" . $manifest['src/main.js']['file'] ?? null;

    if (!$js_file) {
        error_log('Vue main entry not found in manifest');
        return '<!-- Vue main entry not found in manifest -->';
    }

    $deps = [];

    if (wp_script_is('elementor-frontend', 'registered')) {
        $deps[] = 'elementor-frontend';
    }

    // CSS
    if (file_exists($plugin_path . $css_file)) {
        wp_enqueue_style(
            'vue-modules-css',
            $plugin_url . $css_file,
            [],
            filemtime($plugin_path . $css_file)
        );
    }

    // JS
    if (file_exists($plugin_path . $js_file)) {
        wp_enqueue_script(
            'vue-modules-js',
            $plugin_url . $js_file,
            $deps,
            filemtime($plugin_path . $js_file),
            true
        );

        // wp_script_add_data('vue-modules-js', 'type', 'module');

        wp_localize_script('vue-modules-js', 'vueAppData', [
            'nonce'   => wp_create_nonce('wp_rest'),
            'modules' => array_values($vue_requested_modules),
            'restUrl' => esc_url_raw(rest_url()),
        ]);
    }
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