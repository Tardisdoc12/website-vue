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
    $raw_to = $request->get_param('to');
    $raw_to = is_array($raw_to) ? $raw_to : [$raw_to];

    $to = array_values(array_unique(array_filter(
        array_map('sanitize_email', $raw_to),
        'is_email'
    )));

    $subject = sanitize_text_field($request->get_param('subject'));
    $message = sanitize_textarea_field($request->get_param('message'));
    $headers = $request->get_param('headers') ?: '';

    if (empty($to) || empty($subject) || empty($message)) {
        return new WP_Error('missing_fields', 'Champs manquants', ['status' => 400]);
    }

    // --- Pièce jointe ---
    $attachments = [];
    $files = $request->get_file_params();

    if (!empty($files['attachments']) && $files['attachments']['error'] !== UPLOAD_ERR_NO_FILE) {
        $file = $files['attachments'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            return new WP_Error('upload_failed', 'Erreur lors de l\'upload du fichier', ['status' => 400]);
        }
        if ($file['size'] > 5 * MB_IN_BYTES) {
            return new WP_Error('file_too_large', 'Fichier trop volumineux (5 Mo max)', ['status' => 400]);
        }

        require_once ABSPATH . 'wp-admin/includes/file.php';

        $uploaded = wp_handle_upload($file, [
            'test_form' => false,
            'mimes'     => [
                'pdf'      => 'application/pdf',
                'jpg|jpeg' => 'image/jpeg',
                'png'      => 'image/png',
            ],
        ]);

        if (isset($uploaded['error'])) {
            return new WP_Error('upload_rejected', $uploaded['error'], ['status' => 400]);
        }

        $attachments[] = $uploaded['file']; // chemin absolu sur le serveur
    }

    $mail_sent = mps_tools_send_email($to, $subject, $message, $headers, $attachments);

    // Supprimer le fichier temporaire après l'envoi
    foreach ($attachments as $path) {
        if (file_exists($path)) {
            unlink($path);
        }
    }

    if (!$mail_sent) {
        return new WP_Error('mail_failed', 'Échec de l\'envoi de l\'email', ['status' => 500]);
    }

    return rest_ensure_response(['success' => true, 'sent_to' => count($to)]);
}

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------