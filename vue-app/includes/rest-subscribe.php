<?php
/*
* Gère les inscriptions aux events
*/

if (!defined('ABSPATH')) exit;

//------------------------------------------------------------------------------
// IMPORTS

$file = "functions.php";
require_once plugin_dir_path(__FILE__) . $file;

//------------------------------------------------------------------------------


add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1','/subscribe/(?P<user_id>\d+)/(?P<event_id>\d+)',[
        'methods' => 'DELETE',
        'callback' => 'monplugin_delete_subscribe',
        'permission_callback' => 'monplugin_verify_csrf'
    ]);
});

function monplugin_delete_subscribe(WP_REST_Request $request) {
    global $wpdb;
    $table_events   = $wpdb->prefix . "events";
    $table_inscrits = $wpdb->prefix . "inscrits";
    $table_users    = $wpdb->prefix . "users_inscrits";
    $user_id = intval($request['user_id']);
    $event_id = intval($request['event_id']);

    $inscription = $wpdb->get_row(
        $wpdb->prepare("SELECT * FROM $table_inscrits WHERE user_id = %d and event_id = %d", $user_id, $event_id)
    );

    if (!$inscription) {
        return new WP_Error('not_found', 'Inscription non trouvée', ['status' => 404]);
    }

    $wpdb->delete($table_inscrits, ['user_id' => $user_id, 'event_id' => $event_id]);

    $isAdherent = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT is_adherent
             FROM $table_users
             WHERE id = %d",
            $user_id
        )
    );

    // if ($isAdherent) {
    //     $subscribe_places = $wpdb->get_var(
    //         $wpdb->prepare("SELECT subscribe_places FROM $table_events WHERE id = %d", $event_id)
    //     );
    //     if($subscribe_places >= 0) {
    //         $wpdb->query(
    //             $wpdb->prepare("UPDATE $table_events SET subscribe_places = subscribe_places + 1 WHERE id = %d", $event_id)
    //         );
    //     }

    // } else {
    //     $wpdb->query(
    //         $wpdb->prepare("UPDATE $table_events SET nonsubscribe_places = nonsubscribe_places + 1 WHERE id = %d", $event_id)
    //     );
    // }
    return ['success' => true, 'message' => 'Inscription supprimée et place libérée'];
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
    $roles    = isset($user_d['roles']) && is_array($user_d['roles']) ? array_map('sanitize_text_field', $user_d['roles']) : [];

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
        $isAdherent = in_array('non_adherent', $roles, true) ? 0 : 1;
        $wpdb->insert($table_users, [
            "user_name" => isset($user_d['name']) ? sanitize_text_field($user_d['name']) : '',
            "phone" => isset($user_d['phone']) ? sanitize_text_field($user_d['phone']) : '',
            "email" => isset($user_d['email']) ? sanitize_text_field($user_d['email']) : '',
            "experience" => isset($user_d['experience']) ? sanitize_text_field($user_d['experience']) : '',
            "is_adherent" => $isAdherent
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

    // Décider quelle colonne décrémenter
    if (empty($roles) || in_array('nonadherent', $roles, true)) {
        $column = 'nonsubscribe_places';
        // Vérifier s'il reste des places
        if ($event->$column <= 0) {
            return new WP_Error('no_places', 'Plus de places disponibles pour ce type.', ['status' => 400]);
        }
    } else {
        $column = 'subscribe_places';
        // Vérifier s'il reste des places
        if ($event->$column == 0) {
            return new WP_Error('no_places', 'Plus de places disponibles pour ce type.', ['status' => 400]);
        }
    }



    // Décrémenter le nombre de places
    //$wpdb->query($wpdb->prepare(
    //    "UPDATE $table_events SET $column = $column - 1 WHERE id = %d",
    //    $event_id
    //));

    $wpdb->insert($table_inscrits, [
        'user_id' => $user_id,
        'event_id' => $event_id,
        'bike' => isset($user_d['bike']) ? sanitize_text_field($user_d['bike']) : '',
        'goal'=> isset($user_d['goal']) ? sanitize_text_field($user_d['goal']) : '',
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

//------------------------------------------------------------------------------
// End of File
//------------------------------------------------------------------------------