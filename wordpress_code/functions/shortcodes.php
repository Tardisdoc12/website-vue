<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: shortcodes.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-19
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;

//--------------------------------------------------------------------------------------------------
// Functions

function get_mps_tools_shortcodes() {
    return [
        // shortcode => Files vue
        'calendar'         => 'calendar',
        'account'          => 'global_compte',
        'form_adhesion'    => 'adherent',
        'test'             => 'test_list',
        'event-page'       => 'event_page_proper',
        'reinitialisation' => 'reset_password_page',
        'render_payement'  => 'payement_render'
    ];
}

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------