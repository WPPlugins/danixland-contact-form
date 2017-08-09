<?php
defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
// usage:                   $id,                $title,                      $callback,                       $page
add_settings_section('dnxcf_mailfrom_options', __('Sender email address', 'dnxcf'), 'dnxcf_settings_mailfrom_section_text', 'dnxcf_options_sections');
add_settings_section('dnxcf_email_address', __('Recipient email address', 'dnxcf'), 'dnxcf_settings_email_address_section_text', 'dnxcf_options_sections');
add_settings_section('dnxcf_content_type', __('Content type', 'dnxcf'), 'dnxcf_settings_content_type_section_text', 'dnxcf_options_sections');
add_settings_section('dnxcf_subject_options', __('Subject', 'dnxcf'), 'dnxcf_settings_subject_section_text', 'dnxcf_options_sections');
add_settings_section('dnxcf_privacy_policy', __('Privacy policy', 'dnxcf'), 'dnxcf_settings_privacy_section_text', 'dnxcf_options_sections');
add_settings_section('dnxcf_googlemap', __('Google Map', 'dnxcf'), 'dnxcf_settings_googlemap_section_text', 'dnxcf_options_sections');


// usage:    $id, $title, $callback, $page, $section, $args 
add_settings_field('dnxcf_setting_content_type_display', __('content type?', 'dnxcf'), 'dnxcf_setting_content_type_display', 'dnxcf_options_sections', 'dnxcf_content_type');
add_settings_field('dnxcf_setting_email_name_display', __('name?', 'dnxcf'), 'dnxcf_setting_email_name_display', 'dnxcf_options_sections', 'dnxcf_email_address');
add_settings_field('dnxcf_setting_email_address_display', __('email address?', 'dnxcf'), 'dnxcf_setting_email_address_display', 'dnxcf_options_sections', 'dnxcf_email_address');
add_settings_field('dnxcf_setting_subject_display', __('subject options?', 'dnxcf'), 'dnxcf_setting_subject_display', 'dnxcf_options_sections', 'dnxcf_subject_options');
add_settings_field('dnxcf_setting_mailfrom_name_display', __('name?', 'dnxcf'), 'dnxcf_setting_mailfrom_name_display', 'dnxcf_options_sections', 'dnxcf_mailfrom_options');
add_settings_field('dnxcf_setting_mailfrom_mail_display', __('email address?', 'dnxcf'), 'dnxcf_setting_mailfrom_mail_display', 'dnxcf_options_sections', 'dnxcf_mailfrom_options');
add_settings_field('dnxcf_setting_privacy_display', __('policy text?', 'dnxcf'), 'dnxcf_setting_privacy_display', 'dnxcf_options_sections', 'dnxcf_privacy_policy');
add_settings_field('dnxcf_setting_googlemap_latitude', __('Latitude?', 'dnxcf'), 'dnxcf_setting_googlemap_lat_display', 'dnxcf_options_sections', 'dnxcf_googlemap');
add_settings_field('dnxcf_setting_googlemap_longitude', __('Longitude?', 'dnxcf'), 'dnxcf_setting_googlemap_long_display', 'dnxcf_options_sections', 'dnxcf_googlemap');
add_settings_field('dnxcf_setting_googlemap_message', __('Address?', 'dnxcf'), 'dnxcf_setting_googlemap_message_display', 'dnxcf_options_sections', 'dnxcf_googlemap');

function dnxcf_settings_content_type_section_text() { ?>
<p><?php _e( 'Here you can change the content type of your emails, either html or plain text.', 'dnxcf' ); ?></p>
<?php }

function dnxcf_settings_email_address_section_text() { ?>
<p><?php _e( 'This is the email address where you will receive all email from the contact form.', 'dnxcf' ); ?></p>
<?php }

function dnxcf_settings_subject_section_text() { ?>
<p><?php _e( 'These are the options that you are giving as a dropdown list to your users.', 'dnxcf' ); ?></p>
<?php }

function dnxcf_settings_mailfrom_section_text() { ?>
<p>
<?php _e( 'Here you can set the sender email address for the contact form.', 'dnxcf' ); ?><br />
<?php
    global $dnxcf_options;
    $dnxcf_options = get_option( 'dnxcf_options' );
    echo sprintf(
        __( 'The emails you will receive will be from: <code>%s < %s ></code>', 'dnxcf' ),
        $dnxcf_options['dnxcf_from_name'],
        $dnxcf_options['dnxcf_from_email']
    );
?><br />
<?php _e( 'so just make sure you whitelist this address in your mail client to avoid losing important messages.', 'dnxcf' ); ?>
</p>
<?php }

function dnxcf_settings_privacy_section_text() { ?>
<p><?php _e( 'Enter here the content of your privacy policy relative to the contact form.', 'dnxcf' ); ?></p>
<?php }

function dnxcf_settings_googlemap_section_text() { ?>
<p><?php _e( 'Here you can change various settings for the map that will be displayed on the form page. <strong>Note:</strong> if either Longitude or Latitude values are missing the map will be disabled.', 'dnxcf' ); ?></p>
<?php }

