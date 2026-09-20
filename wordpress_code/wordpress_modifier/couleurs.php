<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: couleurs.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-20
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;


//--------------------------------------------------------------------------------------------------
// Functions OR CLASS

add_action('admin_enqueue_scripts', 'mps_tools_enqueue_color_picker');
function mps_tools_enqueue_color_picker($hook) {
    wp_enqueue_style('wp-color-picker');
    wp_enqueue_script('wp-color-picker');

    wp_enqueue_script(
        'wp-color-picker-alpha',
        plugin_dir_url(MPS_TOOLS_MAIN_FILE) . 'javascript/wp-color-picker-alpha.min.js',
        array('wp-color-picker'),
        '3.0.0',
        true
    );

    wp_enqueue_script(
        'mps-tools-color-picker-init',
        plugin_dir_url(MPS_TOOLS_MAIN_FILE) . 'javascript/couleurs.js',
        array('wp-color-picker', 'wp-color-picker-alpha', 'jquery'),
        '1.0.0',
        true
    );
}

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------