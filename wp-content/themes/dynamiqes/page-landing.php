<?php
/**
 * Template Name: Landing page
 * Template Post Type: page
 *
 * SEO / SEM landing page. The hero carries the H1, the intro and the page's CTAs; the body is
 * built into designed sections by inc/landing-sections.php rather than poured into one article
 * column, which is what the review asked for on every one of these pages (item F1).
 *
 * @package dynamiqes
 */

get_header();
the_post();

$hero_img = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'dq-wide' ) : get_post_meta( get_the_ID(), '_dq_hero_image', true );
$intro    = get_post_meta( get_the_ID(), '_dq_landing_intro', true );
$headline = get_post_meta( get_the_ID(), '_dq_landing_h1', true ); // article headline; the page title stays for <title>/menus
$body     = get_post()->post_content;
$live     = null; // not-yet-imported page (e.g. customizer preview before activation): render the old page's content read-only
if ( '' === trim( $body ) && ! get_post_meta( get_the_ID(), '_dq_landing_source', true ) && function_exists( 'dq_landing_live_data' ) ) {
	$live = dq_landing_live_data( get_post()->post_name );
	if ( $live ) {
		$headline = $headline ? $headline : $live['title'];
		$intro    = $intro ? $intro : $live['intro'];
		$hero_img = $hero_img ? $hero_img : $live['hero_image'];
		$body     = $live['content'];
	}
}
if ( ! $headline ) {
	$headline = get_the_title();
}
if ( ! $intro && has_excerpt() ) {
	$intro = '<p>' . esc_html( get_the_excerpt() ) . '</p>';
}

/* Designed sections. Un-imported markup (typed straight into the editor) has no H2 rhythm to
   group on, so it falls back to the plain content column. */
$sections = array( '', false );
if ( function_exists( 'dq_landing_sections' ) ) {
	/* No per-section eyebrow: on these pages it would repeat the (long) page title above
	   every H2. The headings carry the structure on their own. */
	$sections = dq_landing_sections( $body, '' );
}
list( $sections_html, $has_own_cta ) = $sections;

$contact = dq_contact_info();
?>
<main id="main">
	<article <?php post_class( 'landing' ); ?>>
		<section class="landing-hero">
			<div class="wrap landing-hero-grid">
				<div class="landing-hero-copy">
					<h1<?php dq_reveal(); ?>><?php echo esc_html( $headline ); ?></h1>
					<?php if ( $intro ) : ?><div class="landing-intro"<?php dq_reveal( '', 80 ); ?>><?php echo wp_kses_post( $intro ); ?></div><?php endif; ?>
					<?php /* the H1 section carried no call to action, which the review flagged on the
					   promo pages (item F7): the enquiry link and the phone number, as on the live pages. */ ?>
					<div class="hero-actions"<?php dq_reveal( '', 140 ); ?>>
						<a class="btn btn-orange" href="<?php echo esc_url( dq_book_demo_url() ); ?>"><?php esc_html_e( 'Get Your Free Business Analysis', 'dynamiqes' ); ?> <span class="arr" aria-hidden="true">&rarr;</span></a>
						<?php if ( ! empty( $contact['phone1'] ) ) : ?>
						<a class="btn btn-ghost" href="<?php echo esc_attr( dq_tel( $contact['phone1'] ) ); ?>"><?php echo dq_icon_phone(); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( $contact['phone1'] ); ?></a>
						<?php endif; ?>
					</div>
				</div>
				<?php if ( $hero_img ) : ?>
				<div class="landing-hero-media"<?php dq_reveal( 'scale' ); ?>><img src="<?php echo esc_url( $hero_img ); ?>" alt="<?php the_title_attribute(); ?>" fetchpriority="high"></div>
				<?php endif; ?>
			</div>
		</section>
		<?php if ( '' !== $sections_html ) : ?>
			<?php echo $sections_html; // phpcs:ignore WordPress.Security.EscapeOutput — built from esc_*/wp_kses_post in inc/landing-sections.php ?>
		<?php else : ?>
		<section class="landing-body">
			<div class="wrap">
				<div class="entry-content landing-content"><?php echo apply_filters( 'the_content', $body ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
			</div>
		</section>
		<?php endif; ?>
	</article>
	<?php
	/* The page's own CTA copy is rendered in place by the section builder; only fall back to the
	   shared band when the page had none, so the original heading and copy are the ones that
	   close the page (review item F3). */
	if ( ! $has_own_cta ) {
		dq_cta_band();
	}
	?>
</main>
<?php
get_footer();
