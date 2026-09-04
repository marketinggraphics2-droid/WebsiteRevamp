<?php
/**
 * Home page.
 *
 * @package dynamiqes
 */

get_header();

$products  = dq_get_products();
$sap       = dq_get_product_by_key( 'sap' );
$suite     = array_values( array_filter( $products, function ( $p ) { return 'sap' !== $p['key']; } ) );
$news      = dq_news_items( 5 ); // 1 featured + 2×2 grid
$featured  = array_shift( $news );
$contact   = dq_contact_info();
$sap_logo  = get_theme_mod( 'dq_sap_logo', '' );
$sap_logo  = $sap_logo ? $sap_logo : DQ_URI . '/assets/brand/sap-business-one-grad-blk.png';
$badge     = get_theme_mod( 'dq_partner_badge', '' );
$badge     = $badge ? $badge : DQ_URI . '/assets/brand/sap-premier-partner-badge.png';
$handshake = get_theme_mod( 'dq_partner_photo', '' );
$handshake = $handshake ? $handshake : DQ_URI . '/assets/products/site-media/partner-handshake.jpg';
$svg_arrow = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h13M12 6l6 6-6 6"/></svg>';
?>
<main id="main">

<!-- ═══ HOME BANNER ═══ -->
<section class="home-banner">
	<div class="banner-media" aria-hidden="true">
		<video class="banner-video" autoplay muted loop playsinline preload="metadata" poster="<?php echo esc_url( dq_hero_poster_url() ); ?>">
			<source src="<?php echo esc_url( dq_hero_video_url() ); ?>" type="video/mp4">
		</video>
	</div>
	<div class="wrap">
		<div class="banner-content">
			<h1<?php dq_reveal( 'fade', 80 ); ?>><?php echo esc_html( get_theme_mod( 'dq_hero_title', 'End-to-end SAP Software Solution with your SAP Premier Partner, DynamIQ' ) ); ?></h1>
			<p class="lede"<?php dq_reveal( '', 160 ); ?>><?php echo esc_html( get_theme_mod( 'dq_hero_lede', 'Streamline operations and drive your efficiency. As a SAP Premier partner, trust us to power your business\'s future—where innovation meets expertise in perfect harmony' ) ); ?></p>
		</div>
		<div class="banner-base">
			<div class="banner-actions">
				<div class="dynamiq-cta"><a href="<?php echo esc_url( dq_products_url() ); ?>"><?php echo esc_html( get_theme_mod( 'dq_hero_primary_label', 'EXPLORE THE IQ SUITE' ) ); ?> <span class="arr" aria-hidden="true">→</span></a></div>
				<a class="banner-secondary" href="#contact"><?php echo esc_html( get_theme_mod( 'dq_hero_secondary_label', 'Book a Free Demo' ) ); ?> <span class="arr" aria-hidden="true">→</span></a>
			</div>
			<div class="banner-strip" aria-label="<?php esc_attr_e( 'The IQ Suite', 'dynamiqes' ); ?>">
				<div class="banner-strip-marq">
					<div class="banner-strip-track">
						<?php foreach ( $suite as $p ) : ?>
							<a href="<?php echo esc_url( $p['url'] ); ?>" data-name="<?php echo esc_attr( $p['menu_label'] ); ?>" data-desc="<?php echo esc_attr( $p['strip_desc'] ); ?>"><img src="<?php echo esc_url( $p['logo_light'] ? $p['logo_light'] : $p['logo'] ); ?>" alt="<?php echo esc_attr( $p['menu_label'] ); ?>" loading="eager"></a>
						<?php endforeach; ?>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="banner-tip" aria-hidden="true">
		<p class="banner-tip-name"></p>
		<p class="banner-tip-desc"></p>
	</div>
</section>

