<?php
/**
 * Plugin Name: Vue App
 * Description: Intègre une application Vue dans WordPress via des shortcodes, middleware et des ajouts pour la base de donnée
 * Version: 1.0
 * Author: Jean
 */

if (!defined('ABSPATH')) exit;

// Charger tous les fichiers nécessaires
$includes = [
    'includes/shortcodes.php',
    'includes/rest-fields.php',
    'includes/db-tables.php',
    'includes/rest-events.php',
    'includes/rest-subscribe.php',
    'includes/rest-users.php',
    'includes/rest-auth.php',
];

foreach ($includes as $file) {
    require_once plugin_dir_path(__FILE__) . $file;
}

//------------------------------------------------------------------------------
// End of File
//------------------------------------------------------------------------------