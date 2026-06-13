<?php
/* 
* On ajoute la table qui gère les médias
*/
if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'functions.php';

//------------------------------------------------------------------------------

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/medias', [
        'methods' => 'GET',
        'callback' => 'myplugin_get_medias',
        'permission_callback' => 'monplugin_verify_csrf'
    ]);
});

function myplugin_get_medias(WP_REST_Request $request) {
    global $wpdb;

    $table_medias = $wpdb->prefix . "medias";

    $medias = $wpdb->get_results("SELECT * FROM $table_medias");

    return rest_ensure_response($medias);
}

//------------------------------------------------------------------------------

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/medias', [
        'methods'             => 'POST',
        'callback'            => 'myplugin_upload_medias',
        'permission_callback' => 'monplugin_verify_csrf',
    ]);
});

function myplugin_upload_medias(WP_REST_Request $request) {
    global $wpdb;

    $table_medias   = $wpdb->prefix . "medias";
    $kdrive_file_id = sanitize_text_field($request->get_param('kdrive_file_id'));
    $file_name      = sanitize_text_field($request->get_param('file_name'));
    $file_size      = intval($request->get_param('file_size'));
    $file_type      = sanitize_text_field($request->get_param('file_type'));
    $uploaded_by    = get_current_user_id(); // ✅ Côté serveur

    if (empty($kdrive_file_id) || empty($file_name)) {
        return new WP_Error('missing_params', 'Paramètres manquants.', ['status' => 400]);
    }

    $existing = $wpdb->get_row($wpdb->prepare(
        "SELECT id FROM $table_medias WHERE kdrive_file_id = %s",
        $kdrive_file_id
    ));

    if ($existing) {
        return new WP_Error('media_exists', 'Le média existe déjà.', ['status' => 400]);
    }

    $result = $wpdb->insert(
        $table_medias,
        [
            'kdrive_file_id' => $kdrive_file_id,
            'file_name'      => $file_name,
            'file_size'      => $file_size,
            'file_type'      => $file_type,
            'uploaded_by'    => $uploaded_by,
            'date_creation'  => current_time('mysql'),
        ],
        ['%s', '%s', '%d', '%s', '%d', '%s']
    );

    if ($result === false) {
        return new WP_Error('db_insert_error', 'Erreur lors de l\'insertion.', ['status' => 500]);
    }

    return rest_ensure_response([
        'success' => true,
        'id'      => $wpdb->insert_id, // ✅ Retourne l'ID inséré
        'message' => 'Média ajouté avec succès.',
    ]);
}

//------------------------------------------------------------------------------

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/medias/(?P<id>\d+)', [
        'methods'             => 'DELETE',
        'callback'            => 'myplugin_delete_medias',
        'permission_callback' => 'monplugin_verify_csrf',
    ]);
});

function myplugin_delete_medias(WP_REST_Request $request) {
    global $wpdb;

    $id = intval($request['id']);
    $table_medias = $wpdb->prefix . "medias";

    $deleted = $wpdb->delete($table_medias, ['id' => $id], ['%d']);

    if ($deleted === false) {
        return new WP_Error('db_delete_error', 'Erreur lors de la suppression.', ['status' => 500]);
    }

    if ($deleted === 0) {
        return new WP_Error('not_found', "Aucun média trouvé avec l'ID $id.", ['status' => 404]);
    }

    return rest_ensure_response(['success' => true, 'deleted_id' => $id]);
}

//------------------------------------------------------------------------------
// End of file
//------------------------------------------------------------------------------
