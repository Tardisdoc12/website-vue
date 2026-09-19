<?php
// Sécurité
if (!defined('ABSPATH')) {
    exit;
}
//------------------------------------------------------------------------------
// IMPORTS

require_once MPS_TOOLS_FUNCTIONS_DIR . 'shortcodes.php';
require_once MPS_TOOLS_OBJECTS_DIR . "shortcodesHookers.php";

//------------------------------------------------------------------------------
/**
 * Instanciation de la classe ShortcodesHookers
 */
$shortcodesHookers = new ShortcodesHookers(get_mps_tools_shortcodes());

//------------------------------------------------------------------------------
// End of File
//------------------------------------------------------------------------------