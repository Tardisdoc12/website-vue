<?php
/**
 * Onglet de paramètres pour la section événements
 */
if (!defined('ABSPATH')) exit;

return [
    'label' => 'Événements',
    'fields' => [
        'mon_plugin_categories' => [
            'label'   => 'Catégories',
            'type'    => 'repeater',
            'columns' => [
                'nom'     => ['label' => 'Nom', 'type' => 'text'],
                'couleur' => ['label' => 'Couleur', 'type' => 'color'],
                'adherent_payant'      => ['label' => 'Adhérent doit payer', 'type' => 'checkbox'],
                'non_adherent_payant'  => ['label' => 'Non-adhérent doit payer', 'type' => 'checkbox'],
                'liste_attente'        => ['label' => 'Liste d\'attente possible', 'type' => 'checkbox'],
                'champs_speciaux'     => ['label' => 'Champs spéciaux (séparés par virgule)', 'type' => 'text'],
            ],
        ],
    ],
];