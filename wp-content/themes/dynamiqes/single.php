<?php
/**
 * Single news post.
 *
 * @package dynamiqes
 */

get_header();
the_post();
$img  = dq_post_thumb_url( get_the_ID(), 'dq-wide' );
$cats = get_the_category();
?>
<main id="main">
	<article <?php post_class(); ?>>
		<header class="entry-hero">
			<div class="wrap">
				<nav class="breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'dynamiqes' ); ?>"><a href="<?php echo esc_url( dq_news_url() ); ?>"><?php esc_html_e( 'News & Events', 'dynamiqes' ); ?></a> / <?php echo esc_html( $cats ? $cats[0]->name : __( 'Post', 'dynamiqes' ) ); ?></nav>
				<div class="news-meta" style="margin-top:22px">
					<?php if ( $cats ) : ?><span class="news-tag"><?php echo esc_html( $cats[0]->name ); ?></span><?php endif; ?>
					<time class="news-date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'M j, Y' ) ); ?></time>
				</div>
				<h1<?php dq_reveal(); ?>><?php the_title(); ?></h1>
				<?php if ( $img ) : ?><div class="entry-media"<?php dq_reveal( 'scale' ); ?>><img src="<?php echo esc_url( $img ); ?>" alt="<?php the_title_attribute(); ?>" fetchpriority="high"></div><?php endif; ?>
			</div>
		</header>
		<div class="entry">
			<div class="wrap">
				<div class="entry-content"><?php the_content(); ?></div>
				<div class="entry-foot">
					<a class="tlink" href="<?php echo esc_url( dq_news_url() ); ?>">← <?php esc_html_e( 'All News & Events', 'dynamiqes' ); ?></a>
					<?php the_tags( '<div class="news-meta">', ' ', '</div>' ); ?>
				</div>
			</div>
		</div>
	</article>
	<?php dq_cta_band(); ?>
</main>
<?php
get_footer();
