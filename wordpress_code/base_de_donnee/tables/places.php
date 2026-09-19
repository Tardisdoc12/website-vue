<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: places.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-19
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;



//--------------------------------------------------------------------------------------------------
// Functions OR CLASS

function mps_tools_create_places_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_places = $wpdb->prefix . "places";
    $sql = "CREATE TABLE $table_places (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(200) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    return $sql;
}

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------