<!-- ═══ TRUST LOGOS ═══ -->
<section class="trust" aria-label="<?php esc_attr_e( 'Trusted by leading Philippine businesses', 'dynamiqes' ); ?>">
	<div class="wrap">
		<p class="trust-note"<?php dq_reveal( 'fade' ); ?>><?php echo esc_html( get_theme_mod( 'dq_trust_note', 'Trusted by manufacturing, healthcare, retail, and trading companies across the Philippines — including MacroAsia Corporation, Presline Steel, Toyo Adtec, and Cecile\'s Pharmacy.' ) ); ?></p>
	</div>
	<div class="trust-marq">
		<div class="trust-track">
			<?php foreach ( dq_trust_logos() as $l ) : ?>
				<img src="<?php echo esc_url( dq_asset( $l[1] ) ); ?>" alt="<?php echo esc_attr( $l[0] ); ?>" loading="lazy" height="40">
			<?php endforeach; ?>
			<?php foreach ( dq_trust_logos() as $l ) : ?>
				<img src="<?php echo esc_url( dq_asset( $l[1] ) ); ?>" alt="" aria-hidden="true" loading="lazy" height="40">
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- ═══ IQ SUITE (PRODUCTS) ═══ -->
<section class="iq" id="products">
	<div class="wrap">
		<div class="sec-head"<?php dq_reveal(); ?>>
			<h2><?php esc_html_e( 'Our Products', 'dynamiqes' ); ?></h2>
		</div>
		<div class="iq-grid">
			<?php if ( $sap ) : ?>
			<a class="iq-card all feature" href="<?php echo esc_url( $sap['url'] ); ?>"<?php dq_reveal( '', 0 ); ?>>
				<span class="iq-art no-lazyload skip-lazy" style="background-image:url('<?php echo esc_url( $sap['card_art'] ); ?>')"></span>
				<span class="iq-logo sap-card-logo">
					<img class="lg-d no-lazyload skip-lazy" src="<?php echo esc_url( $sap['logo'] ); ?>" alt="<?php echo esc_attr( $sap['name'] ); ?>">
					<img class="lg-l no-lazyload skip-lazy" src="<?php echo esc_url( $sap['logo_light'] ); ?>" alt="" aria-hidden="true">
				</span>
				<div class="iq-foot">
					<div class="iq-copy">
						<span class="iq-tagline"><?php echo esc_html( $sap['card_tagline'] ); ?></span>
						<div class="iq-detail">
							<span class="iq-title-row">
								<span class="iq-title"><?php echo esc_html( $sap['card_title'] ); ?></span>
								<span class="iq-go"><?php echo $svg_arrow; // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
							</span>
							<p class="iq-desc"><?php echo esc_html( $sap['card_desc'] ); ?></p>
						</div>
					</div>
				</div>
			</a>
			<?php endif; ?>
			<?php $delays = array( 0, 70, 140 ); foreach ( $suite as $i => $p ) : ?>
			<a class="iq-card" href="<?php echo esc_url( $p['url'] ); ?>"<?php dq_reveal( '', $delays[ $i % 3 ] ); ?>>
				<span class="iq-art no-lazyload skip-lazy" style="background-image:url('<?php echo esc_url( $p['card_art'] ); ?>')"></span>
				<span class="iq-hover-art no-lazyload skip-lazy" style="background-image:url('<?php echo esc_url( $p['card_photo'] ); ?>')"></span>
				<span class="iq-logo">
					<img class="lg-d no-lazyload skip-lazy" src="<?php echo esc_url( $p['logo'] ); ?>" alt="<?php echo esc_attr( $p['menu_label'] ); ?>">
					<img class="lg-l no-lazyload skip-lazy" src="<?php echo esc_url( $p['logo_light'] ? $p['logo_light'] : $p['logo'] ); ?>" alt="" aria-hidden="true">
				</span>
				<div class="iq-foot">
					<div class="iq-copy">
						<span class="iq-tagline"><?php echo esc_html( $p['card_tagline'] ); ?></span>
						<div class="iq-detail">
							<span class="iq-title-row">
								<span class="iq-title"><?php echo esc_html( $p['card_title'] ); ?></span>
								<span class="iq-go"><?php echo $svg_arrow; // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
							</span>
							<p class="iq-desc"><?php echo esc_html( $p['card_desc'] ); ?></p>
						</div>
					</div>
				</div>
			</a>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- ═══ SAP BUSINESS ONE ═══ -->
