<?php

if (!defined('ABSPATH')) exit;

function mps_tools_create_code_promos_table() {
    global $wpdb;
    $table = $wpdb->prefix . 'promo_codes';
    $sql = "CREATE TABLE $table (
        id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
        code_hash CHAR(64) NOT NULL,
        label VARCHAR(100) NOT NULL,
        discount_type VARCHAR(10) NOT NULL,
        discount_value INT UNSIGNED NOT NULL,
        max_uses INT UNSIGNED NULL,
        used_count INT UNSIGNED NOT NULL DEFAULT 0,
        expiration_date DATE NULL,
        event_id BIGINT UNSIGNED NULL,
        active TINYINT(1) NOT NULL DEFAULT 1,
        PRIMARY KEY  (id),
        UNIQUE KEY code_hash (code_hash)
    ) {$wpdb->get_charset_collate()};";
    return $sql;
}