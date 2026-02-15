<?php
// Sécurité
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Stocke les modules demandés sur la page
 */
function vue_register_requested_module($module) {
    global $vue_requested_modules;

    if (!is_array($vue_requested_modules)) {
        $vue_requested_modules = [];
    }

    if (!in_array($module, $vue_requested_modules, true)) {
        $vue_requested_modules[] = $module;
    }
}

/**
 * Shortcode générique
 */
function vue_shortcode($atts, $content = null, $tag = '') {
    vue_register_requested_module($tag);

    return '<div class="vue-root" data-module="' . esc_attr($tag) . '"></div>';
}

/**
 * Enregistrement des shortcodes
 */
add_action('init', function () {
    $shortcodes = ['login', 'calendar', 'compte'];

    foreach ($shortcodes as $sc) {
        add_shortcode($sc, 'vue_shortcode');
    }
});

/**
 * Enqueue CSS / JS + données Vue
 */
add_action('wp_enqueue_scripts', function () {
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

    $css_file = $manifest['style.css']['file'] ?? null;
    $js_file  = $manifest['src/main.js']['file'] ?? null;

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
            $plugin_url . "dist" . $css_file,
            [],
            filemtime($plugin_path . "dist" . $css_file)
        );
    }

    // JS
    if (file_exists($plugin_path . $js_file)) {
        wp_enqueue_script(
            'vue-modules-js',
            $plugin_url . "dist" . $js_file,
            $deps,
            filemtime($plugin_path . "dist" . $js_file),
            true
        );

        // wp_script_add_data('vue-modules-js', 'type', 'module');

        wp_localize_script('vue-modules-js', 'vueAppData', [
            'nonce'   => wp_create_nonce('wp_rest'),
            'modules' => array_values($vue_requested_modules),
            'restUrl' => esc_url_raw(rest_url()),
        ]);
    }
});