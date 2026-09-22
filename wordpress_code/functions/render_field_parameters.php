<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: render_field_parameters.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-19
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;

//--------------------------------------------------------------------------------------------------

function mps_tools_render_checkbox($key, $field) {
    $value = get_option($key, $field['default'] ?? 0);
    $checked = !empty($value);
    ?>
    <tr>
        <th><label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?></label></th>
        <td>
            <input type="hidden" name="<?php echo esc_attr($key); ?>" value="0" />
            <input type="checkbox"
                   id="<?php echo esc_attr($key); ?>"
                   name="<?php echo esc_attr($key); ?>"
                   value="1"
                   <?php checked($checked); ?> />
            <?php if (!empty($field['description'])): ?>
                <p class="description"><?php echo esc_html($field['description']); ?></p>
            <?php endif; ?>
        </td>
    </tr>
    <?php
}

//--------------------------------------------------------------------------------------------------

function mps_tools_render_html_text($key, $field) {
    ?>
    <tr>
        <td colspan="2" style="padding: 4px 0 16px;">
            <p style="color: #555; font-style: italic; margin: 0;">
                <?php echo $field['text']; ?>
            </p>
        </td>
    </tr>
    <?php
}

//--------------------------------------------------------------------------------------------------

function mps_tools_render_heading($key, $field) {
    ?>
    <tr>
        <td colspan="2" style="padding-top: 24px; padding-bottom: 4px;">
            <h3 style="margin: 0; border-bottom: 1px solid #ccc; padding-bottom: 6px;">
                <?php echo esc_html($field['label']); ?>
            </h3>
        </td>
    </tr>
    <?php
}

//--------------------------------------------------------------------------------------------------

function mps_tools_render_password($key, $field) {
    $current_value = get_option($key, $field['default'] ?? '');
    $has_value = !empty($current_value);
    ?>
    <tr>
        <th><label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?></label></th>
        <td>
            <input type="password"
                   id="<?php echo esc_attr($key); ?>"
                   name="<?php echo esc_attr($key); ?>"
                   value=""
                   placeholder="<?php echo $has_value ? '••••••••••••••••' : 'Non configuré'; ?>"
                   autocomplete="new-password"
                   class="regular-text mps-tools-global" />
            <?php if ($has_value): ?>
                <p class="description">Laissez vide pour conserver la valeur actuelle. Remplissez uniquement pour la remplacer.</p>
            <?php endif; ?>
        </td>
    </tr>
    <?php
}

//--------------------------------------------------------------------------------------------------

function mps_tools_render_paragraph($key, $field) {
    ?>
    <tr>
        <td colspan="2" style="padding: 4px 0 16px;">
            <p style="color: #555; font-style: italic; margin: 0;">
                <?php echo esc_html($field['text']); ?>
            </p>
        </td>
    </tr>
    <?php
}

//--------------------------------------------------------------------------------------------------

function mps_tools_render_textarea($key, $field) {
    $value = get_option($key, $field['default'] ?? '');
    ?>
    <tr>
        <th><label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?></label></th>
        <td>
            <textarea
                id="<?php echo esc_attr($key); ?>"
                name="<?php echo esc_attr($key); ?>"
                rows="6"
                class="large-text"
            ><?php echo esc_textarea($value); ?></textarea>
            <?php if (!empty($field['description'])): ?>
                <p class="description"><?php echo esc_html($field['description']); ?></p>
            <?php endif; ?>
        </td>
    </tr>
    <?php
}

//--------------------------------------------------------------------------------------------------

function mps_tools_render_color($key, $field) {
    $value = get_option($key, $field['default'] ?? '');
    ?>
    <tr>
        <th><label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?></label></th>
        <td>
            <input type="text"
                id="<?php echo esc_attr($key); ?>"
                name="<?php echo esc_attr($key); ?>"
                value="<?php echo esc_attr($value); ?>"
                data-type="full"
                data-alpha-enabled="true"
                data-alpha-color-type="octohex"
                class="mps-tools-color-picker" 
            />
            <?php if (!empty($field['description'])): ?>
                <p class="description"><?php echo esc_html($field['description']); ?></p>
            <?php endif; ?>
        </td>
    </tr>
    <?php
}

//--------------------------------------------------------------------------------------------------

