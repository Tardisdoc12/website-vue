<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: subcategories.php
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

function mps_tools_create_subcategories_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_subcategories = $wpdb->prefix . "subcategories";
    $sql = "CREATE TABLE $table_subcategories (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        id_categorie BIGINT(20) UNSIGNED NOT NULL,
        title VARCHAR(200) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    return $sql;
}

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------