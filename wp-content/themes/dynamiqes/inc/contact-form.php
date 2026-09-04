<?php
/**
 * Contact form handler: AJAX (admin-ajax) with a no-JS fallback via admin-post.
 * Validates, checks nonce + honeypot + a light rate limit, emails the team and
 * stores the inquiry as a private `dq_inquiry` post for back-up.
 *
 * @package dynamiqes
 */

defined( 'ABSPATH' ) || exit;

add_action( 'wp_ajax_dq_contact', 'dq_handle_contact' );
add_action( 'wp_ajax_nopriv_dq_contact', 'dq_handle_contact' );
add_action( 'admin_post_dq_contact', 'dq_handle_contact_fallback' );
add_action( 'admin_post_nopriv_dq_contact', 'dq_handle_contact_fallback' );

/** Validate + process. Returns [ok(bool), message(string)]. */
function dq_process_contact() {
	if ( ! isset( $_POST['dq_contact_nonce'] ) || ! wp_verify_nonce( $_POST['dq_contact_nonce'], 'dq_contact' ) ) {
		return array( false, __( 'Your session expired. Please reload the page and try again.', 'dynamiqes' ) );
	}
	if ( ! empty( $_POST['website'] ) ) { // honeypot
		return array( true, __( 'Thank you — we\'ll be in touch.', 'dynamiqes' ) );
	}
	$ip  = isset( $_SERVER['REMOTE_ADDR'] ) ? sanitize_text_field( wp_unslash( $_SERVER['REMOTE_ADDR'] ) ) : '0';
	$key = 'dq_rl_' . md5( $ip );
	if ( (int) get_transient( $key ) >= 5 ) {
		return array( false, __( 'Too many messages from this connection. Please try again in an hour.', 'dynamiqes' ) );
	}

	$f = array(
		'first-name'      => __( 'First Name', 'dynamiqes' ),
		'last-name'       => __( 'Last Name', 'dynamiqes' ),
		'your-email'      => __( 'Email', 'dynamiqes' ),
		'mobile'          => __( 'Mobile No.', 'dynamiqes' ),
		'company-name'    => __( 'Company Name', 'dynamiqes' ),
		'designation'     => __( 'Designation', 'dynamiqes' ),
		'industry'        => __( 'Industry', 'dynamiqes' ),
		'how-did-you-find'=> __( 'How did you find us?', 'dynamiqes' ),
		'other-found'     => __( 'Other', 'dynamiqes' ),
		'how-much-budget' => __( 'Accounting System budget', 'dynamiqes' ),
		'message-area'    => __( 'Message', 'dynamiqes' ),
	);
	$required = array( 'first-name', 'last-name', 'your-email', 'mobile', 'company-name', 'designation', 'industry', 'how-did-you-find', 'message-area' );
	$data     = array();
	foreach ( $f as $k => $label ) {
		$raw        = isset( $_POST[ $k ] ) ? wp_unslash( $_POST[ $k ] ) : '';
		$data[ $k ] = 'message-area' === $k ? sanitize_textarea_field( $raw ) : sanitize_text_field( $raw );
		if ( in_array( $k, $required, true ) && '' === trim( $data[ $k ] ) ) {
			return array( false, sprintf( __( 'Please fill in "%s".', 'dynamiqes' ), $label ) );
		}
	}
	if ( ! is_email( $data['your-email'] ) ) {
		return array( false, __( 'Please enter a valid email address.', 'dynamiqes' ) );
	}

	$name    = $data['first-name'] . ' ' . $data['last-name'];
	$subject = sprintf( '[%s] New inquiry from %s (%s)', get_bloginfo( 'name' ), $name, $data['company-name'] );
	$lines   = array();
	foreach ( $f as $k => $label ) {
		if ( '' !== $data[ $k ] ) {
			$lines[] = $label . ': ' . $data[ $k ];
		}
	}
	$lines[] = '';
	$lines[] = 'Page: ' . ( isset( $_POST['_wp_http_referer'] ) ? esc_url_raw( wp_unslash( $_POST['_wp_http_referer'] ) ) : home_url( '/' ) );
	$lines[] = 'IP: ' . $ip;
	$body    = implode( "\n", $lines );

	$to      = array_map( 'trim', explode( ',', get_theme_mod( 'dq_contact_email', get_option( 'admin_email' ) ) ) );
	$headers = array( 'Reply-To: ' . $name . ' <' . $data['your-email'] . '>' );
	$sent    = wp_mail( $to, $subject, $body, $headers );

	$post_id = wp_insert_post( array(
		'post_type'    => 'dq_inquiry',
		'post_status'  => 'private',
		'post_title'   => $name . ' — ' . $data['company-name'],
		'post_content' => $body,
	) );
	if ( $post_id && ! is_wp_error( $post_id ) ) {
		update_post_meta( $post_id, '_dq_inquiry', $data );
		update_post_meta( $post_id, '_dq_mail_sent', $sent ? '1' : '0' );
	}
	set_transient( $key, (int) get_transient( $key ) + 1, HOUR_IN_SECONDS );

	return array( true, __( 'Thank you — we\'ve received your message and will get back to you within 24 hours.', 'dynamiqes' ) );
}

function dq_handle_contact() {
	list( $ok, $msg ) = dq_process_contact();
	if ( $ok ) {
		wp_send_json_success( array( 'message' => $msg ) );
	}
	wp_send_json_error( array( 'message' => $msg ), 400 );
}

function dq_handle_contact_fallback() {
	list( $ok, $msg ) = dq_process_contact();
	$back = wp_get_referer() ? wp_get_referer() : home_url( '/' );
	$back = remove_query_arg( array( 'dq_sent', 'dq_msg' ), $back );
	wp_safe_redirect( add_query_arg( array( 'dq_sent' => $ok ? 1 : 0, 'dq_msg' => rawurlencode( $msg ) ), $back ) . '#contact' );
	exit;
}

/** Message from the no-JS fallback redirect. */
function dq_contact_flash() {
	if ( ! isset( $_GET['dq_msg'] ) ) {
		return '';
	}
	$ok  = ! empty( $_GET['dq_sent'] );
	$msg = sanitize_text_field( rawurldecode( wp_unslash( $_GET['dq_msg'] ) ) );
	return '<div class="form-msg show ' . ( $ok ? 'ok' : 'err' ) . '" role="status">' . esc_html( $msg ) . '</div>';
}
