<?php
/**
 * Testimonial media: the video / poster that sits beside each client quote on
 * /client-testimonials/ and /testimonials/<client>/ (text left, media right — the
 * live page's composition).
 *
 * The old theme hard-codes these per client in its templates (Vimeo embeds for some,
 * MP4 files in its own assets folder for others), so nothing is in the database. This
 * file carries the same set as defaults keyed by client slug; an editor can override or
 * add media per post with the "Client video" / "Video poster" fields in the testimonial
 * meta box (works for the live `customer_testimonial` posts and the theme's own).
 *
 * @package dynamiqes
 */

defined( 'ABSPATH' ) || exit;

/**
 * Built-in media per client. Keys are every slug the client is known by (live site and
 * the theme's seeded posts); `poster` is a theme asset, `video` is either a theme asset
 * (served from this theme once the -720p rendition exists, else from the live theme
 * folder so nothing breaks before the files are copied) or an embed page URL.
 */
function dq_testimonial_media_defaults() {
	$live = 'https://dynamiqes.com/wp-content/themes/dynamiqes/assets/images/single-testimonial/';
	return array(
		'tosoh|tosoh-polyvin-corporation'                                   => array( 'poster' => 'assets/testimonials/tosoh-thumbnail.jpg', 'video' => 'assets/testimonials/video/tosoh-720p.mp4', 'fallback' => $live . 'Tosoh Polyvin Corporation.mp4' ),
		'toyo-adtec|toyo-adtec-healthcare-products-inc'                     => array( 'poster' => 'assets/testimonials/toyo-adtec-thumbnail.jpg', 'video' => 'assets/testimonials/video/toyo-adtec-720p.mp4', 'fallback' => $live . 'Toyo-Adtec.mp4' ),
		'metalink|metalink-manufacturing-corp'                              => array( 'poster' => 'assets/testimonials/metalink-thumbnail.jpg', 'video' => 'assets/testimonials/video/metalink-720p.mp4', 'fallback' => $live . 'Metalink Testimony final_1.mp4' ),
		'intelligent-skin-care'                                             => array( 'poster' => 'assets/testimonials/intelligent-skin-care-thumbnail.jpg', 'video' => 'https://player.vimeo.com/video/948560383?h=f04abceda8' ),
		'spartans-3-trading-corporation'                                    => array( 'poster' => 'assets/testimonials/spartans-3-thumbnail.jpg', 'video' => 'https://player.vimeo.com/video/933192818?h=f16f0dbd21' ),
		'ceciles-pharmacy'                                                  => array( 'poster' => 'assets/testimonials/ceciles-pharmacy-thumbnail.jpg', 'video' => 'https://player.vimeo.com/video/912117597?h=ab13c95810' ),
		'macroasia-corp|group-finance-controller-for-macroasia-corporation' => array( 'poster' => 'assets/testimonials/macroasia-thumbnail.jpg', 'video' => 'https://player.vimeo.com/video/922318323?h=d256f55542' ),
		'florabel'                                                          => array( 'poster' => 'assets/testimonials/florabel-thumbnail.jpg', 'video' => 'https://player.vimeo.com/video/1038453332?h=d6a2a14c1a' ),
	);
}

/**
 * Turn a pasted video link into what the lightbox needs.
 *
 * @return array{clip:string,embed:string} `clip` for a plain MP4 (own <video>), `embed`
 *         for a Vimeo / YouTube page or player URL (iframe). Both empty when unknown.
 */
function dq_video_link( $url ) {
	$url = trim( (string) $url );
	if ( '' === $url ) {
		return array( 'clip' => '', 'embed' => '' );
	}
	if ( preg_match( '#\.(mp4|m4v|webm|mov)(\?|$)#i', $url ) ) {
		return array( 'clip' => dq_asset( $url ), 'embed' => '' );
	}
	if ( preg_match( '#vimeo\.com/(?:video/)?(\d+)#i', $url, $m ) ) {
		$embed = 'https://player.vimeo.com/video/' . $m[1] . '?';
		if ( preg_match( '#[?&/]h=([a-f0-9]+)#i', $url, $h ) || preg_match( '#vimeo\.com/\d+/([a-f0-9]{6,})#i', $url, $h ) ) {
			$embed .= 'h=' . $h[1] . '&';
		}
		return array( 'clip' => '', 'embed' => $embed . 'autoplay=1&badge=0&autopause=0&dnt=1' );
	}
	if ( preg_match( '#(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([\w-]{11})#i', $url, $m ) ) {
		return array( 'clip' => '', 'embed' => 'https://www.youtube-nocookie.com/embed/' . $m[1] . '?autoplay=1&rel=0' );
	}
	return array( 'clip' => '', 'embed' => $url );
}

