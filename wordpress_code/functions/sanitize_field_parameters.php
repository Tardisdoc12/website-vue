<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: sanitize_field_parameters.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-19
* DESCRIPTION : Functions to sanitize field parameters
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;

//--------------------------------------------------------------------------------------------------
// Functions

function mps_tools_sanitize_checkbox($field, $key){
    $args['sanitize_callback'] = function($value){
        return !empty($value) ? 1 : 0;
    };
    return $args;
}

//--------------------------------------------------------------------------------------------------

function mps_tools_validate_color_value($value, $default = '') {
    if (empty($value)) {
        return $default;
    }

    $value = trim($value);

    if (preg_match('/^#([A-Fa-f0-9]{3,4}|[A-Fa-f0-9]{6}|[A-Fa-f0-9]{8})$/', $value)) {
        return $value;
    }

    return $default;
}

//--------------------------------------------------------------------------------------------------

function mps_tools_sanitize_color($field, $key) {
    $args['sanitize_callback'] = function($value) use ($field) {
        return mps_tools_validate_color_value($value, $field['default'] ?? '');
    };
    return $args;
}

//--------------------------------------------------------------------------------------------------

function mps_tools_sanitize_number($field, $key){
    $args['sanitize_callback'] = function($value) use ($field) {
        // Si vide ou non numérique, on retombe sur la valeur par défaut
        if ($value === '' || !is_numeric($value)) {
            return $field['default'] ?? 0;
        }

        $value = absint($value); // force en entier positif

        // Applique un minimum si défini dans le champ
        if (isset($field['min']) && $value < $field['min']) {
            $value = $field['min'];
        }

        // Applique un maximum si défini dans le champ
        if (isset($field['max']) && $value > $field['max']) {
            $value = $field['max'];
        }

        return $value;
    };
    return $args;
}

//--------------------------------------------------------------------------------------------------

function mps_tools_sanitize_password($field, $key){
    $args['sanitize_callback'] = function($value) use ($key) {
        // Champ vide soumis = l'utilisateur n'a rien changé, on garde l'ancienne valeur
        if ($value === '') {
            return get_option($key, '');
        }
        return sanitize_text_field($value);
    };
    return $args;
}

//--------------------------------------------------------------------------------------------------

function mps_tools_sanitize_textarea( $field, $key){
    $args['sanitize_callback'] = 'sanitize_textarea_field';
    return $args;
}

//--------------------------------------------------------------------------------------------------


function mps_tools_sanitize_rich_text($html) {
    // Utiliser HTMLPurifier via DOMDocument natif PHP
    $dom = new DOMDocument();
    @$dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    
    $allowed_tags = ['p', 'br', 'strong', 'b', 'em', 'i', 'u', 's', 'span', 'ul', 'ol', 'li', 'a'];
    $allowed_attrs = ['style', 'href', 'target', 'rel', 'class'];
    
    $xpath = new DOMXPath($dom);
    
    // Supprimer les balises non autorisées
    foreach ($xpath->query('//*') as $node) {
        if (!in_array(strtolower($node->nodeName), $allowed_tags)) {
            $node->parentNode->replaceChild($dom->createTextNode($node->textContent), $node);
            continue;
        }
        
        // Supprimer les attributs non autorisés
        $attrs_to_remove = [];
        foreach ($node->attributes as $attr) {
            if (!in_array($attr->name, $allowed_attrs)) {
                $attrs_to_remove[] = $attr->name;
            }
        }
        foreach ($attrs_to_remove as $attr) {
            $node->removeAttribute($attr);
        }
        
        // Valider le style : n'autoriser que color, background-color, text-align
        if ($node->hasAttribute('style')) {
            $style = $node->getAttribute('style');
            $safe_style = '';
            
            foreach (explode(';', $style) as $declaration) {
                $declaration = trim($declaration);
                if (empty($declaration)) continue;
                
                if (preg_match('/^(color|background-color|text-align)\s*:\s*(.+)$/i', $declaration, $m)) {
                    $prop  = strtolower(trim($m[1]));
                    $value = trim($m[2]);
                    
                    // Valider la valeur : hex, rgb(), rgba(), ou nom de couleur
                    if (preg_match('/^(#[0-9a-fA-F]{3,8}|rgb\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*\)|rgba\(\s*\d+\s*,\s*\d+\s*,\s*\d+\s*,\s*[\d.]+\s*\)|[a-zA-Z]+|left|center|right)$/', $value)) {
                        $safe_style .= $prop . ': ' . $value . '; ';
                    }
                }
            }
            
            if (!empty(trim($safe_style))) {
                $node->setAttribute('style', trim($safe_style));
            } else {
                $node->removeAttribute('style');
            }
        }
    }
    
    $result = $dom->saveHTML();
    
    // saveHTML ajoute parfois des wrappers, on les retire
    $result = preg_replace('/^<!DOCTYPE.+?>/i', '', $result);
    $result = trim($result);
    
    return $result;
}


