<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

/* 
Plugin Name:    danixland contact form
Description:    A simple yet powerful contact form plugin
Plugin URI:     http://danixland.net
Version:        1.1.1
Author:         Danilo 'danix' Macr&igrave;
Author URI:     http://danixland.net
License:        GPL2
License URI:    https://www.gnu.org/licenses/gpl-2.0.html
Domain Path:    /languages
Text Domain:    dnxcf
*/

global $dnxcf_form_version;
$dnxcf_form_version = '1.1';

/**
 * Add plugin i18n domain: dnxcf
 * @since 0.2
 */
load_plugin_textdomain('dnxcf', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/');

/**
 * Let's load our helper file ( DB initialization and stuff )
 * @since 0.2
 */
require( dirname( __FILE__ ) . '/include/dnxcf_helper.php' );

/**
 * Check / create the database for the plugin data
 * @since 1.1.3
 */
register_activation_hook( __FILE__, 'dnxcf_set_db_version' );
register_activation_hook( __FILE__, 'dnxcf_db_check' );

if ( is_admin()) {
    require_once( dirname(__FILE__) . '/include/dnxcf_settings.php' );
}

// Add Genericons, used in the contact form.
function dnxcf_scripts() {
    wp_enqueue_style( 'dnxcf_genericons', plugins_url( '/style/genericons/genericons.css', __FILE__ ), array(), '3.4.1' );
    wp_enqueue_style( 'dnxcf_helper_style', plugins_url( '/style/dnxcf_style.css', __FILE__ ), array(), '4.4.2' );
}
add_action( 'wp_enqueue_scripts', 'dnxcf_scripts' );

/*
 * This function displays a google map by adding a js script to the wp_footer
 * @since 1.0
 */
function dnxcf_gmap_enqueue() {
    global $dnxcf_options;
    $dnxcf_options = get_option('dnxcf_options');
    $latitude = $dnxcf_options['dnxcf_latitude'];
    $longitude = $dnxcf_options['dnxcf_longitude'];
    $sitename = get_bloginfo('name');
    $siteurl = get_bloginfo('url');
    $sitemsg = $dnxcf_options['dnxcf_gmap_message'];
    $visitus = __('Come visit us', 'dnxcf');

$popup_content = <<< DNX6655788EOT
<div id="content">
    <div id="siteNotice">
    </div>
    <h1 id="firstHeading" class="firstHeading">$sitename</h1>
    <div id="bodyContent">
        <p>$sitemsg</p>
        <p><a href="$siteurl">$visitus</a></p>
    </div>
</div>
DNX6655788EOT;
?>
<script src="https://maps.googleapis.com/maps/api/js"></script>
<script>
  var dnxcf_map_retrieve = (function () {
    var myLatlng = new google.maps.LatLng(<?php echo $latitude . ', ' . $longitude; ?>),
        mapCenter = new google.maps.LatLng(<?php echo $latitude . ', ' . $longitude; ?>),
        mapCanvas = document.getElementById('dnxcf_gmap'),
        mapOptions = {
          center: mapCenter,
          zoom: 15,
          scrollwheel: true,
          draggable: true,
          disableDefaultUI: true,
          mapTypeId: google.maps.MapTypeId.ROADMAP
        },
        map = new google.maps.Map(mapCanvas, mapOptions),
        contentString = <?php echo json_encode($popup_content); ?>,
        infowindow = new google.maps.InfoWindow({
          content: contentString,
          maxWidth: 300
        }),
        marker = new google.maps.Marker({
          position: myLatlng,
          map: map,
          title: <?php echo json_encode($sitename); ?>
        });

    return {
      init: function () {
        map.set('styles', [{
          featureType: 'landscape',
          elementType: 'geometry',
          stylers: [
            { hue: '#ffff00' },
            { saturation: 30 },
            { lightness: 10}
          ]}
        ]);

        google.maps.event.addListener(marker, 'click', function () {
          infowindow.open(map,marker);
        });
      }
    };
  }());

  dnxcf_map_retrieve.init();
</script>
<?php
}
global $dnxcf_options;
$dnxcf_options = get_option('dnxcf_options');
if ( ! empty($dnxcf_options['dnxcf_longitude']) && ! empty($dnxcf_options['dnxcf_latitude'])) {
    add_action('wp_footer', 'dnxcf_gmap_enqueue');
}


/*
 * The actual contact form displayed using a shortcode
 * @since 0.1
 */
add_shortcode( 'dnx_contactform', 'dnxcf_return_form');

/*
 * we use output buffer so that the form will stay at the bottom of content
 * allowing the user to write some text in the body of the page before 
 * displaying the form with the shortcode
 * @since 0.5
 */
function dnxcf_return_form() {
    ob_start();
    dnxcf_display_form();
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
}

/*
 * Here we handle the input/output flow
 * @since 0.2
 */
function dnxcf_display_form() {
    global $dnxcf_form_version, $dnxcf_options;
    $dnxcf_options = get_option('dnxcf_options');

    $dnxcf_pid            = md5($dnxcf_options['dnxcf_pid_key']);
    $dnxcf_vers_id        = md5($dnxcf_form_version);
    $dnxcf_date_id        = md5(date('TOZ'));
    $dnxcf_eml_id         = md5($dnxcf_options['dnxcf_recv_email']);
    $dnxcf_form_id        = $dnxcf_pid . $dnxcf_vers_id . $dnxcf_eml_id . $dnxcf_date_id;
    $dnxcf_form_id        = strtoupper(md5($dnxcf_form_id));
    $dnxcf_form_id        = 'ID' . $dnxcf_form_id . 'DNX';
    $dnxcf_send_value     = trim( strtolower( 'submit_' . md5($dnxcf_form_id) ) );

    if ( isset( $_POST[$dnxcf_send_value] ) ) { // the form has been submitted
        $dnxcf_email_output = ( '1' == $dnxcf_options['dnxcf_content_type'] ) ? 'text/plain' : 'text/html';
        $dnxcf_form_name = 'dnxcf_form_' . $dnxcf_pid;
        $dnxcf_form_action = 'dnxcf_submit_' . $dnxcf_form_id;
        // valid html used to validate the comment content.
        $valid_html = array(
            'a' => array(
                'href' => array(),
                'title' => array()
            ),
            'br' => array(),
            'em' => array(),
            'strong' => array(),
            'p' => array(),
            'pre' => array(),
            'code' => array()
        );
        // security checks before submitting the form
        if ( $_SERVER['REQUEST_URI'] == $_POST['_wp_http_referer'] && wp_verify_nonce( $_POST[ $dnxcf_form_name], $dnxcf_form_action ) ) {

            $dnxcf_posted = array();
            // let's gather some data about the user submitting the form
            $dnxcf_ltd    = trim(strip_tags(stripslashes(current_time("mysql"))));
            $dnxcf_hst    = trim(strip_tags(stripslashes(getenv("REMOTE_ADDR"))));
            $dnxcf_ua     = trim(strip_tags(stripslashes($_SERVER['HTTP_USER_AGENT'])));
            // our posted options, arranged in one nice array
            $dnxcf_posted['dnxcf_name'] = sanitize_text_field($_POST['dnxcf_name']);
            $dnxcf_posted['dnxcf_email'] = sanitize_email($_POST['dnxcf_email']);
            $dnxcf_posted['dnxcf_website'] = esc_url($_POST['dnxcf_website']);
            $dnxcf_posted['dnxcf_subject'] = sanitize_text_field($_POST['dnxcf_subject']);
            $dnxcf_posted['dnxcf_message'] = wp_kses($_POST['dnxcf_message'], $valid_html);
            // let's begin with our email data, like receiver email, subject ecc.
            $dnxcf_to         = $dnxcf_options['dnxcf_recv_email'];
            $dnxcf_headers    = "Reply-To: " . $dnxcf_posted['dnxcf_email'];
            $dnxcf_subject    = __('Contact from "', 'dnxcf') . get_bloginfo('name') . '" - ' . $dnxcf_posted['dnxcf_subject'];

            // check for our content type and arrange our info accordingly
            if ( 'text/html' == $dnxcf_email_output ) {
                require( apply_filters( 'dnxcf_template_file', dirname( __FILE__ ) . '/include/dnxcf_mail_template_danixland.php') );
                $dnxcf_email_data = array(
                    'ownname' => $dnxcf_options['dnxcf_recv_name'],
                    'site' => get_bloginfo('name'),
                    'time' => $dnxcf_ltd,
                    'host' => $dnxcf_hst,
                    'ua' => $dnxcf_ua,
                );
                $dnxcf_message = dnxcf_email_content( $dnxcf_email_data, $dnxcf_posted );
            } else { // content_type is set to text/plain
                $dnxcf_message = sprintf(
                    __("Hello \"%s\",\nyou are being contacted by %s on %s.\n%s has provided the following informations:\n\tEmail:\t\t%s\n\tWebsite:\t%s\n\tMessage:\n\n%s", 'dnxcf'),
                    $dnxcf_options['dnxcf_recv_name'],
                    $dnxcf_posted['dnxcf_name'],
                    get_bloginfo('name'),
                    $dnxcf_posted['dnxcf_name'],
                    $dnxcf_posted['dnxcf_email'],
                    $dnxcf_posted['dnxcf_website'],
                    $dnxcf_posted['dnxcf_message']
                );
                $dnxcf_message .= "\n\n##-----------#-----------#-----------##\n\n";
                $dnxcf_message .= sprintf(
                    __("We have also collected the following informations:\n\tBrowser:\t%s\n\tTime:\t\t%s\n\tIP Address:\t%s\n", 'dnxcf'),
                    $dnxcf_ua,
                    $dnxcf_ltd,
                    $dnxcf_hst
                );
            } // end check for mail_content_type
            $dnxcf_mailed = wp_mail( $dnxcf_to, $dnxcf_subject, $dnxcf_message, $dnxcf_headers );
            if ( $dnxcf_mailed ) { ?>
            <p id="dnxcf_success"><?php _e('your email was sent successfully. Here\'s the data you submitted via our form.', 'dnxcf' ); ?></p>
            <p>
                <dl>
                    <dt><?php _e('Your Name:', 'dnxcf'); ?></dt>
                    <dd><?php echo $dnxcf_posted['dnxcf_name']; ?></dd>
                    <dt><?php _e('Your e-mail:', 'dnxcf'); ?></dt>
                    <dd><?php echo $dnxcf_posted['dnxcf_email']; ?></dd>
                    <dt><?php _e('Subject of your message:', 'dnxcf'); ?></dt>
                    <dd><?php echo $dnxcf_posted['dnxcf_subject']; ?></dd>
                    <dt><?php _e('Text of your Message:', 'dnxcf'); ?></dt>
                    <dd><blockquote><?php echo wptexturize( wpautop( $dnxcf_posted['dnxcf_message'] ) ); ?></blockquote></dd>
                </dl>
            </p>
            <p><?php _e('we also collected some data for tecnical reasons.', 'dnxcf'); ?></p>
            <p>
                <dl>
                    <dt><?php _e('Your IP address:', 'dnxcf'); ?></dt>
                    <dd><?php echo $dnxcf_hst; ?></dd>
                    <dt><?php _e('Your Browser:', 'dnxcf'); ?></dt>
                    <dd><?php echo $dnxcf_ua; ?></dd>
                </dl>
            </p>
            <?php } else { ?>
            <p id="dnxcf_result_failure"><?php printf( __('there was a problem processing your email. Please contact the <a href="mailto:%s">administrator</a>.', 'dnxcf'), get_bloginfo('admin_email') ); ?></p>
            <?php } ?>

<?php
        } else { // Houston we have a problem, spammer detected
                // we might want to use the data we have and send a mail to the admin just in case...
?>
    <p id="dnxcf_spammer"><?php _e('looks like you don\'t belong here. We don\'t like spammers. Go away now!', 'dnxcf'); ?></p>
<?php
        } // end check for wp_nonce

    } else { // the post hasn't been submitted. Let's show the form
        global $dnxcf_options;
        $dnxcf_options = get_option('dnxcf_options');
?>
    <!-- begin #dnxcf_form -->
    <form id="dnxcf_form" method="post" action="<?php echo htmlentities( $_SERVER['REQUEST_URI'] ); ?>">
        <?php wp_nonce_field( 'dnxcf_submit_' . $dnxcf_form_id, 'dnxcf_form_' . $dnxcf_pid, true, true ); 
        if ( ! empty($dnxcf_options['dnxcf_longitude']) && ! empty($dnxcf_options['dnxcf_latitude'])) : ?>
        <h4><?php _e('Our location', 'dnxcf'); ?></h4>
        <div id="dnxcf_gmap"></div>
        <?php endif; ?>
        <p class="comment-notes"><?php _e('Required fields are marked ', 'dnxcf'); ?><span class="required">*</span></p>
        <fieldset id="dnxcf_formwrap">
        <?php if ( $dnxcf_options['dnxcf_privacy'] ) : ?>
            <fieldset id="dnxcf_privacy">
                <h4><?php _e('Privacy:', 'dnxcf'); ?></h4>
                <div class="policy"><?php echo wpautop( wptexturize( $dnxcf_options['dnxcf_privacy'] ) ); ?></div>
            </fieldset>
        <?php endif; ?>
            <fieldset id="dnxcf_personal">
                <h4><?php _e('Personal Informations:', 'dnxcf'); ?></h4>
                <p class="dnxcf-name">
                    <label class="genericon genericon-user" for="name"><span class="screen-reader-text"><?php _e('Full Name', 'dnxcf'); ?></span><span class="required">*</span></label>
                    <input name="dnxcf_name" id="name" size="35" maxlength="40" type="text" placeholder="<?php _e('name', 'dnxcf'); ?>" required>
                </p>
                <p class="dnxcf-email">
                    <label class="genericon genericon-mail" for="email"><span class="screen-reader-text"><?php _e('email address', 'dnxcf'); ?></span><span class="required">*</span></label>
                    <input name="dnxcf_email" id="email" size="35" maxlength="50" type="email" placeholder="<?php _e('e-mail', 'dnxcf'); ?>" required>
                </p>
                <p class="dnxcf-website">
                    <label for="website" class="genericon genericon-website"><span class="screen-reader-text"><?php _e('website', 'dnxcf'); ?></span></label>
                    <input name="dnxcf_website" id="website" size="35" maxlength="50" type="url" placeholder="<?php _e('website', 'dnxcf'); ?>">
                </p>
            </fieldset>
            <fieldset id="dnxcf_message">
                <h4><?php _e('your message:', 'dnxcf'); ?></h4>
                <p class="dnxcf-subject">
                    <label class="genericon genericon-tag" for="subject"><span class="screen-reader-text"><?php _e('subject', 'dnxcf'); ?></span><span class="required">*</span></label>
                    <select name="dnxcf_subject" id="subject" required>
                    <?php global $dnxcf_options;
                    $dnxcf_options = get_option('dnxcf_options');
                    $subject_options = $dnxcf_options['dnxcf_subject'];
                    foreach ($subject_options as $option) {
                        echo '<option value="' . $option . '">' . $option . '</option>';
                    } ?>
                    </select>
                </p>
                <p class="dnxcf-message">
                    <label class="genericon genericon-comment" for="message"><span class="screen-reader-text"><?php _e('message', 'dnxcf'); ?></span><span class="required">*</span></label>
                    <textarea name="dnxcf_message" id="message" cols="60" rows="12" placeholder="<?php _e('Comment Here', 'dnxcf'); ?>" required></textarea>
                </p>
            </fieldset>
            <fieldset id="dnxcf_send">
                <input type="submit" name="<?php echo $dnxcf_send_value; ?>" id="<?php echo $dnxcf_send_value; ?>" value="<?php _e('send message', 'dnxcf'); ?>">
            </fieldset>
        </fieldset><!-- #formwrap -->
    </form>
    <!-- end #dnxcf_form -->
<?php

    } // end check for post submission
} // end dnxcf_display_form()
?>