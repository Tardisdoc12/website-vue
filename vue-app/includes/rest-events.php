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
            "SELECT u.id, u.user_name, u.email, u.phone, u.experience, i.goal
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
// Récupère un event en particulier

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
            "SELECT u.id, u.user_name, u.email, u.phone, u.experience, i.goal
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