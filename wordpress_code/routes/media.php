<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: media.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-19
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;

require_once MPS_TOOLS_FUNCTIONS_DIR . 'route_callback.php';
require_once MPS_TOOLS_CONTROLLERS_DIR . 'media.php';

//--------------------------------------------------------------------------------------------------
// Functions OR CLASS

add_action('rest_api_init', function () {
    $routes = [
        ['GET',     '/medias/thumbnails',                      'mps_tools_get_medias_thumbnails'],
        ['GET',     '/medias/directory/(?P<directory_id>\d+)', 'mps_tools_get_medias'],
        ['GET',     '/medias',                                 'mps_tools_get_dir_medias'],
        ['POST',    '/medias',                                 'mps_tools_upload_medias'],
        ['POST',    '/medias/directory',                       'mps_tools_create_directory_medias'],
        ['DELETE',  '/medias/(?P<id>\d+)',                     'mps_tools_delete_medias'],
    ];

    foreach ($routes as [$method, $path, $callback]) {
        register_rest_route('vue-plugin/v1', $path, [
            'methods'             => $method,
            'callback'            => $callback,
            'permission_callback' => 'mps_tools_verify_csrf_and_jwt',
        ]);
    }
});

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------