function mps_tools_render_default($key, $field) {
    $value = get_option($key, $field['default'] ?? '');
    ?>
    <tr>
        <th><label for="<?php echo esc_attr($key); ?>"><?php echo esc_html($field['label']); ?></label></th>
        <td>
            <?php if ($field['type'] === 'readonly_url'): ?>
                <input type="text" readonly
                       value="<?php echo esc_url($field['callback']()); ?>"
                       class="regular-text  mps-tools-global" />
            <?php else: ?>
                <input type="<?php echo esc_attr($field['type']); ?>"
                       id="<?php echo esc_attr($key); ?>"
                       name="<?php echo esc_attr($key); ?>"
                       value="<?php echo esc_attr($value); ?>"
                       class="regular-text mps-tools-global" />
            <?php endif; ?>
        </td>
    </tr>
    <?php
}

//--------------------------------------------------------------------------------------------------

function mps_tools_render_repeater($key, $field) {
    $columns = $field['columns'];
    $rows = get_option($key, []);
    if (!is_array($rows)) $rows = [];
    ?>
    <tr>
        <th><?php echo esc_html($field['label']); ?></th>
        <td>
            <table class="widefat mps-tools-repeater" data-key="<?php echo esc_attr($key); ?>">
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
                        <?php mps_tools_render_repeater_row($key, $columns, $i, $row); ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <button type="button" class="button mps-tools-add-row" data-key="<?php echo esc_attr($key); ?>">
                + Ajouter une ligne
            </button>

            <!-- Template caché utilisé par le JS pour cloner une nouvelle ligne -->
            <template id="tpl-<?php echo esc_attr($key); ?>">
                <?php mps_tools_render_repeater_row($key, $columns, '__INDEX__', []); ?>
            </template>
        </td>
    </tr>
    <?php
}

//--------------------------------------------------------------------------------------------------

function mps_tools_render_repeater_row($key, $columns, $index, $row) {
    ?>
    <tr>
        <?php foreach ($columns as $col_key => $col): ?>
            <td>
                <?php
                $input_type = $col['type'] ?? 'text';
                $name = esc_attr($key) . '[' . esc_attr($index) . '][' . esc_attr($col_key) . ']';
                $raw_value = $row[$col_key] ?? '';
                $default = $col['default'] ?? ($input_type === 'color' ? '#000000' : '');

                $raw_value = $row[$col_key] ?? $default;
                // Si la valeur est un tableau (ex: champs_speciaux), on la réaffiche en string séparée par virgules
                if (is_array($raw_value)) {
                    $raw_value = implode(', ', $raw_value);
                }
                ?>

                <?php if ($input_type === 'checkbox'): ?>
                    <?php $checked = !empty($row[$col_key]); ?>
                    <input type="hidden" name="<?php echo $name; ?>" value="0" />
                    <input type="checkbox"
                           name="<?php echo $name; ?>"
                           value="1"
                           <?php checked($checked); ?> />

                <?php elseif ($input_type === 'color'): ?>
                    <input type="color" name="<?php echo $name; ?>" value="<?php echo esc_attr($raw_value); ?>" />

                <?php else: ?>
                    <input type="text" name="<?php echo $name; ?>" value="<?php echo esc_attr($raw_value); ?>" class="regular-text" />
                <?php endif; ?>
            </td>
        <?php endforeach; ?>
        <td>
            <button type="button" class="button mps-tools-remove-row">✕</button>
        </td>
    </tr>
    <?php
}

//--------------------------------------------------------------------------------------------------

function mps_tools_render_categories($key, $field) {
    $columns     = $field['columns'];
    $sub_columns = $field['sub_columns'] ?? [];
    $rows        = get_option($key, []);
    $ajout       = $field['ajout'] ?? 'Ajouter une catégorie';
    if (!is_array($rows)) $rows = [];
    ?>
    <tr>
        <th><?php echo esc_html($field['label']); ?></th>
        <td>
            <div class="mps-tools-categories" data-key="<?php echo esc_attr($key); ?>">
                <div class="mps-tools-categories-list">
                    <?php foreach ($rows as $i => $row): ?>
                        <?php mps_tools_render_category_card($key, $columns, $sub_columns, $i, $row); ?>
                    <?php endforeach; ?>
                </div>
            </div>

            <button type="button" class="button mps-tools-add-category" data-key="<?php echo esc_attr($key); ?>">
                <?php echo esc_html($ajout); ?>
            </button>

            <template id="tpl-<?php echo esc_attr($key); ?>-category">
                <?php mps_tools_render_category_card($key, $columns, $sub_columns, '__CAT_INDEX__', []); ?>
            </template>
        </td>
    </tr>
    <?php
}

//--------------------------------------------------------------------------------------------------

