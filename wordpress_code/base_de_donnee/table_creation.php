<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: table_creation.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-19
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;

//--------------------------------------------------------------------------------------------------
// Constants

$tables = [
    'event',
    'conseils',
    'favoris',
    'inscription',
    'medias',
    'notes',
    'places',
    'source',
    'subcategories',
    'users_inscrits'
];

foreach ($tables as $table) {
    $file = MPS_TOOLS_BDD_DIR . "tables/{$table}.php";
    if (file_exists($file)) {
        require_once $file;
    } else {
        error_log("MPS Tools : fichier de table manquant pour '{$table}' : {$file}");
    }
}

//--------------------------------------------------------------------------------------------------
// Functions

function mps_tools_creer_tables($tables) {
    $BASE_NAME = "mps_tools_create_";
    $SUFFIX = "_table";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    foreach ($tables as $table) {
        $function_create_table_name = $BASE_NAME . $table . $SUFFIX;
        if (function_exists($function_create_table_name)) {
            dbDelta($function_create_table_name());
        }
    }
}

//--------------------------------------------------------------------------------------------------

register_activation_hook(MPS_TOOLS_MAIN_FILE, function() use ($tables) {
    mps_tools_creer_tables($tables);
});

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------