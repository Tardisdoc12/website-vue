<?php
//--------------------------------------------------------------------------------------------------
/*
* FILENAME: cron.php
* AUTHOR: Jean Anquetil
* DATE: 2026-09-19
* DESCRIPTIOn : 
*/
//--------------------------------------------------------------------------------------------------
// Imports

if (!defined('ABSPATH')) exit;

require_once MPS_TOOLS_FUNCTIONS_DIR . 'payement.php';

//--------------------------------------------------------------------------------------------------
// Functions OR CLASS


add_action('helloasso_check_pending_checkouts', 'helloasso_run_pending_checkouts_check');

if (!wp_next_scheduled('helloasso_check_pending_checkouts')) {
    wp_schedule_event(time(), 'hourly', 'helloasso_check_pending_checkouts');
}

//--------------------------------------------------------------------------------------------------

function helloasso_run_pending_checkouts_check() {
    $pending = helloasso_get_pending_checkouts();

    foreach ($pending as $checkout_intent_id => $meta) {
        // On ne revérifie que les paiements de plus de 50 minutes (marge sur la limite des 45 min de HelloAsso)
        if ((time() - $meta['created_at']) < 50 * 60) {
            continue;
        }

        mps_tools_finalize_helloasso_payment($checkout_intent_id, 'cron');

        // Qu'il ait réussi ou échoué définitivement (abandonné), on arrête de le suivre :
        // s'il a réussi, c'est fait ; s'il a échoué après 50 min, HelloAsso le considère abandonné.
        helloasso_untrack_pending_checkout($checkout_intent_id);
    }
}

//--------------------------------------------------------------------------------------------------
// End of file
//--------------------------------------------------------------------------------------------------