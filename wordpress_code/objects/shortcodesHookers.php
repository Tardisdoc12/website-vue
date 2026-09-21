<?php
/**
 * Shortcodes Hookers
 * @package WordPress
 * But : définir une classe pour gérer les shortcodes et les fonctions associées
 * @author : Jean Anquetil
 * @version 1.0
 */

defined('ABSPATH') || exit;

class ShortcodesHookers {
    // propiétés
    private $shortcodes_name;
    private $manifest;
    private $vue_requested_modules;

    // public functions
    public function __construct($shortcodes_name) {
        $this->shortcodes_name = $shortcodes_name;

        $this->get_manifest();

        add_action('init', function(){
            $this->register_shortcodes();
        });
        add_action('wp_enqueue_scripts', function(){
            $this->enqueue_vue_scripts();
        });
        add_action('wp', function () {
            if (is_singular('event')) {
                $this->vue_register_requested_module('event-page');
            }
        });
    }

    public function register_shortcodes() {
        foreach ($this->shortcodes_name as $shortcode => $component) {
            add_shortcode($shortcode, function($atts, $content, $tag){
                return $this->render_vue_component($atts, $content, $tag);
            });
        }
    }

    public function enqueue_vue_scripts() {
        if (empty($this->manifest)) {
            return;
        }

        if (empty($this->vue_requested_modules)) {
            return;
        }

        $module_name = 'vue-app-';

        $plugin_url  = plugin_dir_url(__DIR__);
        $plugin_path = plugin_dir_path(__DIR__);

        $css_file = "dist/" . $this->manifest['style.css']['file'] ?? null;
        $js_file  = "dist/" . $this->manifest['src/main.js']['file'] ?? null;

        if ($css_file && file_exists($plugin_path . $css_file)) {
            $this->enqueue_css_scripts($module_name . 'css', $plugin_path . $css_file, $plugin_url . $css_file);
        }

        if ($js_file && file_exists($plugin_path . $js_file)) {
            $this->enqueue_js_scripts($module_name . 'js', $plugin_path . $js_file, $plugin_url . $js_file);
        }
    }

    // privates functions
    private function enqueue_css_scripts($name_modules, $path_file, $url_file) {
        wp_enqueue_style(
            $name_modules,
            $url_file,
            array(),
            filemtime($path_file)
        );

        $custom_css = ":root {
            --main-color: " . esc_attr(get_option('main_color', '#245473')) . ";
            --secondary-color: " . esc_attr(get_option('secondary_color', '#2d5c7f')) . ";
            --validate-color: " . esc_attr(get_option('validate_color', '#2e7d32')) . ";
            --validate-hover-color: " . esc_attr(get_option('validate_hover_color', '#1b5e20')) . ";
            --cancel-color: " . esc_attr(get_option('cancel_color', '#c62828')) . ";
            --cancel-hover-color: " . esc_attr(get_option('cancel_hover_color', '#8e0000')) . ";
            --disable-main-color: " . esc_attr(get_option('disable_main_color', '#9e9e9e')) . ";
            --writing-main-color: " . esc_attr(get_option('writing_main_color', '#FFFFFF')) . ";
            --depliant-background-color: " . esc_attr(get_option('depliant_background_color', '#d4e3ed')) . ";
            --deactivate-button-classic-color: " . esc_attr(get_option('deactivate_button_classic_color', '#d4e3ed')) . ";
            --color-button-file: " . esc_attr(get_option('color_button_file', '#000000')) . ";
            --cancel-disabled-color: " . esc_attr(get_option('cancel_disabled_color', '#9ca3af')) . ";
            --validate-disabled-color: " . esc_attr(get_option('validate_disabled_color', '#9ca3af')) . ";
            --modale-header-background-color: " . esc_attr(get_option('modal_header_background_color', '#ffffff')) . ";
            --modale-body-background-color: " . esc_attr(get_option('modal_body_background_color', '#ffffff')) . ";
            --modale-overlay-color: " . esc_attr(get_option('modal_overlay_color', '#4b4c4ed6')) . ";
            --writing-modale-title-color: " . esc_attr(get_option('writing_modale_title_color', '#FFFFFF')) . ";
            --writing-modale-body-color: " . esc_attr(get_option('writing_modale_body_color', '#000000')) . ";
            --writing-secondary-color: " . esc_attr(get_option('writing_secondary_color', '#000000')) . ";
        }";

