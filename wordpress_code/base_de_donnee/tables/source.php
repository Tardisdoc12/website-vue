<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: source.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-19
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;

//--------------------------------------------------------------------------------------------------
// Functions
//--------------------------------------------------------------------------------------------------

function mps_tools_create_source_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_source = $wpdb->prefix . "source";
    $sql = "CREATE TABLE $table_source (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        id_subcategorie BIGINT(20) UNSIGNED NOT NULL,
        path_file VARCHAR(500),
        url_file VARCHAR(500),
        tag VARCHAR(200) NOT NULL,
        id_wp BIGINT(20) UNSIGNED NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    return $sql;
}

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------