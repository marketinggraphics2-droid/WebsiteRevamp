<?php
/**
 * Built-in SEO: titles, meta description, canonical, Open Graph / Twitter cards,
 * JSON-LD (Organization, WebSite, BreadcrumbList, SoftwareApplication + FAQPage,
 * Article), robots, sitemap tuning and a per-post SEO meta box.
 *
 * Steps aside automatically when Yoast, Rank Math, AIOSEO or SEOPress is active
 * (only the product FAQ/software schema is kept, since plugins cannot see it).
 *
 * @package dynamiqes
 */

defined( 'ABSPATH' ) || exit;

/** Is a dedicated SEO plugin handling the head? */
function dq_seo_plugin_active() {
	return defined( 'WPSEO_VERSION' ) || class_exists( 'RankMath' ) || defined( 'AIOSEO_VERSION' ) || defined( 'SEOPRESS_VERSION' );
}

/* ------------------------------------------------------------------ */
/* Title                                                               */
/* ------------------------------------------------------------------ */
add_filter( 'document_title_separator', function () { return '—'; } );
add_filter( 'document_title_parts', function ( $parts ) {
	if ( dq_seo_plugin_active() ) {
		return $parts;
	}
	if ( is_front_page() ) {
		$t = get_theme_mod( 'dq_seo_home_title', 'DynamIQ — SAP Premier Partner Philippines' );
		return array( 'title' => $t );
	}
	if ( is_singular() ) {
		$custom = get_post_meta( get_queried_object_id(), '_dq_seo_title', true );
		if ( $custom ) {
			return array( 'title' => $custom );
		}
	}
	if ( is_post_type_archive( 'dq_product' ) ) {
		$parts['title'] = __( 'Our Products', 'dynamiqes' );
	}
	return $parts;
} );

/* ------------------------------------------------------------------ */
/* Description / image resolution                                      */
/* ------------------------------------------------------------------ */
function dq_seo_trim( $text, $len = 158 ) {
	$text = trim( preg_replace( '/\s+/', ' ', wp_strip_all_tags( strip_shortcodes( (string) $text ) ) ) );
	if ( mb_strlen( $text ) > $len ) {
		$text = mb_substr( $text, 0, $len - 1 );
		$text = preg_replace( '/\s+\S*$/', '', $text ) . '…';
	}
	return $text;
}

function dq_meta_description() {
	if ( is_front_page() ) {
		return get_theme_mod( 'dq_seo_home_description', 'DynamIQ is a Premier SAP implementation partner delivering SAP Business One and the IQ Suite for Philippine small and mid-market businesses.' );
	}
	if ( is_post_type_archive( 'dq_product' ) ) {
		return __( 'Explore SAP Business One and the DynamIQ IQ Suite of ERP modules and business solutions.', 'dynamiqes' );
	}
	if ( is_singular() ) {
		$id     = get_queried_object_id();
		$custom = get_post_meta( $id, '_dq_seo_description', true );
		if ( $custom ) {
			return dq_seo_trim( $custom );
		}
		if ( is_singular( 'dq_product' ) ) {
			$p = dq_get_product( $id );
			if ( $p && $p['description'] ) {
				return dq_seo_trim( $p['description'] );
			}
		}
		if ( has_excerpt( $id ) ) {
			return dq_seo_trim( get_the_excerpt( $id ) );
		}
		$post = get_post( $id );
		if ( $post && $post->post_content ) {
			return dq_seo_trim( $post->post_content );
		}
	}
	if ( is_home() ) {
		return __( 'Discover the latest updates, events, and stories from DynamIQ Enterprise Solution Inc.', 'dynamiqes' );
	}
	if ( is_category() || is_tag() || is_tax() ) {
		$d = term_description();
		if ( $d ) {
			return dq_seo_trim( $d );
		}
	}
	return get_bloginfo( 'description' );
}

function dq_og_image() {
	if ( is_singular() ) {
		$id  = get_queried_object_id();
		$img = get_post_meta( $id, '_dq_seo_og_image', true );
		if ( $img ) {
			return $img;
		}
		if ( has_post_thumbnail( $id ) ) {
			return get_the_post_thumbnail_url( $id, 'full' );
		}
		if ( is_singular( 'dq_product' ) ) {
			$p = dq_get_product( $id );
			if ( $p && $p['hero'] ) {
				return $p['hero'];
			}
		}
		$thumb = dq_asset( get_post_meta( $id, '_dq_thumb', true ) );
		if ( $thumb ) {
			return $thumb;
		}
	}
	$default = get_theme_mod( 'dq_og_default_image', '' );
	return $default ? $default : DQ_URI . '/assets/products/site-media/prod--overview.jpg';
}

