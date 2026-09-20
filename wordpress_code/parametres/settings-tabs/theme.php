<?php
/**
 * General settings tab for the theme.
 */
if (!defined('ABSPATH')) exit;

return [
    'label' => 'Thème',
    'fields' => [
        '_heading_theme' => ['type' => 'heading', 'label' => 'Thème Principal'],
        'main_color' => [
            'label' => 'Couleur principale',
            'type' => 'color',
            'default' => '#245473',
        ],
        'secondary_color' => [
            'label' => 'Couleur secondaire',
            'type' => 'color',
            'default' => '#2d5c7f',
        ],
        'depliant_background_color' => [
            'label' => 'Couleur de fond des dépliants secondaires',
            'type' => 'color',
            'default' => '#d4e3ed',
        ],

        '_heading_buttons' => ['type' => 'heading', 'label' => 'Boutons'],
        'validate_color' => [
            'label' => 'Couleur des boutons de validation',
            'type'=> 'color',
            'default' => '#2e7d32',
        ],
        'validate_hover_color' => [
            'label' => 'Couleur au survol des boutons de validation',
            'type' => 'color',
            'default' => '#1b5e20',
        ],
        'cancel_color' => [
            'label' => 'Couleur des boutons d\'annulation',
            'type' => 'color',
            'default' => '#c62828',
        ],
        'cancel_hover_color' => [
            'label' => 'Couleur au survol des boutons d\'annulation',
            'type' => 'color',
            'default' => '#b71c1c',
        ],

        'color_button_file' => [
            'label' => 'Couleur des boutons de gestion defichier',
            'type' => 'color',
            'default' => '#000000',
        ],

        '_heading_deactivate' => ['type' => 'heading', 'label' => 'Désactivation'],
        'deactivate_button_classic_color' => [
            'label' => 'Couleur de désactivation des boutons classiques',
            'type' => 'color',
            'default'=> '#9ca3af',
        ],
        'validate_disabled_color' => [
            'label' => 'Couleur des boutons de validation désactivés',
            'type' => 'color',
            'default' => '#9ca3af',
        ],
        'cancel_disabled_color' => [
            'label' => 'Couleur des boutons d\'annulation désactivés',
            'type' => 'color',
            'default' => '#9ca3af',
        ],

        '_heading_modal' => ['type' => 'heading', 'label' => 'Modale'],
        'modal_header_background_color' => [
            'label' => 'Couleur de fond de l\'en-tête de la modale',
            'type' => 'color',
            'default' => '#4b4c4ed6',
        ],
        'modal_body_background_color' => [
            'label' => 'Couleur de fond du corps de la modale',
            'type' => 'color',
            'default' => '#ffffff',
        ],
        'modal_overlay_color' => [
            'label' => 'Couleur de l\'overlay de la modale',
            'type' => 'color',
            'default' => '#646464e6',
        ],
        
        

        '_heading_writing' => ['type' => 'heading', 'label' => 'Écriture'],
        'writing_main_color' => [
            'label' => 'Couleur principale de l\'écriture',
            'type' => 'color',
            'default' => '#FFFFFF',
        ],

        'writing_secondary_color' => [
            'label' => 'Couleur secondaire de l\'écriture',
            'type' => 'color',
            'default' => '#000000',
        ],

        'writing_modale_title_color' => [
            'label' => 'Couleur de l\'écriture du titre dans les modales',
            'type' => 'color',
            'default' => '#000000',
        ],
        'writing_modale_body_color' => [
            'label' => 'Couleur de l\'écriture du corps dans les modales',
            'type' => 'color',
            'default' => '#000000',
        ],
    ],
];