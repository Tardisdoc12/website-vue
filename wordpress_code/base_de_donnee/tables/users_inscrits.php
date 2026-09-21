<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: users_inscrits.php
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

function mps_tools_create_users_inscrits_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_users_inscrits = $wpdb->prefix . "users_inscrits";
    $sql= "CREATE TABLE $table_users_inscrits (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_name VARCHAR(200) NOT NULL,
        phone VARCHAR(10) NOT NULL,
        email VARCHAR(200) NOT NULL,
        is_adherent TINYINT(1) NOT NULL DEFAULT 0,
        urgence_phone VARCHAR(10),
        urgence_name VARCHAR(200),
        PRIMARY KEY (id)
    ) $charset_collate;";
    return $sql;
}

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------