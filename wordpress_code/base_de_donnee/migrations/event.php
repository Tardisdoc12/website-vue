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

function mps_tools_migration_event_table() {
    global $wpdb;
    $table_events = $wpdb->prefix . "events";

    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_events LIKE 'description'");
    if (!empty($column) && $column[0]->Type !== 'longtext') {
        $wpdb->query("ALTER TABLE $table_events MODIFY COLUMN description LONGTEXT");
    }

    // --- 1️⃣ Ajouter post_id si elle n'existe pas ---
    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_events LIKE 'post_id'");
    if (empty($column)) {
        $wpdb->query("ALTER TABLE $table_events ADD post_id BIGINT(20) UNSIGNED NULL AFTER id");
    }

    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_events LIKE 'update_date'");
    if (empty($column)) {
        $wpdb->query("ALTER TABLE $table_events ADD update_date DATETIME NULL AFTER billeterie_url");
    }

    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_events LIKE 'attente_places'");
    if (empty($column)) {
        $wpdb->query("ALTER TABLE $table_events ADD attente_places INT UNSIGNED NOT NULL DEFAULT 0 AFTER nonsubscribe_places");
    }

    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_events LIKE 'billeterie_url'");
    if (empty($column)) {
        $wpdb->query("ALTER TABLE $table_events ADD billeterie_url VARCHAR(500) NULL AFTER closed_inscription");
    }

    // --- Ajouter closed_inscription si elle n'existe pas ---
    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_events LIKE 'closed_inscription'");
    if (empty($column)) {
        $wpdb->query("ALTER TABLE $table_events ADD closed_inscription TINYINT(1) NOT NULL DEFAULT 0 AFTER nonsubscribe_places");
    }

    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_events LIKE 'billeterie_id'");
    if (empty($column)) {
        $wpdb->query("ALTER TABLE $table_events ADD billeterie_id BIGINT(20) UNSIGNED NULL AFTER billeterie_url");
    }

    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_events LIKE 'billeterie_url'");
    if (!empty($column)) {
        $wpdb->query("ALTER TABLE $table_events DROP COLUMN billeterie_url");
    }

    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_events LIKE 'billeterie_id'");
    if (!empty($column)) {
        $wpdb->query("ALTER TABLE $table_events DROP COLUMN billeterie_id");
    }

    $column_adherent_price = $wpdb->get_results("SHOW COLUMNS FROM $table_events LIKE 'adherent_price'");
    if (empty($column_adherent_price)) {
        $wpdb->query("ALTER TABLE $table_events ADD adherent_price DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER closed_inscription");
    }

    $column_non_adherent_price = $wpdb->get_results("SHOW COLUMNS FROM $table_events LIKE 'non_adherent_price'");
    if (empty($column_non_adherent_price)) {
        $wpdb->query("ALTER TABLE $table_events ADD non_adherent_price DECIMAL(10,2) NOT NULL DEFAULT 0 AFTER adherent_price");
    }

    $column_payement_title = $wpdb->get_results("SHOW COLUMNS FROM $table_events LIKE 'payement_title'");
    if (empty($column_payement_title)) {
        $wpdb->query("ALTER TABLE $table_events ADD payement_title VARCHAR(255) DEFAULT NULL AFTER non_adherent_price");
    }

}

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------