<?php
/* 
 * Onglet de paramètres pour la section événements
*/

if (!defined('ABSPATH')) exit;

return [
    'label' => 'Événements',
    'fields' => [
        'mps_tools_categories' => [
            'label'   => 'Catégories',
            'type'    => 'categories',
            'columns' => [
                'nom'                 => ['label' => 'Nom', 'type' => 'text'],
                'couleur'             => ['label' => 'Couleur', 'type' => 'color', 'default' => '#000000'],
                'adherent_payant'     => ['label' => 'Adhérent doit payer', 'type' => 'checkbox'],
                'non_adherent_payant' => ['label' => 'Non-adhérent doit payer', 'type' => 'checkbox'],
                'liste_attente'       => ['label' => 'Liste d\'attente possible', 'type' => 'checkbox'],
                'champs_speciaux'     => ['label' => 'Champs supplémentaires'],
            ],
            'sub_columns' => [
                'nom'             => ['label' => 'Nom', 'type' => 'text'],
                'affichage_liste' => ['label' => 'Affichage du champ dans la liste des inscrits', 'type' => 'checkbox'],
                'affichage_adherent' => ['label' => 'Affichage des réponses des adhérents', 'type' => 'checkbox'],
                'affichage_non_adherent' => ['label' => 'Affichage des réponses des non-adhérents', 'type' => 'checkbox'],
                'obligatoire_adherent'     => ['label' => 'Obligatoire pour les adhérents', 'type' => 'checkbox'],
                'obligatoire_non_adherent' => ['label' => 'Obligatoire pour les non-adhérents', 'type' => 'checkbox'],
                'choix d\'un document' => ['label' => 'Choix d\'un document', 'type' => 'file'],
                'choix du type de champ' => [
                    'label' => 'Choix du type de champ',
                    'type' => 'select',
                    'options' => [
                        'texte' => 'Texte',
                        'nombre' => 'Nombre',
                        'date' => 'Date',
                        'checkbox' => 'Case à cocher'
                    ]
                ],
            ],
        ],
    ],
];