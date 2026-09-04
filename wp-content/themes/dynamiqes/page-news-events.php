<?php
/**
 * "News & Events" page (slug: news-events). Lists posts from the news categories
 * (Customizer → DynamIQ Theme → Content sources), leaving the rest to the Blog.
 * Not used when the site has its own news post type (its archive is linked instead).
 *
 * @package dynamiqes
 */

get_header();
the_post();

$paged = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
$cats  = dq_news_category_ids();
$args  = array( 'post_type' => 'post', 'post_status' => 'publish', 'posts_per_page' => 9, 'paged' => $paged, 'ignore_sticky_posts' => true );
if ( $cats ) {
	$args['category__in'] = $cats;
}
$news = new WP_Query( $args );
?>
<main id="main">
	<section class="page-hero">
		<div class="wrap">
			<span class="eyebrow"<?php dq_reveal(); ?>><?php esc_html_e( 'News & Events', 'dynamiqes' ); ?></span>
			<h1<?php dq_reveal(); ?>><?php the_title(); ?></h1>
			<p<?php dq_reveal(); ?>><?php echo has_excerpt() ? esc_html( get_the_excerpt() ) : esc_html__( 'Discover the latest updates, events, and stories from DynamIQ.', 'dynamiqes' ); ?></p>
		</div>
	</section>
	<?php if ( '' !== trim( wp_strip_all_tags( get_the_content() ) ) ) : ?>
	<section class="entry"><div class="wrap"><div class="entry-content"><?php the_content(); ?></div></div></section>
	<?php endif; ?>
	<section>
		<div class="wrap">
			<?php if ( $news->have_posts() ) : ?>
				<div class="post-grid">
					<?php while ( $news->have_posts() ) : $news->the_post(); get_template_part( 'template-parts/post-card' ); endwhile; wp_reset_postdata(); ?>
				</div>
				<nav class="pagination" aria-label="<?php esc_attr_e( 'Pagination', 'dynamiqes' ); ?>"><?php echo paginate_links( array( 'total' => $news->max_num_pages, 'current' => $paged, 'prev_text' => '←', 'next_text' => '→' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></nav>
			<?php else : ?>
				<div class="not-found"><p><?php esc_html_e( 'No news yet. Check back soon.', 'dynamiqes' ); ?></p></div>
			<?php endif; ?>
		</div>
	</section>
	<?php dq_cta_band(); ?>
</main>
<?php
get_footer();
