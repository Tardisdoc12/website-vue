<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: favoris.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-19
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;



//--------------------------------------------------------------------------------------------------
// Functions OR CLASS
//--------------------------------------------------------------------------------------------------

function mps_tools_create_favoris_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_favoris = $wpdb->prefix . "favoris";
    $sql = "CREATE TABLE $table_favoris (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        wp_user_id BIGINT(20) UNSIGNED NOT NULL,
        file_id BIGINT(20) UNSIGNED NOT NULL,
        PRIMARY KEY (id),
        KEY user_file (wp_user_id, file_id)
    ) $charset_collate;";
    return $sql;
}



//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------