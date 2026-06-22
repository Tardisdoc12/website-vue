<?php
/*
*
* Gère les notifications HelloAsso
*
*/
if (!defined('ABSPATH')) exit;

//------------------------------------------------------------------------------

add_action('rest_api_init', function () {
    register_rest_route('helloasso/v1', '/notification', [
        'methods'             => 'POST',
        'callback'            => 'myplugin_handle_helloasso_notification',
        'permission_callback' => '__return_true',
    ]);
});

function myplugin_handle_helloasso_notification(WP_REST_Request $request) {
    $data = $request->get_json_params();

    // Vérifiez si les données sont valides
    if (!isset($data['eventType']) || !isset($data['data'])) {
        return new WP_Error('invalid_data', 'Données invalides : eventType ou payer manquant.', ['status' => 400]);
    }

    $eventType = $data['eventType'];
    $payload  = $data['data'];

    if ($eventType === 'Form' && $payload['formType'] === 'Event') {
        error_log('Notification de création de formulaire reçue : ' . print_r($payload, true));
        treat_creation_form_notification($payload);
    }

    $payer = $payload['payer'] ?? null;
    if (!$payer) {
        return new WP_Error('invalid_data', 'Données invalides : payer manquant.', ['status' => 400]);
    }

    // Récupérer l'ID de l'utilisateur WordPress à partir de l'adresse e-mail du payeur
    $user = get_user_by('email', $payer['email']);
    if (!$user) {
        return new WP_Error('user_not_found', 'Utilisateur non trouvé pour l\'email fourni.', ['status' => 404]);
    }

    // Vous pouvez maintenant traiter la notification en fonction du type d'événement
    if ($eventType === 'Order') {
        treat_order_notification($payload);
    }
    else if ($eventType === 'Payment') {
        treat_payment_notification($payload, $user);

    }
    else {
        return new WP_Error('unknown_event', 'Type d\'événement inconnu.', ['status' => 400]);
    }

    return ['success' => true];
}

//------------------------------------------------------------------------------

function treat_order_notification($data) {
    // Traitez la notification de commande ici
    error_log('en attente de paiement');
}

//------------------------------------------------------------------------------

function treat_payment_notification($data, $user) {
    // Traitez la notification de paiement ici
    error_log('Notification de paiement reçue pour l\'utilisateur : ' . $user->user_login);
    error_log('paiement effectué');

    if ($data['state'] != 'Authorized') {
        error_log('paiement non autorisé');
        return new WP_Error('payment_not_authorized', 'Paiement non autorisé.', ['status' => 400]);
    }

    $formSlug = $data['order']['formSlug'] ?? null;
    if (!$formSlug) {
        return new WP_Error('invalid_data', 'Données invalides : formSlug manquant.', ['status' => 400]);
    }

    if ($formSlug === get_option('helloasso_form_slug_adherent','devenir-adherent')) {
        treat_adherent_payment($user);
    } else {
        treat_inscription_payement($data, $user);
    }
}

//------------------------------------------------------------------------------

function treat_adherent_payment($user) {
    // Marquez l'utilisateur comme ayant payé

    $user->add_role('adherent');
    $user->remove_role('non_adherent');
    update_user_meta($user->ID, 'subscriber_date', current_time('mysql'));

    return ['success' => true, 'message' => 'Utilisateur ayant son rôle mis à jour.'];
}

//------------------------------------------------------------------------------

function treat_inscription_payement($data, $user) {
    global $wpdb;
    // Traitez le paiement d'inscription ici
    error_log('Traitement du paiement d\'inscription pour l\'utilisateur : ' . $user->user_login);
    // Vous pouvez ajouter votre logique de traitement ici, par exemple, mettre à jour la base de données, envoyer un e-mail, etc.
    $table_events = $wpdb->prefix . "events";
    $table_users_inscrits = $wpdb->prefix . "users_inscrits";
    $table_inscrits = $wpdb->prefix . "inscrits";
    $table_billeterie = $wpdb->prefix . "billetteries";

    $event_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM $table_events WHERE billeterie_id = (SELECT id FROM $table_billeterie WHERE slug = %s)",
            $data['order']['formSlug']
        )
    );

    if (!$event_id) {
        error_log('Événement non trouvé pour le formSlug : ' . $data['order']['formSlug']);
        return new WP_Error('event_not_found', 'Événement non trouvé pour le formSlug fourni.', ['status' => 404]);
    }

    $user_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM $table_users_inscrits WHERE email = %s",
            $user->user_email
        )
    );

    if (!$user_id) {
        error_log('Utilisateur inscrit non trouvé pour l\'email : ' . $user->user_email);
        return new WP_Error('user_inscrit_not_found', 'Utilisateur inscrit non trouvé pour l\'email fourni.', ['status' => 404]);
    }

    $inscription_id = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT id FROM $table_inscrits WHERE user_id = %d AND event_id = %d",
            $user_id,
            $event_id
        )
    );

    if (!$inscription_id) {
        error_log('Inscription non trouvée pour l\'utilisateur et l\'événement : ' . $user_id . ', ' . $event_id);
        return new WP_Error('inscription_not_found', 'Inscription non trouvée pour l\'utilisateur et l\'événement.', ['status' => 404]);
    }

    $result = $wpdb->update(
        $table_inscrits,
        ['payement_status' => 'completed'],
        ['id' => $inscription_id],
        ['%s'],
        ['%d']
    );

    if ($result === false) {
        error_log('Erreur lors de la mise à jour du statut de paiement : ' . $wpdb->last_error);
        return new WP_Error('db_update_error', 'Erreur lors de la mise à jour du statut de paiement.', ['status' => 500]);
    }

    if ($result === 0) {
        error_log('Aucune ligne mise à jour pour l\'inscription : ' . $inscription_id);
        return new WP_Error('no_rows_updated', 'Aucune ligne mise à jour pour l\'inscription.', ['status' => 400]);
    }

    return [
        'success' => true,
        'message' => 'Statut de paiement mis à jour avec succès.',
        'user_id' => $user_id,
        'event_id' => $event_id,
        'inscription_id' => $inscription_id
    ];

}

//------------------------------------------------------------------------------

function treat_creation_form_notification($data) {
    global $wpdb;
    // Traitez la notification de création de formulaire ici
    error_log('Notification de création de formulaire reçue : ' . print_r($data, true));
    $table_billeterie = $wpdb->prefix . "billetteries";

    $title = $data['title'] ?? null;
    $slug = $data['formSlug'] ?? null;
    $url = $data['url'] ?? null;

    error_log('Titre du formulaire : ' . $title);
    error_log('Slug du formulaire : ' . $slug);
    error_log('URL du formulaire : ' . ($url ?? 'N/A'));

    $result = $wpdb->query(
        $wpdb->prepare(
            "INSERT INTO $table_billeterie (title, slug, url) VALUES (%s, %s, %s)
            ON DUPLICATE KEY UPDATE title = VALUES(title), url = VALUES(url)",
            $title, $slug, $url
        )
    );

    return ['success' => true, 'message' => 'Formulaire inséré avec succès.', 'formulaire' => $result];
}

//------------------------------------------------------------------------------
// End of File
//------------------------------------------------------------------------------