<?php
defined('ABSPATH') || exit;

/**
 * Désactive Elementor uniquement sur les pages Vue full app
 */
add_action('wp', function () {

    // Pages Vue full (pas de shortcode)
    if (!is_singular('event')) {
        return;
    }

    // Elementor core
    wp_dequeue_script('elementor-frontend');
    wp_dequeue_style('elementor-frontend');

    // Elementor addons (BDThemes)
    wp_dequeue_script('bdt-element-pack-site');
    wp_dequeue_script('bdt-uikit');
    wp_dequeue_style('bdt-uikit');

}, 20);