/**
 * Media for one testimonial post: the editor's fields first, then the built-in set.
 *
 * @return array{poster:string,clip:string,embed:string} URLs; all empty when the client has no video.
 */
function dq_testimonial_media( $post ) {
	$post   = get_post( $post );
	$poster = $post ? (string) get_post_meta( $post->ID, '_dq_poster', true ) : '';
	$video  = $post ? (string) get_post_meta( $post->ID, '_dq_video', true ) : '';
	if ( $post && ( '' === $poster || '' === $video ) ) {
		$keys = array_unique( array_filter( array( $post->post_name, sanitize_title( $post->post_title ) ) ) );
		foreach ( dq_testimonial_media_defaults() as $aliases => $d ) {
			if ( array_intersect( $keys, explode( '|', $aliases ) ) ) {
				$poster = '' === $poster ? $d['poster'] : $poster;
				if ( '' === $video ) {
					$video = $d['video'];
					if ( ! empty( $d['fallback'] ) && ! preg_match( '#^https?://#i', $video ) && ! file_exists( DQ_DIR . '/' . $video ) ) {
						$video = $d['fallback'];
					}
				}
				break;
			}
		}
	}
	$link = dq_video_link( $video );
	return array( 'poster' => dq_asset( $poster ), 'clip' => $link['clip'], 'embed' => $link['embed'] );
}

/* The live install keeps testimonials in its own post type: give it the video fields too
 * (the theme's dq_testimonial gets them inside its details box, see post-types.php). */
add_action( 'add_meta_boxes', function () {
	$type = dq_source_post_type( 'testimonial' );
	if ( 'dq_testimonial' !== $type ) {
		add_meta_box( 'dq_testimonial_media', __( 'Client video', 'dynamiqes' ), 'dq_testimonial_media_box', $type, 'normal', 'high' );
	}
} );

