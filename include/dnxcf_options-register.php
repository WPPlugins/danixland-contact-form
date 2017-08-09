<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

// include the file with the actual markup for the options to display
require( dirname( __FILE__ ) . '/dnxcf_options-display.php' );

/**
 * Register the set of options required for the plugin to work
 * @since 1.1.1
 */
register_setting( 'dnxcf_options', 'dnxcf_options', 'dnxcf_setup_validate' );

// our validation function
function dnxcf_setup_validate( $input ) {

    $dnxcf_options = get_option('dnxcf_options');
    $valid_input = $dnxcf_options;

    $submit = ( ! empty( $input['submit'] ) ? true : false );
    $reset = ( ! empty( $input['reset'] ) ? true : false );

    if($submit) {
        $default_options = dnxcf_set_options();
        // content type
        $valid_input['dnxcf_content_type'] = ( '1' == $input['dnxcf_content_type'] ? $default_options['dnxcf_content_type'] : "2" );
        // email address
        $valid_input['dnxcf_recv_name'] = ( '' == $input['dnxcf_recv_name'] ? $default_options['dnxcf_recv_name'] : sanitize_text_field($input['dnxcf_recv_name']) );
        $valid_input['dnxcf_recv_email'] = ( '' == $input['dnxcf_recv_email'] ? $default_options['dnxcf_recv_email'] : sanitize_email($input['dnxcf_recv_email']) );
        // subject options
        if ( '' == $input['dnxcf_subject'] ) {
            $valid_input['dnxcf_subject'] = $default_options['dnxcf_subject'];
        } else {
            $valid_input['dnxcf_subject'] = rtrim(esc_textarea( $input['dnxcf_subject'] ));
            $valid_input['dnxcf_subject'] = explode("\n", $valid_input['dnxcf_subject']);
        }
        // from email address and name
        $valid_input['dnxcf_from_email'] = ( '' == $input['dnxcf_from_email'] ? $default_options['dnxcf_from_email'] : sanitize_email($input['dnxcf_from_email']) );
        $valid_input['dnxcf_from_name'] = ( '' == $input['dnxcf_from_name'] ? $default_options['dnxcf_from_name'] : sanitize_text_field($input['dnxcf_from_name']) );
        // privacy policy
        $valid_html = array(
            'a' => array(
                'href' => array(),
                'title' => array()
            ),
            'br' => array(),
            'em' => array(),
            'strong' => array(),
            'p' => array()
        );
        $valid_input['dnxcf_privacy'] = ( '' == $input['dnxcf_privacy'] ? false : wp_kses($input['dnxcf_privacy'], $valid_html) );
        // latitude and longitude
        $valid_input['dnxcf_gmap_message'] = ( '' == $input['dnxcf_gmap_message'] ? $default_options['dnxcf_gmap_message'] : sanitize_text_field($input['dnxcf_gmap_message']) );
        if ( '' != $input['dnxcf_latitude'] ) {
            $valid_input['dnxcf_latitude'] = ( preg_match("/^[-]?[0-8]?[0-9]\.\d+|[-]?90\.0+?/A", $input['dnxcf_latitude']) ? $input['dnxcf_latitude'] : '' );
        } else {
            $valid_input['dnxcf_latitude'] = '';
        }
        if ( '' != $input['dnxcf_longitude'] ) {
            $valid_input['dnxcf_longitude'] = ( preg_match("/[-]?1[0-7][0-9]\.\d+|[-]?[0-9]?[0-9]\.\d+|[-]?180\.0+?/A", $input['dnxcf_longitude']) ? $input['dnxcf_longitude'] : '' );
        } else {
            $valid_input['dnxcf_longitude'] = '';
        }
    } elseif ($reset) {
        $default_options = dnxcf_set_options();
        // content type
        $valid_input['dnxcf_content_type'] = $default_options['dnxcf_content_type'];
        // email address
        $valid_input['dnxcf_recv_name'] = $default_options['dnxcf_recv_name'];
        $valid_input['dnxcf_recv_email'] = $default_options['dnxcf_recv_email'];
        // subject options
        $valid_input['dnxcf_subject'] = $default_options['dnxcf_subject'];
        // from email address and name
        $valid_input['dnxcf_from_email'] = $default_options['dnxcf_from_email'];
        $valid_input['dnxcf_from_name'] = $default_options['dnxcf_from_name'];
        // subject options
        $valid_input['dnxcf_privacy'] = $default_options['dnxcf_privacy'];
        // latitude and longitude
        $valid_input['dnxcf_gmap_message'] = $default_options['dnxcf_gmap_message'];
        $valid_input['dnxcf_latitude'] = $default_options['dnxcf_latitude'];
        $valid_input['dnxcf_longitude'] = $default_options['dnxcf_longitude'];
    }
    return $valid_input;
}

