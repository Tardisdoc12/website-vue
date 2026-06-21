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
    $table_billeterie = $wpdb->prefix . "billeteries";

    $billeteries = $wpdb->get_results(
        "SELECT * FROM $table_billeterie"
    );

    return [
        'success' => true,
        'data' => $billeteries
    ];

}

//------------------------------------------------------------------------------