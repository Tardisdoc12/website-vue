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
    $user_id = (int) $request->get_param('user_id');
    $user = get_user_by('id', $user_id);

    if (!$user) {
        return new WP_REST_Response(array(
            'message' => 'Utilisateur introuvable'
        ), 404);
    }

    $note = sanitize_text_field($request->get_param('notes'));
    $is_personal = (int) $request->get_param('is_personal');

    $exists = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_notes WHERE is_personal = %d AND wp_user_id = %d",
            $is_personal,
            $user_id
        )
    );

    if ($exists) {
        return new WP_Error(
            'notes_found',
            'Cette note existe déjà.',
            ['status' => 404]
        );
    }

    $wpdb->insert(
        $table_notes,
        [
            'wp_user_id'  => $user_id,
            'note_write'  => $note,
            'is_personal' => $is_personal
        ]
    );

    iif ($wpdb->last_error) {
        return new WP_Error('db_error', $wpdb->last_error, ['status' => 500]);
    }
    return [
        'success' => true
    ];
}

//------------------------------------------------------------------------------