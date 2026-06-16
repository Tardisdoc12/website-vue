<?php
/* 
* On ajoute la table qui gère les médias
*/
if (!defined('ABSPATH')) exit;

require_once plugin_dir_path(__FILE__) . 'functions.php';

//------------------------------------------------------------------------------

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/medias/thumbnails', [
        'methods' => 'GET',
        'callback' => 'myplugin_get_medias_thumbnails',
        'permission_callback' => 'monplugin_verify_csrf'
    ]);
});

function myplugin_get_medias_thumbnails(WP_REST_Request $request) {
    $urls = $request->get_param('urls');

    if (empty($urls) || !is_array($urls)) {
        return new WP_Error('missing_urls', 'URLs manquantes.', ['status' => 400]);
    }

    // Validation de toutes les URLs d'abord
    foreach ($urls as $key => $url) {
        $parsed = wp_parse_url($url);
        if (empty($parsed['host']) || $parsed['host'] !== 'api.infomaniak.com') {
            unset($urls[$key]);
        }
    }

    // Init curl_multi
    $multi   = curl_multi_init();
    $handles = [];

    foreach ($urls as $key => $url) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => ['Authorization: Bearer ' . KDRIVE_TOKEN],
            CURLOPT_TIMEOUT        => 15,
        ]);
        curl_multi_add_handle($multi, $ch);
        $handles[$key] = $ch;
    }

    // Exécution parallèle
    $running = null;
    do {
        curl_multi_exec($multi, $running);
        curl_multi_select($multi);
    } while ($running > 0);

    // Récupération des résultats
    $thumbnails = [];
    foreach ($handles as $key => $ch) {
        $body         = curl_multi_getcontent($ch);
        $content_type = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);

        $thumbnails[$key] = $body
            ? 'data:' . $content_type . ';base64,' . base64_encode($body)
            : null;

        curl_multi_remove_handle($multi, $ch);
        curl_close($ch);
    }

    curl_multi_close($multi);

    return rest_ensure_response([
        'success'    => true,
        'thumbnails' => $thumbnails,
    ]);
}

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

    $medias = $wpdb->get_results("SELECT * FROM {$table_medias}");

    $result = [];
    
    $drive_id = KDRIVE_DRIVE_ID;

    $supported_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];

    foreach ($medias as $media) {
        $media->file_name = sanitize_text_field($media->file_name);
        $media->file_type = sanitize_text_field($media->file_type);
        $media->kdrive_file_id = sanitize_text_field($media->kdrive_file_id);
        $media->uploaded_by = intval($media->uploaded_by);
        $media->parent_id = sanitize_text_field($media->parent_id);

        if (in_array($media->file_type, $supported_types)) {
            $media->thumbnail_300 = "https://api.infomaniak.com/3/drive/{$drive_id}/files/{$media->kdrive_file_id}/thumbnail?height=300&width=250";
            $media->thumbnail_100 = "https://api.infomaniak.com/3/drive/{$drive_id}/files/{$media->kdrive_file_id}/thumbnail?height=100&width=100";
        } else {
            $media->thumbnail_300 = null;
            $media->thumbnail_100 = null;
        }
        $result[$media->id] = $media;
    }

    return rest_ensure_response([
        'success' => true,
        'medias' => $result
    ]);
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
    $files = $request->get_file_params();

    if (empty($files['file'])) {
        return new WP_Error('missing_file', 'Aucun fichier reçu.', ['status' => 400]);
    }

    $file           = $files['file'];
    $file_tmp       = $file['tmp_name'];
    $file_name      = sanitize_text_field($request->get_param('file_name'));
    $file_size      = intval($file['size']);
    $uploaded_by    = get_current_user_id(); // ✅ Côté serveur
    $folder_id      = intval($request->get_param('folder_id')) ?: 19;

    if (empty($file_name) || empty($file_size) || empty($file_type)) {
        return new WP_Error('missing_params', 'Paramètres manquants.', ['status' => 400]);
    }

    $token = KDRIVE_TOKEN;
    $drive_id = KDRIVE_DRIVE_ID;

    if (empty($token) || empty($drive_id)) {
        return new WP_Error('kdrive_config_error', 'Configuration KDrive manquante.', ['status' => 505]);
    }

    // 3. Envoyer à kDrive en multipart/form-data (comme Postman)
    $url = "https://api.infomaniak.com/3/drive/{$drive_id}/upload?"
        . http_build_query([
            'total_size'   => $file_size,
            'file_name'    => $file_name,
            'directory_id' => $folder_id,
        ]);

    $curl = curl_init();

    curl_setopt_array($curl, [
        CURLOPT_URL            => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . $token,
        ],
        CURLOPT_POSTFIELDS     => [
            'file' => new CURLFile($file_tmp, $file_type, $file_name),
        ],
        CURLOPT_TIMEOUT        => 60,
    ]);

    $raw_response = curl_exec($curl);
    $curl_error   = curl_error($curl);
    curl_close($curl);

    if ($curl_error) {
        return new WP_Error('kdrive_error', 'Erreur cURL : ' . $curl_error, ['status' => 500]);
    }

    $body = json_decode($raw_response, true);

    if (empty($body['data']['id'])) {
        return new WP_Error('kdrive_response_error', 'Réponse kDrive invalide : ' . $raw_response, ['status' => 500]);
    }

    $kdrive_file_id = sanitize_text_field($body['data']['id']);
    $file_type = sanitize_text_field($body['data']['mime_type']);

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
            'parent_id'      => $folder_id,
            'date_creation'  => current_time('mysql'),
        ],
        ['%s', '%s', '%d', '%s', '%d',  '%s', '%s']
    );

    if ($result === false) {
        return new WP_Error('db_insert_error', 'Erreur lors de l\'insertion.', ['status' => 500]);
    }

    return rest_ensure_response([
        'success' => true,
        'id'      => $wpdb->insert_id,
        'kdrive_file_id' => $kdrive_file_id,
        'message' => 'Média ajouté avec succès.',
    ]);
}

