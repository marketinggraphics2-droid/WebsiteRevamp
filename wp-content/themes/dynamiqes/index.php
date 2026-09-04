<?php
/**
 * Blog index, archives (category / tag / date / author) and search results.
 * Cards come from template-parts/post-card.php; single posts use single.php.
 *
 * @package dynamiqes
 */

get_header();

if ( is_search() ) {
	$eyebrow = __( 'Search', 'dynamiqes' );
	/* translators: %s: search query */
	$title   = sprintf( __( 'Results for “%s”', 'dynamiqes' ), get_search_query() );
	$lede    = '';
} elseif ( is_home() ) {
	$eyebrow = __( 'Blogs', 'dynamiqes' );
	$title   = get_option( 'page_for_posts' ) ? get_the_title( get_option( 'page_for_posts' ) ) : __( 'Blog', 'dynamiqes' );
	$lede    = __( 'Insights on ERP, SAP Business One, accounting, compliance and growing a business in the Philippines.', 'dynamiqes' );
} else {
	$eyebrow = __( 'Archive', 'dynamiqes' );
	$title   = get_the_archive_title();
	$lede    = wp_strip_all_tags( get_the_archive_description() );
}
?>
<main id="main">
	<section class="page-hero">
		<div class="wrap">
			<span class="eyebrow"<?php dq_reveal(); ?>><?php echo esc_html( $eyebrow ); ?></span>
			<h1<?php dq_reveal(); ?>><?php echo wp_kses_post( $title ); ?></h1>
			<?php if ( $lede ) : ?><p<?php dq_reveal(); ?>><?php echo esc_html( $lede ); ?></p><?php endif; ?>
		</div>
	</section>
	<section>
		<div class="wrap">
			<?php if ( have_posts() ) : ?>
				<div class="post-grid">
					<?php while ( have_posts() ) : the_post(); get_template_part( 'template-parts/post-card' ); endwhile; ?>
				</div>
				<nav class="pagination" aria-label="<?php esc_attr_e( 'Pagination', 'dynamiqes' ); ?>"><?php echo paginate_links( array( 'prev_text' => '←', 'next_text' => '→' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></nav>
			<?php else : ?>
				<div class="not-found">
					<p><?php echo is_search() ? esc_html__( 'Nothing matched your search. Try a different keyword.', 'dynamiqes' ) : esc_html__( 'No posts yet. Check back soon.', 'dynamiqes' ); ?></p>
					<?php get_search_form(); ?>
				</div>
			<?php endif; ?>
		</div>
	</section>
	<?php dq_cta_band(); ?>
</main>
<?php
get_footer();
