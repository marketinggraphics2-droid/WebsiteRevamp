<?php
/**
 * Featured post — the newest story at the top of the Blogs index (page 1) and of News & Events,
 * mirroring the live pages: title, excerpt and READ MORE beside the photo.
 *
 * Title tag follows the old site: H2 on /blogs/ (the only heading besides the H1), H3 on
 * /news-events/ (under its "Featured News and Events" H2). Pass 'heading' => 'h3' for the latter.
 *
 * @package dynamiqes
 */

$args = isset( $args ) && is_array( $args ) ? $args : array();
$tag  = ! empty( $args['heading'] ) && in_array( $args['heading'], array( 'h2', 'h3', 'p' ), true ) ? $args['heading'] : 'h2';
$img  = dq_post_thumb_url( get_the_ID(), 'dq-wide' );
$cats = array_values( array_filter( get_the_category(), function ( $c ) { return 'uncategorized' !== $c->slug; } ) );
?>
<article class="post-feature"<?php dq_reveal( 'scale' ); ?>>
	<div class="post-feature-body">
		<div class="news-meta">
			<?php if ( $cats ) : ?><span class="news-tag"><?php echo esc_html( $cats[0]->name ); ?></span><?php endif; ?>
			<span class="news-date"><?php echo esc_html( get_the_date( 'F j, Y' ) ); ?></span>
		</div>
		<<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput ?> class="feature-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></<?php echo $tag; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
		<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 40 ) ); ?></p>
		<a class="btn btn-orange" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read more', 'dynamiqes' ); ?> <span class="arr" aria-hidden="true">→</span></a>
	</div>
	<a class="post-feature-media<?php echo $img ? '' : ' no-img'; ?>" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
		<?php if ( $img ) : ?><img src="<?php echo esc_url( $img ); ?>" alt="" fetchpriority="high"><?php endif; ?>
	</a>
</article>
