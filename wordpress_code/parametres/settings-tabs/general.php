<?php
if (!defined('ABSPATH')) exit;

return [
    'label' => 'Général',
    'fields' => [
        'mon_plugin_mail_from' => [
            'label' => 'Email expéditeur',
            'type'  => 'email',
        ],
        'mon_plugin_mail_name' => [
            'label' => 'Nom expéditeur',
            'type'  => 'text',
        ],
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