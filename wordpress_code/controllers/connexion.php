<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: connexion.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-19
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;

require_once MPS_TOOLS_OBJECTS_DIR . "jwt_generator.php";

//--------------------------------------------------------------------------------------------------
// Functions

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

    $roles = array_values(array_unique(array_map('sanitize_text_field', (array) $user->roles)));

    $is_non_adherent = in_array('non_adherent', $roles, true);
    $is_admin        = in_array('administrator', $roles, true);
    $date_adherent = get_user_meta($user->ID, 'subscriber_date', true);

    if (!$is_non_adherent && !$is_admin) {

        if (empty($date_adherent)) {
            // Pas de date : on démarre l'adhésion aujourd'hui
            update_user_meta($user->ID, 'subscriber_date', current_time('mysql'));
        }
        elseif (strtotime($date_adherent . ' +1 year') < strtotime(current_time('mysql'))) {
            // Adhésion expirée (plus d'un an)
            delete_user_meta($user->ID, 'subscriber_date');
            $user->add_role('non_adherent');
            $user->remove_role('adherent');
            $user->remove_role('bureau');
            $user->remove_role('encadrant');
        }
    }
    elseif ($is_non_adherent && !$is_admin && !empty($date_adherent)) {
        // L'utilisateur est non adhérent mais a une date d'adhésion : on la supprime
        delete_user_meta($user->ID, 'subscriber_date');
        $user->remove_role('adherent');
        $user->remove_role('bureau');
        $user->remove_role('encadrant');
    }

    return rest_ensure_response([
        'token'   => $token,
        'user_id' => $user->ID,
        'email'   => $user->user_email,
        'display_name' => $user->display_name,
    ]);
}

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------