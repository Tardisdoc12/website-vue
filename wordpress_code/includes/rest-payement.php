<?php
/*
* Gère les paiements HelloAsso
*/
if (!defined('ABSPATH')) exit;

const HELLOASSO_BASE_URL = 'https://api.helloasso-sandbox.com';
const HELLOASSO_TOKEN_TRANSIENT_KEY = 'helloasso_encrypted_access_token';

/**
 * Récupère un token d'accès HelloAsso valide, depuis le cache si possible.
 * Fonction interne uniquement — jamais exposée via une route REST.
 */
function helloasso_get_access_token() {
    $cached_encrypted = get_transient(HELLOASSO_TOKEN_TRANSIENT_KEY);

    if ($cached_encrypted !== false) {
        $token = myplugin_decrypt($cached_encrypted);
        if ($token) {
            return $token;
        }
    }

    return helloasso_fetch_new_access_token();
}


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
    $needs_fallback = is_wp_error($response)
        || ($code === 404 && strpos((string) $raw_body, 'Azure Web App') !== false);

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
    $encrypted = myplugin_encrypt($data['access_token']);
    set_transient(HELLOASSO_TOKEN_TRANSIENT_KEY, $encrypted, max($expires_in - 60, 60));

    return $data['access_token'];
}

/**
 * Chiffrement AES-256-CBC, clé dérivée des salts WordPress (jamais stockée séparément)
 */
function myplugin_get_encryption_key() {
    // wp_salt('auth') est unique par installation WordPress et déjà stockée de façon sécurisée
    return hash('sha256', wp_salt('auth'), true); // 32 bytes, requis pour AES-256
}

function myplugin_encrypt($plain_text) {
    $key = myplugin_get_encryption_key();
    $iv = random_bytes(16);
    $encrypted = openssl_encrypt($plain_text, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

    if ($encrypted === false) {
        error_log('Erreur de chiffrement.');
        return false;
    }

    return base64_encode($iv . $encrypted);
}

function myplugin_decrypt($encoded) {
    $key = myplugin_get_encryption_key();
    $data = base64_decode($encoded);

    if ($data === false || strlen($data) < 16) {
        return false;
    }

    $iv = substr($data, 0, 16);
    $encrypted = substr($data, 16);
    $decrypted = openssl_decrypt($encrypted, 'aes-256-cbc', $key, OPENSSL_RAW_DATA, $iv);

    return $decrypted !== false ? $decrypted : false;
}

//-----------------------------------------------------------------------------------------------------
// Routes pour contater HelloAsso et faire des paiements
add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/create_payements', [
        'methods'             => 'POST',
        'callback'            => 'myplugin_create_payements',
        'permission_callback' => 'monplugin_verify_csrf',
    ]);
});

