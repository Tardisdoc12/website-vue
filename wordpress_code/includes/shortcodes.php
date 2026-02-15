<?php
// Sécurité
if (!defined('ABSPATH')) {
    exit;
}
//------------------------------------------------------------------------------
// IMPORTS

$file = "functions.php";
require_once plugin_dir_path(__FILE__) . $file;

//------------------------------------------------------------------------------
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

//------------------------------------------------------------------------------
/**
 * Shortcode générique
 */

function vue_shortcode($atts, $content = null, $tag = '') {
    vue_register_requested_module($tag);

    return '<div class="vue-root" data-module="' . esc_attr($tag) . '"></div>';
}

//------------------------------------------------------------------------------
/**
 * Enregistrement des shortcodes
 */

add_action('init', function () {
    $shortcodes = ['login', 'calendar', 'compte'];

    foreach ($shortcodes as $sc) {
        add_shortcode($sc, 'vue_shortcode');
    }
});

//------------------------------------------------------------------------------
/**
 * Enqueue CSS / JS + données Vue
 */

add_action('wp_enqueue_scripts', 'enqueue_vue_scripts');