<?php if ( $sap ) : ?>
<section class="sap-business-one">
	<div class="wrap sap-inner">
		<div class="sap-copy"<?php dq_reveal(); ?>>
			<img class="sap-logo-rev" src="<?php echo esc_url( $sap_logo ); ?>" alt="SAP Business One" loading="lazy">
			<p class="sap-lead"><?php echo esc_html( $sap['overview'][0] ?? $sap['description'] ); ?></p>
			<?php $sap_photo_copy = get_theme_mod( 'dq_sap_photo_copy', '' ); $sap_photo_copy = $sap_photo_copy ? $sap_photo_copy : DQ_URI . '/assets/products/site-media/sap-b1-warehouse-office.jpg'; ?>
			<figure class="sap-photo sap-photo-copy<?php echo $sap_photo_copy ? '' : ' is-placeholder'; ?>">
				<?php if ( $sap_photo_copy ) : ?><img src="<?php echo esc_url( $sap_photo_copy ); ?>" alt="<?php esc_attr_e( 'Warehouse manager reviewing SAP Business One on a laptop', 'dynamiqes' ); ?>" loading="lazy"><?php else : ?><span><?php esc_html_e( 'Photo', 'dynamiqes' ); ?></span><?php endif; ?>
			</figure>
			<?php if ( ! empty( $sap['overview'][1] ) ) : ?><p class="sap-sub"><?php echo esc_html( $sap['overview'][1] ); ?></p><?php endif; ?>
			<div class="dynamiq-cta"><a href="<?php echo esc_url( $sap['url'] ); ?>"><?php esc_html_e( 'See SAP Business One Features', 'dynamiqes' ); ?> <span class="arr" aria-hidden="true">→</span></a></div>
		</div>
		<div class="sap-bento">
			<?php foreach ( dq_sap_features() as $i => $f ) : ?>
			<article class="feat"<?php dq_reveal( 'reveal', $i * 90 ); ?>>
				<h3><i><?php echo esc_html( sprintf( '%02d', $i + 1 ) ); ?></i> <?php echo esc_html( $f[0] ); ?></h3>
				<p><?php echo esc_html( $f[1] ); ?></p>
			</article>
			<?php endforeach; ?>
			<?php $sap_photo_grid = get_theme_mod( 'dq_sap_photo_grid', '' ); $sap_photo_grid = $sap_photo_grid ? $sap_photo_grid : DQ_URI . '/assets/products/site-media/sap-b1-analytics-monitor.jpg'; ?>
			<figure class="feat feat-photo sap-photo<?php echo $sap_photo_grid ? '' : ' is-placeholder'; ?>"<?php dq_reveal( 'reveal', 5 * 90 ); ?>>
				<?php if ( $sap_photo_grid ) : ?><img src="<?php echo esc_url( $sap_photo_grid ); ?>" alt="<?php esc_attr_e( 'Analyst pointing at SAP Business One reports on a monitor', 'dynamiqes' ); ?>" loading="lazy"><?php else : ?><span><?php esc_html_e( 'Photo', 'dynamiqes' ); ?></span><?php endif; ?>
			</figure>
		</div>
	</div>
</section>
<?php endif; ?>