function dq_canonical_url() {
	if ( is_front_page() ) {
		return home_url( '/' );
	}
	if ( is_singular() ) {
		return get_permalink( get_queried_object_id() );
	}
	if ( is_post_type_archive() ) {
		return get_post_type_archive_link( get_query_var( 'post_type' ) );
	}
	if ( is_home() ) {
		return get_permalink( get_option( 'page_for_posts' ) );
	}
	if ( is_category() || is_tag() || is_tax() ) {
		return get_term_link( get_queried_object() );
	}
	return home_url( add_query_arg( array(), $GLOBALS['wp']->request ) );
}

/* ------------------------------------------------------------------ */
/* Head output                                                         */
/* ------------------------------------------------------------------ */
add_action( 'wp_head', function () {
	$g = get_theme_mod( 'dq_verify_google', '' );
	$b = get_theme_mod( 'dq_verify_bing', '' );
	if ( $g ) {
		echo '<meta name="google-site-verification" content="' . esc_attr( $g ) . '">' . "\n";
	}
	if ( $b ) {
		echo '<meta name="msvalidate.01" content="' . esc_attr( $b ) . '">' . "\n";
	}
	$extra = get_theme_mod( 'dq_head_scripts', '' );
	if ( $extra ) {
		echo $extra . "\n"; // phpcs:ignore WordPress.Security.EscapeOutput -- admin-provided head code.
	}
	if ( dq_seo_plugin_active() ) {
		return;
	}
	$desc  = dq_meta_description();
	$canon = dq_canonical_url();
	$img   = dq_og_image();
	$title = wp_get_document_title();
	$type  = ( is_singular( 'post' ) ) ? 'article' : ( is_singular( 'dq_product' ) ? 'product' : 'website' );

	echo '<meta name="description" content="' . esc_attr( $desc ) . '">' . "\n";
	if ( $canon && ! is_singular() ) { // WP prints rel=canonical for singular already.
		echo '<link rel="canonical" href="' . esc_url( $canon ) . '">' . "\n";
	}
	echo '<meta property="og:locale" content="' . esc_attr( str_replace( '-', '_', get_bloginfo( 'language' ) ) ) . '">' . "\n";
	echo '<meta property="og:type" content="' . esc_attr( $type ) . '">' . "\n";
	echo '<meta property="og:title" content="' . esc_attr( $title ) . '">' . "\n";
	echo '<meta property="og:description" content="' . esc_attr( $desc ) . '">' . "\n";
	echo '<meta property="og:url" content="' . esc_url( $canon ) . '">' . "\n";
	echo '<meta property="og:site_name" content="' . esc_attr( get_bloginfo( 'name' ) ) . '">' . "\n";
	if ( $img ) {
		echo '<meta property="og:image" content="' . esc_url( $img ) . '">' . "\n";
	}
	if ( is_singular( 'post' ) ) {
		echo '<meta property="article:published_time" content="' . esc_attr( get_the_date( 'c' ) ) . '">' . "\n";
		echo '<meta property="article:modified_time" content="' . esc_attr( get_the_modified_date( 'c' ) ) . '">' . "\n";
	}
	echo '<meta name="twitter:card" content="summary_large_image">' . "\n";
	$handle = get_theme_mod( 'dq_twitter_handle', '@dynamiqesInc' );
	if ( $handle ) {
		echo '<meta name="twitter:site" content="' . esc_attr( $handle ) . '">' . "\n";
	}
	echo '<meta name="twitter:title" content="' . esc_attr( $title ) . '">' . "\n";
	echo '<meta name="twitter:description" content="' . esc_attr( $desc ) . '">' . "\n";
	if ( $img ) {
		echo '<meta name="twitter:image" content="' . esc_url( $img ) . '">' . "\n";
	}
	echo '<meta name="theme-color" content="#F88F10">' . "\n";
}, 1 );

/* Robots: sensible defaults + per-post noindex. */
add_filter( 'wp_robots', function ( $robots ) {
	$robots['max-image-preview'] = 'large';
	if ( is_search() || is_attachment() || is_date() || is_author() ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
	}
	if ( is_singular() && get_post_meta( get_queried_object_id(), '_dq_seo_noindex', true ) ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
	}
	return $robots;
} );

