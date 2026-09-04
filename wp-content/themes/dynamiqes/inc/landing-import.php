<?php
/**
 * Landing-page importer: pulls the SEO landing pages from the live dynamiqes.com site
 * (the "Blogs" dropdown) and recreates them as WordPress pages with the same slugs,
 * using the "Landing page" template. Content is reduced to clean blocks
 * (headings, paragraphs, lists, images, FAQ accordions); forms and chrome are dropped.
 *
 * Runs from Appearance → DynamIQ Setup ("Import landing pages"), or via dq_import_landing_pages().
 *
 * @package dynamiqes
 */

defined( 'ABSPATH' ) || exit;

/** Slugs to import (same on dynamiqes.com). Filterable. */
function dq_landing_slugs() {
	return apply_filters( 'dq_landing_slugs', array(
		'accounting-system-philippines',
		'erp-solutions-philippines',
		'it-solutions-company-philippines',
		'sap-software-philippines',
		'barcode-inventory-system-philippines',
		'bir-cas-philippines',
	) );
}

/** Source site. */
function dq_landing_source_base() {
	return untrailingslashit( apply_filters( 'dq_landing_source_base', 'https://dynamiqes.com' ) );
}

/**
 * Fetch and parse one live page into [ title, description, hero_image, intro, content_html ].
 *
 * @param string $slug Page slug.
 * @param string $html Optional raw HTML (skips the HTTP fetch; used for offline testing).
 * @return array|WP_Error
 */