<!-- ═══ WHY CHOOSE DYNAMIQ ═══ -->
<section class="we-are-accredited" id="about">
	<div class="wrap accredited-grid">
		<div class="accredited-left">
			<div class="accredited-header">
				<span class="eyebrow"<?php dq_reveal(); ?>><?php esc_html_e( 'SAP Premier Partner', 'dynamiqes' ); ?></span>
				<h2<?php dq_reveal( '', 80 ); ?>><?php esc_html_e( 'Why Choose DynamIQ as Your SAP Premier Partner?', 'dynamiqes' ); ?></h2>
			</div>
			<?php // photo frame (Customizer: dq_partner_photo, bundled default below) with the partner badge in its top-left corner ?>
			<?php $accredited_photo = get_theme_mod( 'dq_partner_photo', '' ); $accredited_photo = $accredited_photo ? $accredited_photo : DQ_URI . '/assets/brand/sap-partner-store-owners.jpg'; ?>
			<figure class="accredited-visual"<?php dq_reveal(); ?>>
				<img class="accredited-photo" src="<?php echo esc_url( $accredited_photo ); ?>" alt="<?php esc_attr_e( 'Business owners reviewing their SAP Business One system with DynamIQ', 'dynamiqes' ); ?>" loading="lazy">
				<img class="accredited-badge" src="<?php echo esc_url( $badge ); ?>" alt="<?php esc_attr_e( 'SAP Premier Partner', 'dynamiqes' ); ?>" loading="lazy">
			</figure>
		</div>
		<div class="accredited-copy">
			<p<?php dq_reveal( '', 150 ); ?>>DynamIQ Enterprise Solution Inc. stands as your premier choice for SAP solutions in the Philippines, now proudly recognized as a Premier SAP Partner. Our mission is to serve as your preferred partner in global growth and digital transformation.</p>
			<p<?php dq_reveal( '', 220 ); ?>>Client satisfaction is at the heart of everything we do. As a Premier SAP Partner, we prioritize your business needs, ensuring our solutions seamlessly align with your objectives. Our commitment extends beyond being a conventional software provider; we are your dedicated partner invested in your success.</p>
			<p<?php dq_reveal( '', 290 ); ?>>Our deep-rooted expertise in Enterprise Resource Planning (ERP) systems sets us apart. We provide a comprehensive experience that goes beyond traditional consulting, tailoring solutions meticulously to your unique requirements. With a proven track record of successful implementations and satisfied clients, DynamIQ is your trusted partner for SAP excellence. Choose DynamIQ, your Premier SAP Partner, and embark on a transformative journey toward business growth and success.</p>
		</div>
	</div>
</section>

<!-- ═══ LIFESTYLE GALLERY · auto-scrolling photo carousel ═══ -->
<section class="gallery" aria-labelledby="gallery-hook">
	<div class="wrap">
		<div class="sec-head center gallery-head"<?php dq_reveal(); ?>>
			<span class="eyebrow"><?php esc_html_e( 'Life at DynamIQ', 'dynamiqes' ); ?></span>
			<h2 id="gallery-hook"><?php esc_html_e( 'Less friction, more flow.', 'dynamiqes' ); ?></h2>
		</div>
	</div>
	<div class="gallery-marq"<?php dq_reveal( 'fade' ); ?>>
		<div class="gallery-track">
			<?php foreach ( dq_gallery_photos() as $g ) : ?>
				<figure class="gallery-item"><img src="<?php echo esc_url( dq_asset( $g[1] ) ); ?>" alt="<?php echo esc_attr( $g[0] ); ?>" loading="lazy"></figure>
			<?php endforeach; ?>
			<?php foreach ( dq_gallery_photos() as $g ) : ?>
				<figure class="gallery-item" aria-hidden="true"><img src="<?php echo esc_url( dq_asset( $g[1] ) ); ?>" alt="" loading="lazy"></figure>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- ═══ OUR SERVICES ═══ -->
<section class="our-services" id="services">
	<div class="wrap">
		<div class="sec-head center"<?php dq_reveal(); ?>>
			<h2><?php esc_html_e( 'Our Services', 'dynamiqes' ); ?></h2>
			<p>An enterprise resource planning software is only as good as the company that implements it. As the best SAP system service and solutions in the Philippines, we bring out value by putting together a design that serves your business' demands.</p>
		</div>
		<div class="serv-timeline">
			<?php $show_img = get_theme_mod( 'dq_services_images', true ); foreach ( dq_services() as $i => $s ) : ?>
			<div class="serv-step"<?php dq_reveal( '', $i * 90 ); ?>>
				<?php if ( $show_img && ! empty( $s['image'] ) ) : ?><img class="serv-img" src="<?php echo esc_url( dq_asset( $s['image'] ) ); ?>" alt="<?php echo esc_attr( $s['title'] ); ?>" loading="lazy"><?php endif; ?>
				<div class="serv-node"><span class="serv-num-badge"><?php echo esc_html( $s['num'] ); ?></span></div>
				<h3><?php echo esc_html( $s['title'] ); ?></h3>
				<p><?php echo esc_html( $s['text'] ); ?></p>
			</div>
			<?php endforeach; ?>
		</div>
		<div class="serv-cta"<?php dq_reveal(); ?>>
			<a href="#contact" class="btn btn-orange"><?php esc_html_e( 'See How We Can Help', 'dynamiqes' ); ?> <span class="arr" aria-hidden="true">→</span></a>
		</div>
	</div>
