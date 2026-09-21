<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: payement.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-19
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;



//--------------------------------------------------------------------------------------------------
// Functions OR CLASS

function mps_tools_finalize_helloasso_payment($checkout_intent_id, $source = 'unknown') {
    global $wpdb;
    $table_inscrits = $wpdb->prefix . 'inscrits';

    $pending = helloasso_get_pending_checkouts();
    $meta = $pending[$checkout_intent_id] ?? null;

    if (!$meta) {
        error_log("HelloAsso finalize [{$source}] : checkout_intent_id {$checkout_intent_id} introuvable dans le tracking.");
        return new WP_Error('checkout_not_found', 'Checkout intent inconnu ou déjà traité.', ['status' => 404]);
    }

    $inscription_ids = $meta['inscription_ids'];
    $type = $meta['type'] ?? 'event';

    $token = helloasso_get_access_token();
    if (!$token) {
        return new WP_Error('helloasso_auth_error', 'Authentification HelloAsso échouée.', ['status' => 500]);
    }

    $organizationSlug = get_option('helloasso_org_slug', '');
    $get_url = HELLOASSO_BASE_URL . "/v5/organizations/{$organizationSlug}/checkout-intents/{$checkout_intent_id}";

    $response = wp_remote_get($get_url, [
        'headers' => ['Authorization' => 'Bearer ' . $token],
        'timeout' => 15,
    ]);

    $code = null;
    $raw_body = null;

    if (!is_wp_error($response)) {
        $code = wp_remote_retrieve_response_code($response);
        $raw_body = wp_remote_retrieve_body($response);
    }

   $needs_fallback = is_wp_error($response) || $code !== 200;

    if ($needs_fallback) {
        error_log("HelloAsso finalize [{$source}] : bascule sur cURL direct pour checkout-intents GET.");

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $get_url);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer ' . $token]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $fallback_body = curl_exec($ch);
        $fallback_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $fallback_error = curl_error($ch);
        curl_close($ch);

        if ($fallback_error) {
            error_log("HelloAsso finalize [{$source}] : erreur cURL fallback - " . $fallback_error);
            return new WP_Error('helloasso_network_error', 'Erreur réseau HelloAsso.', ['status' => 500]);
        }

        $code = $fallback_code;
        $raw_body = $fallback_body;
    }

    $body = json_decode((string) $raw_body, true);

    if (empty($body['order'])) {
        error_log("HelloAsso finalize [{$source}] : pas de commande pour {$checkout_intent_id}, probablement pending/abandonné. Body: " . $raw_body);
        return ['success' => false, 'message' => 'Paiement non confirmé pour le moment.'];
    }

    $is_authorized = false;
    foreach (($body['order']['payments'] ?? []) as $payment) {
        if ($payment['state'] === 'Authorized') {
            $is_authorized = true;
            break;
        }
    }

    if (!$is_authorized) {
        error_log("HelloAsso finalize [{$source}] : commande trouvée mais aucun paiement Authorized pour {$checkout_intent_id}.");
        return ['success' => false, 'message' => 'Paiement non autorisé.'];
    }

    if ($type === 'adherent') {
        $result = treat_adherent_payment_by_inscription_id($inscription_ids);
    } else {
        $result = treat_inscription_payment_by_id($inscription_ids);
    }

    if (is_wp_error($result)) {
        error_log("HelloAsso finalize [{$source}] : erreur métier - " . $result->get_error_message());
        return $result;
    }

    helloasso_untrack_pending_checkout($checkout_intent_id);

    error_log("HelloAsso finalize [{$source}] : paiement {$checkout_intent_id} finalisé avec succès.");
    return ['success' => true, 'message' => 'Paiement finalisé.'];
}

//--------------------------------------------------------------------------------------------------


function treat_adherent_payment_by_inscription_id($inscription_ids) {
    global $wpdb;
    $table_inscrits = $wpdb->prefix . 'inscrits';
    $table_users_inscrits = $wpdb->prefix . 'users_inscrits';

    // On marque l'inscription comme payée
    $errors = [];
    foreach($inscription_ids as $inscription_id) {
        $result = $wpdb->update(
            $table_inscrits,
            ['payement_status' => 'completed'],
            ['id' => $inscription_id],
            ['%s'],
            ['%d']
        );
        if ($result === false) {
            $errors[] = $inscription_id;
        }
        // On récupère l'email de l'inscrit pour retrouver/créer le compte WP correspondant
        $inscrit = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT ui.email FROM $table_inscrits i
                JOIN $table_users_inscrits ui ON ui.id = i.user_id
                WHERE i.id = %d",
                $inscription_id
            )
        );

        if (!$inscrit) {
            return new WP_Error('inscrit_not_found', 'Inscrit introuvable pour cette inscription.', ['status' => 404]);
        }

        $user = get_user_by('email', $inscrit->email);

        if (!$user) {
            // Pas de compte WP existant : à toi de voir si on en crée un automatiquement ici
            // (cf. notre discussion précédente sur le cas "adhérent sans compte")
            error_log("Paiement adhérent confirmé pour {$inscrit->email}, mais aucun compte WP associé.");
            return ['success' => true, 'message' => 'Paiement confirmé, mais aucun compte WP à mettre à jour.'];
        }

        $user->add_role('adherent');
        $user->remove_role('non_adherent');
        update_user_meta($user->ID, 'subscriber_date', current_time('mysql'));    
    }

    
    return ['success' => true, 'user_id' => $user->ID];
}

//--------------------------------------------------------------------------------------------------

function treat_inscription_payment_by_id($inscription_ids) {
    global $wpdb;
    $table_inscrits = $wpdb->prefix . 'inscrits';


    $errors = [];
    foreach($inscription_ids as $inscription_id) {
        $result = $wpdb->update(
            $table_inscrits,
            ['payement_status' => 'completed'],
            ['id' => $inscription_id],
            ['%s'],
            ['%d']
        );
        if ($result === false) {
            $errors[] = $inscription_id;
        }
    }

    return ['success' => empty($errors), 'inscription_ids' => $inscription_ids, 'errors' => $errors];
}

//--------------------------------------------------------------------------------------------------

function helloasso_get_pending_checkouts() {
    return get_option('helloasso_pending_checkouts', []);
}

//--------------------------------------------------------------------------------------------------

function helloasso_untrack_pending_checkout($checkout_intent_id) {
    $pending = get_option('helloasso_pending_checkouts', []);
    if (isset($pending[$checkout_intent_id])) {
        unset($pending[$checkout_intent_id]);
        update_option('helloasso_pending_checkouts', $pending, false);
    }
}

//--------------------------------------------------------------------------------------------------

function helloasso_track_pending_checkout($checkout_intent_id, $inscription_ids, $event_id, $type = 'event') {
    $pending = get_option('helloasso_pending_checkouts', []);

    $pending[$checkout_intent_id] = [
        'inscription_ids' => $inscription_ids,
        'event_id'       => $event_id,
        'type'       => $type,
        'created_at' => time(),
    ];

    update_option('helloasso_pending_checkouts', $pending, false);
}

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------