<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: users.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-20
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;

require_once MPS_TOOLS_FUNCTIONS_DIR . 'route_callback.php';
require_once MPS_TOOLS_CONTROLLERS_DIR . 'users.php';

//--------------------------------------------------------------------------------------------------
// Functions OR CLASS

add_action('rest_api_init', function () {
    $routes = [
        ['GET',  '/users',             'monplugin_get_users',               'mps_tools_verify_csrf_and_jwt'],
        ['GET',  '/adherents',         'monplugin_get_adherents',           'mps_tools_verify_csrf_and_jwt'],
        ['GET',  '/users/(?P<id>\d+)', 'monplugin_get_user',                'mps_tools_verify_csrf_and_jwt'],
        ['GET',  '/user_connected',    'monplugin_get_user_connected',      'mps_tools_verify_csrf_and_jwt'],
        ['POST', '/register',          'mps_tools_register_user',           'mps_tools_verify_csrf_and_jwt'],
        ['GET',  '/users/search',      'monplugin_search_user',             'mps_tools_verify_csrf_and_jwt'],
        ['POST', '/user/update',       'mps_tools_update_user',             'mps_tools_verify_csrf_and_jwt'],
        ['POST', '/psswd/reset',       'mps_tools_reset_password',          'mps_tools_verify_csrf_and_jwt'],
        ['POST', '/check-reset-key',   'mps_tools_check_reset_key',         '__return_true'],
        ['POST', '/password',          'mps_tools_reset_password_properly', '__return_true']
    ];

    foreach ($routes as [$method, $path, $callback, $permission_callback]) {
        register_rest_route('vue-plugin/v1', $path, [
            'methods'             => $method,
            'callback'            => $callback,
            'permission_callback' => $permission_callback,
        ]);
    }
});

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------