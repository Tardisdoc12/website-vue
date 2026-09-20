<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: favoris.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-19
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;



//--------------------------------------------------------------------------------------------------
// Functions OR CLASS

function mps_tools_add_favoris(WP_REST_Request $request) {
    global $wpdb;
    $table_favoris = $wpdb->prefix . "favoris";
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
        $table_favoris,
        [
            'wp_user_id' => intval($user_id),
            'file_id'    => intval($file_id)
        ]
    );

    if ($wpdb->last_error) {
        return new WP_Error('db_insert_error', 'Erreur SQL (favoris) : ' . $wpdb->last_error, ['status' => 500]);
    }

    return array(
        'success'=>true,
        'message' => 'favoris ajouter',
    );
}

//--------------------------------------------------------------------------------------------------


function mps_tools_get_favoris(WP_REST_Request $request) {
    global $wpdb;
    $table_favoris = $wpdb->prefix . "favoris";
    $user_id = get_current_user_id();

    if (!$user_id) {
        return new WP_Error(
            'not_logged_in',
            'Utilisateur non connecté.',
            ['status' => 401]
        );
    }
    $table_source = $wpdb->prefix . "source";

    $favoris = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT u.id AS source_id, u.path_file, u.url_file, u.tag, u.id_wp
             FROM $table_favoris f
             LEFT JOIN $table_source u
             ON f.file_id = u.id
             WHERE f.wp_user_id = %d",
            $user_id
        )
    );

    return [
        'success' => true,
        'favoris'    => $favoris
    ];
}

//--------------------------------------------------------------------------------------------------

function mps_tools_get_favoris_by_user(WP_REST_Request $request) {
    global $wpdb;
    $table_favoris = $wpdb->prefix . "favoris";
    $user_id = (int) $request->get_param('id');
    $user = get_user_by('id', $user_id);

    if (!$user) {
        return new WP_REST_Response(array(
            'message' => 'Utilisateur introuvable'
        ), 404);
    }
    $table_source = $wpdb->prefix . "source";

    $favoris = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT u.id AS source_id, u.path_file, u.url_file, u.tag, u.id_wp
             FROM $table_favoris f
             LEFT JOIN $table_source u
             ON f.file_id = u.id
             WHERE f.wp_user_id = %d",
            $user_id
        )
    );

    return [
        'success' => true,
        'favoris'    => $favoris
    ];
}

//--------------------------------------------------------------------------------------------------

function mps_tools_delete_favoris(WP_REST_Request $request) {
    global $wpdb;
    $table_favoris = $wpdb->prefix . "favoris";
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
        "SELECT COUNT(*) FROM $table_favoris WHERE wp_user_id = %d AND file_id = %d",
        $user_id,
        $file_id
    ));

    if (!$exists) {
        return new WP_Error(
            'favoris_not_found',
            'Ce favoris n’existe pas.',
            ['status' => 404]
        );
    }

    $wpdb->delete(
        $table_favoris,
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

//--------------------------------------------------------------------------------------------------

function mps_tools_delete_all_by_favoris(WP_REST_Request $request) {
    global $wpdb;
    $table_favoris = $wpdb->prefix . "favoris";
    $file_id = (int) $request->get_param('id');
    
    if (!$file_id) {
        return new WP_Error(
            'invalid_file_id',
            'ID de fichier invalide',
            ['status' => 400]
        );
    }

    $wpdb->delete(
        $table_favoris,
        ['file_id' => $file_id],
        ['%d']
    );

    return [
        'success' => true
    ];
}

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------