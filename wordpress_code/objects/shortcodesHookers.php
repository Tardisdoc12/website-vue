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
    }

    public function register_shortcodes() {
        foreach ($this->shortcodes_name as $shortcode => $component) {
            add_shortcode($shortcode, array($this, 'render_vue_component'));
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
            $this->enqueue_css_scripts($module_name . 'css', $plugin_path . $css_file);
        }

        if ($js_file && file_exists($plugin_path . $js_file)) {
            $this->enqueue_js_scripts($module_name . 'js', $plugin_path . $js_file);
        }
    }

    // privates functions
    private function enqueue_css_scripts($name_modules, $path_file) {
        wp_enqueue_style(
            $name_modules,
            $path_file,
            array(),
            filemtime($path_file)
        );
    }

    private function enqueue_js_scripts($name_modules, $path_file) {
        wp_enqueue_script(
            $name_modules,
            $path_file,
            array(),
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
    }

    private function get_manifest() {
        $plugin_path = plugin_dir_path(__DIR__);
        $manifest_path = $plugin_path . 'manifest.json';
        if (!file_exists($manifest_path)) {
            error_log('Vue manifest not found at: ' . $manifest_path);
            return '<!-- Vue manifest not found -->';
        }

        $this->manifest = json_decode(file_get_contents($manifest_path), true);
    }

    private function render_vue_component($atts, $content = null, $tag = '') {
        $this->vue_register_requested_module($tag);
        return '<div class="vue-root" data-module="' . esc_attr($tag) . '"></div>';
    }

    private function vue_register_requested_module($module) {

        if (!is_array($this->vue_requested_modules)) {
            $this->vue_requested_modules = array();
        }

        if (!in_array($module, $this->vue_requested_modules, true)) {
            $this->vue_requested_modules[] = $module;
        }
    }
}