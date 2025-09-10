<?php
/* 
* On créait les tables nécessaires
* 
* Version : 1.0.0
*/
if (!defined('ABSPATH')) exit;

register_activation_hook(__FILE__, 'mon_plugin_creer_tables');

function mon_plugin_creer_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $table_events = $wpdb->prefix . "events";
    $sql1 = "CREATE TABLE $table_events (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        title VARCHAR(200) NOT NULL,
        start_date DATETIME NOT NULL,
        end_date DATETIME NULL,
        description TEXT NOT NULL,
        place VARCHAR(200) NOT NULL,
        category VARCHAR(100) NOT NULL,
        subscribe_places INT NOT NULL,
        nonsubscribe_places INT UNSIGNED NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $table_inscrits = $wpdb->prefix . "inscrits";
    $sql2 = "CREATE TABLE $table_inscrits (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        event_id BIGINT(20) UNSIGNED NOT NULL,
        date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
        bike VARCHAR(200) NULL,
        goal VARCHAR(200) NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $table_users_inscrits = $wpdb->prefix . "users_inscrits";
    $sql3= "CREATE TABLE $table_users_inscrits (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_name VARCHAR(200) NOT NULL,
        phone VARCHAR(10) NOT NULL,
        email VARCHAR(200) NOT NULL,
        experience VARCHAR(200) NOT NULL,
        is_adherent TINYINT(1) NOT NULL DEFAULT 0,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql1);
    dbDelta($sql2);
    dbDelta($sql3);
}