<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: connexion.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-19
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;

require_once MPS_TOOLS_FUNCTIONS_DIR . 'route_callback.php';
require_once MPS_TOOLS_CONTROLLERS_DIR . 'connexion.php';

//------------------------------------------------------------------------------
// Functions OR CLASS

add_action('rest_api_init', function () {
    register_rest_route('vue-plugin/v1', '/connect', [
        'methods' => 'POST',
        'callback' => 'mps_tools_login_user',
        'permission_callback' => 'mps_tools_verify_csrf_and_jwt',
    ]);
});

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------