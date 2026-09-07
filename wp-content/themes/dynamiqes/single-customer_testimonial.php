<?php
/**
 * Single testimonial (/testimonials/<client>/ — the live site's customer_testimonial post type).
 *
 * Structure mirrors dynamiqes.com/testimonials/<client>/ (SEO): a fixed H1 "Discover What Our
 * Clients Have to Say", previous / next links, the quote, then the client name as a paragraph.
 * Composition follows the live page: quote + logo on the left, the client's video on the right
 * (dq_testimonial_story() in inc/testimonial-media.php). No scroll-reveal here: the whole page is
 * composed to fit the first screen (100dvh) so the quote and video are readable at once when the
 * visitor steps through Prev / Next.
 *
 * @package dynamiqes
 */

get_header();
the_post();

$prev = get_previous_post();
$next = get_next_post();
$hub  = get_page_by_path( 'client-testimonials' );
$hub  = $hub ? get_permalink( $hub ) : home_url( '/client-testimonials/' );
$row  = array(
	'id'    => get_the_ID(),
	'name'  => get_the_title(),
	'role'  => get_post_meta( get_the_ID(), '_dq_role', true ),
	'logo'  => has_post_thumbnail() ? get_the_post_thumbnail_url( get_the_ID(), 'medium' ) : dq_asset( get_post_meta( get_the_ID(), '_dq_logo', true ) ),
	'quote' => wp_strip_all_tags( strip_shortcodes( get_the_content() ) ),
	'link'  => '',
);
?>
<main id="main">
	<article <?php post_class( 'testimonial-single' ); ?>>
		<header class="page-hero">
			<div class="wrap">
				<nav class="breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'dynamiqes' ); ?>"><a href="<?php echo esc_url( $hub ); ?>"><?php esc_html_e( 'Client Testimonials', 'dynamiqes' ); ?></a> / <?php the_title(); ?></nav>
				<h1><?php esc_html_e( 'Discover What Our Clients Have to Say', 'dynamiqes' ); ?></h1>
			</div>
		</header>
		<section class="testimonial-body">
			<div class="wrap">
				<nav class="testi-nav" aria-label="<?php esc_attr_e( 'More testimonials', 'dynamiqes' ); ?>">
					<?php if ( $prev ) : ?><a class="tlink" href="<?php echo esc_url( get_permalink( $prev ) ); ?>"><span class="arr arr-back" aria-hidden="true">←</span> <?php esc_html_e( 'Previous', 'dynamiqes' ); ?></a><?php else : ?><span class="tlink is-disabled" aria-disabled="true"><span class="arr" aria-hidden="true">←</span> <?php esc_html_e( 'Previous', 'dynamiqes' ); ?></span><?php endif; ?>
					<a class="tlink testi-nav-all" href="<?php echo esc_url( $hub ); ?>"><?php esc_html_e( 'All testimonials', 'dynamiqes' ); ?></a>
					<?php if ( $next ) : ?><a class="tlink" href="<?php echo esc_url( get_permalink( $next ) ); ?>"><?php esc_html_e( 'Next', 'dynamiqes' ); ?> <span class="arr" aria-hidden="true">→</span></a><?php else : ?><span class="tlink is-disabled" aria-disabled="true"><?php esc_html_e( 'Next', 'dynamiqes' ); ?> <span class="arr" aria-hidden="true">→</span></span><?php endif; ?>
				</nav>
				<?php dq_testimonial_story( $row, 'p', array() ); ?>
			</div>
		</section>
	</article>
	<?php dq_cta_band(); ?>
</main>
<?php
get_footer();
