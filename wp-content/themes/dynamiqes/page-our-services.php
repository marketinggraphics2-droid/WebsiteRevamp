<?php
/**
 * Our Services (/our-services/) — the old dynamiqes.com services page rebuilt in the home-page
 * design language: full-bleed photo hero (home-banner idiom, the live page's hero image), one full-width band per service (alternating light / dark, copy
 * beside the section's photo composite, icon feature rows, the SAP five-phase flow), the
 * "Why Trust" band, a CTA band and the shared contact section.
 *
 * Content is NOT hard-coded here: it is the page content imported from the live site by the
 * landing importer (H2 per service, paragraphs, caption lists) — editable in WP Admin and kept
 * verbatim for SEO. This template only lays it out; dq_svc_section_meta() adds the presentation
 * extras (tone, photo, icons, CTA, phases) keyed by the H2 text.
 *
 * @package dynamiqes
 */

get_header();
the_post();

$dq_page   = get_post();
$intro     = get_post_meta( $dq_page->ID, '_dq_landing_intro', true );
$headline  = get_post_meta( $dq_page->ID, '_dq_landing_h1', true );
$content   = $dq_page->post_content;
if ( '' === trim( $content ) && function_exists( 'dq_landing_live_data' ) ) {
	$live = dq_landing_live_data( $dq_page->post_name );
	if ( $live ) {
		$content  = $live['content'];
		$intro    = $intro ? $intro : $live['intro'];
		$headline = $headline ? $headline : $live['title'];
	}
}
$headline = $headline ? $headline : get_the_title();

/* ---- intro paragraphs ---- */
$intro_paras = array();
if ( preg_match_all( '#<p[^>]*>(.*?)</p>#si', (string) $intro, $m ) ) {
	foreach ( $m[1] as $t ) {
		$t = trim( wp_strip_all_tags( $t ) );
		if ( '' !== $t ) {
			$intro_paras[] = html_entity_decode( $t, ENT_QUOTES, 'UTF-8' );
		}
	}
}

/* ---- sections: split the imported content on its H2s ---- */
$sections = array();
$chunks   = preg_split( '#<h2[^>]*>#i', $content );
array_shift( $chunks ); // anything before the first H2 (there is nothing on this page)
foreach ( $chunks as $chunk ) {
	$parts = preg_split( '#</h2>#i', $chunk, 2 );
	$title = html_entity_decode( trim( wp_strip_all_tags( $parts[0] ) ), ENT_QUOTES, 'UTF-8' );
	$body  = isset( $parts[1] ) ? $parts[1] : '';
	$plain_title = str_replace( array( '’', '‘' ), "'", $title );
	if ( 0 === strcasecmp( $plain_title, 'Contact Us Today to Learn More About Our Services!' ) || 0 === strcasecmp( $plain_title, "We'd Like To Hear From You" ) ) {
		continue; // the live CTA block and contact block: drawn as the panel and the shared contact section below the service bands
	}
	$sec   = array( 'title' => $title, 'paras' => array(), 'items' => array(), 'images' => array() );
	if ( preg_match_all( '#<p[^>]*>(.*?)</p>#si', $body, $pm ) ) {
		foreach ( $pm[1] as $t ) {
			$t = trim( wp_strip_all_tags( $t ) );
			if ( '' !== $t ) {
				$sec['paras'][] = html_entity_decode( $t, ENT_QUOTES, 'UTF-8' );
			}
		}
	}
	if ( preg_match_all( '#<li[^>]*>(.*?)</li>#si', $body, $lm ) ) {
		foreach ( $lm[1] as $t ) {
			$t = trim( wp_strip_all_tags( $t ) );
			if ( '' !== $t ) {
				$sec['items'][] = html_entity_decode( $t, ENT_QUOTES, 'UTF-8' );
			}
		}
	}
	if ( preg_match_all( '#<img[^>]+src="([^"]+)"#i', $body, $im ) ) {
		$sec['images'] = $im[1];
	}
	if ( '' !== $title ) {
		$sections[] = $sec;
	}
}

/**
 * Presentation extras per section, keyed by the H2 slug. Photos are the old page's composites
 * (assets/services/page/), icons the old caption icons (assets/services/page/icons/).
 */
