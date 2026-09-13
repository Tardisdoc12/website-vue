<?php
/**
 * Page de paramètres du plugin
 */
if (!defined('ABSPATH')) exit;

// ============================================
// 1. CHARGEMENT DES ONGLETS (ordre explicite)
// ============================================
function mon_plugin_get_settings_fields() {
    static $tabs = null;
    if ($tabs !== null) return $tabs;

    $order = ['general', 'events', 'kdrive', 'helloasso', 'mailing'];
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
    foreach (mon_plugin_get_settings_fields() as $tab_key => $tab) {
        $group = 'mon_plugin_options_' . $tab_key; // groupe unique par onglet

        foreach ($tab['fields'] as $key => $field) {
            if (($field['type'] ?? '') === 'readonly_url') continue;

            $args = [];
            if ($field['type'] === 'repeater') {
                $args['sanitize_callback'] = function($value) use ($field) {
                    return mon_plugin_sanitize_repeater($value, $field['columns']);
                };
            }

            register_setting($group, $key, $args);
        }
    }
});

function mon_plugin_sanitize_repeater($value, $columns) {
    if (!is_array($value)) return [];

    $clean = [];
    foreach ($value as $row) {
        if (!is_array($row)) continue;

        $clean_row = [];
        $has_content = false;

        foreach ($columns as $col_key => $col) {
            $type = $col['type'] ?? 'text';
            $raw = $row[$col_key] ?? '';

            // Cas spécial : champs_speciaux (liste séparée par virgules -> tableau)
            if ($col_key === 'champs_speciaux') {
                $val = array_map('trim', explode(',', sanitize_text_field($raw)));
                $val = array_filter($val); // retire les entrées vides
                $val = array_values($val);
            } else {
                switch ($type) {
                    case 'checkbox':
                        $val = !empty($raw) ? 1 : 0;
                        break;
                    case 'color':
                        $val = sanitize_hex_color($raw) ?: '';
                        break;
                    default:
                        $val = sanitize_text_field($raw);
                }
            }

            $clean_row[$col_key] = $val;

            if ($col_key === 'nom' && $val !== '') $has_content = true;
        }

        if ($has_content) $clean[] = $clean_row;
    }

    return $clean;
}

// ============================================
// 3. MENU ADMIN
// ============================================
add_action('admin_menu', function() {
    add_options_page(
        'Plugin Vue – Paramètres',
        'Plugin Vue',
        'manage_options',
        'mon-plugin-settings',
        'mon_plugin_render_settings_page'
    );
});

// ============================================
// 4. RENDU D'UN CHAMP
// ============================================
function mon_plugin_render_field($key, $field) {
    $type = $field['type'];

    if ($type === 'repeater') {
        mon_plugin_render_repeater_field($key, $field);
        return;
    }

    $value = get_option($key, $field['default'] ?? '');
    ?>
    <tr>
        <th><label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?></label></th>
        <td>
            <?php if ($type === 'readonly_url'): ?>
                <input type="text" readonly
                       value="<?php echo esc_url($field['callback']()); ?>"
                       class="regular-text" />
            <?php else: ?>
                <input type="<?php echo esc_attr($type); ?>"
                       id="<?php echo esc_attr($key); ?>"
                       name="<?php echo esc_attr($key); ?>"
                       value="<?php echo esc_attr($value); ?>"
                       class="regular-text" />
            <?php endif; ?>
        </td>
    </tr>
    <?php
}

// ============================================
// 5. RENDU D'UN CHAMP "REPEATER" (liste dynamique)
// ============================================
function mon_plugin_render_repeater_field($key, $field) {
    $columns = $field['columns'];
    $rows = get_option($key, []);
    if (!is_array($rows)) $rows = [];
    ?>
    <tr>
        <th><?php echo esc_html($field['label']); ?></th>
        <td>
            <table class="widefat mon-plugin-repeater" data-key="<?php echo esc_attr($key); ?>">
                <thead>
                    <tr>
                        <?php foreach ($columns as $col): ?>
                            <th><?php echo esc_html($col['label']); ?></th>
                        <?php endforeach; ?>
                        <th style="width:40px;"></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $i => $row): ?>
                        <?php mon_plugin_render_repeater_row($key, $columns, $i, $row); ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="button" class="button mon-plugin-add-row" data-key="<?php echo esc_attr($key); ?>">
                + Ajouter une ligne
            </button>

            <!-- Template caché utilisé par le JS pour cloner une nouvelle ligne -->
            <template id="tpl-<?php echo esc_attr($key); ?>">
                <?php mon_plugin_render_repeater_row($key, $columns, '__INDEX__', []); ?>
            </template>
        </td>
    </tr>
    <?php
}

