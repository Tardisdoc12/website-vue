<?php
/**
 * Plugin Name: Wordpress Modules
 * Description: Intègre une application Vue dans WordPress via des shortcodes et des ajouts pour la base de donnée
 * Version: 1.5.0
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
    'includes/rest-source.php',
    'includes/rest_favoris.php',
    'includes/migrations.php',
    'includes/events_template.php',
    'includes/rest-conseils.php',
    'includes/rest-notes.php',
    'includes/rest-media.php',
];

foreach ($includes as $file) {
    require_once plugin_dir_path(__FILE__) . $file;
}

register_activation_hook(__FILE__, 'mon_plugin_creer_tables');
register_activation_hook(__FILE__, 'monplugin_run_migrations');

//------------------------------------------------------------------------------
// End of File
//------------------------------------------------------------------------------