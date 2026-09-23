<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: users.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-20
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;

require_once MPS_TOOLS_FUNCTIONS_DIR . 'mailing.php';

//--------------------------------------------------------------------------------------------------
// Functions OR CLASS

function monplugin_get_users(WP_REST_Request $request) {
    global $wpdb;
    $table = $wpdb->prefix . "users";
    $users = $wpdb->get_results("SELECT ID FROM $table");

    foreach ($users as &$user) {
        $user->firstName  = get_user_meta($user->ID, 'firstName', true);
        $user->lastName   = get_user_meta($user->ID, 'lastName', true);
        $user->telephone  = get_user_meta($user->ID, 'telephone', true);
        $user->urgence_phone = get_user_meta($user->ID, 'urgence_phone', true);
        $user->urgence_name  = get_user_meta($user->ID, 'urgence_name', true);
    }

    return [
        "message" => "Success",
        "users"   => $users
    ];
}

//--------------------------------------------------------------------------------------------------

function monplugin_get_adherents(WP_REST_Request $request) {
    $users = get_users([
        'role__in' => ['administrator', 'bureau', 'encadrant','adherent'],
    ]);

    $result = [];

    foreach ($users as $user) {
        $result[] = [
            'ID'            => $user->ID,
            'firstName'     => get_user_meta($user->ID, 'firstName', true),
            'lastName'      => get_user_meta($user->ID, 'lastName', true),
            'telephone'     => get_user_meta($user->ID, 'telephone', true),
            'email'         => $user->user_email,
            'urgence_phone' => get_user_meta($user->ID, 'urgence_phone', true),
            'urgence_name'  => get_user_meta($user->ID, 'urgence_name', true),
            'roles'         => $user->roles,
        ];
    }

    return [
        "message" => "Success",
        "users"   => $result
    ];
}

//--------------------------------------------------------------------------------------------------

function monplugin_get_user(WP_REST_Request $request) {
    $user_id = intval($request['id']);
    $user = get_userdata($user_id);

    if (!$user) {
        return [
            "message" => "Utilisateur non trouvé",
            "user"    => null
        ];
    }

    // Construction de l'objet utilisateur
    $user_data = [
        "ID"            => $user->ID,
        "email"         => $user->user_email,
        "firstName"     => get_user_meta($user->ID, 'firstName', true),
        "lastName"      => get_user_meta($user->ID, 'lastName', true),
        "telephone"     => get_user_meta($user->ID, 'telephone', true),
        "roles"         => $user->roles,
        "urgence_phone" => get_user_meta($user->ID, 'urgence_phone', true),
        "urgence_name"  => get_user_meta($user->ID, 'urgence_name', true),
    ];

    return [
        "message" => "Success",
        "user"    => $user_data
    ];
}

//--------------------------------------------------------------------------------------------------

function monplugin_get_user_connected(WP_REST_Request $request) {
    global $wpdb;
    $table_inscrits = $wpdb->prefix . "inscrits";
    $table_users    = $wpdb->prefix . "users_inscrits";
    $table_events   = $wpdb->prefix . "events";

    $user_id = get_current_user_id();
    if (!$user_id) {
        return [
            "message" => "Utilisateur non connecté",
            "user"    => null
        ];
    }

    $user = get_userdata($user_id);

    if (!$user) {
        return [
            "message" => "Utilisateur non trouvé",
            "user"    => null
        ];
    }

    $email = $user->user_email;

    // Une seule requête : on récupère directement les events complets, pas juste leurs IDs
    $events = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT 
                e.id AS event_id,
                e.title,
                e.start_date,
                e.end_date,
                e.place,
                e.category,
                e.description,
                e.subscribe_places,
                e.nonsubscribe_places,
                e.attente_places,
                e.closed_inscription,
                e.adherent_price,
                e.non_adherent_price,
                e.payement_title,
                e.update_date
             FROM $table_inscrits i
             JOIN $table_users u ON u.id = i.user_id
             JOIN $table_events e ON e.id = i.event_id
             WHERE u.email = %s",
            $email
        )
    );

    $user_data = [
        "ID"            => $user->ID,
        "email"         => $user->user_email,
        "firstName"     => get_user_meta($user->ID, 'firstName', true),
        "lastName"      => get_user_meta($user->ID, 'lastName', true),
        "telephone"     => get_user_meta($user->ID, 'telephone', true),
        "roles"         => $user->roles,
        "urgence_phone" => get_user_meta($user->ID, 'urgence_phone', true),
        "urgence_name"  => get_user_meta($user->ID, 'urgence_name', true),
        "events"        => $events,
    ];

    return [
        "message" => "Success",
        "user"    => $user_data
    ];
}

