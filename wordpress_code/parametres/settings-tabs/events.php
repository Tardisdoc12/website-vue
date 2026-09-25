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
                'affichage_list_inscrit' => [
                    'label' => 'Décider des Affichages dans la liste des inscrits',
                    'type' => 'select',
                    'options' => [
                        'everybody' => 'Tous les inscrits',
                        'only_adherents' => 'Seulement les adhérents',
                        'only_non_adherents' => 'Seulement les non-adhérents',
                        'nobody' => 'Personne'
                    ], 
                ],
                'mandatory_response' => [
                    'label' => 'Réponse obligatoire',
                    'type' => 'select',
                    'options' => [
                        'everybody' => 'Tout le monde',
                        'only_adherents' => 'Seulement les adhérents',
                        'only_non_adherents' => 'Seulement les non-adhérents',
                        'nobody' => 'Personne'
                    ],
                ],
                'affichage_formulaire' => [
                    'label' => 'Décider des Affichages dans le formulaire',
                    'type' => 'select',
                    'options' => [
                        'everybody' => 'Tous les inscrits',
                        'only_adherents' => 'Seulement les adhérents',
                        'only_non_adherents' => 'Seulement les non-adhérents',
                        'nobody' => 'Personne'
                    ], 
                ],
                'document_choice' => ['label' => 'Choix d\'un document', 'type' => 'file'],
                'type_field' => [
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