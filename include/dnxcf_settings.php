<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
/**
 * Include settings options for our plugin
 * @since 0.2
 */
add_action('admin_menu', 'dnxcf_settings' );
function dnxcf_settings() {
    add_menu_page('danixland Contact Form Settings', __('Contact Form', 'dnxcf'), 'manage_options', 'dnxcf_options', 'dnxcf_settings_display', 'dashicons-testimonial');
}

/**
 * The function that outputs our admin page
 * @since 0.2
 */
function dnxcf_settings_display() {
?>
    <div class="wrap">
        <h2><?php _e('danixland Contact Form Set up', 'dnxcf') ?></h2>
        <form method="post" action="options.php">
            <?php
                settings_fields('dnxcf_options');
                do_settings_sections('dnxcf_options_sections');
            ?>
            <p class="submit">
                <input name="dnxcf_options[submit]" type="submit" class="button-primary" value="<?php esc_attr_e('Save Changes', 'dnxcf') ?>" />
                <input name="dnxcf_options[reset]" type="submit" class="button-secondary" value="<?php esc_attr_e('Reset Defaults', 'dnxcf'); ?>" />
            </p>
        </form>
    </div>
<?php
}

/**
 * Settings API options initilization and validation
 * @since 0.2
 */
function dnxcf_register_options() {
    require( dirname( __FILE__ ) . '/dnxcf_options-register.php' );
}
add_action('admin_init', 'dnxcf_register_options');
