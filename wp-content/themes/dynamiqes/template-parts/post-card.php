<?php
/**
 * Post card used on the News & Events index, archives and search results.
 *
 * @package dynamiqes
 */

$img  = dq_post_thumb_url( get_the_ID(), 'dq-card' );
$cats = get_the_category();
?>
<article class="post-card"<?php dq_reveal(); ?>>
	<a class="post-card-media" href="<?php the_permalink(); ?>" aria-hidden="true" tabindex="-1">
		<?php if ( $img ) : ?><img src="<?php echo esc_url( $img ); ?>" alt="" loading="lazy"><?php endif; ?>
	</a>
	<div class="post-card-body">
		<div class="news-meta">
			<?php if ( $cats ) : ?><span class="news-tag"><?php echo esc_html( $cats[0]->name ); ?></span><?php endif; ?>
			<span class="news-date"><?php echo esc_html( get_the_date( 'M j, Y' ) ); ?></span>
		</div>
		<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
		<p><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
		<a class="tlink" href="<?php the_permalink(); ?>"><?php esc_html_e( 'Read more', 'dynamiqes' ); ?> <span class="arr" aria-hidden="true">→</span></a>
	</div>
</article>
