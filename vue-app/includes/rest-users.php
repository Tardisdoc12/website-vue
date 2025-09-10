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
// End of File
//------------------------------------------------------------------------------