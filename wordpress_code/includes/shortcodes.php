<?php
// Sécurité
if (!defined('ABSPATH')) {
    exit;
}
//------------------------------------------------------------------------------
// IMPORTS

$file = "functions.php";
require_once plugin_dir_path(__FILE__) . $file;
$shortcodesHookersPath = "objects/shortcodesHookers.php";
require_once plugin_dir_path(dirname(__FILE__)) . $shortcodesHookersPath;
//------------------------------------------------------------------------------
/**
 * Instanciation de la classe ShortcodesHookers
 */
$shortcodesHookers = new ShortcodesHookers(get_vue_shortcodes());

//------------------------------------------------------------------------------
// End of File
//------------------------------------------------------------------------------