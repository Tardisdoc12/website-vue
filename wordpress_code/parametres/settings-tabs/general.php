<?php
/**
 * General settings tab for the theme.
 */
if (!defined('ABSPATH')) exit;

return [
    'label' => 'Général',
    'fields' => [
        '_heading_theme' => ['type' => 'heading', 'label' => 'Thème Principal'],
        'main_color' => [
            'label' => 'Couleur principale',
            'type' => 'color',
        ],
        'secondary_color' => [
            'label' => 'Couleur secondaire',
            'type' => 'color',
        ],
        'depliant_background_color' => [
            'label' => 'Couleur de fond des dépliants secondaires',
            'type' => 'color',
        ],

        '_heading_buttons' => ['type' => 'heading', 'label' => 'Boutons'],
        'validate_color' => [
            'label' => 'Couleur des boutons de validation',
            'type' => 'color',
        ],
        'validate_hover_color' => [
            'label' => 'Couleur au survol des boutons de validation',
            'type' => 'color',
        ],
        
        'cancel_color' => [
            'label' => 'Couleur des boutons d\'annulation',
            'type' => 'color',
        ],
        'cancel_hover_color' => [
            'label' => 'Couleur au survol des boutons d\'annulation',
            'type' => 'color',
        ],

        'color_button_file' => [
            'label' => 'Couleur des boutons de gestion defichier',
            'type' => 'color',
        ],

        '_heading_deactivate' => ['type' => 'heading', 'label' => 'Désactivation'],
        'deactivate_button_classic_color' => [
            'label' => 'Couleur de désactivation des boutons classiques',
            'type' => 'color',
        ],
        'validate_disabled_color' => [
            'label' => 'Couleur des boutons de validation désactivés',
            'type' => 'color',
        ],
        'cancel_disabled_color' => [
            'label' => 'Couleur des boutons d\'annulation désactivés',
            'type' => 'color',
        ],
        
        

        '_heading_writing' => ['type' => 'heading', 'label' => 'Écriture'],
        'writing_main_color' => [
            'label' => 'Couleur principale de l\'écriture',
            'type' => 'color',
            'default' => '#FFFFFF',
        ],

    ],
];