<?php
/**
 * Contact section: the enquiry form (posts to admin-post.php / dq_contact) beside the office
 * details. Shared by the home page (#contact) and the /book-free-demo/ page, so the redirect
 * back to "#contact" after a non-JS submit lands on whichever page the visitor used.
 *
 * @package dynamiqes
 */

$contact = dq_contact_info();
/* Headings are overridable per page (get_template_part $args): the home page keeps
   "We'd Like To Hear From You"; /contact-us/ passes the old site's H2 "Get in Touch" and
   an empty aside heading so the heading is not duplicated. */
$args          = isset( $args ) && is_array( $args ) ? $args : array();
$heading       = array_key_exists( 'heading', $args ) ? $args['heading'] : __( 'We’d Like To Hear From You', 'dynamiqes' ); // curly apostrophe, as on the live pages
$aside_heading = array_key_exists( 'aside_heading', $args ) ? $args['aside_heading'] : __( 'Get in Touch', 'dynamiqes' );
?>
<!-- ═══ CONTACT US ═══ -->
<section class="contact-us" id="contact">
	<?php
	$contact_video = get_theme_mod( 'dq_contact_video', '' );
	if ( ! $contact_video ) {
		foreach ( array( 'contact-gradient.mp4', 'contact-gradient-720p.mp4' ) as $f ) { // the lite build ships only the 720p rendition
			if ( file_exists( DQ_DIR . '/assets/video/' . $f ) ) { $contact_video = DQ_URI . '/assets/video/' . $f; break; }
		}
	}
	if ( $contact_video ) : ?>
	<div class="contact-media" aria-hidden="true">
		<video class="contact-video" muted loop playsinline preload="none">
			<?php foreach ( dq_bg_video_sources( $contact_video ) as $src ) : ?><source src="<?php echo esc_url( $src['src'] ); ?>" type="<?php echo esc_attr( $src['type'] ); ?>">
			<?php endforeach; ?>
		</video>
	</div>
	<?php endif; ?>
	<div class="wrap">
		<div class="contact-panel"<?php dq_reveal( 'scale' ); ?>>
			<div class="contact-grid">
				<div class="contact-form-side">
					<?php if ( $heading ) : ?><h2><?php echo esc_html( $heading ); ?></h2><?php endif; ?>
					<form class="contact--us" id="contactForm" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" novalidate<?php dq_reveal( '', 80 ); ?>>
						<input type="hidden" name="action" value="dq_contact">
						<?php wp_nonce_field( 'dq_contact', 'dq_contact_nonce' ); ?>
						<div class="hp-field" aria-hidden="true"><label>Website <input type="text" name="website" tabindex="-1" autocomplete="off"></label></div>
						<div class="row">
							<div class="field"><label class="screen-reader-text" for="cf-first"><?php esc_html_e( 'First Name', 'dynamiqes' ); ?></label><input id="cf-first" type="text" name="first-name" placeholder="<?php esc_attr_e( 'First Name', 'dynamiqes' ); ?>" autocomplete="given-name" required></div>
							<div class="field"><label class="screen-reader-text" for="cf-last"><?php esc_html_e( 'Last Name', 'dynamiqes' ); ?></label><input id="cf-last" type="text" name="last-name" placeholder="<?php esc_attr_e( 'Last Name', 'dynamiqes' ); ?>" autocomplete="family-name" required></div>
							<div class="field"><label class="screen-reader-text" for="cf-email"><?php esc_html_e( 'Email', 'dynamiqes' ); ?></label><input id="cf-email" type="email" name="your-email" placeholder="<?php esc_attr_e( 'Email', 'dynamiqes' ); ?>" autocomplete="email" required></div>
							<div class="field"><label class="screen-reader-text" for="cf-mobile"><?php esc_html_e( 'Mobile No.', 'dynamiqes' ); ?></label><input id="cf-mobile" type="tel" name="mobile" placeholder="<?php esc_attr_e( 'Mobile No.', 'dynamiqes' ); ?>" autocomplete="tel" required></div>
							<div class="field"><label class="screen-reader-text" for="cf-company"><?php esc_html_e( 'Company Name', 'dynamiqes' ); ?></label><input id="cf-company" type="text" name="company-name" placeholder="<?php esc_attr_e( 'Company Name', 'dynamiqes' ); ?>" autocomplete="organization" required></div>
							<div class="field"><label class="screen-reader-text" for="cf-designation"><?php esc_html_e( 'Designation', 'dynamiqes' ); ?></label><input id="cf-designation" type="text" name="designation" placeholder="<?php esc_attr_e( 'Designation', 'dynamiqes' ); ?>" autocomplete="organization-title" required></div>
							<div class="field field--select">
								<label class="screen-reader-text" for="cf-industry"><?php esc_html_e( 'Industry', 'dynamiqes' ); ?></label>
								<select id="cf-industry" name="industry" required>
									<option value="" selected disabled><?php esc_html_e( 'Industry', 'dynamiqes' ); ?></option>
									<?php foreach ( array( 'Services / BPO', 'Real Estate / Construction', 'Water / Telco / Electricity / Energy', 'Food and Beverage', 'Pharmaceutical / Healthcare Industry', 'Transport', 'Agriculture', 'Finance', 'Trading / Distribution', 'Manufacturing', 'Hospitality / Tourism', 'Media', 'Others' ) as $o ) : ?>
										<option value="<?php echo esc_attr( $o ); ?>"><?php echo esc_html( $o ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="field field--select">
								<label class="screen-reader-text" for="howFound"><?php esc_html_e( 'How did you find us?', 'dynamiqes' ); ?></label>
								<select name="how-did-you-find" id="howFound" required>
									<option value="" selected disabled><?php esc_html_e( 'How did you find us?', 'dynamiqes' ); ?></option>
									<?php foreach ( array( 'Google', 'Facebook', 'LinkedIn', 'Events', 'Referral', 'Newspaper', 'Email', 'Others' ) as $o ) : ?>
										<option value="<?php echo esc_attr( $o ); ?>"><?php echo esc_html( $o ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="field full"><label class="screen-reader-text" for="cf-budget"><?php esc_html_e( 'Accounting System budget', 'dynamiqes' ); ?></label><input id="cf-budget" type="text" name="how-much-budget" placeholder="<?php esc_attr_e( 'Accounting System budget', 'dynamiqes' ); ?>"></div>
							<div class="field full" id="otherField"><label class="screen-reader-text" for="cf-other"><?php esc_html_e( 'Other', 'dynamiqes' ); ?></label><input id="cf-other" type="text" name="other-found" placeholder="<?php esc_attr_e( 'Other', 'dynamiqes' ); ?>"></div>
							<div class="field full"><label class="screen-reader-text" for="cf-message"><?php esc_html_e( 'Message', 'dynamiqes' ); ?></label><textarea id="cf-message" name="message-area" placeholder="<?php esc_attr_e( 'Message', 'dynamiqes' ); ?>" required></textarea></div>
							<div class="full submit-row"><button type="submit" class="btn btn-orange"><?php esc_html_e( 'SUBMIT', 'dynamiqes' ); ?> <span class="arr" aria-hidden="true">→</span></button></div>
						</div>
						<div class="form-msg" role="status" aria-live="polite"></div>
						<?php echo dq_contact_flash(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					</form>
				</div>
				<div class="get-in-touch"<?php dq_reveal( 'right' ); ?>>
					<?php if ( $aside_heading ) : ?><h3><?php echo esc_html( $aside_heading ); ?></h3><?php endif; ?>
					<p><?php esc_html_e( 'Have a question or need a demo? We\'d love to hear from you! Please fill out the form below and we\'ll get back to you promptly.', 'dynamiqes' ); ?></p>
					<div class="contact-info">
						<div class="loc-card">
							<p class="loc-city"><?php echo esc_html( $contact['city'] ); ?></p><?php /* not a heading: no live page has an H4 here */ ?>
							<div class="loc-line">
								<span class="ic"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a7 7 0 0 0-7 7c0 5 7 13 7 13s7-8 7-13a7 7 0 0 0-7-7zm0 9.5A2.5 2.5 0 1 1 12 6.5a2.5 2.5 0 0 1 0 5z"/></svg></span>
								<p><?php echo esc_html( $contact['address'] ); ?></p>
							</div>
							<div class="loc-line">
								<span class="ic"><?php echo dq_icon_phone(); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
								<p><?php if ( $contact['phone1'] ) : ?><a href="<?php echo esc_attr( dq_tel( $contact['phone1'] ) ); ?>"><?php echo esc_html( $contact['phone1'] ); ?></a><?php endif; ?><?php if ( $contact['phone2'] ) : ?><a href="<?php echo esc_attr( dq_tel( $contact['phone2'] ) ); ?>"><?php echo esc_html( $contact['phone2'] ); ?></a><?php endif; ?></p>
							</div>
							<div class="loc-line">
								<span class="ic"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2zm0 4 8 5 8-5V6l-8 5-8-5v2z"/></svg></span>
								<p><a href="mailto:<?php echo esc_attr( $contact['email'] ); ?>"><?php echo esc_html( $contact['email'] ); ?></a></p>
							</div>
							<div class="loc-line">
								<span class="ic"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M19 4h-1V2h-2v2H8V2H6v2H5a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6a2 2 0 0 0-2-2zm0 16H5V10h14v10z"/></svg></span>
								<p><?php echo esc_html( $contact['hours'] ); ?></p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</section>
