<?php
/**
 * Job application form (/application-form/).
 *
 * The review found that page carrying no form at all, and its intro copy was the old form's
 * Position options flattened into a paragraph (item G1). This is the form: it posts to the same
 * dq_contact handler with dq_form=application, which routes it to HR and asks for a position
 * instead of a company and industry (inc/contact-form.php).
 *
 * Positions come from the dq_career post type so the list follows the live openings.
 *
 * @package dynamiqes
 */

$dq_jobs  = function_exists( 'dq_career_jobs' ) ? dq_career_jobs() : array();
$dq_locs  = function_exists( 'dq_career_locations' ) ? dq_career_locations( $dq_jobs ) : array();
$dq_picked = isset( $_GET['position'] ) ? sanitize_text_field( wp_unslash( $_GET['position'] ) ) : '';
?>
<form class="contact--us application-form" id="applicationForm" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" novalidate<?php dq_reveal( '', 80 ); ?>>
	<input type="hidden" name="action" value="dq_contact">
	<input type="hidden" name="dq_form" value="application">
	<?php wp_nonce_field( 'dq_contact', 'dq_contact_nonce' ); ?>
	<div class="hp-field" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
	<div class="row">
		<div class="field"><label class="screen-reader-text" for="af-first"><?php esc_html_e( 'First Name', 'dynamiqes' ); ?></label><input id="af-first" type="text" name="first-name" placeholder="<?php esc_attr_e( 'First Name', 'dynamiqes' ); ?>" autocomplete="given-name" required></div>
		<div class="field"><label class="screen-reader-text" for="af-last"><?php esc_html_e( 'Last Name', 'dynamiqes' ); ?></label><input id="af-last" type="text" name="last-name" placeholder="<?php esc_attr_e( 'Last Name', 'dynamiqes' ); ?>" autocomplete="family-name" required></div>
		<div class="field"><label class="screen-reader-text" for="af-email"><?php esc_html_e( 'Email', 'dynamiqes' ); ?></label><input id="af-email" type="email" name="your-email" placeholder="<?php esc_attr_e( 'Email', 'dynamiqes' ); ?>" autocomplete="email" required></div>
		<div class="field"><label class="screen-reader-text" for="af-mobile"><?php esc_html_e( 'Mobile No.', 'dynamiqes' ); ?></label><input id="af-mobile" type="tel" name="mobile" placeholder="<?php esc_attr_e( 'Mobile No.', 'dynamiqes' ); ?>" autocomplete="tel" required></div>
		<div class="field field--select">
			<label class="screen-reader-text" for="af-position"><?php esc_html_e( 'Position', 'dynamiqes' ); ?></label>
			<select id="af-position" name="position" required>
				<option value="" selected disabled><?php esc_html_e( 'Position', 'dynamiqes' ); ?></option>
				<?php foreach ( $dq_jobs as $dq_job ) : ?>
					<option value="<?php echo esc_attr( $dq_job['title'] ); ?>"<?php selected( $dq_picked, $dq_job['title'] ); ?>><?php echo esc_html( $dq_job['title'] ); ?></option>
				<?php endforeach; ?>
				<option value="<?php echo esc_attr__( 'Other / general application', 'dynamiqes' ); ?>"><?php esc_html_e( 'Other / general application', 'dynamiqes' ); ?></option>
			</select>
		</div>
		<div class="field field--select">
			<label class="screen-reader-text" for="af-location"><?php esc_html_e( 'Location', 'dynamiqes' ); ?></label>
			<select id="af-location" name="location">
				<option value="" selected disabled><?php esc_html_e( 'Location', 'dynamiqes' ); ?></option>
				<?php foreach ( $dq_locs as $dq_loc ) : ?>
					<option value="<?php echo esc_attr( $dq_loc ); ?>"><?php echo esc_html( $dq_loc ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="field full"><label class="screen-reader-text" for="af-cv"><?php esc_html_e( 'Link to your CV or portfolio', 'dynamiqes' ); ?></label><input id="af-cv" type="url" name="cv-link" placeholder="<?php esc_attr_e( 'Link to your CV or portfolio (optional)', 'dynamiqes' ); ?>"></div>
		<div class="field full"><label class="screen-reader-text" for="af-message"><?php esc_html_e( 'Tell us about yourself', 'dynamiqes' ); ?></label><textarea id="af-message" name="message-area" placeholder="<?php esc_attr_e( 'Tell us about yourself and your experience', 'dynamiqes' ); ?>" required></textarea></div>
		<div class="full submit-row"><button type="submit" class="btn btn-orange"><?php esc_html_e( 'SUBMIT APPLICATION', 'dynamiqes' ); ?> <span class="arr" aria-hidden="true">&rarr;</span></button></div>
	</div>
	<div class="form-msg" role="status" aria-live="polite"></div>
	<?php echo dq_contact_flash(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
</form>
