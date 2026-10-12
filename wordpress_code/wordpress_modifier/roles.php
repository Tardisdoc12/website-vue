<?php

if (!defined('ABSPATH')) exit;

//----------------------------------------------------------------------------------------------------------

// Dans le fichier principal du plugin (celui avec l'en-tête "Plugin Name")

register_activation_hook(MPS_TOOLS_MAIN_FILE, 'mps_tools_activate');

function mps_tools_activate() {
    mps_tools_register_roles();
}

function mps_tools_register_roles() {
    // add_role() ne fait rien si le rôle existe déjà
    add_role('non_adherent', 'Non adhérent', [
        'read' => true,
    ]);

    add_role('adherent', 'Adhérent', [
        'read' => true,
    ]);

    add_role('encadrant', 'Encadrant', [
        'read'                => true,
        'mps_view_inscrits'   => true,
    ]);

    add_role('bureau', 'Bureau', [
        'read'                => true,
        'mps_manage_events'   => true,
        'mps_view_inscrits'   => true,
        'mps_manage_members'  => true,
        'mps_manage_settings' => true,
    ]);

    // Donner les droits custom à l'administrateur aussi
    $admin = get_role('administrator');
    if ($admin) {
        foreach (['mps_manage_events', 'mps_view_inscrits', 'mps_manage_members', 'mps_manage_settings'] as $cap) {
            $admin->add_cap($cap);
        }
    }
}

//----------------------------------------------------------------------------------------------------------
// Restrict access to admin pages and hide admin bar for non-privileged users

add_action('admin_init', function () {
    if (wp_doing_ajax()) return; // ne casse pas admin-ajax.php

    if (!current_user_can('mps_manage_events') && !current_user_can('manage_options')) {
        wp_safe_redirect(home_url());
        exit;
    }
});

// Masquer aussi la barre d'admin en haut du site
add_filter('show_admin_bar', function ($show) {
    return (current_user_can('mps_manage_events') || current_user_can('manage_options')) ? $show : false;
});

update_option('default_role', 'non_adherent');