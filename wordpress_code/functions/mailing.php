<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: mailing
* AUTHOR: Jean Anquetil
* DATE: 2026-09-19
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;



//--------------------------------------------------------------------------------------------------
// Functions

/**
 * Remplace les balises {{xxx}} dans un template par leurs vraies valeurs.
 *
 * @param string $template Le texte contenant des balises {{xxx}}
 * @param array  $data      Tableau associatif ['xxx' => 'valeur']
 * @return string Le texte avec les balises remplacées
 */
function mps_tools_render_email_template($template, $data = []) {
    if (empty($template)) return '';

    return preg_replace_callback('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', function($matches) use ($data) {
        $tag = $matches[1];
        return isset($data[$tag]) ? $data[$tag] : $matches[0]; // si la balise n'a pas de valeur, on la laisse telle quelle
    }, $template);
}


//--------------------------------------------------------------------------------------------------

function mps_tools_send_email($to, $subject, $message, $headers = [], $attachments = []) {
    if (empty($to) || empty($subject) || empty($message)) {
        return false;
    }

    return wp_mail($to, $subject, $message, $headers, $attachments);
}

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------