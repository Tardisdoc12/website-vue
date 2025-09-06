<?php
/**
 * Plugin Name: Vue App
 * Description: Intègre une application Vue dans WordPress via des shortcodes, middleware et des ajouts pour la base de donnée
 * Version: 1.0
 * Author: Toi
 */

if (!defined('ABSPATH')) exit;

/**
 * Fonction générique pour créer un shortcode Vue
 */
function vue_shortcode($atts, $content, $tag) {
    $plugin_url = plugin_dir_url(__FILE__);
    $plugin_path = plugin_dir_path(__FILE__);

    // Nom des fichiers CSS/JS basés sur le shortcode
    $css_file = $tag . '.css';
    $js_file  = $tag . '.js';

    // CSS
    if (file_exists($plugin_path . $css_file)) {
        wp_enqueue_style("vue-{$tag}-css", $plugin_url . $css_file);
    }

    // JS
    wp_enqueue_script("vue-{$tag}-js", $plugin_url . $js_file, [], null, true);
    wp_script_add_data("vue-{$tag}-js", 'type', 'module');

    // Localize le nonce **après** l’enqueue du script
    wp_localize_script("vue-{$tag}-js", 'vueAppData', [
        'nonce' => wp_create_nonce('wp_rest'),
    ]);

    // Div ID basé sur le shortcode
    $div_id = str_replace('_', '-', $tag);
    return "<div id=\"{$div_id}\"></div>";
}

// Enregistrer les shortcodes
$shortcodes = ['login', 'calendar', 'events', 'connexion', 'account'];

foreach ($shortcodes as $sc) {
    add_shortcode($sc, 'vue_shortcode');
}

//-----------------------------------------------------------------------------------
// Middelware

add_action('template_redirect', 'redirect_if_logged_in_jwt');

function redirect_if_logged_in_jwt() {
    // Vérifie si on est sur la page de connexion (/home/)
    if (is_page('home')) {
        $jwt = isset($_COOKIE['mps_moto']) ? $_COOKIE['mps_moto'] : null;

        if ($jwt) {
            // Si déjà connecté avec un token valide, on redirige
            wp_redirect(home_url('/test/')); 
            exit;
        }
    }
}

//-----------------------------------------------------------------------------------

add_action('rest_api_init', function() {
    $custom_fields = [
        'firstName',
        'lastName',
        'adherentNumber',
        'telephone',
        'moto',
    ];

    foreach ($custom_fields as $field) {
        register_rest_field('user', $field, [
            'get_callback' => function($user) use ($field) {
                return get_user_meta($user['id'], $field, true);
            },
            'update_callback' => function($value, $user) use ($field) {
                update_user_meta($user->ID, $field, sanitize_text_field($value));
            },
        ]);
    }

    register_rest_field('user', 'email', [
        'get_callback' => function($user) {
            if (get_current_user_id() === $user['id']) {
                return get_userdata($user['id'])->user_email;
            }
            return null;
        },
        'update_callback' => null,
        'schema' => [
            'description' => __('User email'),
            'type' => 'string'
        ],
    ]);
});

//-----------------------------------------------------------------------------------
//Tables

register_activation_hook(__FILE__, 'mon_plugin_creer_tables');

function mon_plugin_creer_tables() {
    global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();

    $table_events = $wpdb->prefix . "events";
    $sql1 = "CREATE TABLE $table_events (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        title VARCHAR(200) NOT NULL,
        start_date DATETIME NOT NULL,
        end_date DATETIME NOT NULL,
        description TEXT NOT NULL,
        place VARCHAR(200) NOT NULL,
        category VARCHAR(100) NOT NULL,
        subscribe_places INT NOT NULL,
        nonsubscribe_places INT UNSIGNED NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $table_inscrits = $wpdb->prefix . "inscrits";
    $sql2 = "CREATE TABLE $table_inscrits (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_id BIGINT(20) UNSIGNED NOT NULL,
        event_id BIGINT(20) UNSIGNED NOT NULL,
        date_inscription DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
        bike VARCHAR(200) NULL,
        goal VARCHAR(200) NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    $table_users_inscrits = $wpdb->prefix . "users_inscrits";
    $sql3= "CREATE TABLE $table_users_inscrits (
        id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
        user_name VARCHAR(200) NOT NULL,
        phone VARCHAR(10) NOT NULL,
        email VARCHAR(200) NOT NULL,
        experience VARCHAR(200) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";

    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql1);
    dbDelta($sql2);
    dbDelta($sql3);
}

