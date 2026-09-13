<?php
/**
 * Onglet de paramètres pour la section mailing
 */
if (!defined('ABSPATH')) exit;

return [
    'label' => 'Mailing',
    'fields' => [
        'mon_plugin_mail_from' => [
            'label' => 'Email expéditeur',
            'type'  => 'email',
        ],
        'mon_plugin_mail_name' => [
            'label' => 'Nom expéditeur',
            'type'  => 'text',
        ],
    ],
];