//--------------------------------------------------------------------------------------------------

function mps_tools_register_user(WP_REST_Request $request) {
    $username = sanitize_user($request->get_param('username'));
    $email    = sanitize_email($request->get_param('email'));
    $password = $request->get_param('password');
    $firstName = sanitize_text_field($request->get_param('firstName'));
    $lastName  = sanitize_text_field($request->get_param('lastName'));
    $telephone  = sanitize_text_field($request->get_param('telephone'));

    if (empty($username) || empty($email) || empty($password)) {
        return new WP_Error('missing_fields', 'Tous les champs sont obligatoires', ['status' => 400]);
    }

    if (username_exists($username)){
        $username .= "." . substr($telephone, -4);
    }

    if (username_exists($username) || email_exists($email)) {
        return new WP_Error('user_exists', 'Utilisateur déjà existant', ['status' => 400]);
    }

    $user_id = wp_create_user($username, $password, $email);

    if (is_wp_error($user_id)) {
        return $user_id;
    }

    update_user_meta($user_id, 'telephone', $telephone);
    update_user_meta($user_id, 'firstName', $firstName);
    update_user_meta($user_id, 'lastName', $lastName);

    return [
        'success' => true,
        'user_id' => $user_id,
        'role'    => get_userdata($user_id)->roles,
    ];
}

//--------------------------------------------------------------------------------------------------

function monplugin_search_user(WP_REST_Request $request) {
    $query = sanitize_text_field($request->get_param('q'));

    if (empty($query)) {
        return new WP_Error('missing_query', 'Paramètre q obligatoire', ['status' => 400]);
    }

    // Cherche par email
    $user = get_user_by('email', $query);

    // Sinon cherche par téléphone
    if (!$user) {
        $users_by_phone = get_users([
            'meta_key'   => 'telephone',
            'meta_value' => $query,
            'number'     => 1,
            'fields'     => 'all',
        ]);
        $user = !empty($users_by_phone) ? $users_by_phone[0] : null;
    }

    if (!$user) {
        return [
            "message" => "Utilisateur non trouvé",
            "user"    => null
        ];
    }

    return [
        "message" => "Success",
        "user"    => [
            "ID"        => $user->ID,
            "email"     => $user->user_email,
            "firstName" => get_user_meta($user->ID, 'firstName', true),
            "lastName"  => get_user_meta($user->ID, 'lastName', true),
            "telephone" => get_user_meta($user->ID, 'telephone', true),
            "roles"     => $user->roles,
        ]
    ];
}

//--------------------------------------------------------------------------------------------------

function mps_tools_update_user(WP_REST_Request $request) {

    // 🔐 utilisateur connecté obligatoire
    $user_id = get_current_user_id();
    if (!$user_id) {
        return new WP_Error('not_logged_in', 'Utilisateur non connecté', ['status' => 401]);
    }

    // Données de base
    $email         = sanitize_email($request->get_param('email'));
    $firstName     = sanitize_text_field($request->get_param('firstName'));
    $lastName      = sanitize_text_field($request->get_param('lastName'));
    $telephone     = sanitize_text_field($request->get_param('telephone'));
    $urgence_phone = sanitize_text_field($request->get_param('urgence_phone'));
    $urgence_name  = sanitize_text_field($request->get_param('urgence_name'));

    // Validation minimale
    if (empty($email)) {
        return new WP_Error('missing_email', 'Email obligatoire', ['status' => 400]);
    }

    // Vérifier email déjà utilisé par un autre utilisateur
    $existing_user = get_user_by('email', $email);
    if ($existing_user && $existing_user->ID !== $user_id) {
        return new WP_Error('email_exists', 'Email déjà utilisé', ['status' => 400]);
    }

    // Mise à jour du user WP
    $user_update = wp_update_user([
        'ID'         => $user_id,
        'user_email' => $email,
        'first_name' => $firstName,
        'last_name'  => $lastName,
    ]);

    if (is_wp_error($user_update)) {
        error_log("Erreur lors de la mise à jour de l'utilisateur ID $user_id : " . print_r($user_update, true));
        return $user_update;
    }

    // Mise à jour des metas
    update_user_meta($user_id, 'telephone', $telephone);
    update_user_meta($user_id, 'urgence_phone', $urgence_phone);
    update_user_meta($user_id, 'urgence_name', $urgence_name);
    update_user_meta($user_id, 'firstName', $firstName);
    update_user_meta($user_id, 'lastName', $lastName);

    return [
        'success' => true,
        'user_id' => $user_id,
    ];
}

