<?php
/**
 * HelloAsso settings tab configuration.
 */
if (!defined('ABSPATH')) exit;

return [
    'label' => 'HelloAsso',
    'fields' => [
        'helloasso_form_slug_adherent' => [
            'label'   => 'Form Slug Adhérent',
            'type'    => 'text',
            'default' => 'devenir-adherent',
        ],
        'helloasso_endpoint' => [
            'label'    => 'Endpoint notifications',
            'type'     => 'readonly_url',
            'callback' => fn() => rest_url('helloasso/v1/notification'),
        ],
    ],
];