</section>

<!-- ═══ HEAR FROM OUR CUSTOMERS ═══ -->
<section class="hear-from-our-customer" id="testimonials">
	<div class="wrap">
		<div class="sec-head"<?php dq_reveal(); ?>>
			<span class="eyebrow"><?php esc_html_e( 'What they say', 'dynamiqes' ); ?></span>
			<h2><?php esc_html_e( 'Hear From Our Customers', 'dynamiqes' ); ?></h2>
		</div>
	</div>
	<div class="stories-marq">
		<div class="stories-track">
			<?php foreach ( dq_testimonials() as $t ) : ?>
			<article class="story">
				<div class="story-head">
					<span class="story-logo"><?php if ( $t['logo'] ) : ?><img src="<?php echo esc_url( $t['logo'] ); ?>" alt="<?php echo esc_attr( $t['name'] ); ?>" loading="lazy"><?php endif; ?></span>
					<span class="story-q" aria-hidden="true">&rdquo;</span>
				</div>
				<blockquote><?php echo esc_html( $t['quote'] ); ?></blockquote>
				<div class="story-foot">
					<div class="story-who"><div class="story-name"><?php echo esc_html( $t['name'] ); ?></div><?php if ( ! empty( $t['role'] ) ) : ?><div class="story-role"><?php echo esc_html( $t['role'] ); ?></div><?php endif; ?></div>
					<?php if ( ! empty( $t['more'] ) ) : ?><a class="story-more" href="<?php echo esc_url( ! empty( $t['link'] ) ? $t['link'] : '#testimonials' ); ?>"><?php echo esc_html( $t['more'] ); ?> →</a><?php endif; ?>
				</div>
			</article>
			<?php endforeach; ?>
		</div>
	</div>
</section>

<!-- ═══ NEWS AND ANNOUNCEMENTS ═══ -->
<section class="whatsnew" id="news-events">
	<div class="wrap">
		<div class="sec-head"<?php dq_reveal(); ?>>
			<span class="eyebrow"><?php esc_html_e( 'News & Events', 'dynamiqes' ); ?></span>
			<h2><?php esc_html_e( 'Explore what\'s new.', 'dynamiqes' ); ?></h2>
			<p><?php esc_html_e( 'Discover the latest updates, events, and stories from DynamIQ.', 'dynamiqes' ); ?></p>
		</div>
		<?php if ( $featured ) : ?>
		<div class="news-layout"<?php dq_reveal( 'scale' ); ?>>
			<a class="news-card news-feature<?php echo $featured['image'] ? '' : ' no-img'; ?>" href="<?php echo esc_url( $featured['url'] ); ?>">
				<?php if ( $featured['image'] ) : ?><img src="<?php echo esc_url( $featured['image'] ); ?>" alt="<?php echo esc_attr( $featured['title'] ); ?>" loading="lazy"><?php endif; ?>
				<div class="news-card-body">
					<div class="news-meta"><span class="news-tag"><?php echo esc_html( $featured['cat'] ); ?></span><span class="news-date"><?php echo esc_html( $featured['date'] ); ?></span></div>
					<h3><?php echo esc_html( $featured['title'] ); ?></h3>
				</div>
			</a>
			<div class="news-grid">
				<?php foreach ( $news as $n ) : ?>
				<a class="news-card<?php echo $n['image'] ? '' : ' no-img'; ?>" href="<?php echo esc_url( $n['url'] ); ?>">
					<?php if ( $n['image'] ) : ?><img src="<?php echo esc_url( $n['image'] ); ?>" alt="<?php echo esc_attr( $n['title'] ); ?>" loading="lazy"><?php endif; ?>
					<div class="news-card-body">
						<div class="news-meta"><span class="news-tag"><?php echo esc_html( $n['cat'] ); ?></span><span class="news-date"><?php echo esc_html( $n['date'] ); ?></span></div>
						<h3><?php echo esc_html( $n['title'] ); ?></h3>
					</div>
				</a>
				<?php endforeach; ?>
			</div>
		</div>
		<div class="news-cta"<?php dq_reveal(); ?>>
			<a class="btn btn-orange" href="<?php echo esc_url( dq_news_url() ); ?>"><?php esc_html_e( 'See All News & Events', 'dynamiqes' ); ?> <span class="arr" aria-hidden="true">→</span></a>
		</div>
		<?php endif; ?>
	</div>