        wp_add_inline_style($name_modules, $custom_css);
    }

    private function enqueue_js_scripts($name_modules, $path_file, $url_file) {
         $dependencies = [];

        // On n'ajoute la dépendance que si elle existe réellement
        if (wp_script_is('elementor-frontend', 'registered')) {
            $dependencies[] = 'elementor-frontend';
        }

        wp_enqueue_script(
            $name_modules,
            $url_file,
            $dependencies,
            filemtime($path_file),
            true
        );

        wp_localize_script($name_modules, 'vueAppData', [
            'nonce'   => wp_create_nonce('wp_rest'),
            'modules' => array_values($this->vue_requested_modules),
            'restUrl' => esc_url_raw(rest_url()),
        ]);

        wp_localize_script(
            $name_modules,
            'VUE_SHORTCODES',
            $this->shortcodes_name
        );

        // on vérifie les configurations des fonctionnalitées:
        $isKDriveConfigured = 0;
        $kdrive_id = get_option('mps_tools_kdrive_id', '');
        $kdrive_dir_id = get_option('mps_tools_kdrive_directory_id', '');
        $kdrive_token = get_option('mps_tools_kdrive_token', '');
        if (!empty($kdrive_id) && !empty($kdrive_dir_id) && !empty($kdrive_token)) {
            $isKDriveConfigured = 1;
        }

        $isHelloAssoConfigured = 0;
        $hello_asso_id = get_option('helloasso_client_id', '');
        $hello_asso_client_secret = get_option('helloasso_client_secret', '');
        $hello_asso_return_url = get_option('helloasso_return_url', '');
        if (!empty($hello_asso_id) && !empty($hello_asso_client_secret) && !empty($hello_asso_return_url)) {
            $isHelloAssoConfigured = 1;
        }

        $isCashAllowed = get_option('accept_cash', 0);

        wp_localize_script($name_modules, 'MPS_TOOLS_SETTINGS', [
            'categories'         => get_option('mps_tools_categories', []),
            'isKdriveConfigured' => $isKDriveConfigured,
            'isHelloAssoConfigured' => $isHelloAssoConfigured,
            'isCashAllowed' => $isCashAllowed,
        ]);

        wp_localize_script($name_modules, 'MyPluginData', [
            'rest_url' => esc_url_raw(rest_url()),
            'site_url' => esc_url_raw(get_site_url()),
        ]);
    }

    private function get_manifest() {
        $plugin_path = plugin_dir_path(__DIR__);
        $manifest_path = $plugin_path . 'manifest.json';
        if (!file_exists($manifest_path)) {
            return '<!-- Vue manifest not found -->';
        }

        $this->manifest = json_decode(file_get_contents($manifest_path), true);
    }

    private function render_vue_component($atts, $content, $tag) {

        $this->vue_register_requested_module($tag);

        static $scripts_enqueued = false;

        if (!$scripts_enqueued) {
            add_action('wp_footer', function () {
                $this->enqueue_vue_scripts();
            }, 1);
            $scripts_enqueued = true;
        }

        return '<div class="vue-root" data-module="' . esc_attr($tag) . '"></div>';
    }

    // private function render_vue_component($atts, $content, $tag) {
    //     $this->vue_register_requested_module($tag);
    //     return '<div class="vue-root" data-module="' . esc_attr($tag) . '"></div>';
    // }

    private function vue_register_requested_module($module) {

        if (!is_array($this->vue_requested_modules)) {
            $this->vue_requested_modules = array();
        }

        if (!in_array($module, $this->vue_requested_modules, true)) {
            $this->vue_requested_modules[] = $module;
        }
    }
}