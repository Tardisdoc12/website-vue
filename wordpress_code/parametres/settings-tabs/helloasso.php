<?php
/**
 * HelloAsso settings tab configuration.
 */
if (!defined('ABSPATH')) exit;

return [
    'label' => 'HelloAsso',
    'fields' => [
        'helloasso_org_slug' => [
            'label'=> 'Organisation Slug d\'HelloAsso',
            'type'    => 'text',
            'default' => '',
        ],
        'helloasso_client_id' => [
            'label'   => 'Client ID d\'HelloAsso',
            'type'    => 'password',
            'default' => '',
        ],
        'helloasso_client_secret' => [
            'label'   => 'Client Secret d\'HelloAsso',
            'type'    => 'password',
            'default' => '',
        ],
        'helloasso_return_url' => [
            'label'   => 'URL de retour d\'HelloAsso après payement',
            'type'    => 'text',
            'default' => '',
        ],
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