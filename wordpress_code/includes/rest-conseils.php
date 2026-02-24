<?php
/*
*
* Gère les conseils des utilisateurs
*
*/
if (!defined('ABSPATH')) exit;

//------------------------------------------------------------------------------
// IMPORTS

$file = "functions.php";
require_once plugin_dir_path(__FILE__) . $file;

//------------------------------------------------------------------------------

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/conseils', [
        'methods' => 'POST',
        'callback' => 'myplugin_add_conseils',
        'permission_callback' => 'monplugin_verify_csrf',
    ]);
});

function myplugin_add_conseils(WP_REST_Request $request) {
    global $wpdb;
    $table_conseils = $wpdb->prefix . "conseils";
    $table_file = $wpdb->prefix . "source";

    $user_id = get_current_user_id();

    if (!$user_id) {
        return new WP_Error(
            'not_logged_in',
            'Utilisateur non connecté.',
            ['status' => 401]
        );
    }
    $file_id = $request->get_param('file_id');
    $exists = $wpdb->get_var(
        $wpdb->prepare(
            "SELECT COUNT(*) FROM $table_file WHERE id = %d",
            $file_id
        )
    );

    $wpdb->insert(
        $table_conseils,
        [
            'wp_user_id' => intval($user_id),
            'file_id'    => intval($file_id)
        ]
    );

    if ($wpdb->last_error) {
        return new WP_Error('db_insert_error', 'Erreur SQL (conseils) : ' . $wpdb->last_error, ['status' => 500]);
    }

    return array(
        'success'=>true,
        'message' => 'conseils ajouter',
    );
}

//------------------------------------------------------------------------------


add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/conseils', [
        'methods' => 'GET',
        'callback' => 'myplugin_get_conseils',
        'permission_callback' => 'monplugin_verify_csrf',
    ]);
});

function myplugin_get_conseils(WP_REST_Request $request) {
    global $wpdb;
    $table_conseils = $wpdb->prefix . "conseils";
    $user_id = get_current_user_id();

    if (!$user_id) {
        return new WP_Error(
            'not_logged_in',
            'Utilisateur non connecté.',
            ['status' => 401]
        );
    }
    $table_source = $wpdb->prefix . "source";

    $conseils = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT u.id AS source_id, u.path_file, u.url_file, u.tag, u.id_wp
             FROM $table_conseils f
             LEFT JOIN $table_source u
             ON f.file_id = u.id
             WHERE f.wp_user_id = %d",
            $user_id
        )
    );

    return [
        'success' => true,
        'conseils'    => $conseils
    ];
}

//------------------------------------------------------------------------------


add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/conseils/(?P<id>\d+)', [
        'methods' => 'GET',
        'callback' => 'myplugin_get_conseils_by_user',
        'permission_callback' => 'monplugin_verify_csrf',
    ]);
});

function myplugin_get_conseils_by_user(WP_REST_Request $request) {
    global $wpdb;
    $table_conseils = $wpdb->prefix . "conseils";
    $user_id = (int) $request->get_param('id');
    $user = get_user_by('id', $user_id);

    if (!$user) {
        return new WP_REST_Response(array(
            'message' => 'Utilisateur introuvable'
        ), 404);
    }
    $table_source = $wpdb->prefix . "source";

    $conseils = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT u.id AS source_id, u.path_file, u.url_file, u.tag, u.id_wp
             FROM $table_conseils f
             LEFT JOIN $table_source u
             ON f.file_id = u.id
             WHERE f.wp_user_id = %d",
            $user_id
        )
    );

    return [
        'success' => true,
        'conseils'    => $conseils
    ];
}

//------------------------------------------------------------------------------

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/conseils', [
        'methods' => 'DELETE',
        'callback' => 'myplugin_delete_conseils',
        'permission_callback' => 'monplugin_verify_csrf',
    ]);
});

function myplugin_delete_conseils(WP_REST_Request $request) {
    global $wpdb;
    $table_conseils = $wpdb->prefix . "conseils";
    $user_id = get_current_user_id();

    if (!$user_id) {
        return new WP_Error(
            'not_logged_in',
            'Utilisateur non connecté.',
            ['status' => 401]
        );
    }
    $file_id = $request->get_param('file_id');
    
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_conseils WHERE wp_user_id = %d AND file_id = %d",
        $user_id,
        $file_id
    ));

    if (!$exists) {
        return new WP_Error(
            'conseils_not_found',
            'Ce conseils n’existe pas.',
            ['status' => 404]
        );
    }

    $wpdb->delete(
        $table_conseils,
        [
            'wp_user_id' => $user_id,
            'file_id'    => $file_id
        ],
        [
            '%d',
            '%d'
        ]
    );

    return [
        'success' => true
    ];
}

//------------------------------------------------------------------------------

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/conseils/(?P<id>\d+)', [
        'methods' => 'DELETE',
        'callback' => 'myplugin_delete_all_by_conseils',
        'permission_callback' => 'monplugin_verify_csrf',
    ]);
});

function myplugin_delete_all_by_conseils(WP_REST_Request $request) {
    global $wpdb;
    $table_conseils = $wpdb->prefix . "conseils";
    $file_id = (int) $request->get_param('id');
    
    if (!$file_id) {
        return new WP_Error(
            'invalid_file_id',
            'ID de fichier invalide',
            ['status' => 400]
        );
    }

    $wpdb->delete(
        $table_conseils,
        ['file_id' => $file_id],
        ['%d']
    );

    return [
        'success' => true
    ];
}

//------------------------------------------------------------------------------
// End of File
//------------------------------------------------------------------------------