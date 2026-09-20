<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: events.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-19
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;

require_once MPS_TOOLS_FUNCTIONS_DIR . "sanitize_field_parameters.php";

//--------------------------------------------------------------------------------------------------
// Functions OR CLASS

function mps_tools_get_events(WP_REST_Request $request) {
    global $wpdb;
    $table_events   = $wpdb->prefix . "events";
    $table_inscrits = $wpdb->prefix . "inscrits";
    $table_users    = $wpdb->prefix . "users_inscrits";
    $table_billeterie = $wpdb->prefix . "billetteries";

    // Récupérer tous les événements
    $events = $wpdb->get_results("SELECT * FROM $table_events");


    $result = [];

    foreach ($events as $event) {
        
        // Récupérer les utilisateurs inscrits pour cet événement
        $users_to_add = $wpdb->get_results($wpdb->prepare(
            "SELECT 
                u.id, 
                u.user_name, 
                u.email, 
                u.phone, 
                u.experience,
                u.is_adherent,
                i.status,
                i.encadrement,
                i.date_inscrit,
                i.payement_status,
                i.champs_speciaux,
                wp_users.ID AS wp_user_id
            FROM $table_inscrits i
            JOIN $table_users u ON u.id = i.user_id
            LEFT JOIN {$wpdb->users} wp_users ON wp_users.user_email = u.email
            WHERE i.event_id = %d",
            $event->id
        ));

        $users = [];
        foreach ($users_to_add as $user) {
            $users[] = $user;
            $user->specialField = json_decode($user->champs_speciaux, true);
        }

        // Ajouter l’événement dans la réponse avec ses utilisateurs
        $result[$event->id] = [
            'id'          => $event->id,
            'post_id'     => $event->post_id,
            'url_post'    => get_permalink($event->post_id),
            'title'       => $event->title,
            'start_date'  => $event->start_date,
            'end_date'    => $event->end_date,
            'description' => $event->description,
            'place'       => $event->place,
            'category'    => $event->category,
            'subscribe_places'   => $event->subscribe_places,
            'nonsubscribe_places'=> $event->nonsubscribe_places,
            'attente_places'     => $event->attente_places,
            'closed_inscription' => $event->closed_inscription,
            'payement_title'       => $event->payement_title,
            'adherent_price'       => $event->adherent_price,
            'non_adherent_price'   => $event->non_adherent_price,
            'update_date'        => $event->update_date,
            'users'       => $users
        ];
    }

    return [ 'events' => $result ];
}

//--------------------------------------------------------------------------------------------------

function mps_tools_get_event_by_user_id(WP_REST_Request $request) {
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

//--------------------------------------------------------------------------------------------------

function mps_tools_get_event_id(WP_REST_Request $request) {
    global $wpdb;
    $table_events   = $wpdb->prefix . "events";
    $table_inscrits = $wpdb->prefix . "inscrits";
    $table_users    = $wpdb->prefix . "users_inscrits";
    $table_billeterie = $wpdb->prefix . "billetteries";

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
    $users_to_add = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT u.id, u.user_name, u.email, u.phone, u.experience, u.is_adherent, i.status, i.date_inscrit, i.payement_status, i.champs_speciaux
             FROM $table_inscrits i
             JOIN $table_users u ON u.id = i.user_id
             WHERE i.event_id = %d",
            $event->id
        )
    );

    $users = [];
    foreach ($users_to_add as $user) {
        $user->specialField = json_decode($user->champs_speciaux, true);
        $users[] = $user;
    }

    // Construire la réponse
    $result = [
        'id'                  => $event->id,
        'post_id'             => $event->post_id,
        'url_post'            => get_permalink($event->post_id),
        'title'               => $event->title,
        'start_date'          => $event->start_date,
        'end_date'            => $event->end_date,
        'description'         => $event->description,
        'place'               => $event->place,
        'category'            => $event->category,
        'subscribe_places'    => $event->subscribe_places,
        'nonsubscribe_places' => $event->nonsubscribe_places,
        'attente_places'       => $event->attente_places,
        'closed_inscription'   => $event->closed_inscription,
        'payement_title'       => $event->payement_title,
        'adherent_price'       => $event->adherent_price,
        'non_adherent_price'   => $event->non_adherent_price,
        'update_date'          => $event->update_date,
        'users'               => $users
    ];

    return $result;
}

//--------------------------------------------------------------------------------------------------

function mps_tools_get_event_post_id(WP_REST_Request $request) {
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
    $users_to_add = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT 
                u.id, 
                u.user_name, 
                u.email,
                u.phone,
                u.experience,
                u.is_adherent,
                i.status,
                i.date_inscrit,
                i.encadrement,
                i.payement_status,
                i.champs_speciaux,
                wp_users.ID AS wp_user_id
            FROM $table_inscrits i
            JOIN $table_users u ON u.id = i.user_id
            LEFT JOIN {$wpdb->users} wp_users ON wp_users.user_email = u.email
            WHERE i.event_id = %d",
            $event->id
        )
    );
    $users = [];
    foreach ($users_to_add as $user) {
        $user->specialField = json_decode($user->champs_speciaux, true);
        $users[] = $user;
    }

    // Construire la réponse
    $result = [
        'id'                  => $event->id,
        'post_id'             => $event->post_id,
        'url_post'            => get_permalink($event->post_id),
        'title'               => $event->title,
        'start_date'          => $event->start_date,
        'end_date'            => $event->end_date,
        'description'         => $event->description,
        'place'               => $event->place,
        'category'            => $event->category,
        'subscribe_places'    => $event->subscribe_places,
        'nonsubscribe_places' => $event->nonsubscribe_places,
        'attente_places'       => $event->attente_places,
        'closed_inscription'   => $event->closed_inscription,
        'payement_title'       => $event->payement_title,
        'adherent_price'       => $event->adherent_price,
        'non_adherent_price'   => $event->non_adherent_price,
        'update_date'          => $event->update_date,
        'users'               => $users
    ];

    return $result;
}

