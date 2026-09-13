<?php
/**
 * Onglet de paramètres pour la section événements
 */
if (!defined('ABSPATH')) exit;

return [
    'label' => 'Événements',
    'fields' => [
        'mon_plugin_categories' => [
            'label'   => 'Catégories',
            'type'    => 'repeater',
            'columns' => [
                'nom'     => ['label' => 'Nom', 'type' => 'text'],
                'couleur' => ['label' => 'Couleur', 'type' => 'color'],
            ],
        ],
    ],
];