<?php
/* 
* On ajoute des colonnes pour les utilisateurs
*/
if (!defined('ABSPATH')) exit;

//------------------------------------------------------------------------------
// IMPORTS

$file = "functions.php";
require_once plugin_dir_path(__FILE__) . $file;

//------------------------------------------------------------------------------

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/notes', [
        'methods' => 'GET',
        'callback' => 'myplugin_get_notes',
        'permission_callback' => 'monplugin_verify_csrf',
    ]);
});

function myplugin_get_notes(WP_REST_Request $request) {
    global $wpdb;
    $table_notes = $wpdb->prefix . "notes";
    $user_id = (int) $request->get_param('user_id');
    $user = get_user_by('id', $user_id);

    if (!$user) {
        return new WP_REST_Response(array(
            'message' => 'Utilisateur introuvable'
        ), 404);
    }
    
    $results = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT *
            FROM $table_notes
            WHERE wp_user_id = %d",
            $user_id
        )
    );

    return [
        'success' => true,
        'notes' => $results
    ];
}


//------------------------------------------------------------------------------
// Créer une note

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1','/notes',[
        'methods' => 'POST',
        'callback' => 'monplugin_create_notes',
        'permission_callback' => 'monplugin_verify_csrf'
    ]);
});

function monplugin_create_notes(WP_REST_Request $request) {
    global $wpdb;
    $table_notes = $wpdb->prefix . "notes";
    $user_id     = (int) $request->get_param('user_id');
    $note        = sanitize_text_field($request->get_param('notes'));
    $is_personal = (int) $request->get_param('is_personal');

    $user = get_user_by('id', $user_id);
    if (!$user) {
        return new WP_REST_Response(['message' => 'Utilisateur introuvable'], 404);
    }

    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_notes WHERE is_personal = %d AND wp_user_id = %d",
        $is_personal, $user_id
    ));

    if ($exists) {
        // Déjà là → on met à jour plutôt que d'échouer
        $wpdb->update(
            $table_notes,
            ['note_write' => $note],
            ['wp_user_id' => $user_id, 'is_personal' => $is_personal]
        );
    } else {
        $wpdb->insert($table_notes, [
            'wp_user_id'  => $user_id,
            'note_write'  => $note,
            'is_personal' => $is_personal
        ]);
    }

    if ($wpdb->last_error) {
        return new WP_Error('db_error', $wpdb->last_error, ['status' => 500]);
    }

    return ['success' => true];
}

//------------------------------------------------------------------------------

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/notes', [
        'methods' => 'PUT',
        'callback' => 'monplugin_update_notes',
        'permission_callback' => 'monplugin_verify_csrf',
    ]);
});

// Fonction pour modifier l'événement
function monplugin_update_notes(WP_REST_Request $request) {
    global $wpdb;
    $table = $wpdb->prefix . "notes";
    $user_id = intval($request->get_param('user_id'));
    $user = get_user_by('id', $user_id);

    if (!$user) {
        return new WP_REST_Response(array(
            'message' => 'Utilisateur introuvable'
        ), 404);
    }
    $note = sanitize_text_field($request->get_param('notes'));
    $is_personal = (int) $request->get_param('is_personal');

    $data = [
        'note_write' => $note
    ];

    $where = [
        'wp_user_id' => $user_id,
        'is_personal' => $is_personal
    ];

    $updated = $wpdb->update($table, $data, $where);

    if ($updated === false) {
        return new WP_Error('db_error', 'Impossible de mettre à jour la notes', ['status' => 500]);
    }

    return [
        'success'=>true,
        'updated' => $updated,
        'note' => $note
    ];
}

//------------------------------------------------------------------------------