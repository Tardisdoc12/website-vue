<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: reglement.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-23
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;



//--------------------------------------------------------------------------------------------------
// Functions OR CLASS

return [
    'label' => 'Réglement',
    'fields' => [
        '_heading_onglet' => ['type' => 'heading', 'label' => 'Réglement et Conditions de l\'Association'],

        '_info_reglement' => [
            'type' => 'paragraph',
            'text' => 'Choisissez la source du règlement intérieur (PDF à héberger, ou lien externe), puis sélectionnez où la signature/acceptation doit être demandée.',
        ],

        'mps_tools_reglement_source_type' => [
            'label'   => 'Source du règlement',
            'type'    => 'radio',
            'options' => [
                'pdf' => 'Déposer un fichier PDF',
                'url' => 'Utiliser un lien externe',
            ],
            'default' => 'pdf',
        ],

        'mps_tools_reglement_pdf' => [
            'label'       => 'Fichier PDF du règlement',
            'type'        => 'file',
            'accept'      => 'application/pdf',
            'description' => 'Sélectionnez un PDF depuis la médiathèque WordPress.',
        ],

        'mps_tools_reglement_url' => [
            'label'       => 'URL du règlement',
            'type'        => 'url',
            'description' => 'Lien complet (https://...) vers le règlement intérieur.',
        ],

        '_heading_emplacements' => ['type' => 'heading', 'label' => 'Où demander l\'acceptation ?'],

        'mps_tools_reglement_locations' => [
            'label'   => 'Emplacements concernés',
            'type'    => 'checkbox_group',
            'options' => [
                'event_inscription'  => 'Inscription à un événement',
                'account_creation'   => 'Création de compte',
                'adhesion_form'      => 'Formulaire d\'adhésion',
            ],
        ],
    ],
];
//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------