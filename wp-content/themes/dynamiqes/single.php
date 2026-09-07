<?php
/**
 * Single post — the ONE template every blog post and news item follows.
 *
 * Structure mirrors the live dynamiqes.com post so nothing SEO-relevant moves:
 *   banner = featured image behind H1 + date · the article body as written · then, as on the
 *   live post, the H2 "Have a Question or Need a Demo?" band and H4 "You May Also Like"; a news
 *   item instead has H4 "Share Article:" after the body and H2 "Other News and Events". Card
 *   titles in those lists are plain links, like the live ones.
 * Everything else is the homepage design system: ink scrim over the photo, eyebrow
 * meta row, 76ch reading column, sticky "On this page" rail built from the H2s,
 * plain share links (no third-party scripts), orange hairline cards for related posts.
 *
 * Blog vs news is decided by the post type when the site has a news post type (the live
 * site's news-events items at /news-event/<slug>/), otherwise by the post's category
 * (Customize → Content sources): news crumbs back to News & Events, everything else to Blogs.
 *
 * @package dynamiqes
 */

get_header();
the_post();

$img      = dq_post_thumb_url( get_the_ID(), 'dq-wide' );
$cats     = array_values( array_filter( get_the_category(), function ( $c ) { return 'uncategorized' !== $c->slug; } ) );
$cat      = $cats ? $cats[0] : null;
$news_ids = array_map( 'intval', (array) dq_news_category_ids() );
$news_pt  = dq_source_post_type( 'news' );
$is_news  = 'post' !== $news_pt && get_post_type() === $news_pt;
foreach ( $cats as $c ) {
	if ( in_array( (int) $c->term_id, $news_ids, true ) ) {
		$is_news = true;
		break;
	}
}
$hub_url   = $is_news ? dq_news_url() : dq_blog_url();
$hub_label = $is_news ? __( 'News & Events', 'dynamiqes' ) : __( 'Blogs', 'dynamiqes' );
$words     = str_word_count( wp_strip_all_tags( strip_shortcodes( get_the_content() ) ) );
$minutes   = max( 1, (int) round( $words / 220 ) );
$permalink = get_permalink();
$share     = array(
	array( 'LinkedIn', 'https://www.linkedin.com/sharing/share-offsite/?url=' . rawurlencode( $permalink ), 'M6.94 8.5H3.56V20h3.38V8.5zM5.25 3.5a1.96 1.96 0 1 0 0 3.92 1.96 1.96 0 0 0 0-3.92zM20.44 20h-3.37v-5.6c0-1.34-.03-3.06-1.87-3.06-1.87 0-2.15 1.46-2.15 2.96V20H9.68V8.5h3.24v1.57h.05c.45-.85 1.55-1.75 3.2-1.75 3.42 0 4.05 2.25 4.05 5.18V20z' ),
	array( 'Facebook', 'https://www.facebook.com/sharer/sharer.php?u=' . rawurlencode( $permalink ), 'M13.5 21v-7.5h2.5l.4-3h-2.9V8.6c0-.9.25-1.5 1.5-1.5h1.6V4.4A21 21 0 0 0 14.3 4.3c-2.3 0-3.9 1.4-3.9 4v2.2H7.8v3h2.6V21h3.1z' ),
	array( 'X', 'https://twitter.com/intent/tweet?url=' . rawurlencode( $permalink ) . '&text=' . rawurlencode( get_the_title() ), 'M17.5 3h3l-6.6 7.6L21.6 21h-6.1l-4.8-6.2L5.2 21h-3l7.1-8.1L2 3h6.2l4.3 5.7L17.5 3zm-1.1 16.2h1.7L7.4 4.7H5.6l10.8 14.5z' ),
	array( 'Email', 'mailto:?subject=' . rawurlencode( get_the_title() ) . '&body=' . rawurlencode( $permalink ), 'M4 4h16a2 2 0 0 1 2 2v12a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2zm0 4 8 5 8-5V6l-8 5-8-5v2z' ),
);