//-----------------------------------------------------------------------------------

function monplugin_verify_csrf(WP_REST_Request $request) {
    // Vérification via X-WP-Nonce (classique WordPress)
    $nonce = $request->get_header('X-WP-Nonce');
    if ($nonce && wp_verify_nonce($nonce, 'wp_rest')) {
        return true;
    }

    // Vérification via JWT
    $auth_header = $request->get_header('Authorization');
    if ($auth_header && preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
        $token = $matches[1];

        // Vérifier le token via le hook du plugin JWT
        $user = apply_filters('jwt_auth_token_before_dispatch', $token);

        if ($user && !is_wp_error($user)) {
            return true;
        }

        return new WP_Error(
            'invalid_jwt_token',
            'JWT token invalide ou expiré.',
            ['status' => 403]
        );
    }

    // Aucun token fourni
    return new WP_Error(
        'missing_auth',
        'Aucun CSRF token ou JWT token fourni.',
        ['status' => 403]
    );
}

//-----------------------------------------------------------------------------------

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/events', [
        'methods' => 'GET',
        'callback' => 'monplugin_get_events',
        'permission_callback' => 'monplugin_verify_csrf'
    ]);
});

function monplugin_get_events(WP_REST_Request $request) {
    global $wpdb;
    $table_events   = $wpdb->prefix . "events";
    $table_inscrits = $wpdb->prefix . "inscrits";
    $table_users    = $wpdb->prefix . "users_inscrits";

    // Récupérer tous les événements
    $events = $wpdb->get_results("SELECT * FROM $table_events");

    $result = [];

    foreach ($events as $event) {
        // Récupérer les utilisateurs inscrits pour cet événement
        $users = $wpdb->get_results($wpdb->prepare(
            "SELECT u.id, u.user_name, u.email, u.phone, u.experience 
             FROM $table_inscrits i
             JOIN $table_users u ON u.id = i.user_id
             WHERE i.event_id = %d",
            $event->id
        ));

        // Ajouter l’événement dans la réponse avec ses utilisateurs
        $result[$event->id] = [
            'id'          => $event->id,
            'title'       => $event->title,
            'start_date'  => $event->start_date,
            'end_date'    => $event->end_date,
            'description' => $event->description,
            'place'       => $event->place,
            'category'    => $event->category,
            'subscribe_places'   => $event->subscribe_places,
            'nonsubscribe_places'=> $event->nonsubscribe_places,
            'users'       => $users
        ];
    }

    return [ 'events' => $result ];
}

//------------------------------------------------------------------------------

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/events/(?P<id>\d+)', [
        'methods'             => 'GET',
        'callback'            => 'monplugin_get_event_id',
        'permission_callback' => 'monplugin_verify_csrf'
    ]);
});

function monplugin_get_event_id(WP_REST_Request $request) {
    global $wpdb;
    $table_events   = $wpdb->prefix . "events";
    $table_inscrits = $wpdb->prefix . "inscrits";
    $table_users    = $wpdb->prefix . "users_inscrits";

    $event_id = intval($request['id']);

    // Récupérer un seul événement
    $event = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table_events WHERE id = %d", $event_id)
    );

    if (!$event) {
        return new WP_Error(
            'event_not_found',
            'Aucun événement trouvé avec cet ID',
            ['status' => 404]
        );
    }

    // Récupérer les utilisateurs inscrits
    $users = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT u.id, u.user_name, u.email, u.phone, u.experience  
             FROM $table_inscrits i
             JOIN $table_users u ON u.id = i.user_id
             WHERE i.event_id = %d",
            $event->id
        )
    );

    // Construire la réponse
    $result = [
        'id'                  => $event->id,
        'title'               => $event->title,
        'start_date'          => $event->start_date,
        'end_date'            => $event->end_date,
        'description'         => $event->description,
        'place'               => $event->place,
        'category'            => $event->category,
        'subscribe_places'    => $event->subscribe_places,
        'nonsubscribe_places' => $event->nonsubscribe_places,
        'users'               => $users
    ];

    return $result;
}

//------------------------------------------------------------------------------

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1','/events',[
        'methods' => 'POST',
        'callback' => 'monplugin_create_events',
        'permission_callback' => 'monplugin_verify_csrf'
    ]);
});