function myplugin_create_payements(WP_REST_Request $request) {
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

    $needs_fallback = is_wp_error($response)
        || ($code === 404 && strpos((string) $raw_body, 'Azure Web App') !== false);

    if ($needs_fallback) {
        error_log('HelloAsso : bascule sur cURL direct pour /checkout-intents.');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $checkout_url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, wp_json_encode($body));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 15);

        $fallback_body = curl_exec($ch);
        $fallback_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $fallback_error = curl_error($ch);
        curl_close($ch);

        if ($fallback_error) {
            error_log('HelloAsso checkout-intent cURL fallback erreur : ' . $fallback_error);
        } elseif ($fallback_body !== false) {
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

//-----------------------------------------------------------------------------------------------------------
// Cron du suivi des paiements en attente

add_action('helloasso_check_pending_checkouts', 'helloasso_run_pending_checkouts_check');

if (!wp_next_scheduled('helloasso_check_pending_checkouts')) {
    wp_schedule_event(time(), 'hourly', 'helloasso_check_pending_checkouts');
}

function helloasso_track_pending_checkout($checkout_intent_id, $inscription_ids, $event_id, $type = 'event') {
    $pending = get_option('helloasso_pending_checkouts', []);

    $pending[$checkout_intent_id] = [
        'inscription_ids' => $inscription_ids,
        'event_id'       => $event_id,
        'type'       => $type,
        'created_at' => time(),
    ];

    update_option('helloasso_pending_checkouts', $pending, false);
}

function helloasso_untrack_pending_checkout($checkout_intent_id) {
    $pending = get_option('helloasso_pending_checkouts', []);
    if (isset($pending[$checkout_intent_id])) {
        unset($pending[$checkout_intent_id]);
        update_option('helloasso_pending_checkouts', $pending, false);
    }
}

function helloasso_get_pending_checkouts() {
    return get_option('helloasso_pending_checkouts', []);
}


function treat_inscription_payment_by_id($inscription_ids) {
    global $wpdb;
    $table_inscrits = $wpdb->prefix . 'inscrits';


    $errors = [];
    foreach($inscription_ids as $inscription_id) {
        $result = $wpdb->update(
            $table_inscrits,
            ['payement_status' => 'completed'],
            ['id' => $inscription_id],
            ['%s'],
            ['%d']
        );
        if ($result === false) {
            $errors[] = $inscription_id;
        }
    }

    return ['success' => empty($errors), 'inscription_ids' => $inscription_ids, 'errors' => $errors];
}

function treat_adherent_payment_by_inscription_id($inscription_ids) {
    global $wpdb;
    $table_inscrits = $wpdb->prefix . 'inscrits';
    $table_users_inscrits = $wpdb->prefix . 'users_inscrits';

    // On marque l'inscription comme payée
    $errors = [];
    foreach($inscription_ids as $inscription_id) {
        $result = $wpdb->update(
            $table_inscrits,
            ['payement_status' => 'completed'],
            ['id' => $inscription_id],
            ['%s'],
            ['%d']
        );
        if ($result === false) {
            $errors[] = $inscription_id;
        }
        // On récupère l'email de l'inscrit pour retrouver/créer le compte WP correspondant
        $inscrit = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT ui.email FROM $table_inscrits i
                JOIN $table_users_inscrits ui ON ui.id = i.user_id
                WHERE i.id = %d",
                $inscription_id
            )
        );

        if (!$inscrit) {
            return new WP_Error('inscrit_not_found', 'Inscrit introuvable pour cette inscription.', ['status' => 404]);
        }

        $user = get_user_by('email', $inscrit->email);

        if (!$user) {
            // Pas de compte WP existant : à toi de voir si on en crée un automatiquement ici
            // (cf. notre discussion précédente sur le cas "adhérent sans compte")
            error_log("Paiement adhérent confirmé pour {$inscrit->email}, mais aucun compte WP associé.");
            return ['success' => true, 'message' => 'Paiement confirmé, mais aucun compte WP à mettre à jour.'];
        }

        $user->add_role('adherent');
        $user->remove_role('non_adherent');
        update_user_meta($user->ID, 'subscriber_date', current_time('mysql'));    
    }

    
    return ['success' => true, 'user_id' => $user->ID];
}


/**
 * Vérifie et finalise un paiement HelloAsso de façon idempotente.
 * Appelée depuis 3 points d'entrée : returnUrl (utilisateur), webhook, cron.
 */
