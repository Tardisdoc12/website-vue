<?php

// functions/roles.php
if (!defined('ABSPATH')) exit;

// Liste FIXE des droits que la page peut attribuer
function mps_tools_available_caps() {
    return [
        'read'                  =>  'Accès de base (connexion, profil)',
        'mps_manage_events'     =>  'Gérer les événements',
        'mps_help_for_events'   =>  'Peut encadrer pour les événements',
        'mps_view_inscrits'     =>  'Voir les inscrits',
        'mps_manage_inscrits'   =>  'Gérer les inscrits',
        'mps_manage_members'    =>  'Gérer les adhérents',
        'mps_manage_settings'   =>  'Gérer les paramètres',
        'mps_show_in_list'      =>  'Visibilité dans les listes du sites',
        'mps_give_advice'       =>  'Peut écrire des notes dans les fiches de suivis des autres utilisateurs.',
        'mps_file_gestionnaire' =>  'Peut gérer les fichiers des Ressources',
        'mps_file_viewer'       =>  'Peut voir les fichiers des Ressources',
        'mps_has_access'        =>  'Peut accéder aux événements en tant qu\'adhérent',
    ];
}

function mps_tools_default_roles() {
    return [
        'non_adherent' => ['label' => 'Non adhérent', 'caps' => ['read']],
        'adherent'     => ['label' => 'Adhérent',     'caps' => ['read']],
        'encadrant'    => ['label' => 'Encadrant',    'caps' => ['read', 'mps_manage_events', 'mps_view_inscrits']],
        'bureau'       => ['label' => 'Bureau',       'caps' => ['read', 'mps_manage_events', 'mps_view_inscrits', 'mps_manage_members', 'mps_manage_settings']],
    ];
}

// Rôles utilisés par ton code : non supprimables
function mps_tools_protected_roles() {
    return ['non_adherent', 'adherent'];
}

function mps_tools_install_roles() {
    $managed = get_option('mps_tools_managed_roles', []);

    foreach (mps_tools_default_roles() as $slug => $def) {
        // On ne touche pas à un rôle existant : les modifs faites dans la page sont conservées
        if (!get_role($slug)) {
            add_role($slug, $def['label'], array_fill_keys($def['caps'], true));
        }
        $managed[] = $slug;
    }
    update_option('mps_tools_managed_roles', array_values(array_unique($managed)), false);

    // L'administrateur a toujours tous les droits du plugin
    $admin = get_role('administrator');
    if ($admin) {
        foreach (array_keys(mps_tools_available_caps()) as $cap) {
            $admin->add_cap($cap);
        }
    }
}

function mps_tools_get_user_caps($user) {
    $caps = [];
    foreach (array_keys(mps_tools_available_caps()) as $cap) {
        if ($user->has_cap($cap)) {
            $caps[] = $cap;
        }
    }
    return $caps;
}

function mps_tools_get_role_caps($role) {
    $caps = [];
    foreach (array_keys(mps_tools_available_caps()) as $cap) {
        if ($role->has_cap($cap)) {
            $caps[] = $cap;
        }
    }
    return $caps;
}

function mps_tools_has_caps_roles($role, $caps) {
    foreach ($caps as $cap) {
        if (!$role->has_cap($cap)) {
            return false;
        }
    }
    return true;
}