//--------------------------------------------------------------------------------------------------

function mps_tools_create_events(WP_REST_Request $request) {
    global $wpdb;

    error_log('HTML brut reçu: ' . $request['description']);

    $post_id = wp_insert_post([
        'post_type'   => 'event',
        'post_title'  => sanitize_text_field($request['title']),
        'post_status' => 'publish',
        'post_content'=> mps_tools_sanitize_rich_text($request['description']),
    ], true);

    $description = mps_tools_sanitize_rich_text($request['description']);
    error_log('Description after kses: ' . $description);


    if (is_wp_error($post_id)) {
        error_log('Post error: ' . $post_id->get_error_message());
        return new WP_Error('post_error', $post_id->get_error_message());
    }

    $result = $wpdb->insert(
        $wpdb->prefix . 'events',
        [
            'post_id' => $post_id,
            'title' => sanitize_text_field($request['title']),
            'start_date' => sanitize_text_field($request['start_date']),
            'end_date' => sanitize_text_field($request['end_date']),
            'description' => mps_tools_sanitize_rich_text($request['description']),
            'place' => sanitize_text_field($request['place']),
            'category' => sanitize_text_field($request['category']),
            'subscribe_places' => intval($request['subscribe_places']),
            'nonsubscribe_places' => intval($request['nonsubscribe_places']),
            'attente_places' => intval($request['attente_places']) ?? 0,
            'closed_inscription' => intval($request['closed_inscription']),
            'payement_title'       => sanitize_text_field($request['payement_title']),
            'adherent_price'       => intval($request['adherent_price']) ?? 0,
            'non_adherent_price'   => intval($request['non_adherent_price']) ?? 0,
        ]
    );

    if ($result === false) {
        error_log('DB Error: ' . $wpdb->last_error);
        error_log('Last query: ' . $wpdb->last_query);
        return new WP_Error(
            'db_error',
            $wpdb->last_error,
            ['status' => 500]
        );
    }

    if ($wpdb->last_error) {

        error_log($wpdb->last_error);

        wp_delete_post($post_id, true);

        return new WP_Error(
            'db_error',
            $wpdb->last_error,
            ['status' => 500]
        );
    }

    return [
        'id'   => $wpdb->insert_id,
        'url'  => get_permalink($post_id),
        'post_id' => $post_id
    ];
}

//--------------------------------------------------------------------------------------------------

function mps_tools_update_event(WP_REST_Request $request) {
    global $wpdb;
    $table = $wpdb->prefix . "events";
    $id = intval($request['id']);

    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT title, start_date, end_date, place FROM $table WHERE id = %d",
            $id
        )
    );

    if (empty($results)) {
        return new WP_Error('event_not_found', 'Événement non trouvé', ['status' => 404]);
    }

    $title_from_request = sanitize_text_field($request['title']);
    $start_date_from_request = sanitize_text_field($request['start_date']);
    $end_date_from_request = sanitize_text_field($request['end_date']);
    $place_from_request = sanitize_text_field($request['place']);

    $same_title = $results[0]->title === $title_from_request;
    $same_start_date = $results[0]->start_date === $start_date_from_request;
    $same_end_date = $results[0]->end_date === $end_date_from_request;
    $same_place = $results[0]->place === $place_from_request;


    $data = [
        'title' => sanitize_text_field($request['title']),
        'start_date' => sanitize_text_field($request['start_date']),
        'end_date' => sanitize_text_field($request['end_date']),
        'description' =>  mps_tools_sanitize_rich_text($request['description']),
        'place' => sanitize_text_field($request['place']),
        'category' => sanitize_text_field($request['category']),
        'subscribe_places' => intval($request['subscribe_places']),
        'nonsubscribe_places' => intval($request['nonsubscribe_places']),
        'attente_places' => intval($request['attente_places']) ?? 0,
        'closed_inscription' => intval($request['closed_inscription']),
        'payement_title'       => sanitize_text_field($request['payement_title']),
        'adherent_price'       => intval($request['adherent_price']) ?? 0,
        'non_adherent_price'   => intval($request['non_adherent_price']) ?? 0,
    ];


    if (!$same_title || !$same_start_date || !$same_end_date || !$same_place) {
        $data['update_date'] = current_time('mysql');
    }

    $where = ['id' => $id];

    $updated = $wpdb->update($table, $data, $where);

    if ($updated === false) {
        return new WP_Error('db_error', 'Impossible de mettre à jour l’événement', ['status' => 500]);
    }

    return ['id' => $id, 'updated' => $updated];
}

//--------------------------------------------------------------------------------------------------

function mps_tools_delete_events(WP_REST_Request $request) {
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

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------