function mps_tools_render_category_card($key, $columns, $sub_columns, $index, $row) {
    ?>
    <div class="mps-tools-category-card" style='margin-bottom:10px;'>
        <div class="mps-tools-category-header">
            <button type="button" class="button-link mps-tools-remove-category">✕ Supprimer cette catégorie</button>
        </div>

        <?php foreach ($columns as $col_key => $col):
            if ($col_key === 'champs_speciaux') continue;

            $input_type = $col['type'] ?? 'text';
            $name       = esc_attr($key) . '[' . esc_attr($index) . '][' . esc_attr($col_key) . ']';
            $default    = $col['default'] ?? ($input_type === 'color' ? '#000000' : '');
            $raw_value  = $row[$col_key] ?? $default;
        ?>
            <div class="mps-tools-category-field">
                <label><?php echo esc_html($col['label']); ?></label>

                <?php if ($input_type === 'checkbox'): ?>
                    <input type="hidden" name="<?php echo $name; ?>" value="0" />
                    <input type="checkbox" name="<?php echo $name; ?>" value="1" <?php checked(!empty($row[$col_key])); ?> />
                <?php elseif ($input_type === 'color'): ?>
                    <input type="text"
                           name="<?php echo $name; ?>"
                           value="<?php echo esc_attr($raw_value); ?>"
                           data-type="full"
                           data-alpha-enabled="true"
                           data-alpha-color-type="octohex"
                           class="mps-tools-color-picker" />
                <?php elseif ($input_type === 'textarea'): ?>
                     <textarea
                        id="<?php echo $name; ?>"
                        name="<?php echo $name; ?>"
                        rows="6"
                        class="large-text"
                    ><?php echo esc_textarea($raw_value); ?></textarea>
                    <?php if (!empty($col['description'])): ?>
                        <p class="description"><?php echo esc_html($col['description']); ?></p>
                    <?php endif; ?>
                <?php else: ?>
                    <input type="text" name="<?php echo $name; ?>" value="<?php echo esc_attr($raw_value); ?>" class="regular-text" />
                <?php endif; ?>
            </div>
        <?php endforeach; ?>

        <?php if ($sub_columns != []): ?>
            <div class="mps-tools-category-field">
                <label><?php echo esc_html($columns['champs_speciaux']['label'] ?? 'Champs supplémentaires'); ?></label>

                <?php
                $special_rows = is_array($row['champs_speciaux'] ?? null) ? $row['champs_speciaux'] : [];
                ?>
                <table class="widefat mps-tools-repeater mps-tools-sub-repeater">
                    <thead>
                        <tr>
                            <?php foreach ($sub_columns as $sub_col): ?>
                                <th><?php echo esc_html($sub_col['label']); ?></th>
                            <?php endforeach; ?>
                            <th style="width:40px;"></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($special_rows as $j => $sub_row): ?>
                            <?php mps_tools_render_special_field_row($key, $index, $sub_columns, $j, $sub_row); ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <button type="button" class="button mps-tools-add-special-field">+ Ajouter un champ</button>

                <template class="tpl-special-field">
                    <?php mps_tools_render_special_field_row($key, $index, $sub_columns, '__SUB_INDEX__', []); ?>
                </template>
            </div>
        <?php endif; ?>
    </div>
    <?php
}

//--------------------------------------------------------------------------------------------------

function mps_tools_render_special_field_row($key, $cat_index, $sub_columns, $sub_index, $row) {
    ?>
    <tr>
        <?php foreach ($sub_columns as $col_key => $col):
            $input_type = $col['type'] ?? 'text';
            $name       = esc_attr($key) . '[' . esc_attr($cat_index) . '][champs_speciaux][' . esc_attr($sub_index) . '][' . esc_attr($col_key) . ']';
            $raw_value  = $row[$col_key] ?? '';
        ?>
            <td>
                <?php if ($input_type === 'checkbox'): ?>
                    <input type="hidden" name="<?php echo $name; ?>" value="0" />
                    <input type="checkbox" name="<?php echo $name; ?>" value="1" <?php checked(!empty($row[$col_key])); ?> />
                <?php else: ?>
                    <input type="text" name="<?php echo $name; ?>" value="<?php echo esc_attr($raw_value); ?>" class="regular-text" />
                <?php endif; ?>
            </td>
        <?php endforeach; ?>
        <td>
            <button type="button" class="button mps-tools-remove-row">✕</button>
        </td>
    </tr>
    <?php
}

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------