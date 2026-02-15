<?php
// Sécurité
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Stocke les modules demandés sur la page
 */
function vue_register_requested_module($module) {
    global $vue_requested_modules;

    if (!is_array($vue_requested_modules)) {
        $vue_requested_modules = [];
    }

    if (!in_array($module, $vue_requested_modules, true)) {
        $vue_requested_modules[] = $module;
    }
}

/**
 * Shortcode générique
 */
function vue_shortcode($atts, $content = null, $tag = '') {
    vue_register_requested_module($tag);

    return '<div class="vue-root" data-module="' . esc_attr($tag) . '"></div>';
}

/**
 * Enregistrement des shortcodes
 */
add_action('init', function () {
    $shortcodes = ['login', 'calendar', 'compte'];

    foreach ($shortcodes as $sc) {
        add_shortcode($sc, 'vue_shortcode');
    }
});

/**
 * Enqueue CSS / JS + données Vue
 */
add_action('wp_enqueue_scripts', function () {
    global $vue_requested_modules;

    if (empty($vue_requested_modules)) {
        return; // Aucun shortcode sur la page
    }

    $plugin_url  = plugin_dir_url(dirname(__FILE__));
    $plugin_path = plugin_dir_path(dirname(__FILE__));

    $css_file = 'dist/app.css';
    $js_file  = 'dist/app.js'; // ton bundle Vite / Vue

    $deps = [];

    if (wp_script_is('elementor-frontend', 'registered')) {
        $deps[] = 'elementor-frontend';
    }

    // CSS
    if (file_exists($plugin_path . $css_file)) {
        wp_enqueue_style(
            'vue-modules-css',
            $plugin_url . $css_file,
            [],
            filemtime($plugin_path . $css_file)
        );
    }

    // JS
    if (file_exists($plugin_path . $js_file)) {
        wp_enqueue_script(
            'vue-modules-js',
            $plugin_url . $js_file,
            $deps,
            filemtime($plugin_path . $js_file),
            true
        );

        // wp_script_add_data('vue-modules-js', 'type', 'module');

        wp_localize_script('vue-modules-js', 'vueAppData', [
            'nonce'   => wp_create_nonce('wp_rest'),
            'modules' => array_values($vue_requested_modules),
            'restUrl' => esc_url_raw(rest_url()),
        ]);
    }
});



// /**
//  * Génère automatiquement les shortcodes Vue depuis le manifest Vite
//  */
// function vue_shortcode($atts, $content, $tag) {
//     $plugin_url  = plugin_dir_url(dirname(__FILE__));
//     $plugin_path = plugin_dir_path(dirname(__FILE__));

//     $deps = [];
//     if (wp_script_is('elementor-frontend', 'registered')) {
//         $deps[] = 'elementor-frontend';
//     }

//     // Path vers le manifest
//     $manifest_path = $plugin_path . 'manifest.json';

//     if (!file_exists($manifest_path)) {
//         return '<!-- Vue manifest not found -->';
//     }

//     $manifest = json_decode(file_get_contents($manifest_path), true);

//     if (!$manifest || !is_array($manifest)) {
//         return '<!-- Vue manifest invalid -->';
//     }

//     // Trouver l'entrée principale (src/main.js)
//     $main = $manifest['src/main.js'] ?? null;
//     if (!$main) {
//         return '<!-- Vue main entry not found in manifest -->';
//     }

//     // Enqueue le CSS global (une seule fois)
//     if (!wp_style_is('vue-plugin-style', 'enqueued')) {
//         $css_file = $main['css'][0] ?? null;
//         if ($css_file && file_exists($plugin_path . $css_file)) {
//             wp_enqueue_style(
//                 'vue-plugin-style',
//                 $plugin_url . $css_file,
//                 [],
//                 filemtime($plugin_path . $css_file)
//             );
//         }
//     }

//     // Enqueue le JS principal (une seule fois)
//     if (!wp_script_is('vue-plugin-main', 'enqueued')) {
//         $js_file = $main['file'] ?? null;
//         if ($js_file && file_exists($plugin_path . $js_file)) {
//             wp_enqueue_script(
//                 'vue-plugin-main',
//                 $plugin_url . $js_file,
//                 $deps,
//                 filemtime($plugin_path . $js_file),
//                 true // In footer
//             );
//         }
//     }
    