//------------------------------------------------------------------------------

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/medias/directory', [
        'methods'             => 'POST',
        'callback'            => 'myplugin_create_directory_medias',
        'permission_callback' => 'monplugin_verify_csrf',
    ]);
});


function myplugin_create_directory_medias(WP_REST_Request $request) {
    global $wpdb;

    $table_medias = $wpdb->prefix . "medias";
    $directory_name = sanitize_text_field($request->get_param('directory_name'));
    $parent_id = intval($request->get_param('parent_id')) ?: 19;

    $file_size = 0; // Taille par défaut pour un répertoire
    $date_creation = current_time('mysql');
    $uploaded_by    = get_current_user_id();

    if (empty($directory_name)) {
        return new WP_Error('missing_directory_name', 'Nom du répertoire manquant.', ['status' => 400]);
    }

    $token = KDRIVE_TOKEN;
    $drive_id = KDRIVE_DRIVE_ID;

    $url_to_create = "https://api.infomaniak.com/3/drive/{$drive_id}/files/team_directory";
    $result = wp_remote_post($url_to_create, [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'application/json',
        ],
        'body'    => json_encode([
            'name'      => $directory_name,
            'for_all_user' => true,
        ]),
    ]);

    if (is_wp_error($result)) {
        return new WP_Error('kdrive_error', 'Erreur lors de la création du répertoire : ' . $result->get_error_message(), ['status' => 500]);
    }

    $body = json_decode(wp_remote_retrieve_body($result), true);
    if (empty($body['data']['id'])) {
        return new WP_Error('kdrive_response_error', 'Réponse kDrive invalide : ' . wp_remote_retrieve_body($result), ['status' => 500]);
    }

    $initial_directory_id = sanitize_text_field($body['data']['id']);
    
    $url_to_move = "https://api.infomaniak.com/3/drive/{$drive_id}/files/{$initial_directory_id}/move/{$parent_id}";
    $move_result = wp_remote_post($url_to_move, [
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
            'Content-Type'  => 'application/json',
        ],
    ]);

    if (is_wp_error($move_result)) {
        return new WP_Error('kdrive_move_error', 'Erreur lors du déplacement du répertoire : ' . $move_result->get_error_message(), ['status' => 500]);
    }

    $result_to_database = $wpdb->insert(
        $table_medias,
        [
            'kdrive_file_id' => $initial_directory_id,
            'file_name'      => $directory_name,
            'file_size'      => $file_size,
            'file_type'      => 'directory',
            'uploaded_by'    => $uploaded_by,
            'parent_id'      => $parent_id,
            'date_creation'  => $date_creation,
        ],
        ['%s', '%s', '%d', '%s', '%d',  '%s', '%s']
    );

    if ($result_to_database === false) {
        return new WP_Error('db_insert_error', 'Erreur lors de l\'insertion du répertoire.', ['status' => 500]);
    }

    return rest_ensure_response([
        'success' => true,
        'directory_id' => $initial_directory_id,
        'kdrive_drive_id' => $drive_id,
        'parent_id' => $parent_id,
        'message' => 'Répertoire créé et déplacé avec succès.',
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

    $kdrive_file_id = $wpdb->get_var($wpdb->prepare("SELECT kdrive_file_id FROM $table_medias WHERE id = %d", $id));

    $deleted = $wpdb->delete($table_medias, ['id' => $id], ['%d']);

    if ($deleted === false) {
        return new WP_Error('db_delete_error', 'Erreur lors de la suppression.', ['status' => 500]);
    }

    if ($deleted === 0) {
        return new WP_Error('not_found', "Aucun média trouvé avec l'ID $id.", ['status' => 404]);
    }

    $kdrive_drive_id = KDRIVE_DRIVE_ID;
    $token = KDRIVE_TOKEN;

    $url_to_delete = "https://api.infomaniak.com/2/drive/{$kdrive_drive_id}/files/{$kdrive_file_id}";

    $delete_result = wp_remote_request($url_to_delete, [
        'method'  => 'DELETE',
        'headers' => [
            'Authorization' => 'Bearer ' . $token,
        ],
    ]);

    if (is_wp_error($delete_result)) {
        return new WP_Error('kdrive_delete_error', 'Erreur lors de la suppression sur kDrive : ' . $delete_result->get_error_message(), ['status' => 500]);
    }

    return rest_ensure_response(['success' => true, 'deleted_id' => $id]);
}

//------------------------------------------------------------------------------
// End of file
//------------------------------------------------------------------------------
