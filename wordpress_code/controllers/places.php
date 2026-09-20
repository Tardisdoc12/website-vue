<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: places.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-20
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;



//--------------------------------------------------------------------------------------------------
// Functions OR CLASS

function mps_tools_get_places(WP_REST_Request $request) {
    global $wpdb;

    $table_places = $wpdb->prefix . "places";

    $places = $wpdb->get_results("SELECT * FROM $table_places");

    return rest_ensure_response(array('success' => true, 'places' => $places));
}

//--------------------------------------------------------------------------------------------------

function mps_tools_create_place(WP_REST_Request $request) {
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

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------