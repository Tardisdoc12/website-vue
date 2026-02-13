<?php
/*
* Gère les raccourcis Vue.js
*/
if (!defined('ABSPATH')) exit;

//------------------------------------------------------------------------------
// IMPORTS

$file = "functions.php";
require_once plugin_dir_path(__FILE__) . $file;

//------------------------------------------------------------------------------

// Enregistrer les shortcodes
$shortcodes = [
    'login',
    'calendar',
    'connexion',
    'account',
    'form_adhesion',
    'test',
    'event-page',
    'reinitialisation'
];

foreach ($shortcodes as $sc) {
    add_shortcode($sc, 'vue_shortcode');
}

//------------------------------------------------------------------------------

add_action('wp_enqueue_scripts', function () {
    if (!is_singular('event')) {
        return;
    }

    $plugin_url  = plugin_dir_url(__DIR__);
    $plugin_path = plugin_dir_path(__DIR__);

    $js  = $plugin_path . 'event-page.js';
    $css = $plugin_path . 'event-page.css';

    if (file_exists($css)) {
        wp_enqueue_style(
            'vue-event-page-css',
            $plugin_url . 'event-page.css',
            [],
            filemtime($css)
        );
    }

    if (file_exists($js)) {
        wp_enqueue_script(
            'vue-event-page-js',
            $plugin_url . 'event-page.js',
            [],
            filemtime($js),
            true
        );

        wp_script_add_data('vue-event-page-js', 'type', 'module');

        wp_localize_script('vue-event-page-js', 'vueAppData', [
            'nonce' => wp_create_nonce('wp_rest'),
        ]);
    }
}, 20);
