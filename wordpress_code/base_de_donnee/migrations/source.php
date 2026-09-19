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

function mps_tools_migration_source_table() {
    global $wpdb;
    $table_source = $wpdb->prefix . "source";
    
    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_source LIKE 'id_wp'");
    if (empty($column)) {
        $wpdb->query("ALTER TABLE $table_source ADD id_wp BIGINT(20) UNSIGNED NULL AFTER tag");
    }
}

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------