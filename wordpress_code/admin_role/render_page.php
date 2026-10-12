<?php

if (!defined('ABSPATH')) exit;

require_once MPS_TOOLS_ADMIN_DIR . 'gestion.php';

add_action('admin_menu', function () {
    add_submenu_page(
        'options-general.php',
        'Rôles MPS Tools',
        'Rôles MPS Tools',
        'manage_options',
        'mps-tools-roles',
        'mps_tools_render_roles_page'
    );
});

function mps_tools_render_roles_page() {
    if (!current_user_can('manage_options')) wp_die('Accès refusé.');

    $caps      = mps_tools_available_caps();
    $managed   = get_option('mps_tools_managed_roles', []);
    $protected = mps_tools_protected_roles();
    ?>
    <div class="wrap">
        <h1>Rôles MPS Tools</h1>
        <?php if (isset($_GET['updated'])): ?>
            <div class="notice notice-success"><p>Modifications enregistrées.</p></div>
        <?php elseif (isset($_GET['error'])): ?>
            <div class="notice notice-error"><p><?php echo esc_html(sanitize_text_field(wp_unslash($_GET['error']))); ?></p></div>
        <?php endif; ?>

        <?php foreach ($managed as $slug):
            $role = get_role($slug);
            if (!$role) continue;
            $label = wp_roles()->roles[$slug]['name'] ?? $slug;
        ?>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="background:#fff;padding:12px 16px;margin-bottom:16px;border:1px solid #ccd0d4;">
                <input type="hidden" name="action" value="mps_tools_save_role">
                <input type="hidden" name="role_slug" value="<?php echo esc_attr($slug); ?>">
                <?php wp_nonce_field('mps_tools_save_role'); ?>

                <h2><?php echo esc_html($label); ?> <code><?php echo esc_html($slug); ?></code></h2>
                <?php foreach ($caps as $cap => $cap_label): ?>
                    <label style="display:block;margin:4px 0;">
                        <input type="checkbox" name="caps[]" value="<?php echo esc_attr($cap); ?>"
                            <?php checked($role->has_cap($cap)); ?>>
                        <?php echo esc_html($cap_label); ?>
                    </label>
                <?php endforeach; ?>

                <p>
                    <button type="submit" class="button button-primary">Enregistrer</button>
                    <?php if (!in_array($slug, $protected, true)): ?>
                        <a class="button button-link-delete"
                           href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=mps_tools_delete_role&role_slug=' . $slug), 'mps_tools_delete_role_' . $slug)); ?>"
                           onclick="return confirm('Supprimer ce rôle ? Ses utilisateurs passeront en non adhérent.');">Supprimer</a>
                    <?php endif; ?>
                </p>
            </form>
        <?php endforeach; ?>

        <h2>Créer un rôle</h2>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="mps_tools_create_role">
            <?php wp_nonce_field('mps_tools_create_role'); ?>
            <p><input type="text" name="role_label" placeholder="Nom du rôle (ex : Trésorier)" required></p>
            <?php foreach ($caps as $cap => $cap_label): ?>
                <label style="display:block;margin:4px 0;">
                    <input type="checkbox" name="caps[]" value="<?php echo esc_attr($cap); ?>"> <?php echo esc_html($cap_label); ?>
                </label>
            <?php endforeach; ?>
            <p><button type="submit" class="button button-primary">Créer</button></p>
        </form>
    </div>
    <?php
}