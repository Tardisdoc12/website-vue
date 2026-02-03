<?php
defined('ABSPATH') || exit;

add_filter('template_include', 'monplugin_event_template');

function monplugin_event_template($template) {
    if (is_singular('event')) {
        return plugin_dir_path(__DIR__) . 'templates/page_event.php';
    }
    return $template;
}