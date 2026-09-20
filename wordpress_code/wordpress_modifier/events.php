<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: events_template.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-19
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;

//--------------------------------------------------------------------------------------------------

add_filter('template_include', 'mps_tools_event_template');
add_action('init', 'mps_tools_register_event_cpt');

//--------------------------------------------------------------------------------------------------

function mps_tools_event_template($template) {
    if (is_singular('event')) {
        return plugin_dir_path(__DIR__) . 'templates/single-event.php';
    }
    return $template;
}

//--------------------------------------------------------------------------------------------------

function mps_tools_register_event_cpt() {
    register_post_type('event', [
        'labels' => [
            'name' => 'Événements',
            'singular_name' => 'Événement'
        ],
        'public' => true,
        'rewrite' => ['slug' => 'evenement'],
        'supports' => ['title', 'editor'],
        'show_in_rest' => true
    ]);
}

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------