/** The two fields, rendered inside whichever box hosts them. */
function dq_testimonial_media_fields( $post ) {
	dq_render_field( '_dq_video', array( 'text', __( 'Client video', 'dynamiqes' ), __( 'MP4 URL (or an assets/… path inside the theme), or a Vimeo / YouTube link. Plays in the site lightbox when the poster is clicked.', 'dynamiqes' ) ), get_post_meta( $post->ID, '_dq_video', true ) );
	dq_render_field( '_dq_poster', array( 'image', __( 'Video poster', 'dynamiqes' ), __( 'The still shown beside the quote (16:9). Empty = the theme\'s built-in poster for this client, if it has one.', 'dynamiqes' ) ), get_post_meta( $post->ID, '_dq_poster', true ) );
}
function dq_testimonial_media_box( $post ) {
	wp_nonce_field( 'dq_testimonial_media_save', 'dq_testimonial_media_nonce' );
	echo '<div class="dq-fields">';
	dq_testimonial_media_fields( $post );
	echo '</div>';
}
add_action( 'save_post', function ( $post_id ) {
	if ( ! isset( $_POST['dq_testimonial_media_nonce'] ) || ! wp_verify_nonce( $_POST['dq_testimonial_media_nonce'], 'dq_testimonial_media_save' ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	dq_testimonial_media_save( $post_id );
} );
/** Store / clear the two fields (also called from the dq_testimonial save handler). */
function dq_testimonial_media_save( $post_id ) {
	foreach ( array( '_dq_video', '_dq_poster' ) as $name ) {
		$val = sanitize_text_field( wp_unslash( $_POST[ $name ] ?? '' ) );
		if ( '' === $val ) {
			delete_post_meta( $post_id, $name );
		} else {
			update_post_meta( $post_id, $name, $val );
		}
	}
}

/** Canonical URL of the page being rendered (permalink for singular pages, else the request). */
function dq_current_url() {
	if ( is_singular() ) {
		return get_permalink();
	}
	return home_url( add_query_arg( array(), $GLOBALS['wp']->request ?? '' ) );
}

/** Ask the footer to print the shared video lightbox on this page. */
function dq_request_lightbox() {
	$GLOBALS['dq_lightbox'] = true;
}

/**
 * One testimonial block: quote, logo and client name on the left; the video poster (or a
 * quote-mark panel when the client has no video) on the right. Used by the Client
 * Testimonials page and the testimonial single.
 *
 * @param array  $t    Row from dq_testimonials( true ).
 * @param string $name 'h5' (listing — the live page marks the client name up as H5) or 'p' (single).
 * @param array  $args { link: bool (show "Read the full story"), reveal: string (attribute string) }
 */
function dq_testimonial_story( $t, $name = 'h5', $args = array() ) {
	$media  = ! empty( $t['id'] ) ? dq_testimonial_media( $t['id'] ) : array( 'poster' => '', 'clip' => '', 'embed' => '' );
	$has    = $media['poster'] && ( $media['clip'] || $media['embed'] );
	$name   = 'p' === $name ? 'p' : 'h5';
	$reveal = isset( $args['reveal'] ) ? $args['reveal'] : '';
	$paras  = preg_split( '/\n{2,}/', trim( (string) $t['quote'] ) );
	$slug   = ! empty( $t['slug'] ) ? $t['slug'] : sanitize_title( $t['name'] );
	/* "Read the full story" only when it leads somewhere else: on installs without single
	 * testimonial pages the link is this page's own #anchor, which would be a dead click. */
	$link   = ! empty( $args['link'] ) && ! empty( $t['link'] ) ? $t['link'] : '';
	if ( $link && untrailingslashit( strtok( $link, '#' ) ) === untrailingslashit( strtok( dq_current_url(), '#' ) ) ) {
		$link = '';
	}
	if ( $has ) {
		dq_request_lightbox();
	}
	?>
	<article class="testi-story<?php echo $has ? ' has-video' : ''; ?>" id="<?php echo esc_attr( $slug ); ?>"<?php echo $reveal; // phpcs:ignore WordPress.Security.EscapeOutput -- attribute string from dq_reveal_attr(). ?>>
		<div class="testi-story-copy">
			<?php if ( $t['logo'] ) : ?><img class="testi-story-logo" src="<?php echo esc_url( $t['logo'] ); ?>" alt="<?php echo esc_attr( $t['name'] ); ?>" loading="lazy"><?php endif; ?>
			<blockquote class="testi-story-quote">
				<?php foreach ( $paras as $para ) : ?><p><?php echo esc_html( trim( $para ) ); ?></p><?php endforeach; ?>
			</blockquote>
			<div class="testi-story-who">
				<<?php echo $name; ?> class="testi-story-name"><?php echo esc_html( $t['name'] ); ?></<?php echo $name; ?>>
				<?php if ( ! empty( $t['role'] ) ) : ?><p class="testi-role"><?php echo esc_html( $t['role'] ); ?></p><?php endif; ?>
			</div>
			<?php if ( $link ) : ?>
			<a class="tlink testi-story-link" href="<?php echo esc_url( $link ); ?>"><?php esc_html_e( 'Read the full story', 'dynamiqes' ); ?> <span class="arr" aria-hidden="true">→</span></a>
			<?php endif; ?>
		</div>
		<div class="testi-story-media">
			<?php if ( $has ) : ?>
			<button type="button" class="testi-play" data-label="<?php echo esc_attr( $t['name'] ); ?>"<?php echo $media['clip'] ? ' data-clip="' . esc_url( $media['clip'] ) . '"' : ' data-embed="' . esc_url( $media['embed'] ) . '"'; ?> aria-label="<?php echo esc_attr( sprintf( /* translators: %s: client name */ __( 'Play the %s story', 'dynamiqes' ), $t['name'] ) ); ?>">
				<img src="<?php echo esc_url( $media['poster'] ); ?>" alt="" width="1280" height="720" loading="lazy">
				<span class="testi-play-badge" aria-hidden="true"><svg viewBox="0 0 24 24"><path d="M8 5.5v13l11-6.5z"/></svg></span>
				<span class="testi-play-cap" aria-hidden="true"><?php esc_html_e( 'Watch the story', 'dynamiqes' ); ?></span>
			</button>
			<?php else : ?>
			<div class="testi-story-mark" aria-hidden="true">
				<span class="testi-story-glyph">&rdquo;</span>
				<span class="testi-story-tag"><?php esc_html_e( 'Client story', 'dynamiqes' ); ?></span>
			</div>
			<?php endif; ?>
		</div>
	</article>
	<?php
}
