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
require_once plugin_dir_path(__DIR__) . $shortcodesHookersPath;

//------------------------------------------------------------------------------
/**
 * Instanciation de la classe ShortcodesHookers
 */
$shortcodesHookers = new ShortcodesHookers(get_vue_shortcodes());

//------------------------------------------------------------------------------
/**
 * Enregistrement des shortcodes
 */

add_action('init', function () use ($shortcodesHookers) {
    $shortcodesHookers->register_shortcodes();
});

//------------------------------------------------------------------------------
/**
 * Enqueue CSS / JS + données Vue
 */

add_action('wp_enqueue_scripts', function () use ($shortcodesHookers) {
    $shortcodesHookers->enqueue_vue_scripts();
});