// Content type for email sent via this form
function dnxcf_setting_content_type_display() {
$dnxcf_options = get_option( 'dnxcf_options' );
// 1 = text/plain
// 2 = text/html
?>
<input type="radio" name="dnxcf_options[dnxcf_content_type]" <?php checked( $dnxcf_options['dnxcf_content_type'], '1' ); ?> value='1' /> <?php _e('text/plain', 'dnxcf'); ?><br />
<input type="radio" name="dnxcf_options[dnxcf_content_type]" <?php checked( $dnxcf_options['dnxcf_content_type'], '2' ); ?> value='2' /> <?php _e('text/html', 'dnxcf'); ?><br />
<span class="description"><?php _e('Send plain (text) or rich (html) messages.', 'dnxcf'); ?></span>
<?php }

// Receiving email name
function dnxcf_setting_email_name_display() {
$dnxcf_options = get_option( 'dnxcf_options' ); ?>
<input type="text" name="dnxcf_options[dnxcf_recv_name]" value="<?php echo $dnxcf_options['dnxcf_recv_name']; ?>" /><br />
<span class="description"><?php _e('This is how you will be called in every email you will receive from this contact form.', 'dnxcf'); ?></span>
<?php }

// Receiving email address
function dnxcf_setting_email_address_display() {
$dnxcf_options = get_option( 'dnxcf_options' ); ?>
<input type="email" name="dnxcf_options[dnxcf_recv_email]" value="<?php echo $dnxcf_options['dnxcf_recv_email']; ?>" /><br />
<span class="description"><?php _e('If you leave this field empty the admin email address will be used.', 'dnxcf'); ?></span>
<?php }

// Custom subject options
function dnxcf_setting_subject_display() {
$dnxcf_options = get_option( 'dnxcf_options' ); ?>
<textarea name="dnxcf_options[dnxcf_subject]" rows="10" cols="80" /><?php echo implode("\n", $dnxcf_options['dnxcf_subject']); ?></textarea><br />
<span class="description"><?php _e('Insert one option per line. If you leave this area empty the default options will be used.', 'dnxcf'); ?></span>
<?php }

// Sender email address
function dnxcf_setting_mailfrom_mail_display() {
$dnxcf_options = get_option( 'dnxcf_options' ); ?>
<input type="email" name="dnxcf_options[dnxcf_from_email]" value="<?php echo $dnxcf_options['dnxcf_from_email']; ?>" /><br />
<span class="description"><?php _e('This is the email address from which you will receive communications.', 'dnxcf'); ?></span>
<?php }

// Sender name
function dnxcf_setting_mailfrom_name_display() {
$dnxcf_options = get_option( 'dnxcf_options' ); ?>
<input type="text" name="dnxcf_options[dnxcf_from_name]" value="<?php echo $dnxcf_options['dnxcf_from_name']; ?>" /><br />
<span class="description"><?php _e('This is the name associated to the above email address.', 'dnxcf'); ?></span>
<?php }

// Privacy Policy
function dnxcf_setting_privacy_display() {
$dnxcf_options = get_option( 'dnxcf_options' ); ?>
<textarea name="dnxcf_options[dnxcf_privacy]" rows="10" cols="80" /><?php echo wptexturize($dnxcf_options['dnxcf_privacy']); ?></textarea><br />
<span class="description"><?php _e('The text of the privacy policy, Leave empty to disable the policy area in the form.', 'dnxcf'); ?></span>
<?php }

// map message
function dnxcf_setting_googlemap_message_display() {
$dnxcf_options = get_option( 'dnxcf_options' ); ?>
<input type="text" name="dnxcf_options[dnxcf_gmap_message]" value="<?php echo $dnxcf_options['dnxcf_gmap_message']; ?>" /><br />
<span class="description"><?php _e('Address to be displayed as a popup inside the map. Leave empty to disable.', 'dnxcf'); ?></span>
<?php }

// Longitude
function dnxcf_setting_googlemap_long_display() {
$dnxcf_options = get_option( 'dnxcf_options' ); ?>
<input type="number" name="dnxcf_options[dnxcf_longitude]" value="<?php echo $dnxcf_options['dnxcf_longitude']; ?>" step="0.000001" /><br />
<span class="description"><?php _e('Longitude value, eg. 16.290340', 'dnxcf'); ?></span><br />
<span class="description"><?php _e('+/- 180 degrees value accepted', 'dnxcf'); ?></span>
<?php }

// Latitude
function dnxcf_setting_googlemap_lat_display() {
$dnxcf_options = get_option( 'dnxcf_options' ); ?>
<input type="number" name="dnxcf_options[dnxcf_latitude]" value="<?php echo $dnxcf_options['dnxcf_latitude']; ?>" step="0.000001" /><br />
<span class="description"><?php _e('Latitude value, eg. 38.269625.', 'dnxcf'); ?></span><br />
<span class="description"><?php _e('+/- 90 degrees value accepted', 'dnxcf'); ?></span>
<?php }