/* Related: same category first, newest fill; never the current post. */
$related_pt   = $is_news ? $news_pt : 'post';
$related_args = array( 'post_type' => $related_pt, 'post_status' => 'publish', 'posts_per_page' => 3, 'post__not_in' => array( get_the_ID() ), 'ignore_sticky_posts' => true, 'no_found_rows' => true );
if ( $cat ) {
	$related_args['cat'] = $cat->term_id;
}
$related = new WP_Query( $related_args );
if ( $related->post_count < 3 ) {
	$seen = wp_list_pluck( $related->posts, 'ID' );
	$fill = new WP_Query( array( 'post_type' => $related_pt, 'post_status' => 'publish', 'posts_per_page' => 3 - $related->post_count, 'post__not_in' => array_merge( array( get_the_ID() ), $seen ), 'ignore_sticky_posts' => true, 'no_found_rows' => true ) );
	$related->posts      = array_merge( $related->posts, $fill->posts );
	$related->post_count = count( $related->posts );
}
?>
<main id="main">
	<article <?php post_class( 'blog-post' ); ?>>

		<header class="post-hero<?php echo $img ? '' : ' no-img'; ?>">
			<?php if ( $img ) : ?><div class="post-hero-media" aria-hidden="true"><img src="<?php echo esc_url( $img ); ?>" alt="" fetchpriority="high" decoding="async"></div><?php endif; ?>
			<div class="wrap">
				<nav class="breadcrumbs" aria-label="<?php esc_attr_e( 'Breadcrumb', 'dynamiqes' ); ?>">
					<a href="<?php echo esc_url( $hub_url ); ?>"><?php echo esc_html( $hub_label ); ?></a>
					<?php if ( $cat ) : ?> / <a href="<?php echo esc_url( get_category_link( $cat ) ); ?>"><?php echo esc_html( $cat->name ); ?></a><?php endif; ?>
				</nav>
				<div class="post-hero-body">
					<div class="news-meta"<?php dq_reveal( 'fade' ); ?>>
						<?php if ( $cat ) : ?><span class="news-tag"><?php echo esc_html( $cat->name ); ?></span><?php endif; ?>
						<time class="news-date" datetime="<?php echo esc_attr( get_the_date( 'c' ) ); ?>"><?php echo esc_html( get_the_date( 'F j, Y' ) ); ?></time>
						<span class="news-date post-read"><?php echo esc_html( sprintf( /* translators: %d: minutes */ _n( '%d min read', '%d min read', $minutes, 'dynamiqes' ), $minutes ) ); ?></span>
					</div>
					<h1<?php dq_reveal( 'fade', 80 ); ?>><?php the_title(); ?></h1>
					<?php if ( has_excerpt() ) : ?><p class="lede"<?php dq_reveal( 'fade', 160 ); ?>><?php echo esc_html( get_the_excerpt() ); ?></p><?php endif; ?>
				</div>
			</div>
		</header>

		<div class="post-body">
			<div class="wrap post-layout">
				<div class="post-main">
					<div class="entry-content" id="entryContent"><?php the_content(); ?></div>
					<footer class="entry-foot">
						<a class="tlink" href="<?php echo esc_url( $hub_url ); ?>">← <?php echo esc_html( sprintf( /* translators: %s: hub label */ __( 'All %s', 'dynamiqes' ), $hub_label ) ); ?></a>
						<?php the_tags( '<div class="post-tags">', '', '</div>' ); ?>
					</footer>
				</div>
				<aside class="post-rail" aria-label="<?php esc_attr_e( 'Article tools', 'dynamiqes' ); ?>">
					<details class="post-toc" id="postToc" hidden>
						<summary><?php esc_html_e( 'On this page', 'dynamiqes' ); ?></summary>
						<ol></ol>
					</details>
					<div class="post-share">
						<?php if ( $is_news ) : /* live news item: H4 "Share Article:" */ ?><h4 class="post-share-label"><?php esc_html_e( 'Share Article:', 'dynamiqes' ); ?></h4><?php else : ?><p class="post-share-label"><?php esc_html_e( 'Share Article:', 'dynamiqes' ); ?></p><?php /* live blog post: <p class="share-article">Share Article:</p> */ ?><?php endif; ?>
						<?php foreach ( $share as $s ) : ?>
							<a href="<?php echo esc_url( $s[1] ); ?>"<?php echo 0 === strpos( $s[1], 'mailto:' ) ? '' : ' target="_blank" rel="noopener"'; ?> aria-label="<?php echo esc_attr( sprintf( /* translators: %s: network */ __( 'Share on %s', 'dynamiqes' ), $s[0] ) ); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="<?php echo esc_attr( $s[2] ); ?>"/></svg></a>
						<?php endforeach; ?>
						<button type="button" class="post-copy" data-url="<?php echo esc_url( $permalink ); ?>" aria-label="<?php esc_attr_e( 'Copy link', 'dynamiqes' ); ?>"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M10.6 13.4a1 1 0 0 1 0-1.4l2.8-2.8a1 1 0 0 1 1.4 1.4L12 13.4a1 1 0 0 1-1.4 0zm-2.1 4.9a3 3 0 0 1 0-4.2l2.1-2.1-1.4-1.4L7.1 12.7a5 5 0 1 0 7.1 7.1l2.1-2.1-1.4-1.4-2.1 2.1a3 3 0 0 1-4.3-.1zm7-16.4-2.1 2.1 1.4 1.4 2.1-2.1a3 3 0 1 1 4.2 4.2l-2.1 2.1 1.4 1.4 2.1-2.1a5 5 0 0 0-7-7z"/></svg><span class="post-copy-done" aria-live="polite"></span></button>
					</div>
				</aside>
			</div>
		</div>
	</article>

	<?php if ( ! $is_news ) { /* live post order: the "Have a Question or Need a Demo?" H2 band, then "You May Also Like" */ dq_cta_band( array( 'heading' => __( 'Have a Question or Need a Demo?', 'dynamiqes' ) ) ); } ?>

	<?php if ( $related->have_posts() ) : ?>
	<section class="post-related" aria-labelledby="relatedHeading">
		<div class="wrap">
			<div class="sec-head"<?php dq_reveal(); ?>>
				<span class="eyebrow"><?php echo esc_html( $hub_label ); ?></span>
				<?php $rel_tag = $is_news ? 'h2' : 'h4'; /* live: H2 "Other News and Events" on news, H4 "You May Also Like" on posts */ ?>
				<<?php echo $rel_tag; // phpcs:ignore WordPress.Security.EscapeOutput ?> id="relatedHeading"><?php echo $is_news ? esc_html__( 'Other News and Events', 'dynamiqes' ) : esc_html__( 'You May Also Like', 'dynamiqes' ); ?></<?php echo $rel_tag; // phpcs:ignore WordPress.Security.EscapeOutput ?>>
			</div>
			<div class="post-grid">
				<?php foreach ( $related->posts as $post ) : setup_postdata( $post ); get_template_part( 'template-parts/post-card', null, array( 'heading' => 'p' ) ); endforeach; wp_reset_postdata(); ?>
			</div>
		</div>
	</section>
	<?php endif; ?>

	<?php if ( $is_news ) { dq_cta_band(); } ?>
