<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: route_callback.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-19
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;

require_once MPS_TOOLS_OBJECTS_DIR . 'jwt_generator.php';

//--------------------------------------------------------------------------------------------------
// Functions

function mps_tools_verify_csrf_and_jwt(WP_REST_Request $request) {
    // Vérification via X-WP-Nonce (classique WordPress)
    
    if (mps_tools_verify_csrf($request)) {
        return true;
    }

    $result = mps_tools_verify_jwt($request);
    if ($result['success']) {
        return true;
    }
    return $result['error'];
}

//--------------------------------------------------------------------------------------------------

function mps_tools_verify_csrf(WP_REST_Request $request) {
    $nonce = $request->get_header('X-WP-Nonce');
    if ($nonce && wp_verify_nonce($nonce, 'wp_rest')) {
        return true;
    }
    return false;
}

//--------------------------------------------------------------------------------------------------

function mps_tools_verify_jwt(WP_REST_Request $request) {
    $auth_header = $request->get_header('Authorization');
    if ($auth_header && preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
        $token = $matches[1];

        $verified_payload = AssoSimpleJWT::verify($token);

        if (!$verified_payload) {
            return ['success' => false, 'error' => new WP_Error(
                'invalid_jwt_token',
                'JWT token invalide ou expiré.',
                ['status' => 403]
            )];
        }

        // Le payload doit contenir un wp_user_id valide
        if (empty($verified_payload['wp_user_id'])) {
            return ['success' => false, 'error' => new WP_Error(
                'invalid_jwt_token',
                'JWT token invalide : utilisateur manquant.',
                ['status' => 403]
            )];
        }

        $user_id = absint($verified_payload['wp_user_id']);
        $user    = get_userdata($user_id);

        // Vérifie que l'utilisateur existe bien en base
        if (!$user) {
            return ['success' => false, 'error' => new WP_Error(
                'invalid_jwt_token',
                'JWT token invalide : utilisateur introuvable.',
                ['status' => 403]
            )];
        }

        // Optionnel mais recommandé : authentifier réellement l'utilisateur
        // pour que current_user_can(), get_current_user_id(), etc. fonctionnent
        wp_set_current_user($user_id);

        return ['success' => true];
    }
    return ['success' => false, 'error' => new WP_Error(
        'invalid_jwt_token',
        'JWT token invalide ou expiré.',
        ['status' => 403]
    )];
}

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------