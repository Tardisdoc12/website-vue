<?php
/**
 * kDrive settings tab configuration.
 */
if (!defined('ABSPATH')) exit;

return [
    'label' => 'kDrive',
    'fields' => [
        'mps_tools_kdrive_id' => [
            'label' => 'kDrive ID',
            'type'  => 'text',
        ],
        'mps_tools_kdrive_directory_id' => [
            'label' => 'kDrive Directory ID',
            'type'  => 'text',
        ],
        'mps_tools_kdrive_token' => [
            'label' => 'Token',
            'type'  => 'password',
        ],
    ],
];