/* Sitemap: products in, internal types out. */
add_filter( 'wp_sitemaps_post_types', function ( $types ) {
	unset( $types['dq_testimonial'], $types['dq_inquiry'], $types['attachment'] );
	return $types;
} );
add_filter( 'wp_sitemaps_add_provider', function ( $provider, $name ) {
	return 'users' === $name ? false : $provider;
}, 10, 2 );

/* ------------------------------------------------------------------ */
/* JSON-LD                                                             */
/* ------------------------------------------------------------------ */
function dq_schema_organization() {
	$c    = dq_contact_info();
	$same = array();
	foreach ( dq_socials() as $s ) {
		$same[] = $s[1];
	}
	$org = array(
		'@type'    => 'Organization',
		'@id'      => home_url( '/#organization' ),
		'name'     => 'DynamIQ Enterprise Solution Inc.',
		'alternateName' => 'DynamIQ',
		'url'      => home_url( '/' ),
		'logo'     => array( '@type' => 'ImageObject', 'url' => DQ_URI . '/assets/logos/DynamIQ_Enterprise_Solution_Inc__with_Tagline_Logo_blk.svg' ),
		'description' => get_theme_mod( 'dq_seo_home_description', 'DynamIQ is a Premier SAP implementation partner delivering SAP Business One and the IQ Suite for Philippine small and mid-market businesses.' ),
		'email'    => $c['email'],
		'telephone'=> $c['phone1'],
		'address'  => array( '@type' => 'PostalAddress', 'streetAddress' => '12 Tagdalit Street, Brgy. Manresa', 'addressLocality' => 'Quezon City', 'postalCode' => '1115', 'addressRegion' => 'Metro Manila', 'addressCountry' => 'PH' ),
		'areaServed' => 'PH',
		'contactPoint' => array( array( '@type' => 'ContactPoint', 'contactType' => 'sales', 'telephone' => $c['phone1'], 'email' => $c['email'], 'availableLanguage' => array( 'en', 'fil' ) ) ),
		'sameAs'   => $same,
	);
	$founded = get_theme_mod( 'dq_org_founding', '' );
	if ( $founded ) {
		$org['foundingDate'] = $founded;
	}
	return $org;
}

function dq_schema_breadcrumbs() {
	$items = array( array( 'name' => __( 'Home', 'dynamiqes' ), 'url' => home_url( '/' ) ) );
	if ( is_post_type_archive( 'dq_product' ) ) {
		$items[] = array( 'name' => __( 'Our Products', 'dynamiqes' ), 'url' => dq_products_url() );
	} elseif ( is_singular( 'dq_product' ) ) {
		$items[] = array( 'name' => __( 'Our Products', 'dynamiqes' ), 'url' => dq_products_url() );
		$items[] = array( 'name' => get_the_title(), 'url' => get_permalink() );
	} elseif ( is_home() ) {
		$items[] = array( 'name' => __( 'News & Events', 'dynamiqes' ), 'url' => dq_news_url() );
	} elseif ( is_singular( 'post' ) ) {
		$items[] = array( 'name' => __( 'News & Events', 'dynamiqes' ), 'url' => dq_news_url() );
		$items[] = array( 'name' => get_the_title(), 'url' => get_permalink() );
	} elseif ( is_singular() ) {
		$items[] = array( 'name' => get_the_title(), 'url' => get_permalink() );
	} else {
		return null;
	}
	$list = array();
	foreach ( $items as $i => $it ) {
		$list[] = array( '@type' => 'ListItem', 'position' => $i + 1, 'name' => $it['name'], 'item' => $it['url'] );
	}
	return array( '@type' => 'BreadcrumbList', 'itemListElement' => $list );
}

