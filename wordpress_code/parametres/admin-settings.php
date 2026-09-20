<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: admin-settings.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-19
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;

require_once MPS_TOOLS_FUNCTIONS_DIR .'render_field_parameters.php';
require_once MPS_TOOLS_FUNCTIONS_DIR .'sanitize_field_parameters.php';

//--------------------------------------------------------------------------------------------------
// ============================================
// 1. CHARGEMENT DES ONGLETS (ordre explicite)
// ============================================
function mps_tools_get_settings_fields() {
    static $tabs = null;
    if ($tabs !== null) return $tabs;

    $order = [
        'general',
        'theme',
        'events',
        'kdrive',
        'helloasso',
        'mailing',
        'documentation'
    ];
    $tabs = [];

    foreach ($order as $tab_key) {
        $file = plugin_dir_path(__FILE__) . "settings-tabs/{$tab_key}.php";
        if (file_exists($file)) {
            $tabs[$tab_key] = require $file;
        }
    }

    return $tabs;
}

// ============================================
// 2. ENREGISTREMENT DES SETTINGS
// ============================================
add_action('admin_init', function() {
    foreach (mps_tools_get_settings_fields() as $tab_key => $tab) {
        $group = 'mps_tools_options_' . $tab_key;

        foreach ($tab['fields'] as $key => $field) {
            $args = []; // ✅ reset à chaque champ, avant toute utilisation

            if (in_array($field['type'] ?? '', ['readonly_url', 'heading', 'paragraph', 'html_text'])) continue;

            $function_name = 'mps_tools_sanitize_' . $field['type'];
            if (function_exists($function_name)) {
                $args = $function_name($field, $key);
            }

            register_setting($group, $key, $args);
        }
    }
});

// ============================================
// 3. MENU ADMIN
// ============================================
add_action('admin_menu', function() {
    add_options_page(
        'Plugin MPS Tools – Paramètres',
        'Plugin MPS Tools',
        'manage_options',
        'mps-tools-settings',
        'mps_tools_render_settings_page'
    );
});

// ============================================
// 4. RENDU D'UN CHAMP
// ============================================
function mps_tools_render_field($key, $field) {
    $type = $field['type'];

    $function_name = 'mps_tools_render_' . $type;

    if (function_exists($function_name)) {
        $function_name($key, $field);
        return;
    }
    
    mps_tools_render_default($key, $field);
}

// ============================================
// 5. RENDU DE LA PAGE
// ============================================

function mps_tools_render_settings_page() {
    $tabs = mps_tools_get_settings_fields();
    $current_tab = $_GET['tab'] ?? array_key_first($tabs);

    if (!isset($tabs[$current_tab])) {
        $current_tab = array_key_first($tabs);
    }

    $current_group = 'mps_tools_options_' . $current_tab;
    ?>
    <div class="wrap">
        <h1>Paramètres MPS Tools</h1>

        <h2 class="nav-tab-wrapper">
            <?php foreach ($tabs as $tab_key => $tab): ?>
                <a href="?page=mps-tools-settings&tab=<?php echo esc_attr($tab_key); ?>"
                   class="nav-tab <?php echo $current_tab === $tab_key ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html($tab['label']); ?>
                </a>
            <?php endforeach; ?>
        </h2>

        <form method="post" action="options.php">
            <?php settings_fields($current_group); ?>
            <table class="form-table">
                <?php foreach ($tabs[$current_tab]['fields'] as $key => $field): ?>
                    <?php mps_tools_render_field($key, $field); ?>
                <?php endforeach; ?>
            </table>
            <?php if ($current_tab !== 'documentation') submit_button(); ?>
        </form>
    </div>

    <style>
    .mps-tools-repeater {
        width: auto;
        max-width: 1900px;
        border-collapse: collapse;
    }
    .mps-tools-repeater th,
    .mps-tools-repeater td {
        padding: 8px 12px;
        text-align: left;
    }
    .mps-tools-repeater input[type="text"] {
        width: 200px;
        box-sizing: border-box;
    }
    .mps-tools-repeater input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
    .mps-tools-global {
        /* styles communs à tous les champs globaux, si besoin */
    }
    .mps-tools-global[type="color"] {
        width: 60px;
        height: 34px;
        padding: 0;
        border: none;
        cursor: pointer;
    }

    .mps-tools-category-card {
        border-bottom: 1px solid #ccc;
        padding: 16px 0;
    }

    .mps-tools-category-card:first-child {
        padding-top: 0;
    }

    .mps-tools-category-header {
        text-align: right;
        margin-bottom: 8px;
    }

    .mps-tools-category-field {
        margin-bottom: 12px;
    }

    .mps-tools-category-field label {
        display: block;
        font-weight: 600;
        margin-bottom: 4px;
    }

    .mps-tools-sub-repeater {
        max-width: 600px;
        margin-bottom: 8px;
    }
    </style>

    <script>
    document.addEventListener('click', function(e) {
        // Ajouter une catégorie
        if (e.target.classList.contains('mps-tools-add-category')) {
            const key = e.target.dataset.key;
            const container = document.querySelector(`.mps-tools-categories[data-key="${key}"] .mps-tools-categories-list`);
            const template = document.getElementById(`tpl-${key}-category`);

            const newIndex = container.children.length;
            const html = template.innerHTML.replaceAll('__CAT_INDEX__', newIndex);

            const wrapper = document.createElement('div');
            wrapper.innerHTML = html;
            container.appendChild(wrapper.firstElementChild);
        }

        // Ajouter un champ spécial (dans la catégorie correspondante)
        if (e.target.classList.contains('mps-tools-add-special-field')) {
            const card = e.target.closest('.mps-tools-category-card');
            const table = card.querySelector('.mps-tools-sub-repeater tbody');
            const template = card.querySelector('template.tpl-special-field');

            const newIndex = table.children.length;
            const html = template.innerHTML.replaceAll('__SUB_INDEX__', newIndex);

            const wrapper = document.createElement('tbody');
            wrapper.innerHTML = html;
            table.appendChild(wrapper.firstElementChild);
        }

        // Supprimer une catégorie entière
        if (e.target.classList.contains('mps-tools-remove-category')) {
            e.target.closest('.mps-tools-category-card').remove();
        }

        // Supprimer une ligne de champ spécial (ou un repeater générique classique)
        if (e.target.classList.contains('mps-tools-remove-row')) {
            e.target.closest('tr').remove();
        }

        // Ajouter une ligne (repeater générique classique existant)
        if (e.target.classList.contains('mps-tools-add-row')) {
            const key = e.target.dataset.key;
            const table = document.querySelector(`.mps-tools-repeater[data-key="${key}"] tbody`);
            const template = document.getElementById(`tpl-${key}`);

            const newIndex = table.children.length;
            const html = template.innerHTML.replaceAll('__INDEX__', newIndex);

            const wrapper = document.createElement('tbody');
            wrapper.innerHTML = html;
            table.appendChild(wrapper.firstElementChild);
        }
    });
    </script>
    <?php
}