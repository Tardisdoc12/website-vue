<?php

if (!defined('ABSPATH')) exit;

require_once MPS_TOOLS_ADMIN_DIR . 'promos_code_gestion.php';

add_action('admin_menu', function () {
    add_submenu_page(
        'options-general.php',
        'Codes Promo MPS Tools',
        'Codes Promo MPS Tools',
        'manage_options',
        'mps-tools-promos',
        'mps_tools_render_promos_page'
    );
});

function mps_tools_render_promos_page() {
    if (!current_user_can('manage_promo_code')) wp_die('Accès refusé.');

    $promos = mps_tools_get_promos();
    $events = mps_tools_get_events_for_promo();
    $categories = get_option('mps_tools_categories', []);
    ?>
    <div class="wrap">
        <h1>Codes Promo MPS Tools</h1>
        <?php if (isset($_GET['updated'])): ?>
            <div class="notice notice-success"><p>Modifications enregistrées.</p></div>
        <?php elseif (isset($_GET['error'])): ?>
            <div class="notice notice-error"><p><?php echo esc_html(sanitize_text_field(wp_unslash($_GET['error']))); ?></p></div>
        <?php endif; ?>
        <?php foreach ($promos as $promo):
            $label = $promo->label;
        ?>
            <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" style="background:#fff;padding:12px 16px;margin-bottom:16px;border:1px solid #ccd0d4;">
                <input type="hidden" name="action" value="mps_tools_toggle_promo">
                <input type="hidden" name="make_active" value="<?php echo $promo->active ? '0' : '1'; ?>">
                <?php wp_nonce_field('mps_tools_toggle_promo_' . $promo->id); ?>

                <h2><?php echo esc_html($label); ?> <code><?php echo esc_html($promo->code_hash); ?></code></h2>
                <p>Valeur de la remise : <?php echo esc_html($promo->discount_value); ?></p>
                <p>Type de remise : <?php echo esc_html($promo->discount_type); ?></p>
                <p>Date d'expiration : <?php if ($promo->expiration_date && $promo->expiration_date!='0000-00-00') { echo esc_html($promo->expiration_date); } else { echo 'Aucune'; } ?></p>
                <p>Nombre maximum d'utilisations : <?php if ($promo->max_uses) { echo esc_html($promo->max_uses); } else { echo 'Illimité'; } ?></p>
                <p>ID de l'événement : <?php if ($promo->event_id) { echo esc_html($promo->event_id); } else { echo 'Aucun'; } ?></p>
                <p>Type d'événement : <?php if ($promo->event_type) { echo esc_html($promo->event_type); } else { echo 'Aucun'; } ?></p>
                <p>
                    <button type="submit" class="button"><?php echo $promo->active ? 'Désactiver' : 'Activer'; ?></button>
                    <a class="button button-link-delete"
                       href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=mps_tools_delete_promo&promo_id=' . $promo->id), 'mps_tools_delete_promo_' . $promo->id)); ?>"
                       onclick="return confirm('Supprimer ce code promo ?');">Supprimer</a>
                </p>
            </form>
        <?php endforeach; ?>
        <h2>Créer un code promo</h2>
        <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
            <input type="hidden" name="action" value="mps_tools_create_promo">
            <?php wp_nonce_field('mps_tools_create_promo'); ?>
            <p><input type="text" name="promo_label" placeholder="Nom du code promo" required></p>
            <p><input type="text" name="promo_code" placeholder="Code promo" required></p>
            <p><input type="number" name="discount_value" placeholder="Valeur de la remise" required></p>
            <p>
                <select name="discount_type" required>
                    <option value="percent">Pourcentage</option>
                    <option value="fixed">Montant fixe</option>
                </select>
            </p>
            <p><input type="date" name="expiration_date" placeholder="Date d'expiration"></p>
            <p><input type="number" name="max_uses" placeholder="Nombre maximum d'utilisations"></p>
            <p>
                <select name="category_name" required>
                    <option value="">Sélectionner une catégorie</option>
                    <?php foreach ($categories as $category): ?>
                        <option value="<?php echo esc_attr($category['nom']); ?>">
                            <?php echo esc_html($category['nom']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>
            <p>
                <select name="event_id">
                    <option value="">Sélectionner un événement</option>
                    <?php foreach ($events as $event): ?>
                        <option value="<?php echo esc_attr($event->id); ?>">
                            <?php echo esc_html($event->title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </p>
            <p><button type="submit" class="button button-primary">Créer</button></p>
        </form>
    </div>
    <?php
}