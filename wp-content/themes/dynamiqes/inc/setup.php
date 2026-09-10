<?php
/**
 * Theme setup: supports, menus, assets, body classes, head clean-up.
 *
 * @package dynamiqes
 */

defined( 'ABSPATH' ) || exit;

add_action( 'after_setup_theme', function () {
	load_theme_textdomain( 'dynamiqes', DQ_DIR . '/languages' );
	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'html5', array( 'search-form', 'gallery', 'caption', 'style', 'script', 'navigation-widgets' ) );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'align-wide' );
	add_theme_support( 'custom-logo', array( 'height' => 70, 'width' => 320, 'flex-height' => true, 'flex-width' => true ) );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'editor-styles' );
	add_editor_style( 'assets/css/editor.css' );

	register_nav_menus( array(
		'primary'        => __( 'Primary Menu', 'dynamiqes' ),
		'footer-quick'   => __( 'Footer: Quick Links', 'dynamiqes' ),
		'footer-explore' => __( 'Footer: Explore', 'dynamiqes' ),
	) );

	add_image_size( 'dq-card', 800, 500, true );
	add_image_size( 'dq-wide', 1600, 900, true );
	set_post_thumbnail_size( 1200, 800, false );
} );

/**
 * Front-end assets. Lenis is bundled locally (no CDN dependency); Roboto from Google Fonts with preconnect.
 */
add_action( 'wp_enqueue_scripts', function () {
	if ( get_theme_mod( 'dq_google_fonts', true ) ) {
		wp_enqueue_style( 'dq-fonts', 'https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap', array(), null );
	}
	wp_enqueue_style( 'dq-lenis', DQ_URI . '/assets/vendor/lenis.css', array(), '1.3.25' );
	wp_enqueue_style( 'dq-main', DQ_URI . '/assets/css/main.css', array( 'dq-lenis' ), DQ_VERSION );

	wp_enqueue_script( 'dq-lenis', DQ_URI . '/assets/vendor/lenis.min.js', array(), '1.3.25', true );
	wp_enqueue_script( 'dq-main', DQ_URI . '/assets/js/main.js', array( 'dq-lenis' ), DQ_VERSION, true );
	wp_localize_script( 'dq-main', 'DQ', array(
		'ajaxUrl' => admin_url( 'admin-ajax.php' ),
		'i18n'    => array(
			'invalid' => __( 'Please complete the highlighted fields.', 'dynamiqes' ),
			'sending' => __( 'Sending…', 'dynamiqes' ),
		),
	) );

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
} );

/** Resource hints for the font host. */
add_filter( 'wp_resource_hints', function ( $urls, $relation ) {
	if ( 'preconnect' === $relation && get_theme_mod( 'dq_google_fonts', true ) ) {
		$urls[] = array( 'href' => 'https://fonts.googleapis.com', 'crossorigin' => false );
		$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' => 'anonymous' );
	}
	return $urls;
}, 10, 2 );

/** Body classes drive the page-specific compositions in main.css. */
add_filter( 'body_class', function ( $classes ) {
	if ( is_front_page() ) {
		$classes[] = 'home-page';
	} elseif ( is_404() ) {
		$classes[] = 'sub-page';
		$classes[] = 'error404-page'; // fully centred composition (review items G7/G8)
	} elseif ( is_post_type_archive( 'dq_product' ) ) {
		$classes[] = 'sub-page';
		$classes[] = 'products-page';
	} elseif ( is_singular( 'dq_product' ) ) {
		$key       = get_post_meta( get_the_ID(), '_dq_product_key', true );
		$key       = $key ? $key : 'portal';
		$classes[] = 'sub-page';
		$classes[] = 'product-detail-page';
		$classes[] = 'product-template-page';
		$classes[] = 'product-' . sanitize_html_class( $key );
		if ( 'portal' === $key ) {
			$classes[] = 'iq-portal-page';
		}
	} else {
		$classes[] = 'sub-page';
		$classes[] = 'generic-page';
		if ( is_page( 'our-services' ) ) {
			$classes[] = 'services-page'; // one-screen composition for the service bands, CTA and contact
		}
	}
	return array_values( array_unique( $classes ) );
} );

