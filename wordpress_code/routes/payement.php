<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: payement.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-19
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;

require_once MPS_TOOLS_FUNCTIONS_DIR . 'route_callback.php';
require_once MPS_TOOLS_CONTROLLERS_DIR . 'payement.php';

//--------------------------------------------------------------------------------------------------
// Functions OR CLASS

add_action('rest_api_init', function () {
    $routes = [
        ['POST',     '/create_payements',  'mps_tools_create_payements',     'mps_tools_verify_csrf_and_jwt'],
        ['GET',    '/check_payment',       'mps_tools_handle_check_payment', '__return_true'],
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