function mon_plugin_render_repeater_row($key, $columns, $index, $row) {
    ?>
    <tr>
        <?php foreach ($columns as $col_key => $col): ?>
            <td>
                <?php
                $input_type = $col['type'] ?? 'text';
                $name = esc_attr($key) . '[' . esc_attr($index) . '][' . esc_attr($col_key) . ']';
                ?>

                <?php if ($input_type === 'checkbox'): ?>
                    <?php $checked = !empty($row[$col_key]); ?>
                    <!-- hidden AVANT la checkbox : si décochée, c'est cette valeur "0" qui part -->
                    <input type="hidden" name="<?php echo $name; ?>" value="0" />
                    <input type="checkbox"
                           name="<?php echo $name; ?>"
                           value="1"
                           <?php checked($checked); ?> />

                <?php elseif ($input_type === 'color'): ?>
                    <?php $value = $row[$col_key] ?? '#000000'; ?>
                    <input type="color" name="<?php echo $name; ?>" value="<?php echo esc_attr($value); ?>" />

                <?php else: ?>
                    <?php $value = $row[$col_key] ?? ''; ?>
                    <input type="text" name="<?php echo $name; ?>" value="<?php echo esc_attr($value); ?>" class="regular-text" />
                <?php endif; ?>
            </td>
        <?php endforeach; ?>
        <td>
            <button type="button" class="button mon-plugin-remove-row">✕</button>
        </td>
    </tr>
    <?php
}

// ============================================
// 6. RENDU DE LA PAGE
// ============================================
function mon_plugin_render_settings_page() {
    $tabs = mon_plugin_get_settings_fields();
    $current_tab = $_GET['tab'] ?? array_key_first($tabs);

    if (!isset($tabs[$current_tab])) {
        $current_tab = array_key_first($tabs);
    }

    $current_group = 'mon_plugin_options_' . $current_tab;
    ?>
    <div class="wrap">
        <h1>Paramètres Plugin Vue</h1>

        <h2 class="nav-tab-wrapper">
            <?php foreach ($tabs as $tab_key => $tab): ?>
                <a href="?page=mon-plugin-settings&tab=<?php echo esc_attr($tab_key); ?>"
                   class="nav-tab <?php echo $current_tab === $tab_key ? 'nav-tab-active' : ''; ?>">
                    <?php echo esc_html($tab['label']); ?>
                </a>
            <?php endforeach; ?>
        </h2>

        <form method="post" action="options.php">
            <?php settings_fields($current_group); ?>
            <table class="form-table">
                <?php foreach ($tabs[$current_tab]['fields'] as $key => $field): ?>
                    <?php mon_plugin_render_field($key, $field); ?>
                <?php endforeach; ?>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>

    <style>
    .mon-plugin-repeater {
        width: auto;
        max-width: 800px;
        border-collapse: collapse;
    }
    .mon-plugin-repeater th,
    .mon-plugin-repeater td {
        padding: 8px 12px;
        text-align: left;
    }
    .mon-plugin-repeater input[type="text"] {
        width: 100%;
        box-sizing: border-box;
    }
    .mon-plugin-repeater input[type="checkbox"] {
        width: 18px;
        height: 18px;
        cursor: pointer;
    }
    </style>

    <script>
    document.addEventListener('click', function(e) {
        // Ajouter une ligne
        if (e.target.classList.contains('mon-plugin-add-row')) {
            const key = e.target.dataset.key;
            const table = document.querySelector(`.mon-plugin-repeater[data-key="${key}"] tbody`);
            const template = document.getElementById(`tpl-${key}`);

            const newIndex = table.children.length;
            const html = template.innerHTML.replaceAll('__INDEX__', newIndex);

            const wrapper = document.createElement('tbody');
            wrapper.innerHTML = html;
            table.appendChild(wrapper.firstElementChild);
        }

        // Supprimer une ligne
        if (e.target.classList.contains('mon-plugin-remove-row')) {
            e.target.closest('tr').remove();
        }
    });
    </script>
    <?php
}