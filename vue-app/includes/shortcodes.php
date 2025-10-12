<?php
/*
* Gère les raccourcis Vue.js
*/
if (!defined('ABSPATH')) exit;

/**
 * Fonction générique pour créer un shortcode Vue
 */
function vue_shortcode($atts, $content, $tag) {
    $plugin_url  = plugin_dir_url(dirname(__FILE__));
    $plugin_path = plugin_dir_path(dirname(__FILE__));

    // Nom des fichiers CSS/JS basés sur le shortcode
    $css_file = $tag . '.css';
    $js_file  = $tag . '.js';

    $deps = [];
    if (wp_script_is('elementor-frontend', 'registered')) {
        $deps[] = 'elementor-frontend';
    }


    // CSS
    if (file_exists($plugin_path . $css_file)) {
        wp_enqueue_style(
            "vue-{$tag}-css",
            $plugin_url . $css_file,
            $deps,
            filemtime($plugin_path . $css_file) // ✅ version dynamique
        );
    }

    // JS
    if (file_exists($plugin_path . $js_file)) {
        wp_enqueue_script(
            "vue-{$tag}-js",
            $plugin_url . $js_file,
            $deps,
            filemtime($plugin_path . $js_file), // ✅ version dynamique
            true
        );
        wp_script_add_data("vue-{$tag}-js", 'type', 'module');

        // Localize le nonce **après** l’enqueue du script
        wp_localize_script("vue-{$tag}-js", 'vueAppData', [
            'nonce' => wp_create_nonce('wp_rest'),
        ]);
    }

    // Div ID basé sur le shortcode
    $div_id = $tag;
    return "<div id=\"{$div_id}\"></div>";
}

// Enregistrer les shortcodes
$shortcodes = ['login', 'calendar', 'events', 'connexion', 'account', 'form_adhesion'];

foreach ($shortcodes as $sc) {
    add_shortcode($sc, 'vue_shortcode');
}