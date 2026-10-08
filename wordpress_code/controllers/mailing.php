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
    // 'to' peut être un tableau (multiselect) ou une chaîne (ancien comportement)
    $raw_to = $request->get_param('to');
    $raw_to = is_array($raw_to) ? $raw_to : [$raw_to];

    // Nettoyer chaque email, puis retirer les vides et les invalides
    $to = array_values(array_unique(array_filter(
        array_map('sanitize_email', $raw_to),
        'is_email'
    )));

    $subject = sanitize_text_field($request->get_param('subject'));
    $message = sanitize_textarea_field($request->get_param('message'));
    $headers = $request->get_param('headers') ?: '';
    $attachments = $request->get_param('attachments') ?: [];

    if (empty($to) || empty($subject) || empty($message)) {
        return new WP_Error('missing_fields', 'Champs manquants', ['status' => 400]);
    }

    $mail_sent = mps_tools_send_email($to, $subject, $message, $headers, $attachments);

    if (!$mail_sent) {
        return new WP_Error(
            'mail_failed',
            'Échec de l\'envoi de l\'email : ' . print_r(error_get_last(), true),
            ['status' => 500]
        );
    }

    return rest_ensure_response(['success' => true, 'sent_to' => count($to)]);
}

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------