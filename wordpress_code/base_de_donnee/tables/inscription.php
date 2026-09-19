<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: inscription.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-19
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;



//--------------------------------------------------------------------------------------------------
// Functions

function mps_tools_create_inscription_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_inscrits = $wpdb->prefix . "inscrits";
    $sql = "CREATE TABLE $table_inscrits (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        event_id BIGINT(20) UNSIGNED NOT NULL,
        date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
        bike VARCHAR(200) NULL,
        encadrement TINYINT(1) NOT NULL DEFAULT 0,
        status ENUM('inscrit', 'attente') NOT NULL DEFAULT 'inscrit',
        date_inscrit DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
        payement_status ENUM('pending', 'completed', 'failed', 'cash') NOT NULL DEFAULT 'pending',
        champs_speciaux JSON NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    return $sql;
}

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------