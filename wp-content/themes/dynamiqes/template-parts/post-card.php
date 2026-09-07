<?php
/**
 * Post card used on the Blogs index, News & Events, archives, related posts and search results.
 *
 * Title tag: on the live dynamiqes.com listings the card titles are plain links (only the
 * featured story carries a heading), so sub-pages pass 'heading' => 'p' to keep that outline.
 * The home page keeps its H2 cards (default).
 *
 * @package dynamiqes
 */

$args  = isset( $args ) && is_array( $args ) ? $args : array();
$tag   = ! empty( $args['heading'] ) && in_array( $args['heading'], array( 'h2', 'h3', 'h4', 'p' ), true ) ? $args['heading'] : 'h2';
$img   = dq_post_thumb_url( get_the_ID(), 'dq-card' );
$cats  = array_values( array_filter( get_the_category(), function ( $c ) { return 'uncategorized' !== $c->slug; } ) );
?>
<article class="post-card"<?php dq_reveal(); ?>>
	<a class="post-card-media" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
		<?php if ( $img ) : ?><img src="<?php echo esc_url( $img ); ?>" alt="" loading="lazy"><?php endif; ?>
	</a>
	<div class="post-card-body">
		<div class="news-meta">
			<?php if ( $cats ) : ?><span class="news-tag"><?php echo esc_html( $cats[0]->name ); ?></span><?php endif; ?>
			<span class="news-date"><?php echo esc_html( get_the_date( 'F j, Y' ) ); ?></span>
		</div>
		<<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput ?> class="card-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
		<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
		<a class="tlink" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read more', 'dynamiqes' ); ?> <span class="arr" aria-hidden="true">→</span></a>
	</div>
</article>