function dq_landing_parse( $slug, $html = '' ) {
	if ( '' === $html ) {
		$res = wp_remote_get( dq_landing_source_base() . '/' . $slug . '/', array( 'timeout' => 45, 'user-agent' => 'Mozilla/5.0 (DynamIQ theme importer)' ) );
		if ( is_wp_error( $res ) ) {
			return $res;
		}
		if ( 200 !== wp_remote_retrieve_response_code( $res ) ) {
			return new WP_Error( 'dq_http', sprintf( 'HTTP %d for %s', wp_remote_retrieve_response_code( $res ), $slug ) );
		}
		$html = wp_remote_retrieve_body( $res );
	}
	if ( ! class_exists( 'DOMDocument' ) ) {
		return new WP_Error( 'dq_dom', 'PHP DOM extension is not available.' );
	}

	$doc = new DOMDocument();
	libxml_use_internal_errors( true );
	$doc->loadHTML( '<?xml encoding="UTF-8">' . $html );
	libxml_clear_errors();
	$xp = new DOMXPath( $doc );

	$meta = function ( $q ) use ( $xp ) {
		$n = $xp->query( $q )->item( 0 );
		return $n ? trim( $n->getAttribute( 'content' ) ) : '';
	};
	$description = $meta( '//meta[@name="description"]' );
	$og_image    = $meta( '//meta[@property="og:image"]' );

	$h1   = $xp->query( '//h1' )->item( 0 );
	$title = $h1 ? dq_landing_text( $h1 ) : ucwords( str_replace( '-', ' ', $slug ) );

	/* Intro: first substantial paragraph(s) in the same section as the H1. */
	$intro = '';
	if ( $h1 ) {
		$sec = $h1;
		while ( $sec && ! in_array( strtolower( $sec->nodeName ), array( 'section', 'body' ), true ) ) {
			$sec = $sec->parentNode;
		}
		if ( $sec ) {
			foreach ( $xp->query( './/p', $sec ) as $p ) {
				$t = dq_landing_text( $p );
				if ( mb_strlen( $t ) > 40 ) {
					$intro .= '<p>' . esc_html( $t ) . '</p>';
				}
			}
			/* hero image: first real image in the hero section */
			if ( ! $og_image ) {
				foreach ( $xp->query( './/img', $sec ) as $img ) {
					$src = dq_landing_img_src( $img );
					if ( $src ) {
						$og_image = $src;
						break;
					}
				}
			}
		}
	}

	/* Body: walk every <section> after the hero; skip forms, chrome and duplicates. */
	$skip_classes = array( 'contact', 'cro-form', 'header', 'footer', 'nav', 'ready-to-get', 'lets-talk', 'form' );
	$blocks       = array();
	$seen         = array();
	$sections     = $xp->query( '//section' );
	$hero_done    = false;
	foreach ( $sections as $section ) {
		$cls = strtolower( $section->getAttribute( 'class' ) . ' ' . $section->getAttribute( 'id' ) );
		if ( $xp->query( './/h1', $section )->length ) {
			$hero_done = true; // hero handled separately
			continue;
		}
		if ( ! $hero_done ) {
			continue;
		}
		$skip = false;
		foreach ( $skip_classes as $s ) {
			if ( false !== strpos( $cls, $s ) ) {
				$skip = true;
			}
		}
		if ( $skip || $xp->query( './/form', $section )->length ) {
			continue;
		}

		/* FAQ accordion → <details> */
		$buttons = $xp->query( './/*[contains(concat(" ",normalize-space(@class)," ")," accordion-button ")]', $section );
		$bodies  = $xp->query( './/*[contains(concat(" ",normalize-space(@class)," ")," accordion-body ")]', $section );
		if ( $buttons->length && $buttons->length === $bodies->length ) {
			$heads = $xp->query( './/h2|.//h3', $section );
			$blocks[] = '<h2>' . esc_html( $heads->length ? dq_landing_text( $heads->item( 0 ) ) : __( 'Frequently Asked Questions', 'dynamiqes' ) ) . '</h2>';
			$faq = '<div class="faq-list">';
			for ( $i = 0; $i < $buttons->length; $i++ ) {
				$q = dq_landing_text( $buttons->item( $i ) );
				$a = dq_landing_text( $bodies->item( $i ) );
				if ( $q && $a ) {
					$faq .= '<details><summary>' . esc_html( $q ) . '</summary><p>' . esc_html( $a ) . '</p></details>';
				}
			}
			$blocks[] = $faq . '</div>';
			continue;
		}

		$section_images = 0;
		foreach ( $xp->query( './/h2|.//h3|.//h4|.//p|.//ul|.//ol|.//img', $section ) as $node ) {
			$name = strtolower( $node->nodeName );
			if ( 'img' === $name ) {
				$src = dq_landing_img_src( $node );
				$w   = (int) $node->getAttribute( 'width' );
				$h   = (int) $node->getAttribute( 'height' );
				$cls = strtolower( $node->getAttribute( 'class' ) . ' ' . $node->parentNode->getAttribute( 'class' ) );
				/* One illustrative image per section; skip icons, badges, logos and tiny graphics. */
				if ( ! $src || isset( $seen[ $src ] ) || $section_images >= 1
					|| preg_match( '/\.(svg|gif)(\?|$)/i', $src )
					|| preg_match( '/(logo|icon|badge|partners?|years|perfect|rate|check|arrow|star)/i', basename( $src ) . ' ' . $cls )
					|| ( $w && $w < 200 ) || ( $h && $h < 200 ) ) {
					continue;
				}
				$section_images++;
				$seen[ $src ] = true;
				$alt = trim( $node->getAttribute( 'alt' ) );
				$blocks[] = '<figure class="wp-block-image"><img src="' . esc_url( $src ) . '" alt="' . esc_attr( $alt ? $alt : $title ) . '" loading="lazy"></figure>';
				continue;
			}
			if ( 'ul' === $name || 'ol' === $name ) {
				if ( $xp->query( 'ancestor::ul|ancestor::ol', $node )->length ) {
					continue; // nested list handled by parent
				}
				$items = '';
				foreach ( $xp->query( './li', $node ) as $li ) {
					$t = dq_landing_text( $li );
					if ( $t ) {
						$items .= '<li>' . esc_html( $t ) . '</li>';
					}
				}
				if ( $items ) {
					$blocks[] = '<' . $name . '>' . $items . '</' . $name . '>';
				}
				continue;
			}
			if ( 'p' === $name && $xp->query( 'ancestor::li', $node )->length ) {
				continue;
			}
			$t = dq_landing_text( $node );
			if ( '' === $t ) {
				continue;
			}
			$key = $name . '|' . $t;
			if ( isset( $seen[ $key ] ) ) {
				continue;
			}
			$seen[ $key ] = true;
			$blocks[] = '<' . $name . '>' . esc_html( $t ) . '</' . $name . '>';
		}
	}

	return array(
		'title'       => $title,
		'description' => $description,
		'hero_image'  => $og_image,
		'intro'       => $intro,
		'content'     => implode( "\n", $blocks ),
	);
}

