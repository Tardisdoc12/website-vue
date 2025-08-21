<?php
/**
 * Plugin Name: Vue App
 * Description: Intègre une application Vue dans WordPress via des shortcodes, middleware et des ajouts pour la base de donnée
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

//-----------------------------------------------------------------------------------
// Middelware

add_action('template_redirect', 'redirect_if_logged_in_jwt');

function redirect_if_logged_in_jwt() {
    // Vérifie si on est sur la page de connexion (/home/)
    if (is_page('home')) {
        $jwt = isset($_COOKIE['mps_moto']) ? $_COOKIE['mps_moto'] : null;

        if ($jwt) {
            // Si déjà connecté avec un token valide, on redirige
            wp_redirect(home_url('/test/')); 
            exit;
        }
    }
}

//-----------------------------------------------------------------------------------

add_action('rest_api_init', function() {
    $custom_fields = [
        'firstName',
        'lastName',
        'adherentNumber',
        'telephone',
        'moto',
    ];

    foreach ($custom_fields as $field) {
        register_rest_field('user', $field, [
            'get_callback' => function($user) use ($field) {
                return get_user_meta($user['id'], $field, true);
            },
            'update_callback' => function($value, $user) use ($field) {
                update_user_meta($user->ID, $field, sanitize_text_field($value));
            },
        ]);
    }

    register_rest_field('user', 'email', [
        'get_callback' => function($user) {
            if (get_current_user_id() === $user['id']) {
                return get_userdata($user['id'])->user_email;
            }
            return null;
        },
        'update_callback' => null,
        'schema' => [
            'description' => __('User email'),
            'type' => 'string'
        ],
    ]);
});