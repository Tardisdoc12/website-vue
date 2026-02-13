<?php
/*
* Gère les raccourcis Vue.js
*/
if (!defined('ABSPATH')) exit;

/**
 * Génère automatiquement les shortcodes Vue depuis le manifest Vite
 */
function register_vue_shortcodes_from_manifest() {

    // Chemin vers le manifest.json généré par Vite
    $manifest_path = plugin_dir_path(__FILE__) . '../dist/manifest.json';

    if (!file_exists($manifest_path)) {
        return; // Pas de manifest, rien à faire
    }

    $manifest = json_decode(file_get_contents($manifest_path), true);

    if (!$manifest || !is_array($manifest)) {
        return;
    }

    // Boucle sur chaque entrée du manifest
    foreach ($manifest as $file => $data) {

        // On ne garde que les fichiers JS qui sont des entrées de composants
        if (!isset($data['isEntry']) || !$data['isEntry']) {
            continue;
        }

        // Nom du composant (sans extension)
        $component_name = pathinfo($file, PATHINFO_FILENAME);

        // Convertir PascalCase ou camelCase en kebab-case pour le shortcode
        $shortcode = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $component_name));

        // Ajouter le shortcode pointant vers la fonction générique vue_shortcode
        add_shortcode($shortcode, 'vue_shortcode');
    }
}

add_action('init', 'register_vue_shortcodes_from_manifest');


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
