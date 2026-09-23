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

require_once MPS_TOOLS_FUNCTIONS_DIR . 'payement.php';

//--------------------------------------------------------------------------------------------------
// Constants

const HELLOASSO_BASE_URL = 'https://api.helloasso.com';
const HELLOASSO_BASE_URL_TEST = 'https://api.helloasso-sandbox.com';
const HELLOASSO_TOKEN_TRANSIENT_KEY = 'helloasso_encrypted_access_token';
const HELLOASSO_TOKEN_TRANSIENT_KEY_TEST = 'helloasso_encrypted_access_token_test';

//--------------------------------------------------------------------------------------------------
// Functions OR CLASS

function helloasso_get_access_token() {
    $cached_encrypted = get_transient(HELLOASSO_TOKEN_TRANSIENT_KEY);

    if ($cached_encrypted !== false) {
        $token = mps_tools_decrypt($cached_encrypted);
        if ($token) {
            return $token;
        }
    }

    return helloasso_fetch_new_access_token();
}

//--------------------------------------------------------------------------------------------------

function helloasso_curl_fallback_post($url, $body_array) {
    if (!function_exists('curl_init')) {
        return false;
    }

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($body_array));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 15);

    $body = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        error_log('HelloAsso cURL fallback erreur : ' . $error);
        return false;
    }

    return ['code' => $code, 'body' => $body];
}

//--------------------------------------------------------------------------------------------------

/**
 * Va chercher un nouveau token auprès de HelloAsso et le met en cache chiffré.
 */
function helloasso_fetch_new_access_token() {
    $client_id = get_option('helloasso_client_id', '');
    $client_secret = get_option('helloasso_client_secret', '');

    if (empty($client_id) || empty($client_secret)) {
        error_log('HelloAsso : client_id ou client_secret manquant.');
        return false;
    }

    $token_url = HELLOASSO_BASE_URL . '/oauth2/token';
    $body_array = [
        'client_id'     => $client_id,
        'client_secret' => $client_secret,
        'grant_type'    => 'client_credentials',
    ];

    $response = wp_remote_post($token_url, [
        'headers' => ['Content-Type' => 'application/x-www-form-urlencoded'],
        'body'    => $body_array,
        'timeout' => 15,
    ]);

    $code = null;
    $raw_body = null;

    if (!is_wp_error($response)) {
        $code = wp_remote_retrieve_response_code($response);
        $raw_body = wp_remote_retrieve_body($response);
    }

    // Mauvais routage Azure connu (ou erreur réseau) : on retente via cURL direct
    $needs_fallback = is_wp_error($response) || $code !== 200;

    if ($needs_fallback) {
        error_log('HelloAsso : bascule sur cURL direct pour /oauth2/token.');
        $fallback = helloasso_curl_fallback_post($token_url, $body_array);

        if ($fallback) {
            $code = $fallback['code'];
            $raw_body = $fallback['body'];
        }
    }

    $data = json_decode((string) $raw_body, true);

    if ($code !== 200 || empty($data['access_token'])) {
        error_log("HelloAsso token : réponse invalide (code {$code}) - " . $raw_body);
        return false;
    }

    $expires_in = $data['expires_in'] ?? 1800;
    $encrypted = mps_tools_encrypt($data['access_token']);
    set_transient(HELLOASSO_TOKEN_TRANSIENT_KEY, $encrypted, max($expires_in - 60, 60));

    return $data['access_token'];
}

//--------------------------------------------------------------------------------------------------

/**
 * Chiffrement AES-256-CBC, clé dérivée des salts WordPress (jamais stockée séparément)
 */
function mps_tools_get_encryption_key() {
    // wp_salt('auth') est unique par installation WordPress et déjà stockée de façon sécurisée
    return hash('sha256', wp_salt('auth'), true); // 32 bytes, requis pour AES-256
}

//--------------------------------------------------------------------------------------------------