function myplugin_finalize_helloasso_payment($checkout_intent_id, $source = 'unknown') {
    global $wpdb;
    $table_inscrits = $wpdb->prefix . 'inscrits';

    // On retrouve l'inscription via le tracking (nécessaire pour connaître l'inscription_id
    // avant même d'avoir confirmé le paiement auprès de HelloAsso)
    $pending = helloasso_get_pending_checkouts();
    $meta = $pending[$checkout_intent_id] ?? null;

    if (!$meta) {
        error_log("HelloAsso finalize [{$source}] : checkout_intent_id {$checkout_intent_id} introuvable dans le tracking.");
        return new WP_Error('checkout_not_found', 'Checkout intent inconnu ou déjà traité.', ['status' => 404]);
    }

    $inscription_ids = $meta['inscription_ids'];
    $type = $meta['type'] ?? 'event';
    

    // Vérification ACTIVE auprès de HelloAsso : jamais confiance à une simple URL ou un webhook seul
    $token = helloasso_get_access_token();
    if (!$token) {
        return new WP_Error('helloasso_auth_error', 'Authentification HelloAsso échouée.', ['status' => 500]);
    }

    $organizationSlug = get_option('helloasso_org_slug', '');
    $response = wp_remote_get(
        HELLOASSO_BASE_URL . "/v5/organizations/{$organizationSlug}/checkout-intents/{$checkout_intent_id}",
        ['headers' => ['Authorization' => 'Bearer ' . $token], 'timeout' => 15]
    );

    if (is_wp_error($response)) {
        error_log("HelloAsso finalize [{$source}] : erreur réseau - " . $response->get_error_message());
        return new WP_Error('helloasso_network_error', 'Erreur réseau HelloAsso.', ['status' => 500]);
    }

    $body = json_decode(wp_remote_retrieve_body($response), true);

    if (empty($body['order'])) {
        error_log("HelloAsso finalize [{$source}] : pas de commande pour {$checkout_intent_id}, probablement pending/abandonné.");
        return ['success' => false, 'message' => 'Paiement non confirmé pour le moment.'];
    }

    $is_authorized = false;
    foreach (($body['order']['payments'] ?? []) as $payment) {
        if ($payment['state'] === 'Authorized') {
            $is_authorized = true;
            break;
        }
    }

    if (!$is_authorized) {
        error_log("HelloAsso finalize [{$source}] : commande trouvée mais aucun paiement Authorized pour {$checkout_intent_id}.");
        return ['success' => false, 'message' => 'Paiement non autorisé.'];
    }

    // Paiement confirmé côté HelloAsso : on applique la logique métier
    if ($type === 'adherent') {
        $result = treat_adherent_payment_by_inscription_id($inscription_ids);
    } else {
        $result = treat_inscription_payment_by_id($inscription_ids);
    }

    if (is_wp_error($result)) {
        error_log("HelloAsso finalize [{$source}] : erreur métier - " . $result->get_error_message());
        return $result;
    }

    helloasso_untrack_pending_checkout($checkout_intent_id);

    error_log("HelloAsso finalize [{$source}] : paiement {$checkout_intent_id} finalisé avec succès.");
    return ['success' => true, 'message' => 'Paiement finalisé.'];
}


function helloasso_run_pending_checkouts_check() {
    $pending = helloasso_get_pending_checkouts();

    foreach ($pending as $checkout_intent_id => $meta) {
        // On ne revérifie que les paiements de plus de 50 minutes (marge sur la limite des 45 min de HelloAsso)
        if ((time() - $meta['created_at']) < 50 * 60) {
            continue;
        }

        myplugin_finalize_helloasso_payment($checkout_intent_id, 'cron');

        // Qu'il ait réussi ou échoué définitivement (abandonné), on arrête de le suivre :
        // s'il a réussi, c'est fait ; s'il a échoué après 50 min, HelloAsso le considère abandonné.
        helloasso_untrack_pending_checkout($checkout_intent_id);
    }
}

//-----------------------------------------------------------------------------------------------------------
//il faut verifier le payement pour le dire à l'utilisateur que son payement a été effectué ou non

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/check_payment', [
        'methods'             => 'GET',
        'callback'            => 'myplugin_handle_check_payment',
        'permission_callback' => '__return_true', // accessible sans connexion : l'utilisateur revient d'un paiement, connecté ou pas
    ]);
});

function myplugin_handle_check_payment(WP_REST_Request $request) {
    $checkout_intent_id = absint($request->get_param('checkoutIntentId'));

    if (!$checkout_intent_id) {
        return new WP_Error('missing_param', 'checkoutIntentId manquant.', ['status' => 400]);
    }

    $result = myplugin_finalize_helloasso_payment($checkout_intent_id, 'returnUrl');

    if (is_wp_error($result)) {
        return $result; // conserve son propre code HTTP (404, 500, etc.)
    }

    return new WP_REST_Response($result, 200); // toujours 200 si pas d'erreur technique, même si success: false
}

//---------------------------------------------------------------------------------------------------------------
// End of file
//---------------------------------------------------------------------------------------------------------------