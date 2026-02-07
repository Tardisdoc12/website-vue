<?php
/*
*
* Gère les utilisateurs 
*
*/
if (!defined('ABSPATH')) exit;

//------------------------------------------------------------------------------
// IMPORTS

$file = "functions.php";
require_once plugin_dir_path(__FILE__) . $file;

//------------------------------------------------------------------------------


add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1','/users',[
        'methods' => 'GET',
        'callback' => 'monplugin_get_users',
        'permission_callback' => 'monplugin_verify_csrf'
    ]);
});

function monplugin_get_users(WP_REST_Request $request) {
    global $wpdb;
    $table = $wpdb->prefix . "users";
    $users = $wpdb->get_results("SELECT ID FROM $table");

    foreach ($users as &$user) {
        $user->firstName  = get_user_meta($user->ID, 'firstName', true);
        $user->lastName   = get_user_meta($user->ID, 'lastName', true);
        $user->telephone  = get_user_meta($user->ID, 'telephone', true);
        $user->moto       = get_user_meta($user->ID, 'moto', true);
        $user->urgence_phone = get_user_meta($user->ID, 'urgence_phone', true);
        $user->urgence_name  = get_user_meta($user->ID, 'urgence_name', true);
    }

    return [
        "message" => "Success",
        "users"   => $users
    ];
}

//------------------------------------------------------------------------------

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1','/users/(?P<id>\d+)',[
        'methods' => 'GET',
        'callback' => 'monplugin_get_user',
        'permission_callback' => 'monplugin_verify_csrf'
    ]);
});

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
        "ID"        => $user->ID,
        "email"     => $user->user_email,
        "firstName" => get_user_meta($user->ID, 'firstName', true),
        "lastName"  => get_user_meta($user->ID, 'lastName', true),
        "telephone" => get_user_meta($user->ID, 'telephone', true),
        "moto"      => get_user_meta($user->ID, 'moto', true),
        "roles"     => $user->roles,
        "urgence_phone" => get_user_meta($user->ID, 'urgence_phone', true),
        "urgence_name"  => get_user_meta($user->ID, 'urgence_name', true),
    ];

    return [
        "message" => "Success",
        "user"    => $user_data
    ];
}

//--------------------------------------------------------------------------------------

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/register', [
        'methods' => 'POST',
        'callback' => 'myplugin_register_user',
        'permission_callback' => 'monplugin_verify_csrf',
    ]);
});

function myplugin_register_user(WP_REST_Request $request) {
    $username = sanitize_user($request->get_param('username'));
    $email    = sanitize_email($request->get_param('email'));
    $password = $request->get_param('password');
    $firstName = sanitize_text_field($request->get_param('firstName'));
    $lastName  = sanitize_text_field($request->get_param('lastName'));
    $telephone  = sanitize_text_field($request->get_param('telephone'));
    $moto       = sanitize_text_field($request->get_param('moto'));

    if (empty($username) || empty($email) || empty($password)) {
        return new WP_Error('missing_fields', 'Tous les champs sont obligatoires', ['status' => 400]);
    }

    if (username_exists($username)){
        $username .= "." . substr($telephone, -4);
    }

    $existing_users = get_users([
        'meta_key' => 'telephone',
        'meta_value' => $telephone,
        'number' => 1,
    ]);


    if (username_exists($username) || email_exists($email) || !empty($existing_users)) {
        return new WP_Error('user_exists', 'Utilisateur déjà existant', ['status' => 400]);
    }

    $user_id = wp_create_user($username, $password, $email);

    if (is_wp_error($user_id)) {
        return $user_id;
    }

    update_user_meta($user_id, 'moto', $moto);
    update_user_meta($user_id, 'telephone', $telephone);
    update_user_meta($user_id, 'firstName', $firstName);
    update_user_meta($user_id, 'lastName', $lastName);

    return [
        'success' => true,
        'user_id' => $user_id,
        'role'    => get_userdata($user_id)->roles,
    ];
}

//------------------------------------------------------------------------------

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/user/update', [
        'methods'  => 'POST',
        'callback' => 'myplugin_update_user',
        'permission_callback' => 'monplugin_verify_csrf',
    ]);
});

function myplugin_update_user(WP_REST_Request $request) {

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
    $moto          = sanitize_text_field($request->get_param('moto'));
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
        return $user_update;
    }

    // Mise à jour des metas
    update_user_meta($user_id, 'telephone', $telephone);
    update_user_meta($user_id, 'moto', $moto);
    update_user_meta($user_id, 'urgence_phone', $urgence_phone);
    update_user_meta($user_id, 'urgence_name', $urgence_name);

    return [
        'success' => true,
        'user_id' => $user_id,
    ];
}

//------------------------------------------------------------------------------

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/psswd/reset', [
        'methods'  => 'POST',
        'callback' => 'myplugin_reset_password',
        'permission_callback' => 'monplugin_verify_csrf',
    ]);
});

function myplugin_reset_password(WP_REST_Request $request) {
    $email = sanitize_email($request->get_param('email'));

    if (empty($email)) {
        return new WP_Error('missing_email', 'Email obligatoire', ['status' => 400]);
    }

    $user = get_user_by('email', $email);
    if (!$user) {
        return new WP_Error('user_not_found', 'Utilisateur non trouvé', ['status' => 404]);
    }

    // Générer une key pour le lien de réinitialisation (optionnel, peut être utilisé pour vérifier la validité du reset)
    $key = get_password_reset_key($user);


    $reset_url = add_query_arg([
        'key'   => $key,
        'login' => rawurlencode($user->user_login),
    ], site_url('/reset-password'));

    // Envoyer un email à l'utilisateur avec le nouveau mot de passe
    $subject = 'Votre nouveau mot de passe';
    wp_mail(
        $user->user_email,
        $subject,
        "Cliquez ici pour réinitialiser votre mot de passe :\n\n$reset_url\n\nSi vous n'avez pas demandé cette réinitialisation, ignorez cet email."
    );

    return [
        'success' => true,
        'message' => 'Un email de réinitialisation a été envoyé si l\'adresse existe dans notre système.',
    ];
}

//------------------------------------------------------------------------------

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/check-reset-key', [
        'methods' => 'POST',
        'callback' => 'myplugin_check_reset_key',
        'permission_callback' => '__return_true',
    ]);
});

function myplugin_check_reset_key(WP_REST_Request $request) {
    $key = $request->get_param('key');
    $login = $request->get_param('login');

    $user = check_password_reset_key($key, $login);

    if (is_wp_error($user)) {
        return new WP_Error('invalid', 'Lien invalide ou expiré', ['status' => 400]);
    }

    return ['success' => true];
}

//------------------------------------------------------------------------------

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/password', [
        'methods' => 'POST',
        'callback' => 'myplugin_reset_password_properly',
        'permission_callback' => '__return_true',
    ]);
});

function myplugin_reset_password_properly(WP_REST_Request $request) {
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


//------------------------------------------------------------------------------
// End of File
//------------------------------------------------------------------------------