/** Head clean-up (leaner markup, fewer leaks; good for SEO and performance). */
remove_action( 'wp_head', 'wp_generator' );
remove_action( 'wp_head', 'wlwmanifest_link' );
remove_action( 'wp_head', 'rsd_link' );
remove_action( 'wp_head', 'wp_shortlink_wp_head' );
remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
remove_action( 'wp_print_styles', 'print_emoji_styles' );
add_filter( 'emoji_svg_url', '__return_false' );
add_filter( 'the_generator', '__return_empty_string' );

/** Excerpts. */
add_filter( 'excerpt_length', function () { return 28; }, 999 );
add_filter( 'excerpt_more', function () { return '…'; } );

/** Images: async decoding and a sensible alt fallback (image SEO). */
add_filter( 'wp_get_attachment_image_attributes', function ( $attr, $attachment ) {
	if ( empty( $attr['alt'] ) && $attachment instanceof WP_Post ) {
		$attr['alt'] = $attachment->post_title;
	}
	$attr['decoding'] = 'async';
	return $attr;
}, 10, 2 );

/** Admin assets for the meta boxes. */
add_action( 'admin_enqueue_scripts', function ( $hook ) {
	if ( in_array( $hook, array( 'post.php', 'post-new.php' ), true ) ) {
		wp_enqueue_media();
		wp_enqueue_script( 'dq-admin', DQ_URI . '/assets/js/admin.js', array( 'jquery' ), DQ_VERSION, true );
		wp_add_inline_style( 'wp-admin', '.dq-fields{display:grid;gap:14px}.dq-fields label{font-weight:600;display:block;margin-bottom:4px}.dq-fields input[type=text],.dq-fields textarea{width:100%}.dq-fields textarea{min-height:90px;font-family:Menlo,Consolas,monospace;font-size:12px}.dq-fields .desc{color:#646970;font-size:12px;margin-top:3px}.dq-image-field{display:flex;gap:10px;align-items:flex-start}.dq-image-field input{flex:1}.dq-image-preview{max-width:120px;max-height:60px;object-fit:contain;border:1px solid #ddd;background:#fff;padding:2px}' );
	}
} );

/** Allow SVG and WebP uploads for logos (administrators only). */
add_filter( 'upload_mimes', function ( $mimes ) {
	if ( current_user_can( 'manage_options' ) ) {
		$mimes['svg']  = 'image/svg+xml';
		$mimes['webp'] = 'image/webp';
	}
	return $mimes;
} );

/** Blogs index = blog posts only. News posts (the categories chosen under Customize → Content
 *  sources) have their own News & Events page, as on the old site where news is a separate
 *  post type; without this the two listings showed the same items. */
add_action( 'pre_get_posts', function ( $q ) {
	if ( is_admin() || ! $q->is_main_query() || ! $q->is_home() || ! function_exists( 'dq_news_category_ids' ) ) {
		return;
	}
	$ids = array_map( 'intval', (array) dq_news_category_ids() );
	if ( $ids ) {
		$q->set( 'category__not_in', array_merge( (array) $q->get( 'category__not_in' ), $ids ) );
	}
} );

/** Search results: 9 per page so the 3-column grid always fills its rows and never
 *  leaves a row holding a single card (SEO Hacker review, item A6). */
add_action( 'pre_get_posts', function ( $q ) {
	if ( is_admin() || ! $q->is_main_query() || ! $q->is_search() ) {
		return;
	}
	$q->set( 'posts_per_page', 9 );
} );

/**
 * Balance the tags in article bodies before they are rendered.
 *
 * The live site's articles carry stray closing tags — the imported "Cloud-Based vs On-Premises
 * ERP" post has two orphan </div> and one </section>, and three of the news items have two each.
 * Each stray closer unwound one more of the template's wrappers (.entry-content, then .post-main,
 * .post-layout and .post-body), so the sticky article rail escaped its grid column and rendered
 * full width and the article footer lost the page gutter — the broken blog controls.
 *
 * force_balance_tags() is WordPress's own repair for this and is what the `use_balanceTags`
 * option applies; running it here fixes every post, including any imported later, without
 * rewriting what is stored. Priority 5 puts it ahead of wpautop.
 */
add_filter( 'the_content', 'dq_balance_content_tags', 5 );
add_filter( 'the_excerpt', 'dq_balance_content_tags', 5 );

/**
 * @param string $content Post content.
 * @return string
 */
function dq_balance_content_tags( $content ) {
	return is_string( $content ) && '' !== $content ? force_balance_tags( $content ) : $content;
}
