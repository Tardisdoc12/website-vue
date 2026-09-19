<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: conseils.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-19
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;



//--------------------------------------------------------------------------------------------------
// Functions OR CLASS

function mps_tools_create_conseils_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_conseils = $wpdb->prefix . "conseils";
    $sql = "CREATE TABLE $table_conseils (
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