function dq_svc_section_meta( $title ) {
	$base  = 'assets/services/page/';
	$icons = $base . 'icons/';
	$map   = array(
		'sap-bir-cas-accreditation-assistance' => array(
			'tone'  => 'dark',
			'image' => $base . 'sap-accreditation.png',
			'icons' => array(
				'a-streamlined-accounting-process'  => 'streamline-accounting.png',
				'online-viewing-of-tax-transaction' => 'online-viewing-tax.png',
				'business-process-flow'             => 'business-process.png',
				'creation-of-blank-database'        => 'creation-data-base.png',
				'alignment-of-forms-to-bir'         => 'alignment-forms.png',
				'bir-mock-demo'                     => 'bir-mock.png',
				'bir-actual-presentation-assistance' => 'bir-presentation.png',
			),
		),
		'consultation' => array(
			'tone'  => 'light',
			'image' => $base . 'consultation-new.png',
			'icons' => array(
				'embrace-digital-transformation'     => 'embrace-digital.svg',
				'streamline-processes-and-procedures' => 'streamline-process.svg',
				'project-realization'                => 'project-realization.svg',
				'eliminate-disjointed-systems'       => 'eliminate-disjointed.svg',
				'consolidate-critical-data'          => 'consolidate-critical.svg',
			),
		),
		'training'    => array( 'tone' => 'dark', 'image' => $base . 'training-new.png' ),
		'development' => array( 'tone' => 'light', 'image' => $base . 'development-new.png' ),
		'technical-and-helpdesk-support' => array(
			'tone'  => 'dark',
			'image' => $base . 'tech-helpdesk.png',
			'icons' => array(
				'basic-sap-functionality-inquiries'          => 'basic-sap-functionality.png',
				'administrative-control-inquiries'           => 'admin-control.png',
				'setting-up-features-or-module-configuration' => 'settings.png',
				'network-support'                            => 'network-support.png',
				'backup-and-restore-procedures'              => 'backup.png',
			),
		),
		'implementation' => array(
			'tone'   => 'light',
			'image'  => $base . 'implementation-new.png',
			/* SAP's five-phase methodology — drawn as an image on the old page, rebuilt as markup */
			'phases' => array( 'Project Preparation', 'Business Blueprint', 'Project Realization', 'Final Preparation', 'Go-Live' ),
		),
		'why-trust-dynamiq-and-our-services' => array( 'tone' => 'dark', 'image' => $base . 'why-trust-dynamiqes.png', 'layout' => 'trust' ),
	);
	$key  = sanitize_title( $title );
	$meta = isset( $map[ $key ] ) ? $map[ $key ] : array( 'tone' => 'light', 'image' => '' );
	if ( ! empty( $meta['icons'] ) ) {
		foreach ( $meta['icons'] as $k => $f ) {
			$meta['icons'][ $k ] = $icons . $f;
		}
	}
	return $meta;
}

