<?php
/**
 * Plugin Name: MPS Tools
 * Description: Plugin wordpress permettant de gérer divers outils et fonctionnalités pour association.
 * Version: 1.5.2
 * Author: Jean
 */

if (!defined('ABSPATH')) exit;

define('MPS_TOOLS_MAIN_FILE', __FILE__);
define('MPS_TOOLS_FUNCTIONS_DIR', __DIR__ . '/functions/');
define('MPS_TOOLS_OBJECTS_DIR', __DIR__ . '/objects/');
define('MPS_TOOLS_INCLUDES_DIR', __DIR__ . '/includes/');
define('MPS_TOOLS_BDD_DIR', __DIR__ . '/base_de_donnee/');
define('MPS_TOOLS_PARAMETERS_DIR', __DIR__ . '/parametres/');

// Charger tous les fichiers nécessaires
$includes = [
    'shortcodes.php',
    'rest-fields.php',
    'rest-events.php',
    'rest-subscribe.php',
    'rest-users.php',
    'rest-auth.php',
    'rest-source.php',
    'rest_favoris.php',
    'events_template.php',
    'rest-conseils.php',
    'rest-notes.php',
    'rest-media.php',
    'rest-places.php',
    'rest-payement.php',
    'rest-notification-helloasso.php',
    
];

$file_functions = [
    'route_callback.php',
    'mailing.php',
    'render_field_parameters.php',
    'sanitize_field_parameters.php',
    'shortcodes.php'
];

$files_database = [
    'table_creation.php',
    'table_migration.php',
];

add_filter('wp_mail_from', function($email) {
    return get_option('mon_plugin_mail_from') ?: $email;
});

add_filter('wp_mail_from_name', function($name) {
    return get_option('mon_plugin_mail_name') ?: $name;
});


foreach ($file_functions as $file) {
    $file_path = MPS_TOOLS_FUNCTIONS_DIR . $file;
    if (file_exists($file_path)) {
        require_once $file_path;
    } else {
        error_log("MPS Tools : fichier de fonction manquant : {$file_path}");
    }
}

foreach ($files_database as $file) {
    $file_path = MPS_TOOLS_BDD_DIR . $file;
    if (file_exists($file_path)) {
        require_once $file_path;
    } else {
        error_log("MPS Tools : fichier de base de donnée manquant : {$file_path}");
    }
}

require_once MPS_TOOLS_PARAMETERS_DIR . 'admin-settings.php';

foreach ($includes as $file) {
    $file_path = MPS_TOOLS_INCLUDES_DIR . $file;
    if (file_exists($file_path)) {
        require_once $file_path;
    } else {
        error_log("MPS Tools : fichier de fonction manquant : {$file_path}");
    }
}



//------------------------------------------------------------------------------
// End of File
//------------------------------------------------------------------------------