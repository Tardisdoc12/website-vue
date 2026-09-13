<?php
/**
 * kDrive settings tab configuration.
 */
if (!defined('ABSPATH')) exit;

return [
    'label' => 'kDrive',
    'fields' => [
        'mon_plugin_kdrive_id' => [
            'label' => 'kDrive ID',
            'type'  => 'text',
        ],
        'mon_plugin_kdrive_directory_id' => [
            'label' => 'kDrive Directory ID',
            'type'  => 'text',
        ],
        'mon_plugin_token' => [
            'label' => 'Token',
            'type'  => 'password',
        ],
    ],
];