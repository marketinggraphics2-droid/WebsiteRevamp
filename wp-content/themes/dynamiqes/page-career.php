<?php
/**
 * Careers (/career/) — the old site's nav target. Picked up by slug (page-{slug}.php).
 *
 * Structure mirrors dynamiqes.com/career/ (SEO): H1 "Discover Your Career Path with Us" + intro,
 * H2 "DynamIQ Enterprise Solution Hiring Position" with the openings filtered by location
 * (All / Metro Manila / Cebu), the "Join Us…" and "Experience the Vibrant Culture…" copy, the
 * four core values and the HR contact block. Openings come from the dq_career post type
 * (inc/careers.php); the page title stays "Careers" for <title> / menus. Content typed into the
 * page in WP Admin replaces the default intro paragraph.
 *
 * @package dynamiqes
 */

get_header();
the_post();

$copy = dq_career_page_copy();
$jobs = dq_career_jobs();
$locs = dq_career_locations( $jobs );
$hr   = dq_career_hr();
$c    = dq_contact_info();
$sap  = function_exists( 'dq_get_product_by_key' ) ? dq_get_product_by_key( 'sap' ) : null;

$headline = get_post_meta( get_the_ID(), '_dq_career_h1', true );
$headline = $headline ? $headline : $copy['h1'];
$intro    = trim( get_the_content() );
$intro    = '' === $intro ? '<p>' . esc_html( $copy['intro'] ) . '</p>' : apply_filters( 'the_content', $intro );

