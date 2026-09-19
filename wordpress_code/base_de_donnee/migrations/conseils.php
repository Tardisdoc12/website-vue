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

function mps_tools_migration_conseils_table() {
    global $wpdb;
    $table_conseils = $wpdb->prefix . "conseils";
    
    $index = $wpdb->get_results("SHOW INDEX FROM $table_conseils WHERE Key_name = 'user_file'");
    if (empty($index)) {
        $wpdb->query("
            ALTER TABLE $table_conseils
            ADD KEY user_file (wp_user_id, file_id)
        ");
    }

    
}

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------