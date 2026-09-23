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

require_once MPS_TOOLS_FUNCTIONS_DIR . 'mailing.php';

//--------------------------------------------------------------------------------------------------
// Functions OR CLASS

function mps_tools_send_custom_email(WP_REST_Request $request) {
    $to = sanitize_email($request->get_param('to'));
    $subject = sanitize_text_field($request->get_param('subject'));
    $message = sanitize_textarea_field($request->get_param('message'));
    $headers = sanitize_text_field($request->get_param('headers'));
    $attachments = sanitize_text_field($request->get_param('attachments'));

    if (empty($to) || empty($subject) || empty($message)) {
        return new WP_Error('missing_fields', 'Champs manquants', ['status' => 400]);
    }

    $mail_sent = mps_tools_send_email($to, $subject, $message, $headers, $attachments);

    if (!$mail_sent) {
        return rest_ensure_response(new WP_Error(
            'mail_failed', 'Échec de l\'envoi de l\'email Pour les raisons suivantes: ' . print_r(error_get_last(), true), 
            ['status' => 500]
        ));
    }

    return rest_ensure_response(['success' => true]);
}

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------