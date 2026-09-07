<?php
/**
 * "News & Events" page (slug: news-events, as on dynamiqes.com/news-events/).
 *
 * Structure mirrors the live page (SEO): H1 "Latest Updates & Events: Stay Connected with DynamIQ"
 * + intro, H2 "Featured News and Events" with the newest story (H3), then H2 "News and Events" with
 * the remaining stories as plain-link cards and pagination. Items come from the site's news post
 * type when it has one (the live site's news-events, /news-event/<slug>/); otherwise from the news
 * categories (Customizer → DynamIQ Theme → Content sources), leaving the rest to Blogs.
 *
 * The page title stays "News and Events" for <title> / menus; the article H1 lives in
 * _dq_news_h1 (default = live copy). Content typed into the page in WP Admin replaces the intro.
 *
 * @package dynamiqes
 */

get_header();
the_post();

$headline = get_post_meta( get_the_ID(), '_dq_news_h1', true );
$headline = $headline ? $headline : __( 'Latest Updates & Events: Stay Connected with DynamIQ', 'dynamiqes' );
$intro    = trim( get_the_content() );
$intro    = '' === $intro ? '<p>' . esc_html__( 'Discover what’s new at DynamIQ. Our News & Events section brings you the latest updates of our digital solutions.', 'dynamiqes' ) . '</p>' : apply_filters( 'the_content', $intro );

$paged   = max( 1, (int) get_query_var( 'paged' ), (int) get_query_var( 'page' ) );
$news_pt = dq_source_post_type( 'news' );
$base    = array( 'post_type' => $news_pt, 'post_status' => 'publish', 'ignore_sticky_posts' => true, 'no_found_rows' => true );
if ( 'post' === $news_pt ) {
	$cats = dq_news_category_ids();
	if ( $cats ) {
		$base['category__in'] = $cats;
	}
}
/* Newest story is the featured one on every page (as on the live page); the grid lists the rest. */
$featured = new WP_Query( $base + array( 'posts_per_page' => 1 ) );
$exclude  = $featured->have_posts() ? array( $featured->posts[0]->ID ) : array();
$news     = new WP_Query( array_merge( $base, array( 'posts_per_page' => 9, 'paged' => $paged, 'post__not_in' => $exclude, 'no_found_rows' => false ) ) );
?>
<main id="main">
	<article <?php post_class( 'news-page' ); ?>>
		<header class="page-hero page-hero--photo" style="--hero-photo:url('<?php echo esc_url( dq_page_hero_photo( 'news-events-banner.jpg' ) ); ?>')"><?php /* the live page's banner photo (Featured Image overrides it) */ ?>
			<div class="wrap">
				<span class="eyebrow"<?php dq_reveal( 'fade' ); ?>><?php the_title(); ?></span>
				<h1<?php dq_reveal(); ?>><?php echo esc_html( $headline ); ?></h1>
				<div class="page-hero-intro"<?php dq_reveal( '', 80 ); ?>><?php echo wp_kses_post( $intro ); ?></div>
				<div class="hero-actions"<?php dq_reveal( '', 160 ); ?>>
					<a class="btn btn-primary" href="<?php echo esc_url( dq_book_demo_url() ); ?>"><?php esc_html_e( 'INQUIRE NOW!', 'dynamiqes' ); ?> <span aria-hidden="true">→</span></a>
				</div>
			</div>
		</header>

		<?php if ( $featured->have_posts() ) : ?>
		<section class="news-featured">
			<div class="wrap">
				<div class="sec-head"<?php dq_reveal(); ?>>
					<span class="eyebrow"><?php esc_html_e( 'Latest', 'dynamiqes' ); ?></span>
					<h2><?php esc_html_e( 'Featured News and Events', 'dynamiqes' ); ?></h2>
				</div>
				<?php $featured->the_post(); get_template_part( 'template-parts/post-feature', null, array( 'heading' => 'h3' ) ); wp_reset_postdata(); ?>
			</div>
		</section>
		<?php endif; ?>

		<section class="news-list">
			<div class="wrap">
				<div class="sec-head"<?php dq_reveal(); ?>>
					<span class="eyebrow"><?php esc_html_e( 'All stories', 'dynamiqes' ); ?></span>
					<h2><?php esc_html_e( 'News and Events', 'dynamiqes' ); ?></h2>
				</div>
				<?php if ( $news->have_posts() ) : ?>
					<div class="post-grid">
						<?php while ( $news->have_posts() ) : $news->the_post(); get_template_part( 'template-parts/post-card', null, array( 'heading' => 'p' ) ); endwhile; wp_reset_postdata(); ?>
					</div>
					<nav class="pagination" aria-label="<?php esc_attr_e( 'Pagination', 'dynamiqes' ); ?>"><?php echo paginate_links( array( 'total' => $news->max_num_pages, 'current' => $paged, 'prev_text' => '←', 'next_text' => '→' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?></nav>
				<?php else : ?>
					<div class="not-found"><p><?php esc_html_e( 'No news yet. Check back soon.', 'dynamiqes' ); ?></p></div>
				<?php endif; ?>
			</div>
		</section>
	</article>
	<?php dq_cta_band(); ?>
</main>
<?php
get_footer();
