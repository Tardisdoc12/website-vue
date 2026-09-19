<?php
/**
 * General settings tab for the theme.
 */
if (!defined('ABSPATH')) exit;

return [
    'label' => 'Général',
    'fields' => [
        '_heading_time_connexion' => ['type' => 'heading', 'label' => 'Temps de connexion (en secondes)'],
        '_info_confirmation' => [
            'type' => 'paragraph',
            'text' => 'le temps minimum est de 1min et au maximum de 30jours',
        ],
        'JWT_TIME_CONNEXION' => [
            'label' => 'Durée de la connexion',
            'type' => 'number',
            'default' => 3600,
            'min'     => 60,        // 1 minute minimum
            'max'     => 2592000,   // 30 jours maximum
        ],

        '_heading_accept_cash' => ['type' => 'heading', 'label' => 'Acceptation des paiements en espèces'],
        'accept_cash' => [
            'label' => 'Accepter les paiements en espèces',
            'type' => 'checkbox',
        ],
    ],
];