<?php
/**
 * Emails
 *
 * @since 1.0
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

/**
 * Add a password reset link to AffiliateWP's affiliate accepted email.
 * Only adds link if:
 * 
 * 1. Password fields are missing altogether from the affiliate registration form
 * 2. Password fields were not filled out
 */
function affwp_afgf_email_password( $message, $args ) {

	// Get the affiliate ID.
	$affiliate_id = $args['affiliate_id'];

	// Determine if the affiliate needs a password reset link.
	$password_reset = (bool) affwp_get_affiliate_meta( $affiliate_id, 'gravity_forms_password_reset', true ); 

	// Return if affiliate does not require a password reset link.
	if ( true !== $password_reset ) {
		return $message;
	}

	// Get the affiliate's user ID from their affiliate ID.
	$user_id = affwp_get_affiliate_user_id( $affiliate_id );

	// Get the affiliate's username.
	$user_login = affwp_get_affiliate_username( $affiliate_id );

	// Get password reset key.
	$key = get_password_reset_key( get_user_by( 'id', $user_id ) );

	if ( ! is_wp_error( $key ) ) {
		$message .= "\r\n\r\n" . __( 'To set your password, visit the following address:', 'affiliatewp-afgf' ) . "\r\n\r\n";
		$message .= network_site_url( "wp-login.php?action=rp&key=$key&login=" . rawurlencode( $user_login ), 'login' ) . "\r\n";
	}

	// Return the message back to AffiliateWP.
	return $message;

}
add_filter( 'affwp_application_accepted_email', 'affwp_afgf_email_password', 10, 2 );
