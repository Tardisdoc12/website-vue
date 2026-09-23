<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: mailing.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-23
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;

require_once MPS_TOOLS_CONTROLLERS_DIR . 'mailing.php';
require_once MPS_TOOLS_FUNCTIONS_DIR . 'route_callback.php';

//--------------------------------------------------------------------------------------------------
// Functions OR CLASS

add_action('rest_api_init', function () {
    register_rest_route('mps-tools/v1', '/send-custom-email', [
        'methods' => 'POST',
        'callback' => 'mps_tools_send_custom_email',
        'permission_callback' => 'mps_tools_verify_csrf_and_jwt',
    ]);
});

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------