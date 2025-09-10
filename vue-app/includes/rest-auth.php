<?php
/*
*
* Gère les connections des utilisateurs
*
*/
if (!defined('ABSPATH')) exit;

//------------------------------------------------------------------------------
// IMPORTS

$file = "functions.php";
require_once plugin_dir_path(__FILE__) . $file;

//------------------------------------------------------------------------------

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/connect', [
        'methods' => 'POST',
        'callback' => 'myplugin_connect_user',
        'permission_callback' => 'monplugin_verify_csrf',
    ]);
});

function myplugin_connect_user(WP_REST_Request $request) {
    $user_id = $request->get_param('user_id');
    $user = get_user_by('id', $user_id);

    if (!$user) {
        return new WP_REST_Response(array(
            'message' => 'Utilisateur introuvable'
        ), 404);
    }

    // Définit l’utilisateur courant
    wp_set_current_user($user->ID);
    wp_set_auth_cookie($user->ID);

    return array(
        'message' => 'Utilisateur défini',
        'id' => $user->ID,
        'username' => $user->user_login
    );
}

//------------------------------------------------------------------------------
// End of File
//------------------------------------------------------------------------------