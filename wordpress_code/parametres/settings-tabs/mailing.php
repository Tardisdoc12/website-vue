<?php
/**
 * Onglet de paramètres pour la section mailing
 */
if (!defined('ABSPATH')) exit;

function monplugin_get_email_tags($context) {
    $common = [
        'date' => date_i18n('d/m/Y'),
    ];

    switch ($context) {
        case 'modification_events':
            return array_merge($common, [
                'event_name'        => '', // rempli au moment de l'envoi
                'modification_date'       => '',
                'event_date'  => '',
                'event_place'       => '',
            ]);

        case 'changement_mot_de_passe':
            return array_merge($common, [
                'user_name'        => '',
                'url_reset'       => '',
                'date'  => '',
            ]);

        case 'email_to_one_user':
            return array_merge($common, [
                'event_name'        => '', // rempli au moment de l'envoi
                'modification_date'       => '',
                'event_date'  => '',
                'event_place'       => '',
                'user_firstName'       => '',
                'user_lastName'       => '',
            ]);

        default:
            return $common;
    }
}

return [
    'label' => 'Mailing',
    'fields' => [
        '_heading_senders' => [
            'type' => 'heading',
            'label' => 'Email - Expéditeurs',
        ],
        'mps_tools_mail_senders' => [
            'label' => 'Email de l\'expéditeur',
            'type'  => 'text',
        ],
        'mps_tools_name_email' => [
            'label' => 'Nom de l\'expéditeur à Afficher',
            'type'  => 'text',
        ],
        '_heading_modification_events' => [
            'type' => 'heading',
            'label' => 'Email - Modification des événements',
        ],
        '_info_confirmation' => [
            'type' => 'paragraph',
            'text' => 'Balises disponibles : ' . implode(', ', array_map(
                fn($tag) => '{{' . $tag . '}}',
                array_keys(monplugin_get_email_tags('modification_events'))
            )),
        ],
        'mps_tools_mail_modification_events' => [
            'label' => 'Email en cas de modification des événements',
            'type'  => 'textarea',
        ],


        '_heading_changement_mot_de_passe' => [
            'type' => 'heading',
            'label' => 'Email - Changement de mot de passe',
        ],
        '_info_changement_mot_de_passe' => [
            'type' => 'paragraph',
            'text' => 'Balises disponibles : ' . implode(', ', array_map(
                fn($tag) => '{{' . $tag . '}}',
                array_keys(monplugin_get_email_tags('changement_mot_de_passe'))
            )),
        ],
        'mps_tools_mail_password_recuperation' => [
            'label' => 'Mail pour la récupération du mot de passe',
            'type'  => 'textarea',
            'default' => "Cliquez ici pour réinitialiser votre mot de passe :\n\n{{url_reset}}\n\nSi vous n'avez pas demandé cette réinitialisation, ignorez cet email.",
        ],

        '_heading_mail_to_inscrits' => ['label' => 'Email - Template pour les envois manuels', 'type'=>'heading'],
        '_info_balise_manuel' => [
            'type' => 'paragraph',
            'text' => 'Balises disponibles : ' . implode(', ', array_map(
                fn($tag) => '{{' . $tag . '}}',
                array_keys(monplugin_get_email_tags('email_to_one_user'))
            )),
        ],
        'mps_tools_mailing_manual' => [
            'label' => 'Templates de mailing',
            'type' => 'template_manager',
            'columns' => [
                'name_template' => ['label' => 'Nom du template', 'type' => 'text'],
                'objet'          => ['label' => 'Objet',           'type' => 'text'],
                'template'       => ['label' => 'Corps du mail',    'type' => 'textarea'],
            ],
        ],
    ],
];