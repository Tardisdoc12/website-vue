<?php
/**
 * Plugin Name: Vue App
 * Description: Intègre une application Vue dans WordPress via des shortcodes [login], [calendar], [events].
 * Version: 1.0
 * Author: Toi
 */

if (!defined('ABSPATH')) exit;

/**
 * Shortcode [login]
 */
function vue_login_shortcode() {
    $plugin_url = plugin_dir_url(__FILE__);

    // CSS spécifique (si généré)
    if (file_exists(plugin_dir_path(__FILE__) . 'login.css')) {
        wp_enqueue_style('vue-login-css', $plugin_url . 'login.css');
    }

    // JS
    wp_enqueue_script('vue-login-js', $plugin_url . 'login.js', [], null, true);
    wp_script_add_data('vue-login-js', 'type', 'module');

    return '<div id="login-app"></div>';
}
add_shortcode('login', 'vue_login_shortcode');

/**
 * Shortcode [calendar]
 */
function vue_calendar_shortcode() {
    $plugin_url = plugin_dir_url(__FILE__);

    if (file_exists(plugin_dir_path(__FILE__) . 'calendar.css')) {
        wp_enqueue_style('vue-calendar-css', $plugin_url . 'calendar.css');
    }

    wp_enqueue_script('vue-calendar-js', $plugin_url . 'calendar.js', [], null, true);
    wp_script_add_data('vue-calendar-js', 'type', 'module');

    return '<div id="calendar"></div>';
}
add_shortcode('calendar', 'vue_calendar_shortcode');

/**
 * Shortcode [events]
 */
function vue_events_shortcode() {
    $plugin_url = plugin_dir_url(__FILE__);

    if (file_exists(plugin_dir_path(__FILE__) . 'events.css')) {
        wp_enqueue_style('vue-events-css', $plugin_url . 'events.css');
    }

    wp_enqueue_script('vue-events-js', $plugin_url . 'events.js', [], null, true);
    wp_script_add_data('vue-events-js', 'type', 'module');

    return '<div id="events-form"></div>';
}
add_shortcode('events', 'vue_events_shortcode');

/**
 * Shortcode [connexion]
 */
function vue_connexion_shortcode() {
    $plugin_url = plugin_dir_url(__FILE__);

    if (file_exists(plugin_dir_path(__FILE__) . 'connexion.css')) {
        wp_enqueue_style('vue-connexion-css', $plugin_url . 'connexion.css');
    }

    wp_enqueue_script('vue-connexion-js', $plugin_url . 'connexion.js', [], null, true);
    wp_script_add_data('vue-connexion-js', 'type', 'module');

    return '<div id="connexion-form"></div>';
}
add_shortcode('connexion', 'vue_connexion_shortcode');