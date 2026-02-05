<?php
/* 
* Routes REST API pour la gestion des sous-catégories et sources
* Version : 1.0.0
*/

if (!defined('ABSPATH')) exit;

//------------------------------------------------------------------------------
// IMPORTS

require_once plugin_dir_path(__FILE__) . 'functions.php';

//------------------------------------------------------------------------------
// ROUTE : Récupération des sous-catégories

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/subcategories', [
        'methods' => 'GET',
        'callback' => 'myplugin_get_subcategories',
        'permission_callback' => '__return_true', // À définir si pas déjà fait
    ]);
});

function myplugin_get_subcategories(WP_REST_Request $request) {
    global $wpdb;

    $table_subcategories = $wpdb->prefix . "subcategories";

    $subcategories = $wpdb->get_results("SELECT * FROM $table_subcategories");

    return rest_ensure_response($subcategories);
}

//------------------------------------------------------------------------------
// ROUTE : Récupération des sous-catégories + sources associées

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/sources', [
        'methods' => 'GET',
        'callback' => 'myplugin_get_sources',
        'permission_callback' => 'monplugin_verify_csrf', // même remarque
    ]);
});

function myplugin_get_sources(WP_REST_Request $request) {
    global $wpdb;

    $table_subcategories = $wpdb->prefix . "subcategories";
    $table_source = $wpdb->prefix . "source";

    $sql = "
        SELECT s.id AS subcat_id, s.title AS subcat_title, s.id_categorie AS categorie_id,
               src.id AS source_id, src.path_file, src.url_file, src.tag
        FROM $table_subcategories s
        LEFT JOIN $table_source src
            ON s.id = src.id_subcategorie
        ORDER BY s.id ASC
    ";

    $results = $wpdb->get_results($sql);

    return rest_ensure_response($results);
}

//------------------------------------------------------------------------------

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/subcategories', [
        'methods' => 'POST',
        'callback' => 'myplugin_add_subcategories',
        'permission_callback' => 'monplugin_verify_csrf', // même remarque
    ]);
});

function myplugin_add_subcategories(WP_REST_Request $request) {
    global $wpdb;

    $table_subcategories = $wpdb->prefix . "subcategories";
    
    $params = $request->get_json_params();
    $title = sanitize_text_field($params['title'] ?? '');
    $id_categorie = intval($params['id_categorie'] ?? 0);

    if (empty($title) || $id_categorie < 0) {
        return new WP_Error('invalid_data', 'Titre ou catégorie invalide', ['status' => 400]);
    }

    $inserted = $wpdb->insert(
        $table_subcategories,
        ['title' => $title, 'id_categorie' => $id_categorie],
        ['%s','%d']
    );

    if (!$inserted) {
        return new WP_Error('db_error', $wpdb->last_error, ['status' => 500]);
    }

    return rest_ensure_response(['id' => $wpdb->insert_id]);
}

//------------------------------------------------------------------------------

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/sources', [
        'methods' => 'POST',
        'callback' => 'myplugin_add_sources',
        'permission_callback' => 'monplugin_verify_csrf', // même remarque
    ]);
});

function myplugin_add_sources(WP_REST_Request $request) {
    global $wpdb;

    $table_source = $wpdb->prefix . "source";
    
    $params = $request->get_json_params();
    $path_file = sanitize_text_field($params['path_file'] ?? '');
    $id_subcategorie = intval($params['id_subcategorie'] ?? 0);
    $url_file = sanitize_text_field($params['url_file'] ?? '');
    $tag = sanitize_text_field($params['tag'] ?? ''); 

    $inserted = $wpdb->insert(
        $table_source,
        ['id_subcategorie' => $id_subcategorie, 'path_file' => $path_file,'url_file' => $url_file, 'tag' => $tag ],
        ['%d','%s','%s', '%s']
    );

    if (!$inserted) {
        return new WP_Error('db_error', $wpdb->last_error, ['status' => 500]);
    }

    return rest_ensure_response(['id' => $wpdb->insert_id]);
}

//------------------------------------------------------------------------------

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/subcategories/(?P<id>\d+)', [
        'methods' => 'DELETE',
        'callback' => 'myplugin_rm_subcategories',
        'permission_callback' => 'monplugin_verify_csrf', // même remarque
    ]);
});

function myplugin_rm_subcategories(WP_REST_Request $request) {
    global $wpdb;
    $id = intval($request['id']);
    $table_subcategories = $wpdb->prefix . "subcategories";
    $deleted = $wpdb->delete($table_subcategories, ['id' => $id], ['%d']);

    if ($deleted === false) {
        return new WP_Error('db_delete_error', 'Erreur lors de la suppression.', ['status' => 500]);
    }

    if ($deleted === 0) {
        return new WP_Error('not_found', "Aucune sous-catégorie trouvée avec l'ID $id.", ['status' => 404]);
    }

    return rest_ensure_response(['success' => true, 'deleted_id' => $id]);
}

//------------------------------------------------------------------------------

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/sources/(?P<id>\d+)', [
        'methods' => 'DELETE',
        'callback' => 'myplugin_rm_sources',
        'permission_callback' => 'monplugin_verify_csrf', // même remarque
    ]);
});

function myplugin_rm_sources(WP_REST_Request $request) {
    global $wpdb;
    $id = intval($request['id']);
    $table_source = $wpdb->prefix . "source";
    $deleted = $wpdb->delete($table_source, ['id' => $id], ['%d']);

    if ($deleted === false) {
        return new WP_Error('db_delete_error', 'Erreur lors de la suppression.', ['status' => 500]);
    }

    if ($deleted === 0) {
        return new WP_Error('not_found', "Aucune source trouvée avec l'ID $id.", ['status' => 404]);
    }

    return rest_ensure_response(['success' => true, 'deleted_id' => $id]);
}

//------------------------------------------------------------------------------
// End of File
//------------------------------------------------------------------------------
