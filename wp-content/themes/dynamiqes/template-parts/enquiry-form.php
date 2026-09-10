<?php
/**
 * Enquiry form — the shared markup that posts to admin-post.php / dq_contact.
 *
 * Extracted from template-parts/contact-section.php so the same form can be dropped into an
 * in-page CTA panel or a landing page section, which the review asked for on the product
 * pages, the SEO/SEM landing pages and the Application Form page (items C2, F2, G1).
 *
 * $args:
 *   'id'      form id (default 'contactForm'; pass a unique one when two forms share a page)
 *   'compact' true to drop the optional budget / "other" / message rows for a short in-page form
 *   'submit'  submit button label
 *
 * @package dynamiqes
 */

$args       = isset( $args ) && is_array( $args ) ? $args : array();
$dq_form_id = ! empty( $args['id'] ) ? $args['id'] : 'contactForm';
$dq_compact = ! empty( $args['compact'] );
$dq_submit  = ! empty( $args['submit'] ) ? $args['submit'] : __( 'SUBMIT', 'dynamiqes' );
$dq_fid     = function ( $n ) use ( $dq_form_id ) { return 'contactForm' === $dq_form_id ? $n : $dq_form_id . '-' . $n; };
?>
<form class="contact--us" id="<?php echo esc_attr( $dq_form_id ); ?>" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" novalidate<?php dq_reveal( '', 80 ); ?>>
	<input type="hidden" name="action" value="dq_contact">
	<?php wp_nonce_field( 'dq_contact', 'dq_contact_nonce' ); ?>
	<div class="hp-field" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
	<div class="row">
		<div class="field"><label class="screen-reader-text" for="<?php echo esc_attr( $dq_fid( 'cf-first' ) ); ?>"><?php esc_html_e( 'First Name', 'dynamiqes' ); ?></label><input id="<?php echo esc_attr( $dq_fid( 'cf-first' ) ); ?>" type="text" name="first-name" placeholder="<?php esc_attr_e( 'First Name', 'dynamiqes' ); ?>" autocomplete="given-name" required></div>
		<div class="field"><label class="screen-reader-text" for="<?php echo esc_attr( $dq_fid( 'cf-last' ) ); ?>"><?php esc_html_e( 'Last Name', 'dynamiqes' ); ?></label><input id="<?php echo esc_attr( $dq_fid( 'cf-last' ) ); ?>" type="text" name="last-name" placeholder="<?php esc_attr_e( 'Last Name', 'dynamiqes' ); ?>" autocomplete="family-name" required></div>
		<div class="field"><label class="screen-reader-text" for="<?php echo esc_attr( $dq_fid( 'cf-email' ) ); ?>"><?php esc_html_e( 'Email', 'dynamiqes' ); ?></label><input id="<?php echo esc_attr( $dq_fid( 'cf-email' ) ); ?>" type="email" name="your-email" placeholder="<?php esc_attr_e( 'Email', 'dynamiqes' ); ?>" autocomplete="email" required></div>
		<div class="field"><label class="screen-reader-text" for="<?php echo esc_attr( $dq_fid( 'cf-mobile' ) ); ?>"><?php esc_html_e( 'Mobile No.', 'dynamiqes' ); ?></label><input id="<?php echo esc_attr( $dq_fid( 'cf-mobile' ) ); ?>" type="tel" name="mobile" placeholder="<?php esc_attr_e( 'Mobile No.', 'dynamiqes' ); ?>" autocomplete="tel" required></div>
		<div class="field"><label class="screen-reader-text" for="<?php echo esc_attr( $dq_fid( 'cf-company' ) ); ?>"><?php esc_html_e( 'Company Name', 'dynamiqes' ); ?></label><input id="<?php echo esc_attr( $dq_fid( 'cf-company' ) ); ?>" type="text" name="company-name" placeholder="<?php esc_attr_e( 'Company Name', 'dynamiqes' ); ?>" autocomplete="organization" required></div>
		<div class="field"><label class="screen-reader-text" for="<?php echo esc_attr( $dq_fid( 'cf-designation' ) ); ?>"><?php esc_html_e( 'Designation', 'dynamiqes' ); ?></label><input id="<?php echo esc_attr( $dq_fid( 'cf-designation' ) ); ?>" type="text" name="designation" placeholder="<?php esc_attr_e( 'Designation', 'dynamiqes' ); ?>" autocomplete="organization-title" required></div>
		<div class="field field--select">
			<label class="screen-reader-text" for="<?php echo esc_attr( $dq_fid( 'cf-industry' ) ); ?>"><?php esc_html_e( 'Industry', 'dynamiqes' ); ?></label>
			<select id="<?php echo esc_attr( $dq_fid( 'cf-industry' ) ); ?>" name="industry" required>
				<option value="" selected disabled><?php esc_html_e( 'Industry', 'dynamiqes' ); ?></option>
				<?php foreach ( array( 'Services / BPO', 'Real Estate / Construction', 'Water / Telco / Electricity / Energy', 'Food and Beverage', 'Pharmaceutical / Healthcare Industry', 'Transport', 'Agriculture', 'Finance', 'Trading / Distribution', 'Manufacturing', 'Hospitality / Tourism', 'Media', 'Others' ) as $o ) : ?>
					<option value="<?php echo esc_attr( $o ); ?>"><?php echo esc_html( $o ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="field field--select">
			<label class="screen-reader-text" for="<?php echo esc_attr( $dq_fid( 'howFound' ) ); ?>"><?php esc_html_e( 'How did you find us?', 'dynamiqes' ); ?></label>
			<select class="dq-how-found" name="how-did-you-find" id="<?php echo esc_attr( $dq_fid( 'howFound' ) ); ?>" required>
				<option value="" selected disabled><?php esc_html_e( 'How did you find us?', 'dynamiqes' ); ?></option>
				<?php foreach ( array( 'Google', 'Facebook', 'LinkedIn', 'Events', 'Referral', 'Newspaper', 'Email', 'Others' ) as $o ) : ?>
					<option value="<?php echo esc_attr( $o ); ?>"><?php echo esc_html( $o ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php if ( ! $dq_compact ) : /* the short in-page form keeps only the identity + message rows */ ?>
		<div class="field full"><label class="screen-reader-text" for="<?php echo esc_attr( $dq_fid( 'cf-budget' ) ); ?>"><?php esc_html_e( 'Accounting System budget', 'dynamiqes' ); ?></label><input id="<?php echo esc_attr( $dq_fid( 'cf-budget' ) ); ?>" type="text" name="how-much-budget" placeholder="<?php esc_attr_e( 'Accounting System budget', 'dynamiqes' ); ?>"></div>
		<div class="field full dq-other-field" id="<?php echo esc_attr( $dq_fid( 'otherField' ) ); ?>"><label class="screen-reader-text" for="<?php echo esc_attr( $dq_fid( 'cf-other' ) ); ?>"><?php esc_html_e( 'Other', 'dynamiqes' ); ?></label><input id="<?php echo esc_attr( $dq_fid( 'cf-other' ) ); ?>" type="text" name="other-found" placeholder="<?php esc_attr_e( 'Other', 'dynamiqes' ); ?>"></div>
		<?php endif; ?>
		<div class="field full"><label class="screen-reader-text" for="<?php echo esc_attr( $dq_fid( 'cf-message' ) ); ?>"><?php esc_html_e( 'Message', 'dynamiqes' ); ?></label><textarea id="<?php echo esc_attr( $dq_fid( 'cf-message' ) ); ?>" name="message-area" placeholder="<?php esc_attr_e( 'Message', 'dynamiqes' ); ?>" required></textarea></div>
		<div class="full submit-row"><button type="submit" class="btn btn-orange"><?php echo esc_html( $dq_submit ); ?> <span class="arr" aria-hidden="true">→</span></button></div>
	</div>
	<div class="form-msg" role="status" aria-live="polite"></div>
	<?php echo dq_contact_flash(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
</form>
