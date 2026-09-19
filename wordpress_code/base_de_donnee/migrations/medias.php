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

function mps_tools_migration_medias_table() {
    global $wpdb;
    $table_medias = $wpdb->prefix . "medias";

    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_medias LIKE 'parent_id'");
    if (empty($column)) {
        $wpdb->query("ALTER TABLE $table_medias ADD parent_id VARCHAR(200) NULL AFTER file_type");
    }
}



//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------