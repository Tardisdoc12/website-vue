<?php
defined('ABSPATH') || exit;

function monplugin_run_migrations() {
    global $wpdb;
    $table_events = $wpdb->prefix . "events";
    $table_source = $wpdb->prefix . "source";

    // --- 1️⃣ Ajouter post_id si elle n'existe pas ---
    $column = $wpdb->get_results("SHOW COLUMNS FROM $table_events LIKE 'post_id'");
    if (empty($column)) {
        $wpdb->query("ALTER TABLE $table_events ADD post_id BIGINT(20) UNSIGNED NULL AFTER id");
    }

    $column2 = $wpdb->get_results("SHOW COLUMNS FROM $table_source LIKE 'ip_wp'");
    if (empty($column2)) {
        $wpdb->query("ALTER TABLE $table_source ADD id_wp BIGINT(20) UNSIGNED NULL AFTER tag");
    }
    // --- 2️⃣ Optionnel : autres migrations futures ---
    // Exemple : ajouter une colonne pour event_type
    /*
    $column2 = $wpdb->get_results("SHOW COLUMNS FROM $table_events LIKE 'event_type'");
    if (empty($column2)) {
        $wpdb->query("ALTER TABLE $table_events ADD event_type VARCHAR(50) NULL AFTER post_id");
    }
    */

    // --- 3️⃣ Flag pour éviter de relancer la migration ---
    update_option('monplugin_last_migration', time());
}