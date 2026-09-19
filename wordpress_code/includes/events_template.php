<?php
defined('ABSPATH') || exit;

add_filter('template_include', 'mps_tools_event_template');

function mps_tools_event_template($template) {
    if (is_singular('event')) {
        return plugin_dir_path(__DIR__) . 'templates/single-event.php';
    }
    return $template;
}