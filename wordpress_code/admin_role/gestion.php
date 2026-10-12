<?php

if (!defined('ABSPATH')) exit;

function mps_tools_sanitize_caps_input($posted) {
    $allowed = array_keys(mps_tools_available_caps());
    // Liste blanche : on ne peut jamais attribuer autre chose que les droits du plugin
    return array_values(array_intersect(array_map('sanitize_key', (array) $posted), $allowed));
}

function mps_tools_roles_redirect($args) {
    wp_safe_redirect(add_query_arg($args, admin_url('options-general.php?page=mps-tools-roles')));
    exit;
}

// Modifier les droits d'un rôle
add_action('admin_post_mps_tools_save_role', function () {
    if (!current_user_can('manage_options')) wp_die('Accès refusé.');
    check_admin_referer('mps_tools_save_role');

    $slug = sanitize_key($_POST['role_slug'] ?? '');
    if (!in_array($slug, get_option('mps_tools_managed_roles', []), true) || !($role = get_role($slug))) {
        mps_tools_roles_redirect(['error' => 'Rôle non modifiable.']);
    }

    $selected = mps_tools_sanitize_caps_input(wp_unslash($_POST['caps'] ?? []));

    foreach (array_keys(mps_tools_available_caps()) as $cap) {
        in_array($cap, $selected, true) ? $role->add_cap($cap) : $role->remove_cap($cap);
    }

    mps_tools_roles_redirect(['updated' => 1]);
});

// Créer un rôle
add_action('admin_post_mps_tools_create_role', function () {
    if (!current_user_can('manage_options')) wp_die('Accès refusé.');
    check_admin_referer('mps_tools_create_role');

    $label = sanitize_text_field(wp_unslash($_POST['role_label'] ?? ''));
    $slug  = sanitize_key(str_replace(' ', '_', remove_accents($label)));

    if ($label === '' || $slug === '') {
        mps_tools_roles_redirect(['error' => 'Nom invalide.']);
    }
    if (get_role($slug)) {
        mps_tools_roles_redirect(['error' => 'Ce rôle existe déjà.']);
    }

    $caps = mps_tools_sanitize_caps_input(wp_unslash($_POST['caps'] ?? []));
    add_role($slug, $label, array_fill_keys($caps, true));

    $managed   = get_option('mps_tools_managed_roles', []);
    $managed[] = $slug;
    update_option('mps_tools_managed_roles', array_values(array_unique($managed)), false);

    mps_tools_roles_redirect(['updated' => 1]);
});

// Supprimer un rôle
add_action('admin_post_mps_tools_delete_role', function () {
    if (!current_user_can('manage_options')) wp_die('Accès refusé.');

    $slug = sanitize_key($_GET['role_slug'] ?? '');
    check_admin_referer('mps_tools_delete_role_' . $slug);

    $managed = get_option('mps_tools_managed_roles', []);
    if (!in_array($slug, $managed, true) || in_array($slug, mps_tools_protected_roles(), true)) {
        mps_tools_roles_redirect(['error' => 'Ce rôle ne peut pas être supprimé.']);
    }

    // Réassigner avant suppression, sinon les utilisateurs n'ont plus de rôle
    foreach (get_users(['role' => $slug]) as $user) {
        $user->remove_role($slug);
        $user->add_role('non_adherent');
    }

    remove_role($slug);
    update_option('mps_tools_managed_roles', array_values(array_diff($managed, [$slug])), false);

    mps_tools_roles_redirect(['updated' => 1]);
});