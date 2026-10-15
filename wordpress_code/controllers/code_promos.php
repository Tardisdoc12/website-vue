<?php

if (!defined('ABSPATH')) exit;

function mps_tools_verify_promo(WP_REST_Request $request) {
    global $wpdb;
    $promo_code = $request->get_param('promo_code');
    $event_id = $request->get_param('event_id');
    $event_categorie = $request->get_param('event_categorie');
    $table_code_promo = $wpdb->prefix . 'promo_codes';

    $promo = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_code_promo WHERE code_hash = %s",
        hash('sha256', strtoupper(trim($promo_code))),
    ));

    if (!$promo) {
        return [
            'success' => false,
            'message' => 'Invalid promo code.',
        ];
    }

    if (($promo->event_id != $event_id && $event_categorie == null) || ($promo->event_type != $event_categorie && $event_id == null)) {
        return [
            'success' => false,
            'message' => 'Promo code is not valid for this event or this kind of event.',
        ];
    }

    if ($promo->active=="0") {
        return [
            'success' => false,
            'message' => 'Promo code is inactive.',
        ];
    }
    $has_date = !empty($promo->expiration_date) && strpos($promo->expiration_date, '0000') !== 0;
    if ($has_date && current_time('Y-m-d') > substr($promo->expiration_date, 0, 10)) {
        return [
            'success' => false,
            'message' => 'Promo code has expired.',
        ];
    }
    
    if ($promo->max_uses && $promo->used_count + 1 > $promo->max_uses) {
        return [
            'success' => false,
            'message' => 'Promo code usage limit has been reached.',
        ];
    }

    return [
        'success' => true,
        'message' => 'Promo code verified successfully.',
        'promo' => [
            'promo_value' => $promo->discount_value,
            'promo_type' => $promo->discount_type,
        ]
    ];
}