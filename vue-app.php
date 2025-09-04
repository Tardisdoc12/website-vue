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

//-----------------------------------------------------------------------------------
//Tables

register_activation_hook(__FILE__, 'mon_plugin_creer_tables');

function mon_plugin_creer_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $table_events = $wpdb->prefix . "events";
    $sql1 = "CREATE TABLE $table_events (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        title VARCHAR(200) NOT NULL,
        start_date DATETIME NOT NULL,
        end_date DATETIME NOT NULL,
        description TEXT NOT NULL,
        place VARCHAR(200) NOT NULL,
        category VARCHAR(100) NOT NULL,
        subscribe_places INT NOT NULL,
        nonsubscribe_places INT UNSIGNED NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $table_inscrits = $wpdb->prefix . "inscrits";
    $sql2 = "CREATE TABLE $table_inscrits (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        event_id BIGINT(20) UNSIGNED NOT NULL,
        date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
        bike VARCHAR(200) NULL,
        goal VARCHAR(200) NULL,
        PRIMARY KEY (id),
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql1);
    dbDelta($sql2);
}

//-----------------------------------------------------------------------------------

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/events', [
        'methods' => 'GET',
        'callback' => 'monplugin_get_events',
        'permission_callback' => '__return_true'
    ]);
});

function monplugin_get_events(WP_REST_Request $request) {
    global $wpdb;
    $table = $wpdb->prefix . "events";
    $events = $wpdb->get_results("SELECT * FROM $table");
    return $events;
}

//------------------------------------------------------------------------------

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1','/events',[
        'methods' => 'POST',
        'callback' => 'monplugin_create_events',
        'permission_callback' => '__return_true'
    ]);
});

function monplugin_create_events(WP_REST_Request $request) {
    global $wpdb;
    $table = $wpdb->prefix . "events";
    $wpdb->insert($table, [
        'title' => sanitize_text_field($request['title']),
        'start_date' => sanitize_text_field($request['start_date']),
        'end_date' => sanitize_text_field($request['end_date']),
        'description' => sanitize_textarea_field($request['description']),
        'place' => sanitize_text_field($request['place']),
        'category' => sanitize_text_field($request['category']),
        'subscribe_places' => intval($request['subscribe_places']),
        'nonsubscribe_places' => intval($request['nonsubscribe_places']),
    ]);

    return ['id' => $wpdb->insert_id];
}

//-----------------------------------------------------------------------------------

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1','/events/(?P<id>\d+)',[
        'methods' => 'DELETE',
        'callback' => 'monplugin_delete_events',
        'permission_callback' => '__return_true'
    ]);
});

function monplugin_delete_events(WP_REST_Request $request) {
    global $wpdb;
    $table = $wpdb->prefix . "events";
    $event_id = intval($request['id']);

    // Vérifier si l'événement existe
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table WHERE id = %d",
        $event_id
    ));

    if (!$exists) {
        return new WP_Error(
            'event_not_found',
            'Cet événement n’existe pas.',
            ['status' => 404]
        );
    }
    // Supprimer l’événement
    $wpdb->delete($table, ['id' => $event_id]);
    return [
        'success' => true,
        'deleted_event_id' => $event_id
    ];
}

//-----------------------------------------------------------------------------------
// End of File
//-----------------------------------------------------------------------------------