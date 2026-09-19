<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: table_migration.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-19
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;

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
    'users_inscrits',
    'billetteries'
];

foreach ($tables as $table) {
    $file = MPS_TOOLS_BDD_DIR . "migrations/{$table}.php";
    if (file_exists($file)) {
        require_once $file;
    } else {
        error_log("MPS Tools : fichier de migration manquant pour '{$table}' : {$file}");
    }
}

//--------------------------------------------------------------------------------------------------
// Functions OR CLASS

function mps_tools_migration_all_tables($tables) {
    $MIGRATION_FUNCTION_BASENAME = "mps_tools_migration_";
    $SUFFIX = "_table";

    foreach ($tables as $table) {
        $function_name = $MIGRATION_FUNCTION_BASENAME . $table . $SUFFIX;
        if (function_exists($function_name)) {
            $function_name();
        }
    }

    update_option('mps_tools_last_migration', time());
}

register_activation_hook(MPS_TOOLS_MAIN_FILE, function() use ($tables) {
    mps_tools_migration_all_tables($tables);
});

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------