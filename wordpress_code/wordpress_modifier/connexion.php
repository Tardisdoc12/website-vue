<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: connexion.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-21
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;



//--------------------------------------------------------------------------------------------------
// Functions OR CLASS

add_filter('determine_current_user', function($user_id) {
    $auth_header = null;

    if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
        $auth_header = $_SERVER['HTTP_AUTHORIZATION'];
    } elseif (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
        $auth_header = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
    } elseif (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
        if (!empty($headers['Authorization'])) {
            $auth_header = $headers['Authorization'];
        }
    }

    // Si un token JWT est présent, il prend systématiquement le dessus sur le cookie
    if ($auth_header && preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
        $token = $matches[1];
        $verified_payload = AssoSimpleJWT::verify($token);

        if ($verified_payload && !empty($verified_payload['wp_user_id'])) {
            $candidate_id = absint($verified_payload['wp_user_id']);
            if (get_userdata($candidate_id)) {
                return $candidate_id;
            }
        }
    }

    // Aucun JWT valide : comportement normal (cookie WordPress classique)
    return $user_id;
}, 20);

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------