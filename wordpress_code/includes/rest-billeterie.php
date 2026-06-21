<?php
/*
*
* Gère la billeterie des événements
*
*/
if (!defined('ABSPATH')) exit;

//------------------------------------------------------------------------------
// IMPORTS

$file = "functions.php";
require_once plugin_dir_path(__FILE__) . $file;

//------------------------------------------------------------------------------

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/billeterie', [
        'methods' => 'GET',
        'callback' => 'myplugin_get_billeterie',
        'permission_callback' => 'monplugin_verify_csrf',
    ]);
});

function myplugin_get_billeterie(WP_REST_Request $request) {
    global $wpdb;
    $table_billeterie = $wpdb->prefix . "billetteries";

    $billeteries = $wpdb->get_results(
        "SELECT * FROM $table_billeterie"
    );

    return [
        'success' => true,
        'data' => $billeteries
    ];

}

//------------------------------------------------------------------------------

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/billeterie', [
        'methods' => 'POST',
        'callback' => 'myplugin_create_billeterie',
        'permission_callback' => '__return_true',
    ]);
});

function myplugin_create_billeterie(WP_REST_Request $request) {
    global $wpdb;
    $table_billeterie = $wpdb->prefix . "billetteries";

    $data = $request->get_json_params();
    $title = sanitize_text_field($data['title']);
    $slug = sanitize_text_field($data['slug']);
    $url = esc_url_raw($data['url']);

    if (empty($title) || empty($slug) || empty($url)) {
        return new WP_Error('invalid_data', 'Le titre, le slug et l\'URL sont requis.', ['status' => 400]);
    }

    $wpdb->insert(
        $table_billeterie,
        [
            'title' => $title,
            'slug' => $slug,
            'url' => $url
        ]
    );

    return [
        'success' => true,
        'message' => 'Billetterie créée avec succès.'
    ];
}

//------------------------------------------------------------------------------

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/billeterie/(?P<id>\d+)', [
        'methods' => 'DELETE',
        'callback' => 'myplugin_delete_billeterie',
        'permission_callback' => '__return_true',
    ]);
});

function myplugin_delete_billeterie(WP_REST_Request $request) {
    global $wpdb;
    $table_billeterie = $wpdb->prefix . "billetteries";
    $billeterie_id = (int) $request->get_param('id');

    if (!$billeterie_id) {
        return new WP_Error('invalid_billeterie_id', 'ID de billetterie invalide', ['status' => 400]);
    }

    $wpdb->delete(
        $table_billeterie,
        ['id' => $billeterie_id],
        ['%d']
    );

    return [
        'success' => true,
        'message' => 'Billetterie supprimée avec succès.'
    ];
}