add_action( 'wp_head', function () {
	$graph = array();
	if ( ! dq_seo_plugin_active() ) {
		$graph[] = dq_schema_organization();
		$graph[] = array( '@type' => 'WebSite', '@id' => home_url( '/#website' ), 'url' => home_url( '/' ), 'name' => get_bloginfo( 'name' ), 'publisher' => array( '@id' => home_url( '/#organization' ) ), 'inLanguage' => get_bloginfo( 'language' ) );
		$bc = dq_schema_breadcrumbs();
		if ( $bc ) {
			$graph[] = $bc;
		}
		if ( is_singular( 'post' ) ) {
			$graph[] = array(
				'@type'         => 'Article',
				'headline'      => get_the_title(),
				'description'   => dq_meta_description(),
				'image'         => dq_og_image(),
				'datePublished' => get_the_date( 'c' ),
				'dateModified'  => get_the_modified_date( 'c' ),
				'author'        => array( '@id' => home_url( '/#organization' ) ),
				'publisher'     => array( '@id' => home_url( '/#organization' ) ),
				'mainEntityOfPage' => get_permalink(),
			);
		}
	}
	if ( is_singular( 'dq_product' ) ) {
		$p = dq_get_product( get_queried_object_id() );
		if ( $p ) {
			$graph[] = array(
				'@type'               => 'SoftwareApplication',
				'name'                => $p['name'],
				'description'         => dq_seo_trim( $p['description'], 300 ),
				'image'               => $p['hero'],
				'url'                 => $p['url'],
				'applicationCategory' => 'BusinessApplication',
				'operatingSystem'     => 'Web, Windows',
				'provider'            => array( '@id' => home_url( '/#organization' ) ),
				'featureList'         => array_map( function ( $g ) { return $g['title']; }, $p['features'] ),
			);
			if ( ! empty( $p['faqs'] ) ) {
				$qa = array();
				foreach ( $p['faqs'] as $f ) {
					$qa[] = array( '@type' => 'Question', 'name' => $f[0], 'acceptedAnswer' => array( '@type' => 'Answer', 'text' => $f[1] ) );
				}
				$graph[] = array( '@type' => 'FAQPage', 'mainEntity' => $qa );
			}
		}
	}
	if ( empty( $graph ) ) {
		return;
	}
	echo '<script type="application/ld+json">' . wp_json_encode( array( '@context' => 'https://schema.org', '@graph' => $graph ), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . '</script>' . "\n";
}, 5 );

/* ------------------------------------------------------------------ */
/* SEO meta box (pages, posts, products)                               */
/* ------------------------------------------------------------------ */
add_action( 'add_meta_boxes', function () {
	if ( dq_seo_plugin_active() ) {
		return;
	}
	foreach ( array( 'page', 'post', 'dq_product' ) as $pt ) {
		add_meta_box( 'dq_seo', __( 'SEO', 'dynamiqes' ), 'dq_seo_meta_box', $pt, 'normal', 'default' );
	}
} );
function dq_seo_meta_box( $post ) {
	wp_nonce_field( 'dq_seo_save', 'dq_seo_nonce' );
	$t = get_post_meta( $post->ID, '_dq_seo_title', true );
	$d = get_post_meta( $post->ID, '_dq_seo_description', true );
	$i = get_post_meta( $post->ID, '_dq_seo_og_image', true );
	$n = get_post_meta( $post->ID, '_dq_seo_noindex', true );
	echo '<div class="dq-fields">';
	dq_render_field( '_dq_seo_title', array( 'text', __( 'Title tag', 'dynamiqes' ), __( 'Recommended 50–60 characters. Empty = "Post title — Site name".', 'dynamiqes' ) ), $t );
	dq_render_field( '_dq_seo_description', array( 'textarea', __( 'Meta description', 'dynamiqes' ), __( 'Recommended 120–158 characters.', 'dynamiqes' ) ), $d );
	dq_render_field( '_dq_seo_og_image', array( 'image', __( 'Social share image', 'dynamiqes' ), __( '1200×630 recommended. Empty = featured image / product hero.', 'dynamiqes' ) ), $i );
	echo '<label><input type="checkbox" name="_dq_seo_noindex" value="1" ' . checked( $n, '1', false ) . '> ' . esc_html__( 'Hide from search engines (noindex)', 'dynamiqes' ) . '</label>';
	echo '</div>';
}
add_action( 'save_post', function ( $post_id ) {
	if ( ! isset( $_POST['dq_seo_nonce'] ) || ! wp_verify_nonce( $_POST['dq_seo_nonce'], 'dq_seo_save' ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	foreach ( array( '_dq_seo_title' => 'sanitize_text_field', '_dq_seo_description' => 'sanitize_textarea_field', '_dq_seo_og_image' => 'sanitize_text_field' ) as $k => $cb ) {
		$v = call_user_func( $cb, wp_unslash( $_POST[ $k ] ?? '' ) );
		if ( '' === $v ) {
			delete_post_meta( $post_id, $k );
		} else {
			update_post_meta( $post_id, $k, $v );
		}
	}
	if ( ! empty( $_POST['_dq_seo_noindex'] ) ) {
		update_post_meta( $post_id, '_dq_seo_noindex', '1' );
	} else {
		delete_post_meta( $post_id, '_dq_seo_noindex' );
	}
} );