/** Visible text of a node, whitespace-collapsed. */
function dq_landing_text( DOMNode $node ) {
	$clone = $node->cloneNode( true );
	$doc   = new DOMDocument();
	$doc->appendChild( $doc->importNode( $clone, true ) );
	$xp = new DOMXPath( $doc );
	foreach ( $xp->query( '//script|//style|//svg|//button|//noscript' ) as $rm ) {
		// Keep the root node itself (an accordion question IS a <button>); drop nested chrome only.
		if ( $rm->parentNode && $rm !== $doc->documentElement ) {
			$rm->parentNode->removeChild( $rm );
		}
	}
	return trim( preg_replace( '/\s+/u', ' ', html_entity_decode( $doc->textContent, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) );
}

/** Real image URL (handles lazy-load attributes). */
function dq_landing_img_src( DOMElement $img ) {
	foreach ( array( 'data-src', 'data-lazy-src', 'data-original', 'src' ) as $a ) {
		$v = trim( $img->getAttribute( $a ) );
		if ( $v && 0 !== strpos( $v, 'data:' ) ) {
			return $v;
		}
	}
	return '';
}

/**
 * Import all landing pages. Returns a report array.
 *
 * @param bool $sideload Copy hero images into the Media Library.
 */
function dq_import_landing_pages( $sideload = false ) {
	$report = array();
	foreach ( dq_landing_slugs() as $slug ) {
		$data = dq_landing_parse( $slug );
		if ( is_wp_error( $data ) ) {
			$report[] = $slug . ': ' . $data->get_error_message();
			continue;
		}
		$existing = get_page_by_path( $slug );
		$args     = array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_name'    => $slug,
			'post_excerpt' => $data['description'],
			'post_content' => $data['content'],
		);
		$prev_tpl = $existing ? get_post_meta( $existing->ID, '_wp_page_template', true ) : '';
		if ( $existing ) {
			/* An existing page keeps its own title (it feeds <title>, Yoast and the menus);
			   the article headline goes to _dq_landing_h1 and the template shows that. */
			$args['ID'] = $existing->ID;
			$id         = wp_update_post( $args );
		} else {
			$args['post_title'] = $data['title'];
			$id                 = wp_insert_post( $args );
		}
		if ( ! $id || is_wp_error( $id ) ) {
			$report[] = $slug . ': could not save';
			continue;
		}
		/* Only claim the template slot when the page has none. A page copied from the old
		   dynamiqes.com theme carries that theme's template name (e.g. page-revamp-template.php);
		   core resets that to "default" on every wp_update_post() because this theme has no such
		   file, so put it back — then the old theme still renders the page if it is re-activated.
		   The template_include filter below routes imported pages to page-landing.php here anyway. */
		if ( $prev_tpl && 'default' !== $prev_tpl ) {
			update_post_meta( $id, '_wp_page_template', $prev_tpl );
		} else {
			update_post_meta( $id, '_wp_page_template', 'page-landing.php' );
		}
		update_post_meta( $id, '_dq_landing_source', dq_landing_source_base() . '/' . $slug . '/' );
		update_post_meta( $id, '_dq_landing_h1', $data['title'] );
		update_post_meta( $id, '_dq_landing_intro', $data['intro'] );
		if ( $data['description'] ) {
			update_post_meta( $id, '_dq_seo_description', $data['description'] );
		}
		if ( $data['hero_image'] ) {
			update_post_meta( $id, '_dq_hero_image', $data['hero_image'] );
			if ( $sideload && ! has_post_thumbnail( $id ) ) {
				require_once ABSPATH . 'wp-admin/includes/media.php';
				require_once ABSPATH . 'wp-admin/includes/file.php';
				require_once ABSPATH . 'wp-admin/includes/image.php';
				$att = media_sideload_image( $data['hero_image'], $id, $data['title'], 'id' );
				if ( ! is_wp_error( $att ) ) {
					set_post_thumbnail( $id, $att );
				}
			}
		}
		$report[] = $slug . ': ' . ( $existing ? 'updated' : 'created' ) . ' (' . mb_strlen( wp_strip_all_tags( $data['content'] ) ) . ' chars)';
	}
	/* Re-point the Blogs dropdown to the new pages (menu links only — not the full seeder,
	   which would also reset the front page and posts page on an existing site). */
	if ( function_exists( 'dq_repair_blog_menu_links' ) ) {
		dq_repair_blog_menu_links();
	}
	return $report;
}

