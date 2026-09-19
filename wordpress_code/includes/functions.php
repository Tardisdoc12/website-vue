<?php
/*
* Les fonctions nécessaires dans plusieurs fichiers
*/

if (!defined('ABSPATH')) exit;

//------------------------------------------------------------------------------

// add_action('wp_print_scripts', function () {
//     if (!is_singular('event')) {
//         return;
//     }

//     global $wp_scripts;

//     foreach ($wp_scripts->queue as $handle) {
//         if (
//             strpos($handle, 'element-pack') !== false ||
//             strpos($handle, 'bdt-uikit') !== false
//         ) {
//             wp_dequeue_script($handle);
//             wp_deregister_script($handle);
//         }
//     }
// }, 0);

//------------------------------------------------------------------------------
// End of File
//------------------------------------------------------------------------------