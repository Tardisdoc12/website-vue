<?php
/**
 * Plugin Name: MPS Tools
 * Description: Plugin wordpress permettant de gérer divers outils et fonctionnalités pour association.
 * Version: 1.5.2
 * Author: Jean
 */

if (!defined('ABSPATH')) exit;

//--------------------------------------------------------------------------------------------------
// Définition des constantes pour les chemins des différents répertoires du plugin

define('MPS_TOOLS_MAIN_FILE',               __FILE__);
define('MPS_TOOLS_FUNCTIONS_DIR',           __DIR__ . '/functions/');
define('MPS_TOOLS_OBJECTS_DIR',             __DIR__ . '/objects/');
define('MPS_TOOLS_WORDPRESS_MODIFIER_DIR',  __DIR__ . '/wordpress_modifier/');
define('MPS_TOOLS_ROUTES_DIR',              __DIR__ . '/routes/');
define('MPS_TOOLS_CONTROLLERS_DIR',         __DIR__ . '/controllers/');
define('MPS_TOOLS_BDD_DIR',                 __DIR__ . '/base_de_donnee/');
define('MPS_TOOLS_PARAMETERS_DIR',          __DIR__ . '/parametres/');
define('MPS_TOOLS_JAVASCRIPT_DIR',          __DIR__ . '/javascript/');
define('MPS_TOOLS_PLUGIN_URL',              plugin_dir_url(__FILE__));
define('MPS_TOOLS_PLUGIN_ASSETS_URL',        plugin_dir_url(__FILE__) . 'assets/');

$files_wordpress_modifier = [
    'shortcodes.php',
    'cron.php',
    'events.php',
    'users.php',
    'rest-notification-helloasso.php',
    'couleurs.php',
    'connexion.php',
    'mailing.php',
];

$files_routes_controllers = [
    'connexion.php',
    'conseils.php',
    'events.php',
    'favoris.php',
    'media.php',
    'notes.php',
    'payement.php',
    'places.php',
    'source.php',
    'subscribe.php',
    'users.php',
];

$file_functions = [
    'route_callback.php',
    'mailing.php',
    'render_field_parameters.php',
    'sanitize_field_parameters.php',
    'shortcodes.php',
    'payement.php',
];

$files_database = [
    'table_creation.php',
    'table_migration.php',
];

//--------------------------------------------------------------------------------------------------
// Import des fichiers contenant les fonctions nécessaires

foreach ($file_functions as $file) {
    $file_path = MPS_TOOLS_FUNCTIONS_DIR . $file;
    if (file_exists($file_path)) {
        require_once $file_path;
    } else {
        error_log("MPS Tools : fichier de fonction manquant : {$file_path}");
    }
}

//--------------------------------------------------------------------------------------------------
// Import des fichiers de Base de Donnée (création + migration)

foreach ($files_database as $file) {
    $file_path = MPS_TOOLS_BDD_DIR . $file;
    if (file_exists($file_path)) {
        require_once $file_path;
    } else {
        error_log("MPS Tools : fichier de base de donnée manquant : {$file_path}");
    }
}

//--------------------------------------------------------------------------------------------------
// Import des fichiers de paramètres

require_once MPS_TOOLS_PARAMETERS_DIR . 'admin-settings.php';

//--------------------------------------------------------------------------------------------------
// Import des fichiers WordPress Modifier

foreach ($files_wordpress_modifier as $file) {
    $file_path = MPS_TOOLS_WORDPRESS_MODIFIER_DIR . $file;
    if (file_exists($file_path)) {
        require_once $file_path;
    } else {
        error_log("MPS Tools : fichier de WordPress Modifier manquant : {$file_path}");
    }
}

//--------------------------------------------------------------------------------------------------
// Import des fichiers des controllers (fonctions appelés par les routes)

foreach ($files_routes_controllers as $file) {
    $file_path = MPS_TOOLS_CONTROLLERS_DIR . $file;
    if (file_exists($file_path)) {
        require_once $file_path;
    } else {
        error_log("MPS Tools : fichier de controller manquant : {$file_path}");
    }
}

//--------------------------------------------------------------------------------------------------
// Import des fichiers des routes (définition des endpoints)

foreach ($files_routes_controllers as $file) {
    $file_path = MPS_TOOLS_ROUTES_DIR . $file;
    if (file_exists($file_path)) {
        require_once $file_path;
    } else {
        error_log("MPS Tools : fichier de route manquant : {$file_path}");
    }
}

//------------------------------------------------------------------------------
// End of File
//------------------------------------------------------------------------------