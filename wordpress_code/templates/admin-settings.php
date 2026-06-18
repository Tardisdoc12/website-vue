<?php
/**
* Page de paramètres du plugin
*/
if (!defined('ABSPATH')) exit;

// Enregistrement des settings
add_action('admin_init', function() {
    register_setting('mon_plugin_options', 'mon_plugin_kdrive_id');
    register_setting('mon_plugin_options', 'mon_plugin_kdrive_directory_id');
    register_setting('mon_plugin_options', 'mon_plugin_token');
});

// Ajout de la page dans le menu admin
add_action('admin_menu', function() {
    add_options_page(
        'Plugin Vue – Paramètres',
        'Plugin Vue',
        'manage_options',
        'mon-plugin-settings',
        'mon_plugin_render_settings_page'
    );
});

// Rendu de la page
function mon_plugin_render_settings_page() {
    ?>
    <div class="wrap">
        <h1>Paramètres Plugin Vue</h1>
        <form method="post" action="options.php">
            <?php settings_fields('mon_plugin_options'); ?>
            <table class="form-table">
                <tr>
                    <th>kDrive ID</th>
                    <td>
                        <input type="text" name="mon_plugin_kdrive_id"
                               value="<?php echo esc_attr(get_option('mon_plugin_kdrive_id')); ?>"
                               class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th>kDrive Directory ID</th>
                    <td>
                        <input type="text" name="mon_plugin_kdrive_directory_id"
                               value="<?php echo esc_attr(get_option('mon_plugin_kdrive_directory_id')); ?>"
                               class="regular-text" />
                    </td>
                </tr>
                <tr>
                    <th>Token</th>
                    <td>
                        <input type="password" name="mon_plugin_token"
                               value="<?php echo esc_attr(get_option('mon_plugin_token')); ?>"
                               class="regular-text" />
                    </td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}