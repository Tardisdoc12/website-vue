<?php
/* 
* Routes REST API pour la gestion des lieux
* Version : 1.0.0
*/

if (!defined('ABSPATH')) exit;

//------------------------------------------------------------------------------
// IMPORTS

require_once plugin_dir_path(__FILE__) . 'functions.php';

//------------------------------------------------------------------------------

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/places', [
        'methods' => 'GET',
        'callback' => 'myplugin_get_places',
        'permission_callback' => 'monplugin_verify_csrf',
    ]);
});

function myplugin_get_places(WP_REST_Request $request) {
    global $wpdb;

    $table_places = $wpdb->prefix . "places";

    $places = $wpdb->get_results("SELECT * FROM $table_places");

    return rest_ensure_response(array('success' => true, 'places' => $places));
}

//------------------------------------------------------------------------------

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/places', [
        'methods' => 'POST',
        'callback' => 'myplugin_create_place',
        'permission_callback' => 'monplugin_verify_csrf',
    ]);
});

function myplugin_create_place(WP_REST_Request $request) {
    global $wpdb;

    $table_places = $wpdb->prefix . "places";

    $name = sanitize_text_field($request->get_param('name'));

    if (empty($name)) {
        return new WP_Error('invalid_data', 'Le nom du lieu est requis.', array('status' => 400));
    }

    $result = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $table_places WHERE name = %s", $name));

    if ($result > 0) {
        return new WP_Error('duplicate_place', 'Un lieu avec ce nom existe déjà.', array('status' => 400));
    }

    $wpdb->insert($table_places, array('name' => $name));

    return rest_ensure_response(array('success' => true, 'message' => 'Lieu créé avec succès.', 'place' => array('id' => $wpdb->insert_id, 'name' => $name)));
}

//------------------------------------------------------------------------------
// End of file
//------------------------------------------------------------------------------