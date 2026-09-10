<?php
/**
 * Landing-page importer: pulls the SEO landing pages from the live dynamiqes.com site
 * (the "Blogs" dropdown, plus the "Our Services" page) and recreates them as WordPress
 * pages with the same slugs, using the "Landing page" template. Content is reduced to clean blocks
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
		'our-services', // top-level nav item on the old site: /our-services/ (H1 "Our Services", H2 per service)
		'accounting-system-philippines',
		'erp-solutions-philippines',
		'it-solutions-company-philippines',
		'sap-software-philippines',
		'barcode-inventory-system-philippines',
		'bir-cas-philippines',
		/* SEM / campaign pages in the live sitemap, built on the same page builder */
		'bir-cas-provider-philippines',
		'sap-business-one-provider-philippines',
		'promo/bir-cas-solution',
		'promo/accounting-inventory-system',
		'promo/erp-system',
		'application-form',
		'thank-you-ad',
		'thank-you-accounting',
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
	$html = preg_replace( '#<br\s*/?>#i', ' ', $html ); // "High Success<br>Rate!" must read "High Success Rate!"

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
	$title_node  = $xp->query( '//title' )->item( 0 );
	$seo_title   = $title_node ? trim( preg_replace( '/\s+/', ' ', $title_node->textContent ) ) : '';

	$h1   = $xp->query( '//h1' )->item( 0 );
	$title = $h1 ? dq_landing_text( $h1 ) : ucwords( str_replace( '-', ' ', $slug ) );

	/* Intro: first substantial paragraph(s) in the same section as the H1, up to the section's
	   first H2. Some pages open their first content block inside the hero section (the live
	   /our-services/ puts "SAP BIR CAS Accreditation Assistance" there); from that H2 on the
	   hero is body copy and is walked with the other sections below. */
	$intro    = '';
	$hero_sec = null;
	$hero_h2  = false; // hero section holds an H2 (body copy follows it)
	if ( $h1 ) {
		$sec = $h1;
		while ( $sec && ! in_array( strtolower( $sec->nodeName ), array( 'section', 'body' ), true ) ) {
			$sec = $sec->parentNode;
		}
		if ( $sec ) {
			$hero_sec = $sec;
			foreach ( $xp->query( './/h2|.//p|.//img', $sec ) as $node ) { // document order
				$name = strtolower( $node->nodeName );
				if ( ! dq_landing_owned( $node, $sec ) ) {
					continue;
				}
				if ( 'h2' === $name ) {
					$hero_h2 = true;
					break;
				}
				if ( 'p' === $name ) {
					$t = dq_landing_text( $node );
					if ( mb_strlen( $t ) > 40 ) {
						$intro .= '<p>' . esc_html( $t ) . '</p>';
					}
				} elseif ( ! $og_image ) {
					/* hero image: first real image in the hero section */
					$src = dq_landing_img_src( $node );
					if ( $src ) {
						$og_image = $src;
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
		$cls       = strtolower( $section->getAttribute( 'class' ) . ' ' . $section->getAttribute( 'id' ) );
		$from_h2   = false; // hero remainder: emit nodes only from the first H2 on, and no images (icons)
		if ( dq_landing_owned_nodes( $xp->query( './/h1', $section ), $section ) ) {
			$hero_done = true; // hero intro/image handled above
			if ( ! ( $hero_h2 && $section->isSameNode( $hero_sec ) ) ) {
				continue;
			}
			$from_h2 = true;
		}
		if ( ! $hero_done ) {
			continue;
		}
		if ( ! $from_h2 ) {
			/* The in-page CTA block around the enquiry form ("Ready To Get Started?" H3, a heading and a
			   line of copy): part of the live outline, so keep its text — never the form itself. The old
			   site's builders name these sections differently (lp-contact-form, sectionContact,
			   inquire-with-us, sem--form …), so the form itself is the marker. */
			if ( dq_landing_owned_nodes( $xp->query( './/form', $section ), $section ) || preg_match( '/(lp-contact-form|contact-form|sectioncontact|lets-talk|ready-to|inquire-with-us)/', $cls ) ) {
				$cta = '';
				foreach ( $xp->query( './/h2|.//h3|.//p', $section ) as $node ) {
					if ( ! dq_landing_owned( $node, $section ) ) {
						continue;
					}
					/* The newer builder puts the H3 + its line inside the <form> header; keep headings anywhere,
					   and paragraphs in the form only when they are copy, not field wrappers. */
					if ( 'p' === strtolower( $node->nodeName ) && $xp->query( 'ancestor::form', $node )->length
						&& ( $xp->query( './/*[contains(@class,"wpcf7-form-control")]|.//input|.//select|.//textarea|.//button', $node )->length || mb_strlen( dq_landing_text( $node ) ) < 25 ) ) {
						continue;
					}
					$t = dq_landing_text( $node );
					if ( '' !== $t ) {
						$cta .= '<' . strtolower( $node->nodeName ) . '>' . dq_landing_fix_copy( esc_html( $t ) ) . '</' . strtolower( $node->nodeName ) . '>';
					}
				}
				if ( $cta ) {
					$blocks[] = '<div class="landing-cta">' . $cta . '<p class="landing-cta-btn"><a class="btn btn-orange" href="' . esc_url( dq_book_demo_url() ) . '">' . esc_html__( 'Get Your Free Business Analysis', 'dynamiqes' ) . '</a></p></div>';
				}
				continue;
			}
			$skip = false;
			foreach ( $skip_classes as $s ) {
				if ( false !== strpos( $cls, $s ) ) {
					$skip = true;
				}
			}
			if ( $skip || dq_landing_owned_nodes( $xp->query( './/form', $section ), $section ) ) {
				continue;
			}
		}

		/* FAQ accordion → <details>. Questions marked up as H3 on the live page stay H3s (inside the
		   summary); answers keep their paragraphs and lists. */
		$buttons = dq_landing_owned_nodes( $xp->query( './/*[contains(concat(" ",normalize-space(@class)," ")," accordion-button ")]', $section ), $section );
		$bodies  = dq_landing_owned_nodes( $xp->query( './/*[contains(concat(" ",normalize-space(@class)," ")," accordion-body ")]', $section ), $section );
		if ( $buttons && count( $buttons ) === count( $bodies ) ) {
			$heads = dq_landing_owned_nodes( $xp->query( './/h2', $section ), $section );
			$blocks[] = '<h2>' . esc_html( $heads ? dq_landing_text( $heads[0] ) : __( 'Frequently Asked Questions', 'dynamiqes' ) ) . '</h2>';
			$faq = '<div class="faq-list">';
			foreach ( $buttons as $i => $button ) {
				$q = dq_landing_text( $button );
				$a = dq_landing_rich( $bodies[ $i ] );
				if ( $q && $a ) {
					$is_h3 = $xp->query( 'ancestor-or-self::h3', $button )->length > 0;
					$faq  .= '<details><summary>' . ( $is_h3 ? '<h3>' . esc_html( $q ) . '</h3>' : esc_html( $q ) ) . '</summary><div class="faq-answer">' . $a . '</div></details>';
				}
			}
			$blocks[] = $faq . '</div>';
			continue;
		}

		$section_images = 0;
		$started        = ! $from_h2;
		$caption_list   = array(); // icon + caption rows on the old site are bare <p>s after a "...:" lead-in; rebuild them as a list
		foreach ( $xp->query( './/h2|.//h3|.//h4|.//p|.//ul|.//ol|.//img', $section ) as $node ) {
			$name = strtolower( $node->nodeName );
			if ( ! dq_landing_owned( $node, $section ) ) {
				continue; // belongs to a later section libxml nested inside this one (unclosed tags on the old site)
			}
			if ( ! $started ) {
				if ( 'h2' !== $name ) {
					continue;
				}
				$started = true;
			}
			if ( 'img' === $name ) {
				if ( $from_h2 ) {
					continue;
				}
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
				/* The old pages often ship the icons with no width/height attributes and a name
				   the filter above cannot tell from a photo ("top-finance.png" is 54x60), so a
				   54px icon became a section image and got stretched across the column. Measure
				   it. A probe that fails leaves the image in — the size cap in the CSS covers it. */
				$dims = dq_landing_image_size( $src );
				if ( $dims && ( $dims[0] < 200 || $dims[1] < 200 ) ) {
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
						$inline = dq_landing_inline( $li );
						$items .= '<li>' . dq_landing_fix_copy( $inline ? $inline : esc_html( $t ) ) . '</li>';
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
			$key = spl_object_hash( $section ) . '|' . $name . '|' . $t; // the old site repeats blocks within a section (desktop/mobile copies); the same heading in two sections is real
			if ( isset( $seen[ $key ] ) ) {
				continue;
			}
			$seen[ $key ] = true;
			$lead_in = $blocks && ':</p>' === substr( end( $blocks ), -5 );
			if ( 'p' === $name && mb_strlen( $t ) <= 60 && ! preg_match( '/[.!?]$/u', $t ) && ( $caption_list || $lead_in ) ) {
				$caption_list[] = '<li>' . esc_html( $t ) . '</li>';
				continue;
			}
			if ( $caption_list ) {
				$blocks[]     = '<ul>' . implode( '', $caption_list ) . '</ul>';
				$caption_list = array();
			}
			/* paragraphs keep their in-text links (item F4); headings stay plain text */
			$body     = 'p' === $name ? dq_landing_inline( $node ) : '';
			$blocks[] = '<' . $name . '>' . dq_landing_fix_copy( $body ? $body : esc_html( $t ) ) . '</' . $name . '>';
		}
		if ( $caption_list ) {
			$blocks[] = '<ul>' . implode( '', $caption_list ) . '</ul>';
		}
	}

	$title       = dq_landing_fix_copy( $title );
	$seo_title   = dq_landing_fix_copy( $seo_title );
	$description = dq_landing_fix_copy( $description );
	$intro       = dq_landing_fix_copy( $intro );

	return array(
		'title'       => $title,
		'seo_title'   => $seo_title,
		'description' => $description,
		'hero_image'  => $og_image,
		'intro'       => $intro,
		'content'     => implode( "\n", $blocks ),
	);
}

/**
 * Does $node belong to $section itself? The old site leaves tags unclosed, so libxml nests the
 * following <section>s inside the current one; nodes whose nearest section ancestor is another
 * section are that section's and are walked when it comes up.
 */
function dq_landing_owned( DOMNode $node, DOMNode $section ) {
	$n = $node->parentNode;
	while ( $n && XML_ELEMENT_NODE === $n->nodeType ) {
		if ( 'section' === strtolower( $n->nodeName ) ) {
			return $n->isSameNode( $section );
		}
		$n = $n->parentNode;
	}
	return true;
}

/** The nodes of a list that belong to $section (see dq_landing_owned()), as an array. */
function dq_landing_owned_nodes( DOMNodeList $nodes, DOMNode $section ) {
	$out = array();
	foreach ( $nodes as $n ) {
		if ( dq_landing_owned( $n, $section ) ) {
			$out[] = $n;
		}
	}
	return $out;
}

/** Paragraphs and lists inside a node as clean HTML (FAQ answers); falls back to one paragraph. */
function dq_landing_rich( DOMNode $node ) {
	$doc = $node->ownerDocument;
	$xp  = new DOMXPath( $doc );
	$out = '';
	foreach ( $xp->query( './/p|.//ul|.//ol', $node ) as $n ) {
		$name = strtolower( $n->nodeName );
		if ( 'p' === $name ) {
			if ( $xp->query( 'ancestor::li', $n )->length ) {
				continue;
			}
			$t = dq_landing_text( $n );
			if ( '' !== $t ) {
				$inline = dq_landing_inline( $n );
				$out   .= '<p>' . dq_landing_fix_copy( $inline ? $inline : esc_html( $t ) ) . '</p>';
			}
			continue;
		}
		if ( $xp->query( 'ancestor::ul|ancestor::ol', $n )->length ) {
			continue;
		}
		$items = '';
		foreach ( $xp->query( './li', $n ) as $li ) {
			$t = dq_landing_text( $li );
			if ( '' !== $t ) {
				$inline = dq_landing_inline( $li );
				$items .= '<li>' . dq_landing_fix_copy( $inline ? $inline : esc_html( $t ) ) . '</li>';
			}
		}
		if ( $items ) {
			$out .= '<' . $name . '>' . $items . '</' . $name . '>';
		}
	}
	if ( '' === $out ) {
		$t = dq_landing_text( $node );
		if ( '' !== $t ) {
			$out = '<p>' . esc_html( $t ) . '</p>';
		}
	}
	return $out;
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
	/* textContent concatenates descendants with no separator, so a page builder that splits
	   a headline across <span>s or stacks <option>s came out run together — "Why
	   ChooseDynamIQ" on the IT Solutions landing page and "PositionTechnical
	   ConsultantFunctional Consultant" on the Application Form (review items F6 and G1).
	   Give every non-inline element boundary an explicit space before extracting. */
	$inline = array( 'a', 'span', 'strong', 'b', 'em', 'i', 'u', 's', 'small', 'sub', 'sup', 'code',
		'abbr', 'mark', 'font', 'time', 'q', 'cite', 'var', 'kbd', 'samp', 'bdi', 'bdo', 'wbr' );
	foreach ( iterator_to_array( $xp->query( '//*' ) ) as $el ) {
		if ( $el === $doc->documentElement || in_array( strtolower( $el->nodeName ), $inline, true ) || ! $el->parentNode ) {
			continue;
		}
		$el->parentNode->insertBefore( $doc->createTextNode( ' ' ), $el );
		if ( $el->nextSibling ) {
			$el->parentNode->insertBefore( $doc->createTextNode( ' ' ), $el->nextSibling );
		} else {
			$el->parentNode->appendChild( $doc->createTextNode( ' ' ) );
		}
	}
	$text = trim( preg_replace( '/\s+/u', ' ', html_entity_decode( $doc->textContent, ENT_QUOTES | ENT_HTML5, 'UTF-8' ) ) );
	$text = preg_replace( '/\s+([,.;:!?%)\]])/u', '$1', $text ); // the inserted spaces must not push punctuation off its word
	$text = preg_replace( '/([(\[])\s+/u', '$1', $text );
	return trim( $text );
}

/**
 * A node's copy as inline HTML, keeping its in-text links.
 *
 * dq_landing_text() flattens everything to plain text, which is why the imported landing
 * pages lost the in-text internal links the review asked for (item F4). This keeps <a>,
 * <strong>/<em> and <br>, drops everything else, and rewrites dynamiqes.com URLs to this
 * site so the links resolve on staging and after go-live.
 *
 * @param DOMNode $node Source node.
 * @return string Inline HTML (already sanitised through dq_inline_html()).
 */
function dq_landing_inline( DOMNode $node ) {
	$clone = $node->cloneNode( true );
	$doc   = new DOMDocument();
	$doc->appendChild( $doc->importNode( $clone, true ) );
	$xp = new DOMXPath( $doc );
	foreach ( $xp->query( '//script|//style|//svg|//button|//noscript|//select|//input|//textarea' ) as $rm ) {
		if ( $rm->parentNode && $rm !== $doc->documentElement ) {
			$rm->parentNode->removeChild( $rm );
		}
	}
	$live = dq_landing_source_base();
	foreach ( iterator_to_array( $xp->query( '//a[@href]' ) ) as $a ) {
		$href = trim( $a->getAttribute( 'href' ) );
		if ( 0 === strpos( $href, $live ) ) {
			$href = home_url( substr( $href, strlen( $live ) ) ); // internal: point at this site
		}
		if ( '' === $href || 0 === strpos( $href, '#' ) || preg_match( '/^javascript:/i', $href ) ) {
			/* not a real destination — unwrap, keeping the words */
			while ( $a->firstChild ) {
				$a->parentNode->insertBefore( $a->firstChild, $a );
			}
			$a->parentNode->removeChild( $a );
			continue;
		}
		$a->setAttribute( 'href', $href );
		foreach ( array( 'class', 'id', 'style', 'onclick', 'data-wpel-link' ) as $attr ) {
			$a->removeAttribute( $attr );
		}
		if ( 0 !== strpos( $href, home_url() ) ) {
			$a->setAttribute( 'target', '_blank' );
			$a->setAttribute( 'rel', 'noopener' );
		}
	}
	$html = '';
	foreach ( $doc->documentElement->childNodes as $child ) {
		$html .= $doc->saveHTML( $child );
	}
	$html = html_entity_decode( $html, ENT_QUOTES | ENT_HTML5, 'UTF-8' );
	$html = trim( preg_replace( '/\s+/u', ' ', $html ) );
	$html = preg_replace( '/\s+([,.;:!?%)\]])/u', '$1', $html );
	$out  = trim( dq_inline_html( $html ) );
	/* If nothing but whitespace survived, or the markup carried no link worth keeping,
	   fall back to the plain-text path so the copy is never lost. */
	return '' === trim( wp_strip_all_tags( $out ) ) ? '' : $out;
}

/**
 * Copy corrections applied to every imported landing page.
 *
 * The review asked for the SAP partner tier to read "Premier Partner" everywhere (item F5);
 * "Gold Partner" is the old tier and still appears in the live copy.
 *
 * @param string $text Copy (plain text or inline HTML).
 * @return string
 */
function dq_landing_fix_copy( $text ) {
	$text = (string) $text;
	/* "SAP Business One - Gold Partner in the Philippines" should read as one phrase, so the
	   dash goes with the tier rather than leaving "SAP Business One - Premier Partner". */
	$text = preg_replace( '/SAP Business One\s*[-\x{2010}-\x{2015}]\s*Gold Partner/u', 'SAP Business One Premier Partner', $text );
	return str_replace( 'Gold Partner', 'Premier Partner', $text );
}

/**
 * Pixel size of a remote image, memoised per request.
 *
 * The importer needs this to tell a section photo from an icon (see the image filter above);
 * the old pages' markup carries no reliable width/height. Failures return null so a probe that
 * cannot reach the file never drops an image.
 *
 * @param string $url Image URL.
 * @return array{0:int,1:int}|null [ width, height ]
 */
function dq_landing_image_size( $url ) {
	static $cache = array();
	if ( array_key_exists( $url, $cache ) ) {
		return $cache[ $url ];
	}
	$cache[ $url ] = null;
	$res = wp_remote_get( $url, array( 'timeout' => 15, 'user-agent' => 'Mozilla/5.0 (DynamIQ theme importer)' ) );
	if ( is_wp_error( $res ) || 200 !== wp_remote_retrieve_response_code( $res ) ) {
		return null;
	}
	$body = wp_remote_retrieve_body( $res );
	if ( '' === $body ) {
		return null;
	}
	$info = @getimagesizefromstring( $body ); // phpcs:ignore WordPress.PHP.NoSilencedErrors
	if ( ! $info || empty( $info[0] ) || empty( $info[1] ) ) {
		return null;
	}
	$cache[ $url ] = array( (int) $info[0], (int) $info[1] );
	return $cache[ $url ];
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
		$parts    = explode( '/', $slug ); // "promo/erp-system": a child page of /promo/
		$args     = array(
			'post_type'    => 'page',
			'post_status'  => 'publish',
			'post_name'    => end( $parts ),
			'post_excerpt' => $data['description'],
			'post_content' => $data['content'],
		);
		if ( ! $existing && count( $parts ) > 1 ) {
			$parent = get_page_by_path( implode( '/', array_slice( $parts, 0, -1 ) ) );
			if ( ! $parent ) { // the live /promo/ itself is not a public page: a private parent keeps the child URLs
				$parent_id = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'private', 'post_title' => ucwords( str_replace( '-', ' ', $parts[0] ) ), 'post_name' => $parts[0] ) );
			} else {
				$parent_id = $parent->ID;
			}
			if ( $parent_id && ! is_wp_error( $parent_id ) ) {
				$args['post_parent'] = (int) $parent_id;
			}
		}
		$prev_tpl = $existing ? get_post_meta( $existing->ID, '_wp_page_template', true ) : '';
		if ( $existing ) {
			/* An existing page keeps its own title (it feeds <title>, Yoast and the menus);
			   the article headline goes to _dq_landing_h1 and the template shows that.
			   The one exception is the retired partner tier: the review asked for "Premier
			   Partner" everywhere (item F5), and the title feeds the breadcrumb and the
			   image alt text as well as <title>. */
			$fixed_title = dq_landing_fix_copy( $existing->post_title );
			if ( $fixed_title !== $existing->post_title ) {
				$args['post_title'] = $fixed_title;
			}
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
		/* Keep the old page's exact <title> (it ranks); an editor-set title tag is never overwritten. */
		if ( ! empty( $data['seo_title'] ) && ! get_post_meta( $id, '_dq_seo_title', true ) ) {
			update_post_meta( $id, '_dq_seo_title', $data['seo_title'] );
		}
		if ( ! $data['hero_image'] ) {
			delete_post_meta( $id, '_dq_hero_image' ); // only the importer writes this; drop a stale value from an earlier parse
		} else {
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
		/* A slug template (page-our-services.php) is a purpose-built layout for that import. It must win
		   even though the importer stored page-landing.php in _wp_page_template (which WordPress
		   resolves ahead of page-{slug}.php in the hierarchy). */
		$by_slug = locate_template( 'page-' . get_queried_object()->post_name . '.php' );
		if ( $by_slug ) {
			return $by_slug;
		}
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
