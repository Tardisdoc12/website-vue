<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: medias.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-19
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;



//--------------------------------------------------------------------------------------------------
// Functions OR CLASS

function mps_tools_create_medias_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_medias = $wpdb->prefix . "medias";
    $sql = "CREATE TABLE $table_medias (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        kdrive_file_id VARCHAR(200) NOT NULL,
        file_name VARCHAR(200) NOT NULL,
        file_size BIGINT(20) UNSIGNED DEFAULT NULL,
        file_type VARCHAR(100) DEFAULT NULL,
        parent_id VARCHAR(200) UNSIGNED DEFAULT NULL,
        uploaded_by BIGINT(20) UNSIGNED DEFAULT NULL,
        date_creation DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id),
        KEY kdrive_file (kdrive_file_id),
        KEY uploaded_by (uploaded_by),
        KEY parent_id (parent_id)
    ) $charset_collate;";
    return $sql;
}



//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------