/**
 * Imported landing pages always render with page-landing.php in this theme, whatever
 * template name the page carries. Pages copied from the old dynamiqes.com theme keep
 * that theme's template in _wp_page_template (e.g. page-revamp-template.php); without
 * this they would fall back to the generic page.php and show only the title.
 */
add_filter( 'template_include', function ( $template ) {
	if ( is_singular( 'page' ) && dq_is_landing_page( get_queried_object() ) ) {
		$landing = locate_template( 'page-landing.php' );
		if ( $landing ) {
			return $landing;
		}
	}
	return $template;
}, 20 );

/** True for an imported landing page, or for a still-empty page with one of the landing slugs. */
function dq_is_landing_page( $post ) {
	if ( ! $post instanceof WP_Post || 'page' !== $post->post_type ) {
		return false;
	}
	if ( get_post_meta( $post->ID, '_dq_landing_source', true ) ) {
		return true;
	}
	return '' === trim( $post->post_content ) && in_array( $post->post_name, dq_landing_slugs(), true );
}

/**
 * Read-only stand-in for a landing page that has not been imported yet (the customizer
 * preview of this theme before activation, or a site where the first-load import failed):
 * the old page is fetched and parsed on demand and kept in a transient for 12 hours.
 * Nothing is written to the page. Returns the parsed array, or null when unavailable.
 */
function dq_landing_live_data( $slug ) {
	if ( ! in_array( $slug, dq_landing_slugs(), true ) ) {
		return null;
	}
	$key  = 'dq_landing_live_' . $slug;
	$data = get_transient( $key );
	if ( is_array( $data ) ) {
		return $data;
	}
	if ( 'fail' === $data ) {
		return null; // recent fetch failed; do not retry on every view
	}
	$data = dq_landing_parse( $slug );
	if ( is_wp_error( $data ) ) {
		set_transient( $key, 'fail', 10 * MINUTE_IN_SECONDS );
		return null;
	}
	set_transient( $key, $data, 12 * HOUR_IN_SECONDS );
	return $data;
}

/**
 * First admin load after activation: a site that already has the six landing pages as
 * empty shells (their content lived in the old theme's template files) gets them filled
 * from dynamiqes.com automatically, so the "Blogs" dropdown works without a manual import.
 * Pages that have content, or were already imported, are left alone. Returns the report.
 */
function dq_auto_import_landing_pages() {
	$pending = array();
	foreach ( dq_landing_slugs() as $slug ) {
		$page = get_page_by_path( $slug );
		if ( $page && 'publish' === $page->post_status && '' === trim( $page->post_content ) && ! get_post_meta( $page->ID, '_dq_landing_source', true ) ) {
			$pending[] = $slug;
		}
	}
	if ( ! $pending ) {
		return array();
	}
	$only = function () use ( $pending ) {
		return $pending;
	};
	add_filter( 'dq_landing_slugs', $only, 99 );
	$report = dq_import_landing_pages( true );
	remove_filter( 'dq_landing_slugs', $only, 99 );
	return $report;
}