function mps_tools_encrypt($plain_text) {
    $key = mps_tools_get_encryption_key();
    $iv = random_bytes(16);
    $encrypted = openssl_encrypt($plain_text, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

    if ($encrypted === false) {
        error_log('Erreur de chiffrement.');
        return false;
    }

    return base64_encode($iv . $encrypted);
}

function mps_tools_decrypt($encoded) {
    $key = mps_tools_get_encryption_key();
    $data = base64_decode($encoded);

    if ($data === false || strlen($data) < 16) {
        return false;
    }

    $iv = substr($data, 0, 16);
    $encrypted = substr($data, 16);
    $decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

    return $decrypted !== false ? $decrypted : false;
}

//--------------------------------------------------------------------------------------------------

function mps_tools_create_payements(WP_REST_Request $request) {
    global $wpdb;
    $params = $request->get_json_params();

    $required = ['totalAmount', 'initialAmount', 'itemName', 'event_id', 'firstName', 'lastName', 'email', 'inscription_id'];
    foreach ($required as $field) {
        if (!isset($params[$field]) || $params[$field] === '') {
            return new WP_Error('invalid_params', "Champ manquant : {$field}", ['status' => 400]);
        }
    }

    $totalAmount    = intval($params['totalAmount']);
    $initialAmount  = intval($params['initialAmount']);
    $itemName       = sanitize_text_field($params['itemName']);
    $event_id       = intval($params['event_id']);
    $firstName      = sanitize_text_field($params['firstName']);
    $lastName       = sanitize_text_field($params['lastName']);
    $email          = sanitize_email($params['email']);
    if (!isset($params['inscription_id']) || !is_array($params['inscription_id']) || empty($params['inscription_id'])) {
        return new WP_Error('invalid_params', 'inscription_id doit être un tableau non vide.', ['status' => 400]);
    }

    $inscription_ids = array_map('intval', $params['inscription_id']);
    $inscription_ids = array_filter($inscription_ids); // retire les 0/valeurs invalides après intval
    $inscription_ids = array_values(array_unique($inscription_ids)); // dédoublonne, réindexe

    if (empty($inscription_ids)) {
        return new WP_Error('invalid_params', 'Aucun inscription_id valide fourni.', ['status' => 400]);
    }
    $type           = sanitize_text_field($params['type'] ?? 'event');

    if (!is_email($email)) {
        return new WP_Error('invalid_email', 'Adresse email invalide.', ['status' => 400]);
    }

    $table_inscrits = $wpdb->prefix . 'inscrits';

    // On construit dynamiquement les placeholders %d pour la clause IN (...)
    $placeholders = implode(',', array_fill(0, count($inscription_ids), '%d'));

    $query_args = array_merge($inscription_ids, [$event_id]);
    $inscriptions = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $table_inscrits WHERE id IN ($placeholders) AND event_id = %d",
            ...$query_args
        )
    );

    if (count($inscriptions) !== count($inscription_ids)) {
        return new WP_Error('inscription_not_found', 'Une ou plusieurs inscriptions sont introuvables pour cet événement.', ['status' => 404]);
    }

    foreach ($inscriptions as $inscription) {
        if ($inscription->payement_status === 'completed') {
            return new WP_Error('already_paid', "L'inscription {$inscription->id} est déjà payée.", ['status' => 400]);
        }
    }

    $organizationSlug = get_option('helloasso_org_slug', '');
    $urlSite = esc_url_raw(get_site_url());
    $backUrl = get_option('helloasso_return_url', $urlSite);

    $checkout_url = HELLOASSO_BASE_URL . "/v5/organizations/{$organizationSlug}/checkout-intents";

    $body = [
        'totalAmount'      => $totalAmount,
        'initialAmount'    => $initialAmount,
        'itemName'         => $itemName,
        'backUrl'          => $backUrl,
        'errorUrl'         => $backUrl,
        'returnUrl'        => $backUrl,
        'containsDonation' => false,
        'payer' => [
            'firstName' => $firstName,
            'lastName'  => $lastName,
            'email'     => $email,
        ],
        'metadata' => [
            'inscription_ids' => $inscription_ids,
            'type'            => $type,
            'event_id'        => $event_id,
        ],
    ];

    $token = helloasso_get_access_token();
    if (!$token) {
        error_log('HelloAsso : échec de l\'authentification, impossible d\'obtenir le token.');
        return new WP_Error('helloasso_auth_error', 'Authentification HelloAsso échouée.', ['status' => 500]);
    }

    $response = wp_remote_post($checkout_url, [
        'headers' => ['Authorization' => 'Bearer ' . $token, 'Content-Type' => 'application/json'],
        'body'    => wp_json_encode($body),
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
        error_log('HelloAsso : bascule sur cURL direct pour /checkout-intents.');
        error_log('HelloAsso debug — slug: "' . $organizationSlug . '" | token prefix: ' . substr((string)$token, 0, 10) . '... | url: ' . $checkout_url);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $checkout_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, wp_json_encode($body));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true); // AVANT curl_exec, pour capturer les headers de réponse
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $fallback_raw = curl_exec($ch);
        $fallback_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $fallback_error = curl_error($ch);
        $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        curl_close($ch);

        if ($fallback_error) {
            error_log('HelloAsso checkout-intent cURL fallback erreur : ' . $fallback_error);
        } elseif ($fallback_raw !== false) {
            $fallback_headers = substr($fallback_raw, 0, $header_size);
            $fallback_body    = substr($fallback_raw, $header_size);

            error_log('HelloAsso fallback response headers : ' . $fallback_headers);
            error_log('HelloAsso fallback response body : ' . $fallback_body);

            $code = $fallback_code;
            $raw_body = $fallback_body;
        }
    }

    $data = json_decode((string) $raw_body, true);

    if ($code !== 200 || empty($data['redirectUrl']) || empty($data['id'])) {
        error_log("HelloAsso checkout-intent échec ({$code}) : " . $raw_body);
        return new WP_Error('helloasso_checkout_error', 'Impossible de créer le paiement.', ['status' => 500]);
    }

    helloasso_track_pending_checkout($data['id'], $inscription_ids, $event_id, $type);

    return rest_ensure_response([
        'success'         => true,
        'redirectUrl'     => $data['redirectUrl'],
        'checkoutIntentId'=> $data['id'],
    ]);
}

//--------------------------------------------------------------------------------------------------

function mps_tools_handle_check_payment(WP_REST_Request $request) {
    $checkout_intent_id = absint($request->get_param('checkoutIntentId'));

    if (!$checkout_intent_id) {
        return new WP_Error('missing_param', 'checkoutIntentId manquant.', ['status' => 400]);
    }

    $result = mps_tools_finalize_helloasso_payment($checkout_intent_id, 'returnUrl');

    if (is_wp_error($result)) {
        return $result; // conserve son propre code HTTP (404, 500, etc.)
    }

    return new WP_REST_Response($result, 200); // toujours 200 si pas d'erreur technique, même si success: false
}

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------