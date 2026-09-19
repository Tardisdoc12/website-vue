<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: notes.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-19
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;



//--------------------------------------------------------------------------------------------------
// Functions OR CLASS

function mps_tools_create_notes_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_notes = $wpdb->prefix . "notes";
    $sql = "CREATE TABLE $table_notes (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        wp_user_id BIGINT(20) UNSIGNED NOT NULL,
        note_write TEXT NOT NULL,
        is_personal TINYINT(1) NOT NULL DEFAULT 0,
        PRIMARY KEY (id),
        KEY user_personal (wp_user_id, is_personal)
    ) $charset_collate;";
    return $sql;
}

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------