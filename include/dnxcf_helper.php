<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// generate a unique code, allows for lenght parameter
if ( ! function_exists('dnxcf_get_unique_code') ) {

    function dnxcf_get_unique_code( $length = "" ) {
        $code = md5( uniqid( rand(), true ) );

        if ($length != "") {
            return substr($code, 0, $length);
        } else {
            return $code;
        }
    }

}

// add button in edit pages to help include our form
function dnxcf_show_form_button() {
    $currentScreen = get_current_screen();
    if ( $currentScreen->parent_base == "edit" ) {
        echo '<button type="button" id="dnxcf-contact-form" class="button" onclick="dnxcf_send_code()"><span class="dashicons dashicons-testimonial"></span> ' . __('Add Contact Form', 'dnxcf' ) . '</button>';
    }
}
add_action( 'media_buttons', 'dnxcf_show_form_button', 11 );

// the actual function that outputs our shortcode once the button is pressed
function dnxcf_insert_shortcode() {
    $currentScreen = get_current_screen();
    if ( $currentScreen->parent_base != "edit" ) {
        return;
    } ?>
<script>
    function dnxcf_send_code() {
        //Send the shortcode to the editor
        window.send_to_editor("[dnx_contactform]");
    }
</script>
<?php
}
add_action( 'admin_footer', 'dnxcf_insert_shortcode' );


// set default options for the plugin
function dnxcf_set_options() {
    $defaults = array(
        'dnxcf_pid_key'         => dnxcf_get_unique_code(12),
        'dnxcf_recv_name'       => 'admin',
        'dnxcf_recv_email'      => get_bloginfo('admin_email'),
        'dnxcf_from_email'      => 'info@some.url',
        'dnxcf_from_name'       => 'webmaster',
        'dnxcf_subject'         => array(
            __('I want to make a comment.', 'dnxcf'),
            __('I want to ask a question.', 'dnxcf'),
            __('I am interested in a product.', 'dnxcf'),
            __('I have to report a problem.', 'dnxcf'),
            __('Other (explain below)', 'dnxcf')
        ),
        // 1 = text/plain
        // 2 = text/html
        'dnxcf_content_type'    => '1',
        'dnxcf_privacy'         => '',
        'dnxcf_latitude'        => '38.2704',
        'dnxcf_longitude'       => '16.2971',
        'dnxcf_gmap_message'    => '',
        'dnxcf_DB_VERSION'      => '1'
    );
    return $defaults;
}

// helper function that starts up the DB
function dnxcf_db_init() {
    global $dnxcf_options;
    $dnxcf_options = get_option('dnxcf_options');
    if( false === $dnxcf_options ) {
        $dnxcf_options = dnxcf_set_options();
    }
    update_option('dnxcf_options', $dnxcf_options);
}

// helper function that performs a DB version update when needed
function dnxcf_db_update($db_version) {
    global $dnxcf_options;
    $db_defaults = dnxcf_set_options();
    $merge = wp_parse_args( $dnxcf_options, $db_defaults );
    // update DB version
    $merge['dnxcf_DB_VERSION'] = $db_version;
    update_option('dnxcf_options', $merge);
}

// helper function that performs a DB check and then an init/update action
function dnxcf_db_check() {
    global $dnxcf_options;
    if(false === $dnxcf_options) {
        dnxcf_db_init();
    }
    $old_db_version = $dnxcf_options['dnxcf_DB_VERSION'];
    $new_db_version = DNXCF_CURRENT_DB_VERSION;
    if(empty($old_db_version)) {
        dnxcf_db_init();
    }
    if( intval($old_db_version) < intval($new_db_version) ) {
        dnxcf_db_update( $new_db_version );
    }
}

// helper function that sets the current DB Version for comparison
function dnxcf_set_db_version() {
    // Define plugin database version. This should only change when new settings are added.
    if ( ! defined( 'DNXCF_CURRENT_DB_VERSION' ) ) {
        define( 'DNXCF_CURRENT_DB_VERSION', 3 );
    }
}

// set the "from" email name to a custom option specified by the user
function dnxcf_update_from_name() {
    global $dnxcf_options;
    $dnxcf_options = get_option('dnxcf_options');
    $dnxcf_defaults = dnxcf_set_options();
    $from_name = $dnxcf_options['dnxcf_from_name'];
    $orig_name = 'WordPress';

    $name = ( $orig_name != $from_name ) ? $from_name : false;
    return $name;
}
if (dnxcf_update_from_name())
    add_filter( 'wp_mail_from_name', 'dnxcf_update_from_name' );


// set the "from" email address to a custom option specified by the user
function dnxcf_update_from_email() {
    global $dnxcf_options;
    $dnxcf_options = get_option('dnxcf_options');
    $dnxcf_defaults = dnxcf_set_options();
    $from_mail = $dnxcf_options['dnxcf_from_email'];
    $orig_mail = $dnxcf_defaults['dnxcf_from_email'];

    $mail = ( $orig_mail != $from_mail ) ? $from_mail : false;
    return $mail;
}
if (dnxcf_update_from_email())
    add_filter( 'wp_mail_from', 'dnxcf_update_from_email' );

function dnxcf_update_content_type() {
    global $dnxcf_options;
    $dnxcf_options = get_option('dnxcf_options');

    // 1 = text/plain
    // 2 = text/html
    $content_type = ( "1" == $dnxcf_options['dnxcf_content_type'] ) ? 'text/plain' : 'text/html';

    return $content_type;
}
add_filter( 'wp_mail_content_type', 'dnxcf_update_content_type' );
