<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: code_promos.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-19
* DESCRIPTION : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;



//--------------------------------------------------------------------------------------------------
// Functions OR CLASS

function mps_tools_migration_code_promos_table() {
    global $wpdb;
    $table_code_promos = $wpdb->prefix . "promo_codes";
    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_code_promos LIKE 'event_type'");
    if (empty($column)) {
        $wpdb->query("ALTER TABLE $table_code_promos ADD event_type VARCHAR(50) NULL AFTER event_id");
    }
}

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------