//--------------------------------------------------------------------------------------------------

function mps_tools_reset_password(WP_REST_Request $request) {
    $email = sanitize_email($request->get_param('email'));

    if (empty($email)) {
        return new WP_Error('missing_email', 'Email obligatoire', ['status' => 400]);
    }

    $user = get_user_by('email', $email);
    if (!$user) {
        return new WP_Error('user_not_found', 'Utilisateur non trouvé', ['status' => 404]);
    }

    $reset_url_base = get_option('mps_tools_rest_url', site_url('/reset-password'));

    // Générer une key pour le lien de réinitialisation (optionnel, peut être utilisé pour vérifier la validité du reset)
    $key = get_password_reset_key($user);

    if (is_wp_error($key)) {
        error_log('get_password_reset_key error: ' . $key->get_error_message());
        return new WP_Error('reset_key_error', 'Erreur lors de la génération du lien.', ['status' => 500]);
    }

    $reset_url = add_query_arg([
        'key'   => $key,
        'login' => rawurlencode($user->user_login),
    ], $reset_url_base);


    $template_body = get_option(
        'mps_tools_mail_password_recuperation',
        "Cliquez ici pour réinitialiser votre mot de passe :\n\n{{url_reset}}\n\nSi vous n'avez pas demandé cette réinitialisation, ignorez cet email."
    );
    $template_body = mps_tools_render_email_template($template_body, ['url_reset' => $reset_url]);

    // Envoyer un email à l'utilisateur avec le nouveau mot de passe
    $subject = get_option('mps_tools_mail_password_recuperation_objet', 'Votre nouveau mot de passe');
    $mail_sent = mps_tools_send_email(
        $user->user_email,
        $subject,
        $template_body
    );

    if (!$mail_sent) {
        error_log("mps_tools_reset_password : échec d'envoi d'email pour {$user->user_email}");
    }

    return [
        'success' => true,
        'message' => 'Un email de réinitialisation a été envoyé si l\'adresse existe dans notre système.',
    ];
}

//--------------------------------------------------------------------------------------------------

function mps_tools_check_reset_key(WP_REST_Request $request) {
    $key = $request->get_param('key');
    $login = $request->get_param('login');

    $user = check_password_reset_key($key, $login);

    if (is_wp_error($user)) {
        return new WP_Error('invalid', 'Lien invalide ou expiré', ['status' => 400]);
    }

    return ['success' => true];
}

//--------------------------------------------------------------------------------------------------

function mps_tools_reset_password_properly(WP_REST_Request $request) {
    $login    = sanitize_user($request->get_param('login'));
    $key      = sanitize_text_field($request->get_param('key'));
    $password = $request->get_param('password');

    if (!$login || !$key || !$password) {
        return new WP_Error('missing_fields', 'Champs manquants', ['status' => 400]);
    }

    if (strlen($password) < 8) {
        return new WP_Error('weak_password', 'Mot de passe trop court', ['status' => 400]);
    }

    // 🔐 Vérification officielle WordPress
    $user = check_password_reset_key($key, $login);

    if (is_wp_error($user)) {
        return new WP_Error('invalid_key', 'Lien invalide ou expiré', ['status' => 400]);
    }

    // ✅ Reset sécurisé
    reset_password($user, $password);

    return [
        'success' => true,
        'message' => 'Mot de passe mis à jour avec succès',
    ];
}

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------