//--------------------------------------------------------------------------------------------------

function mps_tools_sanitize_repeater( $field, $key){
    $args['sanitize_callback'] = function($value) use ($field) {
        $columns = $field['columns'] ?? [];
        return mps_tools_sanitize_repeater_callback($value, $columns);
    };
    return $args;
}

function mps_tools_sanitize_repeater_callback($value, $columns) {
    if (!is_array($value)) return [];

    $clean = [];
    foreach ($value as $row) {
        if (!is_array($row)) continue;

        $clean_row = [];
        $has_content = false;

        foreach ($columns as $col_key => $col) {
            $type = $col['type'] ?? 'text';
            $row_default = isset($row['default']) ? $row['default'] : '';
            $raw = $row[$col_key] ?? $row_default;

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
                        $val = mps_tools_validate_color_value($raw, $row_default) ?: '#000000';
                        break;
                    case 'textarea':
                        $val = sanitize_textarea_field($raw);
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
//--------------------------------------------------------------------------------------------------

function mps_tools_sanitize_template_manager($field, $key) {
    $args['sanitize_callback'] = function($value) use ($field) {
        $columns = $field['columns'] ?? [];
        return mps_tools_sanitize_template_manager_callback($value, $columns);
    };
    return $args;
}

//--------------------------------------------------------------------------------------------------

function mps_tools_sanitize_template_manager_callback($value, $columns) {
    if (!is_array($value)) return [];

    $clean = [];
    foreach ($value as $row) {
        if (!is_array($row)) continue;

        $clean_row = [];
        $has_content = false;

        foreach ($columns as $col_key => $col) {
            $type = $col['type'] ?? 'text';
            $default = $col['default'] ?? '';
            $raw = $row[$col_key] ?? $default;

            switch ($type) {
                case 'checkbox':
                    $val = !empty($raw) ? 1 : 0;
                    break;
                case 'color':
                    $val = mps_tools_validate_color_value($raw, $default) ?: '#000000';
                    break;
                case 'textarea':
                    $val = sanitize_textarea_field($raw);
                    break;
                case 'file':
                    $attachment_id = absint($raw);
                    $val = ($attachment_id && get_post($attachment_id)) ? $attachment_id : 0;
                    break;
                default:
                    $val = sanitize_text_field($raw);
            }

            $clean_row[$col_key] = $val;

            if ($val !== '') $has_content = true;
        }

        if ($has_content) $clean[] = $clean_row;
    }

    return array_values($clean);
}


//--------------------------------------------------------------------------------------------------

function mps_tools_sanitize_radio($field, $key) {
    $args['sanitize_callback'] = function($value) use ($field) {
        $allowed = array_keys($field['options'] ?? []);
        return in_array($value, $allowed, true) ? $value : ($field['default'] ?? '');
    };
    return $args;
}

//--------------------------------------------------------------------------------------------------

function mps_tools_sanitize_checkbox_group($field, $key) {
    $args['sanitize_callback'] = function($value) use ($field) {
        if (!is_array($value)) return [];
        $allowed = array_keys($field['options'] ?? []);
        $clean = array_values(array_intersect($value, $allowed));
        return $clean;
    };
    return $args;
}

//--------------------------------------------------------------------------------------------------

function mps_tools_sanitize_file($field, $key) {
    $args['sanitize_callback'] = function($value) {
        $attachment_id = absint($value);
        // Vérifie que l'attachment existe réellement, pour éviter d'enregistrer un ID invalide
        if ($attachment_id && !get_post($attachment_id)) {
            return 0;
        }
        return $attachment_id;
    };
    return $args;
}

//--------------------------------------------------------------------------------------------------

function mps_tools_sanitize_url($field, $key) {
    $args['sanitize_callback'] = function($value) use ($field) {
        return sanitize_url($value) ?: ($field['default'] ?? '');
    };
    return $args;
}

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------