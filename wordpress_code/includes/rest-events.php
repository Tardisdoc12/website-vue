<?php
/*
* Gère les routes des events
*/
if (!defined('ABSPATH')) exit;

//------------------------------------------------------------------------------
// IMPORT :

$file = "functions.php";
require_once plugin_dir_path(__FILE__) . $file;

//------------------------------------------------------------------------------
// Enregistre le Custom Post Type "event"

add_action('init', 'monplugin_register_event_cpt');

function monplugin_register_event_cpt() {
    register_post_type('event', [
        'labels' => [
            'name' => 'Événements',
            'singular_name' => 'Événement'
        ],
        'public' => true,
        'rewrite' => ['slug' => 'evenement'],
        'supports' => ['title', 'editor'],
        'show_in_rest' => true
    ]);
}

//------------------------------------------------------------------------------
// Récupère tous les events

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
            "SELECT u.id, u.user_name, u.email, u.phone, u.experience, i.goal, u.is_adherent, i.bike
             FROM $table_inscrits i
             JOIN $table_users u ON u.id = i.user_id
             WHERE i.event_id = %d",
            $event->id
        ));

        // Ajouter l’événement dans la réponse avec ses utilisateurs
        $result[$event->id] = [
            'id'          => $event->id,
            'post_id'     => $event->post_id,
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
// Récupère un event en particulier via l'email de l'user

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/events/(?P<user_id>\d+)', [
        'methods'             => 'POST',
        'callback'            => 'monplugin_get_event_by_user_id',
        'permission_callback' => 'monplugin_verify_csrf'
    ]);
});

function monplugin_get_event_by_user_id(WP_REST_Request $request) {
    global $wpdb;
    $table_events   = $wpdb->prefix . "events";
    $table_inscrits = $wpdb->prefix . "inscrits";
    $table_users    = $wpdb->prefix . "users_inscrits";
    $email          = $request->get_param('email');

    $events = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT i.event_id
             FROM $table_inscrits i
             JOIN $table_users u ON u.id = i.user_id
             WHERE u.email = %s",
            $email
        )
    );

    return [
        "success" => 200,
        "results" => $events
    ];
}


//------------------------------------------------------------------------------
// Récupère un event en particulier via l'id de l'event

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
            "SELECT u.id, u.user_name, u.email, u.phone, u.experience, i.goal, u.is_adherent, i.bike
             FROM $table_inscrits i
             JOIN $table_users u ON u.id = i.user_id
             WHERE i.event_id = %d",
            $event->id
        )
    );

    // Construire la réponse
    $result = [
        'id'                  => $event->id,
        'post_id'             => $event->post_id,
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
// Récupère un event en particulier via l'id de l'event

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/events/post/(?P<id>\d+)', [
        'methods'             => 'GET',
        'callback'            => 'monplugin_get_event_post_id',
        'permission_callback' => 'monplugin_verify_csrf'
    ]);
});

function monplugin_get_event_post_id(WP_REST_Request $request) {
    global $wpdb;
    $table_events   = $wpdb->prefix . "events";
    $table_inscrits = $wpdb->prefix . "inscrits";
    $table_users    = $wpdb->prefix . "users_inscrits";

    $event_post_id = intval($request['id']);

    // Récupérer un seul événement
    $event = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table_events WHERE post_id = %d", $event_post_id)
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
            "SELECT u.id, u.user_name, u.email, u.phone, u.experience, i.goal, u.is_adherent, i.bike
             FROM $table_inscrits i
             JOIN $table_users u ON u.id = i.user_id
             WHERE i.event_id = %d",
            $event->id
        )
    );

    // Construire la réponse
    $result = [
        'id'                  => $event->id,
        'post_id'             => $event->post_id,
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
// Créer un event

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1','/events',[
        'methods' => 'POST',
        'callback' => 'monplugin_create_events',
        'permission_callback' => 'monplugin_verify_csrf'
    ]);
});

function monplugin_create_events(WP_REST_Request $request) {
    global $wpdb;

    $post_id = wp_insert_post([
        'post_type'   => 'event',
        'post_title'  => sanitize_text_field($request['title']),
        'post_status' => 'publish',
        'post_content'=> sanitize_textarea_field($request['description']),
    ], true);

    if (is_wp_error($post_id)) {
        return new WP_Error('post_error', 'Erreur création page');
    }

    $wpdb->insert(
        $wpdb->prefix . 'events',
        [
            'post_id' => $post_id,
            'title' => sanitize_text_field($request['title']),
            'start_date' => sanitize_text_field($request['start_date']),
            'end_date' => sanitize_text_field($request['end_date']),
            'description' => sanitize_textarea_field($request['description']),
            'place' => sanitize_text_field($request['place']),
            'category' => sanitize_text_field($request['category']),
            'subscribe_places' => intval($request['subscribe_places']),
            'nonsubscribe_places' => intval($request['nonsubscribe_places']),
        ]
    );

    if ($wpdb->last_error) {
        wp_delete_post($post_id, true);
        return new WP_Error('db_error', 'Erreur DB');
    }

    return [
        'id'   => $wpdb->insert_id,
        'url'  => get_permalink($post_id),
        'post_id' => $post_id
    ];
}
//------------------------------------------------------------------------------
// Modifie un évènement

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/events/(?P<id>\d+)', [
        'methods' => 'PUT',
        'callback' => 'monplugin_update_event',
        'permission_callback' => 'monplugin_verify_csrf',
    ]);
});

// Fonction pour modifier l'événement
function monplugin_update_event(WP_REST_Request $request) {
    global $wpdb;
    $table = $wpdb->prefix . "events";
    $id = intval($request['id']);

    $data = [
        'title' => sanitize_text_field($request['title']),
        'start_date' => sanitize_text_field($request['start_date']),
        'end_date' => sanitize_text_field($request['end_date']),
        'description' => sanitize_textarea_field($request['description']),
        'place' => sanitize_text_field($request['place']),
        'category' => sanitize_text_field($request['category']),
        'subscribe_places' => intval($request['subscribe_places']),
        'nonsubscribe_places' => intval($request['nonsubscribe_places']),
    ];

    $where = ['id' => $id];

    $updated = $wpdb->update($table, $data, $where);

    if ($updated === false) {
        return new WP_Error('db_error', 'Impossible de mettre à jour l’événement', ['status' => 500]);
    }

    return ['id' => $id, 'updated' => $updated];
}

//-----------------------------------------------------------------------------------
// Supprime un event

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

    //supprimer le post lié à l'événement
    $post_id = $wpdb->get_var($wpdb->prepare(
        "SELECT post_id FROM $table_events WHERE id = %d",
        $event_id
    ));
    if ($post_id) {
        wp_delete_post($post_id, true);
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

//------------------------------------------------------------------------------
// End of File
//------------------------------------------------------------------------------