$demo_url = function_exists( 'dq_book_demo_url' ) ? dq_book_demo_url() : dq_home_anchor( 'contact' );
$svg_arrow = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h13M12 6l6 6-6 6"/></svg>';
?>
<main id="main">
	<article <?php post_class( 'services-page' ); ?>>

		<!-- ═══ HERO ═══ -->
		<?php /* Same structure as the live page's .services-top-content: the centred H1 and its three intro
		   paragraphs, nothing else (no buttons, no service index). Presentation follows the home banner:
		   the live page's hero photo full-bleed behind the copy, one screen tall on desktop. */ ?>
		<section class="svc-hero" aria-labelledby="svc-title">
			<div class="banner-media svc-hero-media" aria-hidden="true">
				<?php /* the public page's own hero photo (assets/images/our-services/services-hero-bg.jpg on dynamiqes.com),
				   copied into the theme; it ships with a dark tint baked in, so the scrim is lighter than the home banner's */ ?>
				<img src="<?php echo esc_url( dq_asset( 'assets/services/page/services-hero-bg.jpg' ) ); ?>" alt="" width="1309" height="948" fetchpriority="high" decoding="async">
			</div>
			<div class="wrap">
				<div class="svc-hero-copy">
					<h1 id="svc-title" class="text-center"<?php dq_reveal( 'fade', 60 ); ?>><?php echo esc_html( $headline ); ?></h1>
					<?php foreach ( $intro_paras as $k => $p ) : ?><p<?php dq_reveal( '', 140 + $k * 60 ); ?>><?php echo esc_html( $p ); ?></p><?php endforeach; ?>
				</div>
				<?php /* On the live page this H3 sits in a hidden enquiry modal (trigger button commented out), so it is
				   in the HTML but never displayed. Same here: present for the outline, not rendered. */ ?>
				<div class="svc-consult-modal" hidden><h3 class="svc-consult"><?php esc_html_e( 'Consult with our SAP Business One Specialist today!', 'dynamiqes' ); ?></h3></div>
			</div>
		</section>

		<!-- ═══ SERVICE BANDS (one per imported H2) ═══ -->
		<?php $n = 0; foreach ( $sections as $i => $sec ) :
			$meta   = dq_svc_section_meta( $sec['title'] );
			$trust  = ! empty( $meta['layout'] ) && 'trust' === $meta['layout'];
			$image  = ! empty( $meta['image'] ) ? dq_asset( $meta['image'] ) : ( ! empty( $sec['images'] ) ? $sec['images'][0] : '' );
			$flip   = ( $i % 2 ) === 1; // photo left on every second band
			if ( ! $trust ) { $n++; }
			$paras  = $sec['paras'];
			/* a lead-in ending with ":" introduces the list / phases — keep it as the last paragraph */
		?>
		<section class="svc-band svc-<?php echo esc_attr( $meta['tone'] ); ?><?php echo $flip ? ' svc-flip' : ''; ?><?php echo $trust ? ' svc-trust' : ''; ?>" id="svc-<?php echo esc_attr( sanitize_title( $sec['title'] ) ); ?>">
			<div class="wrap svc-grid">
				<div class="svc-copy">
					<?php if ( ! $trust ) : ?><span class="eyebrow"<?php dq_reveal(); ?>><?php echo esc_html( sprintf( __( 'Service %02d', 'dynamiqes' ), $n ) ); ?></span>
					<?php else : ?><span class="eyebrow"<?php dq_reveal(); ?>><?php esc_html_e( 'Why DynamIQ', 'dynamiqes' ); ?></span><?php endif; ?>
					<h2<?php dq_reveal( '', 60 ); ?>><?php echo esc_html( $sec['title'] ); ?></h2>
					<?php foreach ( $paras as $k => $p ) : ?><p<?php dq_reveal( '', 100 + $k * 50 ); ?>><?php echo esc_html( $p ); ?></p><?php endforeach; ?>

					<?php /* the imported list is the section's icon captions — except on Implementation, where it
					   is the five-phase diagram's labels, drawn below as the phase flow instead */ ?>
					<?php if ( $sec['items'] && empty( $meta['phases'] ) ) : ?>
					<ul class="svc-features"<?php dq_reveal( '', 220 ); ?>>
						<?php foreach ( $sec['items'] as $item ) : $ik = sanitize_title( $item ); $icon = ! empty( $meta['icons'][ $ik ] ) ? dq_asset( $meta['icons'][ $ik ] ) : ''; ?>
						<li><span class="svc-ic"><?php if ( $icon ) : ?><img src="<?php echo esc_url( $icon ); ?>" alt="" loading="lazy"><?php else : ?><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg><?php endif; ?></span><span><?php echo esc_html( $item ); ?></span></li>
						<?php endforeach; ?>
					</ul>
					<?php endif; ?>

					<?php if ( ! empty( $meta['phases'] ) ) : ?>
					<ol class="svc-phases" aria-label="<?php esc_attr_e( 'SAP Five-Phase Implementation Methodology', 'dynamiqes' ); ?>"<?php dq_reveal( '', 220 ); ?>>
						<?php foreach ( $meta['phases'] as $pi => $phase ) : ?>
						<li><span class="svc-phase-num"><?php echo esc_html( sprintf( '%02d', $pi + 1 ) ); ?></span><span class="svc-phase-label"><?php echo esc_html( $phase ); ?></span></li>
						<?php endforeach; ?>
					</ol>
					<?php endif; ?>

					<?php if ( ! empty( $meta['cta'] ) ) : $cta_page = get_page_by_path( $meta['cta'][1] ); $cta_url = $cta_page ? get_permalink( $cta_page ) : home_url( '/' . $meta['cta'][1] . '/' ); ?>
					<div class="svc-cta"<?php dq_reveal( '', 260 ); ?>><a class="btn btn-orange" href="<?php echo esc_url( $cta_url ); ?>"><?php echo esc_html( $meta['cta'][0] ); ?> <span aria-hidden="true">→</span></a></div>
					<?php endif; ?>
				</div>
				<?php if ( $image ) : ?>
				<figure class="svc-media"<?php dq_reveal( 'scale', 120 ); ?>><img src="<?php echo esc_url( $image ); ?>" alt="<?php echo esc_attr( $sec['title'] ); ?>" loading="lazy"></figure>
				<?php endif; ?>
			</div>
		</section>
		<?php endforeach; ?>

		<!-- ═══ CTA ═══ -->
		<section class="cta-section svc-cta-band">
			<div class="wrap">
				<div class="cta-panel"<?php dq_reveal( 'scale' ); ?>>
					<h2><?php esc_html_e( 'Contact Us Today to Learn More About Our Services!', 'dynamiqes' ); ?></h2>
					<p><?php esc_html_e( 'Tell us about your business and our SAP Business One specialists will show you where consultation, implementation, training and support can make the biggest difference.', 'dynamiqes' ); ?></p>
					<div class="dynamiq-cta"><a href="<?php echo esc_url( $demo_url ); ?>"><?php esc_html_e( 'Get Your Free Business Analysis', 'dynamiqes' ); ?> <span class="arr" aria-hidden="true">→</span></a></div>
				</div>
			</div>
		</section>
	</article>

	<?php get_template_part( 'template-parts/contact-section' ); ?>
</main>
<?php
get_footer();