function monplugin_create_events(WP_REST_Request $request) {
    global $wpdb;
    $table = $wpdb->prefix . "events";
    $wpdb->insert($table, [
        'title' => sanitize_text_field($request['title']),
        'start_date' => sanitize_text_field($request['start_date']),
        'end_date' => sanitize_text_field($request['end_date']),
        'description' => sanitize_textarea_field($request['description']),
        'place' => sanitize_text_field($request['place']),
        'category' => sanitize_text_field($request['category']),
        'subscribe_places' => intval($request['subscribe_places']),
        'nonsubscribe_places' => intval($request['nonsubscribe_places']),
    ]);

    return ['id' => $wpdb->insert_id];
}

//-----------------------------------------------------------------------------------

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1','/events/(?P<id>\d+)',[
        'methods' => 'DELETE',
        'callback' => 'monplugin_delete_events',
        'permission_callback' => 'monplugin_verify_csrf'
    ]);
});

function monplugin_delete_events(WP_REST_Request $request) {
    global $wpdb;
    $table_events = $wpdb->prefix . "events";
    $table_inscrits = $wpdb->prefix . "inscrits";
    $event_id = intval($request['id']);

    // Vérifier si l'événement existe
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_events WHERE id = %d",
        $event_id
    ));

    if (!$exists) {
        return new WP_Error(
            'event_not_found',
            'Cet événement n’existe pas.',
            ['status' => 404]
        );
    }

    //supprimer les inscrits
    $wpdb->delete($table_inscrits, ['event_id' => $event_id]);
    // Supprimer l’événement
    $wpdb->delete($table_events, ['id' => $event_id]);
    return [
        'success' => true,
        'deleted_event_id' => $event_id
    ];
}

//-----------------------------------------------------------------------------------

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1','/subscribe',[
        'methods' => 'POST',
        'callback' => 'monplugin_create_subscribe',
        'permission_callback' => 'monplugin_verify_csrf'
    ]);
});

function monplugin_create_subscribe(WP_REST_Request $request) {
    global $wpdb;
    $table_events   = $wpdb->prefix . "events";
    $table_inscrits = $wpdb->prefix . "inscrits";
    $table_users    = $wpdb->prefix . "users_inscrits";
    $event_id = $request->get_param('event_id'); 
    $user_d = $request->get_param('user');

    // Vérifier si l’événement existe
    $event = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_events WHERE id = %d",
        $event_id
    ));

    if (!$event) {
        return new WP_Error(
            'event_not_found',
            'Cet événement n’existe pas.',
            ['status' => 404]
        );
    }

    // verifier si l'utilisateur existe
    $user = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM $table_users WHERE email = %s",
        $user_d["email"]
    ));

    if (!$user) {
        $wpdb->insert($table_users, [
            "user_name" => isset($user_d['name']) ? sanitize_text_field($user_d['name']) : '',
            "phone" => isset($user_d['phone']) ? sanitize_text_field($user_d['phone']) : '',
            "email" => isset($user_d['email']) ? sanitize_text_field($user_d['email']) : '',
            "experience" => isset($user_d['experience']) ? sanitize_text_field($user_d['experience']) : ''
        ]);
        if ($wpdb->last_error) {
            return new WP_Error('db_insert_error', 'Erreur SQL (users) : ' . $wpdb->last_error, ['status' => 500]);
        }
        $user_id = $wpdb->insert_id;
    } else {
        $user_id = intval($user->id);
    }

    // Vérifier si l’utilisateur est déjà inscrit à cet event
    $already = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_inscrits WHERE event_id = %d AND user_id = %d",
        $event_id, $user_id
    ));

    if ($already > 0) {
        return [
            'success' => false,
            'message' => 'Utilisateur déjà inscrit à cet événement.'
        ];
    }

    $wpdb->insert($table_inscrits, [
        'user_id' => $user_id,
        'event_id' => $event_id,
        'bike' => sanitize_text_field($request['bike']),
        'goal'=> sanitize_textarea_field($request['goal']),
    ]);

    if ($wpdb->last_error) {
        return new WP_Error('db_insert_error', 'Erreur SQL (inscrits) : ' . $wpdb->last_error, ['status' => 500]);
    }

    return [
        'success' => true,
        'event_id' => $event_id,
        'user_id' => $user_id,
        'message' => 'Inscription réussie.'
    ];
}

//-----------------------------------------------------------------------------------

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

    if (username_exists($username) || email_exists($email)) {
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

//-----------------------------------------------------------------------------------
// End of File
//-----------------------------------------------------------------------------------