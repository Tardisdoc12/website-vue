<?php
/* 
* On créait les tables nécessaires
* 
* Version : 1.0.0
*/
if (!defined('ABSPATH')) exit;

// register_activation_hook(__FILE__, 'mon_plugin_creer_tables');

function mon_plugin_creer_tables() {
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
    $sql1 = "CREATE TABLE $table_events (
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
        billeterie_url VARCHAR(500) NULL,
        update_date DATETIME NULL,
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
        encadrement TINYINT(1) NOT NULL DEFAULT 0,
        status ENUM('inscrit', 'attente') NOT NULL DEFAULT 'inscrit',
        date_inscrit DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
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
        urgence_phone VARCHAR(10),
        urgence_name VARCHAR(200),
        PRIMARY KEY (id)
    ) $charset_collate;";

    $table_subcategories = $wpdb->prefix . "subcategories";
    $sql4 = "CREATE TABLE $table_subcategories (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        id_categorie BIGINT(20) UNSIGNED NOT NULL,
        title VARCHAR(200) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $table_source = $wpdb->prefix . "source";
    $sql5 = "CREATE TABLE $table_source (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        id_subcategorie BIGINT(20) UNSIGNED NOT NULL,
        path_file VARCHAR(500),
        url_file VARCHAR(500),
        tag VARCHAR(200) NOT NULL,
        id_wp BIGINT(20) UNSIGNED NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $table_favoris = $wpdb->prefix . "favoris";
    $sql6 = "CREATE TABLE $table_favoris (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        wp_user_id BIGINT(20) UNSIGNED NOT NULL,
        file_id BIGINT(20) UNSIGNED NOT NULL,
        PRIMARY KEY (id),
        KEY user_file (wp_user_id, file_id)
    ) $charset_collate;";

    $table_conseils = $wpdb->prefix . "conseils";
    $sql7 = "CREATE TABLE $table_conseils (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        wp_user_id BIGINT(20) UNSIGNED NOT NULL,
        file_id BIGINT(20) UNSIGNED NOT NULL,
        PRIMARY KEY (id),
        KEY user_file (wp_user_id, file_id)
    ) $charset_collate;";

    $table_notes = $wpdb->prefix . "notes";
    $sql8 = "CREATE TABLE $table_notes (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        wp_user_id BIGINT(20) UNSIGNED NOT NULL,
        note_write TEXT NOT NULL,
        is_personal TINYINT(1) NOT NULL DEFAULT 0,
        PRIMARY KEY (id),
        KEY user_personal (wp_user_id, is_personal)
    ) $charset_collate;";

    $table_medias = $wpdb->prefix . "medias";
    $sql9 = "CREATE TABLE $table_medias (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        kdrive_file_id VARCHAR(200) NOT NULL,
        file_name VARCHAR(200) NOT NULL,
        file_size BIGINT(20) UNSIGNED DEFAULT NULL,
        file_type VARCHAR(100) DEFAULT NULL,
        parent_id VARCHAR(200) UNSIGNED DEFAULT NULL,
        uploaded_by BIGINT(20) UNSIGNED DEFAULT NULL,
        date_creation DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY (id),
        KEY kdrive_file (kdrive_file_id),
        KEY uploaded_by (uploaded_by),
        KEY parent_id (parent_id)
    ) $charset_collate;";

    $table_places = $wpdb->prefix . "places";
    $sql10 = "CREATE TABLE $table_places (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        name VARCHAR(200) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    $sqls = [
        $sql1,
        $sql2,
        $sql3,
        $sql4,
        $sql5,
        $sql6,
        $sql7,
        $sql8,
        $sql9,
        $sql10
    ];

    foreach ($sqls as $sql) {
        dbDelta($sql);
    }
}