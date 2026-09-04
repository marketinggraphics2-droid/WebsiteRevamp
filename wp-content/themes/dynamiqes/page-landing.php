<?php
/**
 * Template Name: Landing page
 * Template Post Type: page
 *
 * SEO landing page (dark hero with orange headline + image, then long-form content),
 * matching the dynamiqes.com landing pages linked under "Blogs".
 *
 * @package dynamiqes
 */

get_header();
the_post();

$hero_img = has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'dq-wide' ) : get_post_meta( get_the_ID(), '_dq_hero_image', true );
$intro    = get_post_meta( get_the_ID(), '_dq_landing_intro', true );
if ( ! $intro && has_excerpt() ) {
	$intro = '<p>' . esc_html( get_the_excerpt() ) . '</p>';
}
?>
<main id="main">
	<article <?php post_class( 'landing' ); ?>>
		<section class="landing-hero">
			<div class="wrap landing-hero-grid">
				<div class="landing-hero-copy">
					<h1<?php dq_reveal(); ?>><?php the_title(); ?></h1>
					<?php if ( $intro ) : ?><div class="landing-intro"<?php dq_reveal( '', 80 ); ?>><?php echo wp_kses_post( $intro ); ?></div><?php endif; ?>
					<div class="hero-actions"<?php dq_reveal( '', 160 ); ?>>
						<a class="btn btn-primary" href="<?php echo esc_url( dq_home_anchor( 'contact' ) ); ?>"><?php esc_html_e( 'GET IN TOUCH', 'dynamiqes' ); ?> <span aria-hidden="true">→</span></a>
						<a class="btn btn-ghost" href="<?php echo esc_url( dq_products_url() ); ?>"><?php esc_html_e( 'Explore the IQ Suite', 'dynamiqes' ); ?></a>
					</div>
				</div>
				<?php if ( $hero_img ) : ?>
				<div class="landing-hero-media"<?php dq_reveal( 'scale' ); ?>><img src="<?php echo esc_url( $hero_img ); ?>" alt="<?php the_title_attribute(); ?>" fetchpriority="high"></div>
				<?php endif; ?>
			</div>
		</section>
		<section class="landing-body">
			<div class="wrap">
				<div class="entry-content landing-content"><?php the_content(); ?></div>
			</div>
		</section>
	</article>
	<?php dq_cta_band(); ?>
</main>
<?php
get_footer();
