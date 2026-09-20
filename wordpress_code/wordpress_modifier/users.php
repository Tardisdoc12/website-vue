<?php
/* 
* On ajoute des colonnes pour les utilisateurs
*/
if (!defined('ABSPATH')) exit;

add_action('rest_api_init', function() {
    $custom_fields = [
        'firstName',
        'lastName',
        'adherentNumber',
        'telephone',
        'moto',
        'urgence_phone',
        'urgence_name',
        'subscriber_date',
    ];

    foreach ($custom_fields as $field) {
        register_rest_field('user', $field, [
            'get_callback' => function($user) use ($field) {
                return get_user_meta($user['id'], $field, true);
            },
            'update_callback' => function($value, $user) use ($field) {
                update_user_meta($user->ID, $field, sanitize_text_field($value));
            },
        ]);
    }

    register_rest_field('user', 'email', [
        'get_callback' => function($user) {
            if (get_current_user_id() === $user['id']) {
                return get_userdata($user['id'])->user_email;
            }
            return null;
        },
        'update_callback' => null,
        'schema' => [
            'description' => __('User email'),
            'type' => 'string'
        ],
    ]);
});

//------------------------------------------------------------------------------
// End of File
//------------------------------------------------------------------------------