//     // IMPORTANT : Toujours générer vueAppData inline APRÈS l'enqueue
//     // (WordPress l'ajoutera automatiquement AVANT le script)
//     if (wp_script_is('vue-plugin-main', 'enqueued') || wp_script_is('vue-plugin-main', 'done')) {
//         vue_generate_vue_app_data_inline();
//     }

//     // Retourne un conteneur avec le nom du composant
//     return '<div class="vue-app" data-component="' . esc_attr($tag) . '"></div>';
// }

// /**
//  * Génère window.vueAppData et l'injecte en inline AVANT main.js
//  */
// function vue_generate_vue_app_data_inline() {
//     // Éviter les doublons
//     static $already_generated = false;
//     if ($already_generated) {
//         return;
//     }
//     $already_generated = true;
    
//     $plugin_url  = plugin_dir_url(dirname(__FILE__));
//     $plugin_path = plugin_dir_path(dirname(__FILE__));
//     $manifest_path = $plugin_path . 'manifest.json';

//     if (!file_exists($manifest_path)) {
//         return;
//     }

//     $manifest = json_decode(file_get_contents($manifest_path), true);
//     if (!$manifest) {
//         return;
//     }

//     $components = [];

//     // Parcourir le manifest pour trouver tous les composants
//     foreach ($manifest as $key => $data) {
//         // On cherche les fichiers dans src/components/*.vue
//         if (strpos($key, 'src/components/') === 0 && isset($data['file'])) {
//             // Extraire le nom du composant (sans .vue)
//             $basename = basename($key, '.vue');
//             // Convertir en kebab-case pour correspondre au shortcode
//             $componentName = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $basename));
            
//             $components[$componentName] = [
//                 'file' => $data['file'],
//                 'css' => $data['css'] ?? []
//             ];
//         }
//     }

//     // Créer le script inline
//     $script = 'window.vueAppData = ' . wp_json_encode([
//         'pluginUrl' => $plugin_url,
//         'components' => $components,
//         'nonce' => wp_create_nonce('wp_rest'),
//     ], JSON_UNESCAPED_SLASHES) . ';';

//     // Injecter AVANT main.js
//     wp_add_inline_script('vue-plugin-main', $script, 'before');
// }

// /**
//  * Force le type="module" sur le script principal
//  */
// add_filter('script_loader_tag', function($tag, $handle, $src) {
//     if ($handle === 'vue-plugin-main') {
//         return '<script type="module" src="' . esc_url($src) . '" id="vue-plugin-main-js"></script>' . "\n";
//     }
//     return $tag;
// }, 10, 3);

// /**
//  * Enregistre automatiquement tous les shortcodes depuis le manifest
//  */
// function register_vue_shortcodes_from_manifest() {
//     $manifest_path = plugin_dir_path(__DIR__) . 'manifest.json';

//     if (!file_exists($manifest_path)) {
//         return;
//     }

//     $manifest = json_decode(file_get_contents($manifest_path), true);

//     if (!$manifest || !is_array($manifest)) {
//         return;
//     }

//     // Parcourir le manifest pour enregistrer les shortcodes
//     foreach ($manifest as $file => $data) {
//         // On cherche tous les fichiers .vue dans src/components/
//         if (strpos($file, 'src/components/') === 0 && substr($file, -4) === '.vue') {
//             // Extraire le nom du composant
//             $basename = basename($file, '.vue');
            
//             // Convertir PascalCase en kebab-case
//             $shortcode = strtolower(preg_replace('/(?<!^)[A-Z]/', '-$0', $basename));
            
//             // Enregistrer le shortcode
//             if (!shortcode_exists($shortcode)) {
//                 add_shortcode($shortcode, 'vue_shortcode');
//             }
//         }
//     }
// }

// add_action('init', 'register_vue_shortcodes_from_manifest');

// /**
//  * Hook supplémentaire pour garantir que vueAppData est toujours généré
//  */
// add_action('wp_footer', function() {
//     // Si le script est enqueued mais que vueAppData n'a pas été généré
//     if (wp_script_is('vue-plugin-main', 'enqueued')) {
//         vue_generate_vue_app_data_inline();
//     }
// }, 9); // Priorité 9 pour s'exécuter avant le footer standard (10)
