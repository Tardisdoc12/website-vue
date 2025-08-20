<?php
/**
 * Plugin Name: Vue App
 * Description: Intègre une application Vue dans WordPress via des shortcodes [login], [calendar], [events].
 * Version: 1.0
 * Author: Toi
 */

if (!defined('ABSPATH')) exit;

/**
 * Fonction générique pour créer un shortcode Vue
 */
function vue_shortcode($atts, $content, $tag) {
    $plugin_url = plugin_dir_url(__FILE__);
    $plugin_path = plugin_dir_path(__FILE__);

    // Nom des fichiers CSS/JS basés sur le shortcode
    $css_file = $tag . '.css';
    $js_file  = $tag . '.js';

    // CSS
    if (file_exists($plugin_path . $css_file)) {
        wp_enqueue_style("vue-{$tag}-css", $plugin_url . $css_file);
    }

    // JS
    wp_enqueue_script("vue-{$tag}-js", $plugin_url . $js_file, [], null, true);
    wp_script_add_data("vue-{$tag}-js", 'type', 'module');

    // Div ID basé sur le shortcode
    $div_id = str_replace('_', '-', $tag);
    return "<div id=\"{$div_id}\"></div>";
}

// Enregistrer les shortcodes
$shortcodes = ['login', 'calendar', 'events', 'connexion'];

foreach ($shortcodes as $sc) {
    add_shortcode($sc, 'vue_shortcode');
}