$pin = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 2a7 7 0 0 0-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 0 0-7-7zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z"/></svg>';
?>
<main id="main">
	<article <?php post_class( 'career-page' ); ?>>
		<header class="page-hero page-hero--photo" style="--hero-photo:url('<?php echo esc_url( dq_page_hero_photo( 'career-banner.jpg' ) ); ?>')"><?php /* the live page's banner photo (Featured Image overrides it) */ ?>
			<div class="wrap">
				<span class="eyebrow"<?php dq_reveal( 'fade' ); ?>><?php the_title(); ?></span>
				<h1<?php dq_reveal(); ?>><?php echo esc_html( $headline ); ?></h1>
				<div class="page-hero-intro"<?php dq_reveal( '', 80 ); ?>><?php echo wp_kses_post( $intro ); ?></div>
			</div>
		</header>

		<section class="career-jobs" id="openings">
			<div class="wrap">
				<div class="sec-head"<?php dq_reveal(); ?>>
					<span class="eyebrow"><?php esc_html_e( 'Open positions', 'dynamiqes' ); ?></span>
					<h2><?php echo esc_html( $copy['hiring_h2'] ); ?></h2>
					<p><?php echo esc_html( $copy['hiring_lead'] ); ?></p>
				</div>
				<?php if ( count( $locs ) > 1 ) : ?>
				<div class="career-filter" role="tablist" aria-label="<?php esc_attr_e( 'Filter openings by location', 'dynamiqes' ); ?>"<?php dq_reveal( 'fade' ); ?>>
					<button type="button" class="is-active" role="tab" aria-selected="true" data-location=""><?php esc_html_e( 'All', 'dynamiqes' ); ?></button>
					<?php foreach ( $locs as $l ) : ?>
					<button type="button" role="tab" aria-selected="false" data-location="<?php echo esc_attr( sanitize_title( $l ) ); ?>"><?php echo esc_html( $l ); ?></button>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>
				<div class="career-grid">
					<?php foreach ( $jobs as $i => $j ) : ?>
					<article class="career-card" data-location="<?php echo esc_attr( sanitize_title( $j['location'] ) ); ?>"<?php dq_reveal( '', min( $i, 7 ) * 70 ); ?>>
						<h3><?php echo esc_html( $j['title'] ); ?></h3>
						<?php /* always rendered — an opening with no location keeps its row so the cards in a line stay aligned (review item A2) */ ?>
						<p class="career-loc"><?php if ( $j['location'] ) : ?><?php echo $pin; // phpcs:ignore WordPress.Security.EscapeOutput ?><?php echo esc_html( $j['location'] ); ?><?php if ( $j['type'] ) : ?> <span class="career-type">· <?php echo esc_html( $j['type'] ); ?></span><?php endif; ?><?php endif; ?></p>
						<h4><?php esc_html_e( 'Job Description', 'dynamiqes' ); ?></h4>
						<p><?php echo esc_html( wp_trim_words( $j['summary'], 28, '…' ) ); ?></p>
						<?php if ( $j['url'] ) : ?>
						<a class="text-link" href="<?php echo esc_url( $j['url'] ); ?>"><?php esc_html_e( 'Read More', 'dynamiqes' ); ?> <span aria-hidden="true">→</span></a>
						<?php else : ?>
						<a class="text-link" href="<?php echo esc_url( dq_career_apply_url( $j['title'] ) ); ?>"><?php esc_html_e( 'Apply Now', 'dynamiqes' ); ?> <span aria-hidden="true">→</span></a>
						<?php endif; ?>
					</article>
					<?php endforeach; ?>
				</div>
				<p class="career-empty" hidden><?php esc_html_e( 'No openings in this location right now — send your CV to HR and we will keep it on file.', 'dynamiqes' ); ?></p>
			</div>
		</section>

		<section class="career-join">
			<div class="wrap career-join-grid">
				<div<?php dq_reveal(); ?>>
					<span class="eyebrow"><?php esc_html_e( 'SAP Business One', 'dynamiqes' ); ?></span>
					<h2><?php echo esc_html( $copy['join_h2'] ); ?></h2>
					<p><?php echo esc_html( $copy['join_p'] ); ?></p>
				</div>
				<?php if ( $sap && ! empty( $sap['hero'] ) ) : ?>
				<div class="career-join-media"<?php dq_reveal( 'scale' ); ?>><img src="<?php echo esc_url( $sap['hero'] ); ?>" alt="<?php esc_attr_e( 'SAP Business One software displayed on a monitor', 'dynamiqes' ); ?>" loading="lazy"></div>
				<?php endif; ?>
			</div>
		</section>

		<section class="career-culture">
			<div class="wrap">
				<div class="sec-head center"<?php dq_reveal(); ?>>
					<span class="eyebrow"><?php esc_html_e( 'Life at DynamIQ', 'dynamiqes' ); ?></span>
					<h2><?php echo esc_html( $copy['culture_h2'] ); ?></h2>
					<p><?php echo esc_html( $copy['culture_p'] ); ?></p>
				</div>
				<div class="career-values-head"<?php dq_reveal(); ?>>
					<h2><?php echo esc_html( $copy['values_h2'] ); ?></h2>
					<p><?php echo esc_html( $copy['values_lead'] ); ?></p>
				</div>
				<div class="career-values">
					<?php foreach ( $copy['values'] as $i => $v ) : ?>
					<article class="feature-group"<?php dq_reveal( '', $i * 90 ); ?>>
						<h3><?php echo esc_html( $v[0] ); ?></h3>
						<p><?php echo esc_html( $v[1] ); ?></p>
					</article>
					<?php endforeach; ?>
				</div>
			</div>
		</section>

		<section class="career-hr" id="hr">
			<div class="wrap career-hr-grid">
				<div<?php dq_reveal(); ?>>
					<span class="eyebrow"><?php esc_html_e( 'HR Contact Information', 'dynamiqes' ); ?></span>
					<p class="h2"><?php esc_html_e( 'Ready to apply?', 'dynamiqes' ); ?></p><?php /* not a heading: the live /career/ outline ends with the core values */ ?>
					<p><?php esc_html_e( 'Send your CV and the position you are applying for to our HR team, or call us during office hours.', 'dynamiqes' ); ?></p>
					<ul>
						<li><a href="<?php echo esc_attr( dq_tel( $hr['phone'] ) ); ?>"><?php echo esc_html( $hr['phone'] ); ?></a></li>
						<li><a href="<?php echo esc_url( dq_career_apply_url() ); ?>"><?php echo esc_html( $hr['email'] ); ?></a></li>
					</ul>
				</div>
				<div<?php dq_reveal( '', 120 ); ?>>
					<p class="name">DYNAMIQ ENTERPRISE SOLUTION INC.</p>
					<p><?php echo esc_html( $c['address'] ); ?></p>
					<?php if ( ! empty( $c['phone1'] ) ) : ?><p><a href="<?php echo esc_attr( dq_tel( $c['phone1'] ) ); ?>"><?php echo esc_html( $c['phone1'] ); ?></a></p><?php endif; ?>
					<?php if ( ! empty( $c['hours'] ) ) : ?><p><?php echo esc_html( $c['hours'] ); ?></p><?php endif; ?>
				</div>
			</div>
		</section>
	</article>
	<?php dq_cta_band(); ?>
</main>
<?php
get_footer();
