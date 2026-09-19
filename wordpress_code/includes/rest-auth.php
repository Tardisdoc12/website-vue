<?php
/*
*
* Gère les connections des utilisateurs
*
*/
if (!defined('ABSPATH')) exit;

//------------------------------------------------------------------------------
// IMPORTS

require_once MPS_TOOLS_FUNCTIONS_DIR . 'route_callback.php';
require_once MPS_TOOLS_OBJECTS_DIR . "jwt_generator.php";

//------------------------------------------------------------------------------

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/connect', [
        'methods' => 'POST',
        'callback' => 'mps_tools_login_user',
        'permission_callback' => 'mps_tools_verify_csrf_and_jwt',
    ]);
});

function mps_tools_login_user(WP_REST_Request $request) {
    $email    = sanitize_email($request->get_param('email'));
    $password = $request->get_param('password');

    if (empty($email) || empty($password)) {
        return new WP_Error(
            'missing_credentials',
            'Email et mot de passe requis.',
            ['status' => 400]
        );
    }

    // wp_authenticate accepte un login OU un email dans son premier paramètre
    $user = wp_authenticate($email, $password);

    if (is_wp_error($user)) {
        // On volontairement un message générique (voir explication plus bas)
        return new WP_Error(
            'invalid_credentials',
            'Email ou mot de passe incorrect.',
            ['status' => 403]
        );
    }

    $connexion_time = get_option('JWT_TIME_CONNEXION', 3600);

    // À ce stade, $user est un objet WP_User valide
    $token = AssoSimpleJWT::generate([
        'wp_user_id' => $user->ID,
    ], $connexion_time);

    return rest_ensure_response([
        'token'   => $token,
        'user_id' => $user->ID,
        'email'   => $user->user_email,
        'display_name' => $user->display_name,
    ]);
}

//------------------------------------------------------------------------------
// End of File
//------------------------------------------------------------------------------