</main>
<script>
/* "On this page" rail: built from the article's H2s (ids added when missing) so the
   content itself is never edited; hidden when a post has fewer than two sections. */
(function () {
	var toc = document.getElementById('postToc'), body = document.getElementById('entryContent');
	if (!toc || !body) { return; }
	var hs = [].slice.call(body.querySelectorAll('h2')), list = toc.querySelector('ol'), used = {};
	if (hs.length < 2) { return; }
	hs.forEach(function (h) {
		if (!h.id) { var s = (h.textContent || '').toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '') || 'section'; while (used[s] || document.getElementById(s)) { s += '-'; } h.id = s; }
		used[h.id] = 1;
		var li = document.createElement('li'), a = document.createElement('a');
		a.href = '#' + h.id; a.textContent = h.textContent; li.appendChild(a); list.appendChild(li);
	});
	toc.hidden = false;
	var wide = window.matchMedia('(min-width:1024px)');
	var sync = function () { toc.open = wide.matches; };
	sync(); wide.addEventListener ? wide.addEventListener('change', sync) : wide.addListener(sync);
	if ('IntersectionObserver' in window) {
		var links = list.querySelectorAll('a');
		new IntersectionObserver(function (es) {
			es.forEach(function (e) { if (e.isIntersecting) { links.forEach(function (l) { l.classList.toggle('is-active', l.hash === '#' + e.target.id); }); } });
		}, { rootMargin: '-25% 0px -60% 0px' }).observe && hs.forEach(function (h) { new IntersectionObserver(function (es) { es.forEach(function (e) { if (e.isIntersecting) { links.forEach(function (l) { l.classList.toggle('is-active', l.hash === '#' + e.target.id); }); } }); }, { rootMargin: '-25% 0px -60% 0px' }).observe(h); });
	}
	var copy = document.querySelector('.post-copy');
	if (copy) {
		copy.addEventListener('click', function () {
			var done = copy.querySelector('.post-copy-done'), url = copy.getAttribute('data-url');
			var ok = function () { copy.classList.add('is-done'); done.textContent = 'Link copied'; setTimeout(function () { copy.classList.remove('is-done'); done.textContent = ''; }, 1800); };
			if (navigator.clipboard) { navigator.clipboard.writeText(url).then(ok); } else { window.prompt('Copy this link', url); }
		});
	}
})();
</script>
<?php
get_footer();
