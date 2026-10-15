<?php
/**
 * Gestion des codes promo MPS Tools
 * ce n'est pas des routes a proprement parler
 * Contient les fonctions de gestion des codes promo MPS Tools
 */


if (!defined('ABSPATH')) exit;

function mps_tools_get_promos() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'promo_codes';
    return $wpdb->get_results("SELECT * FROM $table_name");
}

function mps_tools_get_events_for_promo() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'events';
    return $wpdb->get_results("SELECT id, title FROM $table_name");
}


function mps_tools_create_promo($label, $code, $discount, $type, $expiration_date, $usage_limit, $event_id = null, $event_type = null) {
    global $wpdb;
    $ok = $wpdb->insert(
        $wpdb->prefix . 'promo_codes',
        [
            'label'           => $label,
            'code_hash'       => hash('sha256', strtoupper(trim($code))),
            'discount_value'  => $discount,
            'discount_type'   => $type,
            'expiration_date' => $expiration_date,
            'max_uses'        => $usage_limit,
            'event_id'        => $event_id,
            'event_type'      => $event_type,
            'active'          => 1,
        ]
    );
    if ($ok === false) {
        error_log('Promo insert error : ' . $wpdb->last_error);
        error_log('Promo insert query : ' . $wpdb->last_query);
    }
    return $ok !== false;
}

function mps_tools_verify_date($promo_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'promo_codes';
    $promo = $wpdb->get_row($wpdb->prepare("SELECT expiration_date FROM $table_name WHERE id = %d", $promo_id));
    if (!$promo) {
        return false;
    }
    $current_date = current_time('Y-m-d');
    return $current_date <= $promo->expiration_date;
}

function mps_tools_desactivate_promo($promo_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'promo_codes';
    $wpdb->update(
        $table_name,
        ['active' => 0],
        ['id' => $promo_id]
    );
}

function mps_tools_activate_promo($promo_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'promo_codes';
    $wpdb->update(
        $table_name,
        ['active' => 1],
        ['id' => $promo_id]
    );
}

function mps_tools_delete_promo($promo_id) {
    global $wpdb;
    $table_name = $wpdb->prefix . 'promo_codes';
    $wpdb->delete(
        $table_name,
        ['id' => $promo_id]
    );
}

function mps_tools_promos_redirect($args) {
    wp_safe_redirect(add_query_arg($args, admin_url('options-general.php?page=mps-tools-promos')));
    exit;
}

add_action('admin_post_mps_tools_create_promo', function () {
    if (!current_user_can('manage_options')) wp_die('Accès refusé.');
    check_admin_referer('mps_tools_create_promo');

    $label = sanitize_text_field(wp_unslash($_POST['promo_label'] ?? ''));
    $code  = strtoupper(trim(sanitize_text_field(wp_unslash($_POST['promo_code'] ?? ''))));
    $type  = sanitize_key($_POST['discount_type'] ?? '');
    $date  = sanitize_text_field(wp_unslash($_POST['expiration_date'] ?? ''));
    $event_type = sanitize_text_field($_POST['category_name'] ?? null);

    $raw_value = (float) ($_POST['discount_value'] ?? 0);
    $value = $type === 'fixed' ? (int) round($raw_value * 100) : (int) $raw_value; // euros -> centimes

    $max_uses = ($_POST['max_uses'] ?? '') !== '' ? absint($_POST['max_uses']) : null;
    $event_id = ($_POST['event_id'] ?? '') !== '' ? absint($_POST['event_id']) : null;

    if ($label === '' || strlen($code) < 6)
        mps_tools_promos_redirect(['error' => 'Nom ou code invalide (6 caractères minimum).']);
    if (!in_array($type, ['percent', 'fixed'], true))
        mps_tools_promos_redirect(['error' => 'Type de remise invalide.']);
    if ($value < 1 || ($type === 'percent' && $value > 100))
        mps_tools_promos_redirect(['error' => 'Valeur de remise invalide.']);

    if (!mps_tools_create_promo($label, $code, $value, $type, $date, $max_uses, $event_id, $event_type))
        mps_tools_promos_redirect(['error' => 'Création impossible (code déjà existant ?).']);

    mps_tools_promos_redirect(['updated' => 1]);
});

add_action('admin_post_mps_tools_delete_promo', function () {
    if (!current_user_can('manage_options')) wp_die('Accès refusé.');
    $id = absint($_GET['promo_id'] ?? 0);
    check_admin_referer('mps_tools_delete_promo_' . $id);

    mps_tools_delete_promo($id);
    mps_tools_promos_redirect(['updated' => 1]);
});

add_action('admin_post_mps_tools_toggle_promo', function () {
    if (!current_user_can('manage_options')) wp_die('Accès refusé.');
    $id = absint($_POST['promo_id'] ?? 0);
    check_admin_referer('mps_tools_toggle_promo_' . $id);

    ($_POST['make_active'] ?? '') === '1'
        ? mps_tools_activate_promo($id)
        : mps_tools_desactivate_promo($id);
    mps_tools_promos_redirect(['updated' => 1]);
});