<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: event.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-19
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;

//--------------------------------------------------------------------------------------------------
// Functions

function mps_tools_create_event_table() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
    $table_events = $wpdb->prefix . "events";

    /** 
     * le closed_inscription est à 
     * 1 si les inscriptions sont fermées, 
     * 2 si l'evenement es complet, 
     * 3 si il est dépassé 
     * sinon 0
     */
    $sql = "CREATE TABLE $table_events (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        post_id BIGINT(20) UNSIGNED NOT NULL,
        title VARCHAR(200) NOT NULL,
        start_date DATETIME NOT NULL,
        end_date DATETIME NULL,
        description LONGTEXT NOT NULL,
        place VARCHAR(200) NOT NULL,
        category VARCHAR(100) NOT NULL,
        subscribe_places INT NOT NULL,
        nonsubscribe_places INT UNSIGNED NOT NULL,
        attente_places INT UNSIGNED NOT NULL,
        closed_inscription TINYINT(1) NOT NULL DEFAULT 0,
        adherent_price DECIMAL(10,2) NOT NULL DEFAULT 0,
        non_adherent_price DECIMAL(10,2) NOT NULL DEFAULT 0,
        payement_title VARCHAR(255) DEFAULT NULL,
        update_date DATETIME NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    return $sql;
}

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------