</section>

<!-- ═══ BOOK A FREE DEMO ═══ -->
<section class="book-free-demo cta-section">
	<div class="wrap">
		<div class="book-inner cta-panel"<?php dq_reveal( 'scale' ); ?>>
			<div class="book-copy">
				<span class="eyebrow"><?php esc_html_e( 'Ready when you are', 'dynamiqes' ); ?></span>
				<h2><?php esc_html_e( 'Let Us Be Your Partner in Growth', 'dynamiqes' ); ?></h2>
				<p><?php esc_html_e( 'We will get in touch with you within 24 hours so that we can discuss how we can best help your company.', 'dynamiqes' ); ?></p>
				<div class="dynamiq-cta"><a href="#contact"><?php esc_html_e( 'Get Your Free Business Analysis', 'dynamiqes' ); ?> <span class="arr" aria-hidden="true">→</span></a></div>
			</div>
			<div class="book-visual" aria-hidden="true">
				<img src="<?php echo esc_url( $handshake ); ?>" alt="" loading="lazy">
			</div>
		</div>
	</div>
</section>

<!-- ═══ CONTACT US ═══ -->
<section class="contact-us" id="contact">
	<?php $contact_video = get_theme_mod( 'dq_contact_video', '' ); if ( ! $contact_video && file_exists( DQ_DIR . '/assets/video/contact-gradient.mp4' ) ) { $contact_video = DQ_URI . '/assets/video/contact-gradient.mp4'; } if ( $contact_video ) : ?>
	<div class="contact-media" aria-hidden="true">
		<video class="contact-video" muted loop playsinline preload="none">
			<source src="<?php echo esc_url( $contact_video ); ?>" type="video/mp4">
		</video>
	</div>
	<?php endif; ?>
	<div class="wrap">
		<div class="contact-panel"<?php dq_reveal( 'scale' ); ?>>
			<div class="contact-grid">
				<div class="contact-form-side">
					<h2><?php esc_html_e( 'We\'d Like To Hear From You', 'dynamiqes' ); ?></h2>
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
							<div class="field">
								<label class="screen-reader-text" for="cf-industry"><?php esc_html_e( 'Industry', 'dynamiqes' ); ?></label>
								<select id="cf-industry" name="industry" required>
									<option value="" selected disabled><?php esc_html_e( 'Industry', 'dynamiqes' ); ?></option>
									<?php foreach ( array( 'Services / BPO', 'Real Estate / Construction', 'Water / Telco / Electricity / Energy', 'Food and Beverage', 'Pharmaceutical / Healthcare Industry', 'Transport', 'Agriculture', 'Finance', 'Trading / Distribution', 'Manufacturing', 'Hospitality / Tourism', 'Media', 'Others' ) as $o ) : ?>
										<option value="<?php echo esc_attr( $o ); ?>"><?php echo esc_html( $o ); ?></option>
									<?php endforeach; ?>
								</select>
							</div>
							<div class="field">
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
					<h3><?php esc_html_e( 'Get in Touch', 'dynamiqes' ); ?></h3>
					<p><?php esc_html_e( 'Have a question or need a demo? We\'d love to hear from you! Please fill out the form and we\'ll get back to you promptly.', 'dynamiqes' ); ?></p>
					<div class="contact-info">
						<div class="loc-card">
							<h4><?php echo esc_html( $contact['city'] ); ?></h4>
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

</main>
<?php
get_footer();
