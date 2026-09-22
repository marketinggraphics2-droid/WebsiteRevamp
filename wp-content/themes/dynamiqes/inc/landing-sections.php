<?php
/**
 * Landing-page section builder.
 *
 * The SEO/SEM landing pages are imported from the live site as one flat stream of headings,
 * paragraphs, lists, images and FAQ accordions (inc/landing-import.php) and were rendered
 * inside a single 80ch article column. The review's verdict on every one of them was "this
 * has no proper design … requires a design that covers all content and sections" (item F1).
 *
 * This groups that stream at its H2 boundaries and gives each group a designed section,
 * picked from the group's own shape:
 *
 *   split    H2 + copy + one image         → image beside copy, alternating sides
 *   cards    H2 + H3s that carry copy       → the product pages' card grid
 *   signals  H2 + H3s with no copy          → trust signals with icons (also item F6: those
 *                                             four credentials were four bare stacked H3s)
 *   list     H2 + a bullet list             → a checklist panel
 *   faq      an imported accordion          → the product pages' FAQ treatment
 *   cta      the live page's own CTA block  → a CTA panel carrying the enquiry form
 *                                             (items F2 and F3)
 *   copy     anything else                  → prose on the shared measure
 *
 * The heading order and every word of copy are preserved — only the presentation changes, so
 * the pages keep the outline they rank on.
 *
 * @package dynamiqes
 */

defined( 'ABSPATH' ) || exit;

/** A DOM node's inner HTML. */
function dq_lp_inner_html( DOMNode $node ) {
	$html = '';
	foreach ( $node->childNodes as $child ) {
		$html .= $node->ownerDocument->saveHTML( $child );
	}
	return $html;
}

/** A DOM node's own markup. */
function dq_lp_outer_html( DOMNode $node ) {
	return $node->ownerDocument->saveHTML( $node );
}

/** An empty section group. */
function dq_lp_new_group() {
	return array(
		'title' => '',
		'intro' => array(),
		'media' => '',
		'items' => array(), // each: [ title, text[], list ]
		'lists' => array(),
		'faq'   => '',
		'cta'   => '',
		'extra' => array(),
		'button' => array(), // the section's own button, from the importer's <p class="landing-btn">
		'table'  => array(), // data table rows (first = header) — sections added by inc/landing-additions.php
		'kicker' => '',
		'closing' => array(),
	);
}

/**
 * Split imported content into groups: one per H2, a lede group for anything before the first
 * H2, and a group of its own for each CTA block.
 *
 * @param string $html Stored post content.
 * @return array<int, array>
 */
function dq_lp_parse_groups( $html ) {
	$html = trim( (string) $html );
	if ( '' === $html || ! class_exists( 'DOMDocument' ) ) {
		return array();
	}
	$doc = new DOMDocument();
	libxml_use_internal_errors( true );
	$doc->loadHTML( '<?xml encoding="UTF-8"><div id="dq-lp">' . $html . '</div>' );
	libxml_clear_errors();
	$xp   = new DOMXPath( $doc );
	$root = $xp->query( '//div[@id="dq-lp"]' )->item( 0 );
	if ( ! $root ) {
		return array();
	}

	$groups  = array();
	$group   = dq_lp_new_group();
	$pending = null; // the H3 item currently being filled

	foreach ( $root->childNodes as $node ) {
		if ( XML_ELEMENT_NODE !== $node->nodeType ) {
			continue;
		}
		$name = strtolower( $node->nodeName );
		$cls  = strtolower( $node instanceof DOMElement ? $node->getAttribute( 'class' ) : '' );

		/* ── the live page's own CTA block: its own section, wherever it falls ── */
		if ( false !== strpos( $cls, 'landing-cta' ) ) {
			if ( $pending ) {
				$group['items'][] = $pending;
				$pending          = null;
			}
			$groups[] = $group;
			$cta      = dq_lp_new_group();
			$cta['cta'] = dq_lp_inner_html( $node );
			$groups[] = $cta;
			$group    = dq_lp_new_group();
			continue;
		}
		if ( false !== strpos( $cls, 'faq-list' ) ) {
			$group['faq'] = dq_lp_inner_html( $node );
			continue;
		}
		if ( 'h1' === $name || 'h2' === $name ) {
			if ( $pending ) {
				$group['items'][] = $pending;
				$pending          = null;
			}
			$groups[]       = $group;
			$group          = dq_lp_new_group();
			$group['title'] = trim( wp_strip_all_tags( dq_lp_inner_html( $node ) ) );
			continue;
		}
		if ( 'h3' === $name || 'h4' === $name ) {
			if ( $pending ) {
				$group['items'][] = $pending;
			}
			$pending = array(
				'title' => trim( wp_strip_all_tags( dq_lp_inner_html( $node ) ) ),
				/* the importer parks the live card art on the heading: data-icon for a ~60px
				   icon, data-photo for full-size art that deserves its own row */
				'icon'  => $node instanceof DOMElement ? trim( $node->getAttribute( 'data-icon' ) ) : '',
				'photo' => $node instanceof DOMElement && dq_main_image_ok( $node->getAttribute( 'data-photo' ) ) ? trim( $node->getAttribute( 'data-photo' ) ) : '',
				'text'  => array(),
				'list'  => '',
			);
			continue;
		}
		if ( 'figure' === $name || 'img' === $name ) {
			$img = 'img' === $name ? $node : $xp->query( './/img', $node )->item( 0 );
			if ( $img && false !== strpos( $cls, 'is-seal' ) ) {
				$group['extra'][] = dq_lp_outer_html( $node ); // accreditation seals: a row under the copy, never the section photo
				continue;
			}
			if ( $img && ! dq_main_image_ok( $img instanceof DOMElement ? $img->getAttribute( 'src' ) : '' ) ) {
				continue; // icon, logo or check-mark art is never a section image
			}
			if ( $img ) {
				if ( '' === $group['media'] ) {
					$group['media'] = dq_lp_outer_html( $img );
				} else {
					$group['extra'][] = dq_lp_outer_html( $img );
				}
			}
			continue;
		}
		if ( 'ul' === $name || 'ol' === $name ) {
			if ( $pending ) {
				$pending['list'] = dq_lp_inner_html( $node );
			} else {
				$group['lists'][] = array( 'tag' => $name, 'items' => dq_lp_inner_html( $node ) );
			}
			continue;
		}
		if ( 'p' === $name ) {
			if ( false !== strpos( $cls, 'landing-btn' ) ) {
				$a = $xp->query( './/a', $node )->item( 0 );
				if ( $a instanceof DOMElement ) {
					$group['button'] = array( 'label' => trim( wp_strip_all_tags( dq_lp_inner_html( $a ) ) ), 'href' => trim( $a->getAttribute( 'href' ) ) );
				}
				continue;
			}
			$copy = trim( dq_lp_inner_html( $node ) );
			if ( '' === trim( wp_strip_all_tags( $copy ) ) ) {
				continue;
			}
			if ( $pending ) {
				$pending['text'][] = $copy;
			} else {
				$group['intro'][] = $copy;
			}
			continue;
		}
		$group['extra'][] = dq_lp_outer_html( $node );
	}
	if ( $pending ) {
		$group['items'][] = $pending;
	}
	$groups[] = $group;

	/* drop the empties left by the flushes above */
	$groups = array_values( array_filter( $groups, function ( $g ) {
		return '' !== $g['title'] || $g['intro'] || $g['items'] || $g['lists']
			|| '' !== $g['media'] || '' !== $g['faq'] || '' !== $g['cta'] || $g['extra'] || $g['button'];
	} ) );

	/* The old pages put a section's illustration above its H2, so it arrives as a group
	   holding nothing but an image — which would render as a split section with an empty
	   copy column. Hand it to the section it introduces. */
	$merged = array();
	$carry  = '';
	foreach ( $groups as $g ) {
		$media_only = '' !== $g['media'] && '' === $g['title'] && ! $g['intro'] && ! $g['items']
			&& ! $g['lists'] && '' === $g['faq'] && '' === $g['cta'] && ! $g['extra'];
		if ( $media_only ) {
			$carry = '' === $carry ? $g['media'] : $carry; // keep the first; a second would only crowd the section
			continue;
		}
		if ( '' !== $carry && '' === $g['media'] && '' === $g['cta'] ) {
			$g['media'] = $carry;
			$carry      = '';
		}
		$merged[] = $g;
	}
	if ( '' !== $carry && $merged ) {
		$last = count( $merged ) - 1;
		if ( '' === $merged[ $last ]['media'] && '' === $merged[ $last ]['cta'] ) {
			$merged[ $last ]['media'] = $carry;
		}
	}

	return $merged;
}

/**
 * Is this group one of the live pages' client-logo bands?
 *
 * The importer skips logo images by design, so the promo pages' logo strips arrived as a
 * lone heading — "Trusted by Companies Across Industries", "See Why Top Businesses Choose
 * SAP B1 and DynamIQ" — with nothing under them (review item F8). Recognise them by that
 * heading and fill them from the theme's own client logos.
 *
 * @param array $g Group.
 * @return bool
 */
function dq_lp_is_logo_band( array $g ) {
	if ( $g['items'] || $g['lists'] || '' !== $g['faq'] || '' !== $g['cta'] ) {
		return false;
	}
	return (bool) preg_match( '/\b(trusted by|top businesses|companies across|our clients|client logos)\b/i', $g['title'] );
}

/** Client-logo strip, using the campaign pages' own logo set in the home marquee's markup. */
function dq_lp_logos() {
	$logos = function_exists( 'dq_sem_logos' ) ? dq_sem_logos() : dq_trust_logos();
	$out   = '<div class="trust-marq"><div class="trust-track">';
	foreach ( array( false, true ) as $dup ) {
		foreach ( $logos as $l ) {
			$out .= '<img class="no-lazyload skip-lazy" src="' . esc_url( dq_asset( $l[1] ) ) . '" alt="' . ( $dup ? '' : esc_attr( $l[0] ) ) . '"'
				. ( $dup ? ' aria-hidden="true"' : '' ) . ' loading="eager" height="48">'; // eager: a lazy second copy never loads off-screen and the loop shows a hole
		}
	}
	return $out . '</div></div>';
}

/** Does this heading name a FAQ section? */
function dq_lp_is_faq_title( $title ) {
	return (bool) preg_match( '/frequently asked question|(^|\s)faq(s)?(\s|$|\?|\))/i', (string) $title );
}

/** Which section variant a group renders as. */
function dq_lp_variant( array $g ) {
	if ( '' !== $g['cta'] ) {
		return 'cta';
	}
	if ( '' !== $g['faq'] ) {
		return 'faq';
	}
	if ( $g['items'] ) {
		/* A "Frequently Asked Questions" heading always means an accordion, whatever the old
		   page's markup was: four of the landing pages spell their FAQ out as headings and
		   paragraphs, which would otherwise render as a grid of cards. */
		if ( dq_lp_is_faq_title( $g['title'] ) ) {
			return 'faq-items';
		}
		/* Items whose art is a full-size photo get a row each — heading, copy and photo —
		   which is how the live page lays them out. */
		foreach ( $g['items'] as $it ) {
			if ( ! empty( $it['photo'] ) ) {
				return 'photo-rows';
			}
		}
		foreach ( $g['items'] as $it ) {
			if ( $it['text'] || '' !== $it['list'] ) {
				return 'cards';
			}
		}
		return 'signals';
	}
	if ( dq_lp_is_logo_band( $g ) ) {
		return 'logos';
	}
	if ( '' !== $g['media'] ) {
		return 'split';
	}
	if ( $g['lists'] ) {
		return 'list';
	}
	return 'copy';
}

/** Section head: eyebrow, H2 and intro on one shared measure (as on the product pages). */
function dq_lp_head( array $g, $eyebrow = '', $centred = false ) {
	if ( '' === $g['title'] && ! $g['intro'] ) {
		return '';
	}
	$out = '<div class="lp-head' . ( $centred ? ' is-centred' : '' ) . '"' . dq_reveal_attr() . '>';
	/* no eyebrow: the live pages carry none, and the sub-pages follow the live text (the parameter stays for callers) */
	if ( '' !== $g['title'] ) {
		$out .= '<h2>' . esc_html( $g['title'] ) . '</h2>';
	}
	foreach ( $g['intro'] as $p ) {
		$out .= '<p>' . dq_inline_html( $p ) . '</p>';
	}
	return $out . '</div>';
}

/**
 * A product-page icon for a card by its title. Used where the old page gave a card a full-size
 * illustration (523 x 359 px on "Streamline Inventory Management", SAP B1 Provider) or nothing
 * at all: SEO Hacker asked for icons on those cards like the product pages carry (09-14, rows
 * 75 and 112). First keyword match wins; the last entry is the fallback.
 *
 * @param string $title Card heading.
 * @return string Theme-relative icon path.
 */
function dq_lp_icon_for( $title ) {
	$map = array(
		'/inventory|stock|warehouse/i'                    => 'sect-five-inventory',
		'/customi[sz]|configur|flexib|tailor/i'           => 'sect-six-customizable',
		'/report|dashboard|analytic|intelligence|insight/i' => 'sect-five-business',
		'/pick|pack|deliver|distribut|logistic|shipping/i' => 'sect-seven-distribution',
		'/integrat|erp-integrated|connect/i'              => 'sect-six-integrates',
		'/label|automat/i'                                => 'sect-four-simple',
		'/qr|barcode|scan|track/i'                        => 'sect-four-data',
		'/account|financ|ledger|book/i'                   => 'sect-five-accounting',
		'/sales|customer|crm/i'                           => 'sect-five-sales',
		'/purchas|procure|supplier|vendor/i'              => 'sect-five-purchasing',
		'/production|manufactur|bom|shop floor/i'         => 'sect-five-production',
		'/project|resource|budget/i'                      => 'sect-five-project',
		'/mobil|cloud|remote|anywhere|access/i'           => 'sect-five-mobility',
		'/scal|grow|expand/i'                             => 'sect-six-scalable',
		'/complian|tax|bir|regulat|audit/i'               => 'sect-four-compliance',
		'/secur|data|backup|protect/i'                    => 'sect-four-data',
		'/simple|easy|user|intuitive|friendly/i'          => 'sect-four-easy',
		'/manage|admin|control|efficien/i'                => 'sect-five-management',
		'/support|service|help|train/i'                   => 'sect-eight-support',
		'/./'                                             => 'sect-four-complete',
	);
	foreach ( $map as $rx => $icon ) {
		if ( preg_match( $rx, (string) $title ) ) {
			return 'assets/products/icons/' . $icon . '.png';
		}
	}
	return 'assets/products/icons/sect-four-complete.png';
}

/** The H3 groups as cards. */
function dq_lp_cards( array $items ) {
	$out = '<div class="feature-grid">';
	foreach ( $items as $i => $it ) {
		$out .= '<article class="feature-group"' . dq_reveal_attr( '', min( $i, 5 ) * 60 ) . '>';
		if ( ! empty( $it['icon'] ) ) {
			$out .= '<span class="feature-icon">' . dq_product_item_icon( $it ) . '</span>';
		}
		$out .= '<h3>' . esc_html( $it['title'] ) . '</h3>';
		foreach ( $it['text'] as $p ) {
			$out .= '<p>' . dq_inline_html( $p ) . '</p>';
		}
		if ( '' !== $it['list'] ) {
			$out .= '<ul>' . wp_kses_post( $it['list'] ) . '</ul>';
		}
		$out .= '</article>';
	}
	return $out . '</div>';
}

/** Title-only H3 groups as trust signals with icons (the product pages' component). */
function dq_lp_signals( array $items ) {
	$out = '<ul class="trust-grid">';
	foreach ( $items as $i => $it ) {
		$out .= '<li class="trust-signal"' . dq_reveal_attr( '', min( $i, 5 ) * 60 ) . '>';
		$out .= '<span class="trust-badge">' . dq_product_item_icon( $it ) . '</span>';
		$out .= '<h3>' . esc_html( $it['title'] ) . '</h3>';
		$out .= '</li>';
	}
	return $out . '</ul>';
}

/**
 * H3 items whose art is a full-size photo: one row each, copy beside the photo, sides
 * alternating. Squeezing a 638x471 photograph into a 56px icon badge is what this replaces.
 */
function dq_lp_photo_rows( array $items ) {
	$out = '<div class="lp-photo-rows">';
	foreach ( $items as $i => $it ) {
		$flip = ( $i % 2 ) ? ' is-flipped' : '';
		$out .= '<div class="lp-photo-row' . $flip . '"' . dq_reveal_attr() . '>';
		$out .= '<div class="lp-photo-copy"><h3>' . esc_html( $it['title'] ) . '</h3>';
		foreach ( $it['text'] as $para ) {
			$out .= '<p>' . dq_inline_html( $para ) . '</p>';
		}
		if ( '' !== $it['list'] ) {
			$out .= '<ul class="lp-checklist">' . wp_kses_post( $it['list'] ) . '</ul>';
		}
		$out .= '</div>';
		$out .= '<figure class="lp-photo-media"><img src="' . esc_url( $it['photo'] ) . '" alt="' . esc_attr( $it['title'] ) . '" loading="lazy" decoding="async"></figure>';
		$out .= '</div>';
	}
	return $out . '</div>';
}

/**
 * H3 items as an accordion, for the FAQ sections whose source markup was plain headings and
 * paragraphs rather than an accordion widget. Same <details> treatment as the product pages.
 */
function dq_lp_faq_items( array $items ) {
	$out = '<div class="faq-list"' . dq_reveal_attr() . '>';
	foreach ( $items as $it ) {
		$out .= '<details><summary><h3>' . esc_html( $it['title'] ) . '</h3></summary><div class="faq-answer">';
		foreach ( $it['text'] as $para ) {
			$out .= '<p>' . dq_inline_html( $para ) . '</p>';
		}
		if ( '' !== $it['list'] ) {
			$out .= '<ul>' . wp_kses_post( $it['list'] ) . '</ul>';
		}
		$out .= '</div></details>';
	}
	return $out . '</div>';
}

/** Bullet lists as checklists. */
function dq_lp_lists( array $lists ) {
	$out = '';
	foreach ( $lists as $l ) {
		$tag  = 'ol' === $l['tag'] ? 'ol' : 'ul';
		$out .= '<' . $tag . ' class="lp-checklist"' . dq_reveal_attr() . '>' . wp_kses_post( $l['items'] ) . '</' . $tag . '>';
	}
	return $out;
}

/**
 * The live page's own CTA block, as a panel that carries the enquiry form.
 *
 * The block holds the original kicker, heading and copy, which the review asked us to keep
 * rather than replace with the shared "We'd like to hear from you" band (item F3), plus the
 * form the review found missing (item F2). The two CTA links the review said could stay — the
 * free business analysis and the phone number — sit under the form.
 *
 * @param string $cta_html Inner HTML of the imported .landing-cta block.
 * @param int    $index    Nth CTA on the page (keeps form ids unique).
 * @return string
 */
function dq_lp_cta_section( $cta_html, $index = 0 ) {
	$kicker  = '';
	$heading = '';
	$copy    = array();

	if ( class_exists( 'DOMDocument' ) ) {
		$doc = new DOMDocument();
		libxml_use_internal_errors( true );
		$doc->loadHTML( '<?xml encoding="UTF-8"><div id="dq-cta">' . $cta_html . '</div>' );
		libxml_clear_errors();
		$xp   = new DOMXPath( $doc );
		$root = $xp->query( '//div[@id="dq-cta"]' )->item( 0 );
		if ( $root ) {
			foreach ( $root->childNodes as $node ) {
				if ( XML_ELEMENT_NODE !== $node->nodeType ) {
					continue;
				}
				$name = strtolower( $node->nodeName );
				$cls  = strtolower( $node instanceof DOMElement ? $node->getAttribute( 'class' ) : '' );
				$text = trim( wp_strip_all_tags( dq_lp_inner_html( $node ) ) );
				if ( '' === $text || false !== strpos( $cls, 'landing-cta-btn' ) ) {
					continue; // the imported button is replaced by the panel's own links
				}
				if ( 'h3' === $name && '' === $kicker ) {
					$kicker = $text;
				} elseif ( ( 'h2' === $name || 'h1' === $name ) && '' === $heading ) {
					$heading = $text;
				} elseif ( 'p' === $name ) {
					$copy[] = trim( dq_lp_inner_html( $node ) );
				}
			}
		}
	}
	if ( '' === $heading && '' === $kicker && ! $copy ) {
		return '';
	}

	$contact = dq_contact_info();
	ob_start();
	?>
	<section class="cta-section lp-cta">
		<div class="wrap">
			<div class="cta-panel"<?php echo dq_reveal_attr( 'scale' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
				<?php if ( $kicker ) : ?><h3 class="cta-kicker"><?php echo esc_html( $kicker ); ?></h3><?php endif; ?>
				<?php if ( $heading ) : ?><h2><?php echo esc_html( $heading ); ?></h2><?php endif; ?>
				<?php foreach ( $copy as $dq_p ) : ?><p><?php echo dq_inline_html( $dq_p ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p><?php endforeach; ?>
				<div class="cta-form">
					<?php get_template_part( 'template-parts/enquiry-form', null, array( 'id' => 'lpForm-' . (int) $index, 'compact' => true, 'submit' => __( 'SEND ENQUIRY', 'dynamiqes' ) ) ); ?>
				</div>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}

/**
 * Render an imported landing-page body as designed sections.
 *
 * @param string $html    Stored content.
 * @param string $eyebrow Eyebrow for the section heads (usually the page's own label).
 * @return array [ html, has_cta ] — has_cta says the page carried its own CTA block, so the
 *               caller can drop the shared closing band (item F3).
 */
function dq_landing_sections( $html, $eyebrow = '' ) {
	$groups = dq_lp_parse_groups( $html );
	/* The 2026 redesign adds data tables the old pages never had (inc/landing-additions.php). */
	$groups = apply_filters( 'dq_lp_groups', $groups );
	if ( ! $groups ) {
		return array( '', false );
	}
	$out     = '';
	$has_cta = false;
	$ctas    = 0;
	$band    = 0; // alternating section ground, for some vertical rhythm
	$splits  = 0; // alternating image side

	foreach ( $groups as $gi => $g ) {
		$g       = array_merge( dq_lp_new_group(), $g );
		$variant = dq_lp_variant( $g );
		$next    = isset( $groups[ $gi + 1 ] ) ? $groups[ $gi + 1 ] : null;

		if ( ! empty( $g['table'] ) && function_exists( 'dq_lp_table_section' ) ) {
			$alt = ( $band % 2 ) ? ' is-alt' : '';
			$band++;
			$out .= dq_lp_table_section( $g, $alt );
			continue;
		}

		/* Cards whose original art was a full-size illustration rendered as photo tiles; SEO
		   Hacker asked for icon cards like the product pages instead (09-14, rows 75 and 112).
		   Every card in such a group gets a product-page icon matched to its heading, so a card
		   the old page left without art ("Customization") is not the odd one out. */
		if ( 'photo-rows' === $variant ) {
			foreach ( $g['items'] as $k => $it ) {
				$g['items'][ $k ]['photo'] = '';
				if ( empty( $it['icon'] ) ) {
					$g['items'][ $k ]['icon'] = dq_lp_icon_for( $it['title'] );
				}
			}
			$variant = 'cards';
		}

		if ( 'cta' === $variant ) {
			$section = dq_lp_cta_section( $g['cta'], $ctas );
			$ctas++;
			if ( '' !== $section ) {
				$out    .= $section;
				$has_cta = true;
			}
			continue;
		}

		$alt   = ( $band % 2 ) ? ' is-alt' : '';
		$band++;

		/* Accreditation section: copy whose only art is the seals (BIR, Trusted Brand on the two SEM
		   provider pages). The seals stand in their own panel beside the copy, each with its name,
		   rather than in a row under the last paragraph (SEO Hacker, 09-14 final round). */
		if ( $g['extra'] && ! $g['items'] && ! $g['lists'] && '' === $g['media'] && '' === $g['faq']
			&& count( $g['extra'] ) === count( preg_grep( '/is-seal/', $g['extra'] ) ) ) {
			$seals = '';
			foreach ( $g['extra'] as $fig ) {
				$src  = preg_match( '/src="([^"]+)"/', $fig, $m ) ? $m[1] : '';
				$name = preg_match( '/alt="([^"]*)"/', $fig, $m ) ? trim( html_entity_decode( $m[1], ENT_QUOTES ) ) : '';
				if ( '' === $src ) {
					continue;
				}
				$seals .= '<figure class="lp-seal"><img src="' . esc_url( $src ) . '" alt="' . esc_attr( $name ) . '" loading="lazy" decoding="async">'
					. ( '' !== $name ? '<figcaption>' . esc_html( $name ) . '</figcaption>' : '' ) . '</figure>';
			}
			$out .= '<section class="lp-section lp-split lp-accredited' . $alt . '"><div class="wrap lp-split-grid">'
				. '<div class="lp-split-copy">' . dq_lp_head( $g, $eyebrow ) . '</div>'
				. '<div class="lp-seal-panel"' . dq_reveal_attr( 'scale' ) . '>' . $seals . '</div>'
				. '</div></section>';
			continue;
		}

		$extra = $g['extra'] ? '<div class="lp-extra">' . wp_kses_post( implode( '', $g['extra'] ) ) . '</div>' : '';

		if ( 'split' === $variant ) {
			$flip = ( $splits % 2 ) ? ' is-flipped' : '';
			$splits++;
			$out .= '<section class="lp-section lp-split' . $alt . $flip . '"><div class="wrap lp-split-grid">'
				. '<div class="lp-split-copy">' . dq_lp_head( $g, $eyebrow ) . dq_lp_lists( $g['lists'] ) . $extra . '</div>'
				. '<figure class="lp-split-media"' . dq_reveal_attr( 'scale' ) . '>' . wp_kses_post( $g['media'] ) . '</figure>'
				. '</div></section>';
			continue;
		}

		$media = '' !== $g['media'] ? '<figure class="lp-inline-media"' . dq_reveal_attr( 'scale' ) . '>' . wp_kses_post( $g['media'] ) . '</figure>' : '';

		if ( 'faq-items' === $variant ) {
			$out .= '<section class="lp-section lp-faq' . $alt . '"><div class="wrap faq-grid">'
				. '<div class="faq-head">' . dq_lp_head( $g, $eyebrow ) . '</div>'
				. dq_lp_faq_items( $g['items'] )
				. '</div></section>';
			continue;
		}
		if ( 'photo-rows' === $variant ) {
			$out .= '<section class="lp-section lp-photos' . $alt . '"><div class="wrap">'
				. dq_lp_head( $g, $eyebrow ) . $media . dq_lp_photo_rows( $g['items'] ) . $extra
				. '</div></section>';
			continue;
		}
		if ( 'cards' === $variant ) {
			$out .= '<section class="lp-section lp-cards' . $alt . '"><div class="wrap">'
				. dq_lp_head( $g, $eyebrow ) . $media . dq_lp_cards( $g['items'] ) . dq_lp_lists( $g['lists'] ) . $extra
				. '</div></section>';
			continue;
		}
		if ( 'signals' === $variant ) {
			$out .= '<section class="lp-section lp-signals' . $alt . '"><div class="wrap">'
				. dq_lp_head( $g, $eyebrow ) . $media . dq_lp_signals( $g['items'] ) . dq_lp_lists( $g['lists'] ) . $extra
				. '</div></section>';
			continue;
		}
		if ( 'logos' === $variant ) {
			$out .= '<section class="lp-section lp-logos' . $alt . '" aria-label="' . esc_attr__( 'Trusted by leading Philippine businesses', 'dynamiqes' ) . '">'
				. '<div class="wrap">' . dq_lp_head( $g, $eyebrow, true ) . '</div>' . dq_lp_logos()
				. '</section>';
			continue;
		}
		if ( 'faq' === $variant ) {
			if ( '' === $g['title'] ) {
				$g['title'] = __( 'Frequently Asked Questions', 'dynamiqes' );
			}
			$out .= '<section class="lp-section lp-faq' . $alt . '"><div class="wrap faq-grid">'
				. '<div class="faq-head">' . dq_lp_head( $g, $eyebrow ) . '</div>'
				. '<div class="faq-list"' . dq_reveal_attr() . '>' . wp_kses_post( $g['faq'] ) . '</div>'
				. '</div></section>';
			continue;
		}
		if ( 'list' === $variant ) {
			$out .= '<section class="lp-section lp-list' . $alt . '"><div class="wrap">'
				. dq_lp_head( $g, $eyebrow ) . $media . '<div class="lp-list-panel">' . dq_lp_lists( $g['lists'] ) . '</div>' . $extra
				. '</div></section>';
			continue;
		}
		if ( dq_lp_is_cta_copy( $g, $next ) ) {
			$out .= dq_lp_cta_copy_section( $g );
			continue;
		}
		$out .= '<section class="lp-section lp-copy' . $alt . '"><div class="wrap">'
			. dq_lp_head( $g, $eyebrow ) . $media . dq_lp_lists( $g['lists'] ) . $extra
			. '</div></section>';
	}

	return array( $out, $has_cta );
}

/**
 * Is this copy-only group the page's own call to action? The old pages close each argument with
 * a short "Trust DynamIQ ..." / "Get Started Today!" block, sometimes with a button; the review
 * asked for those to get the CTA design rather than reading as one more paragraph (item "This is
 * a CTA section"). A group is a CTA when it carries a button, when it is the last group or the one
 * before the FAQ, or when its title reads as an invitation.
 *
 * @param array      $g    Group.
 * @param array|null $next The group that follows, or null.
 * @return bool
 */
function dq_lp_is_cta_copy( array $g, $next ) {
	if ( $g['items'] || $g['lists'] || '' !== $g['media'] || '' !== $g['faq'] || '' !== $g['cta'] ) {
		return false;
	}
	if ( ! empty( $g['button'] ) ) {
		return true;
	}
	if ( count( $g['intro'] ) > 3 || '' === $g['title'] ) {
		return false;
	}
	if ( null === $next ) {
		return true;
	}
	$nv = dq_lp_variant( $next );
	if ( 'faq' === $nv || 'faq-items' === $nv ) {
		return true;
	}
	return '!' === substr( trim( $g['title'] ), -1 )
		|| (bool) preg_match( '/^(trust |get started|grow with|implement |enhance |access |streamline your|empower )/i', $g['title'] );
}

/** The CTA panel for a copy group: heading, copy, the page's own button (or the enquiry link). */
function dq_lp_cta_copy_section( array $g ) {
	$label = ! empty( $g['button']['label'] ) ? $g['button']['label'] : __( 'Get Your Free Business Analysis', 'dynamiqes' );
	$href  = ! empty( $g['button']['href'] ) ? $g['button']['href'] : '';
	$live  = function_exists( 'dq_landing_source_base' ) ? dq_landing_source_base() : '';
	if ( '' !== $href && '' !== $live && 0 === strpos( $href, $live ) ) {
		$href = home_url( substr( $href, strlen( $live ) ) ); // the old page's destination, on this site
	}
	if ( '' === $href || 0 === strpos( $href, '#' ) ) {
		$href = dq_book_demo_url();
	}
	ob_start();
	?>
	<section class="cta-section lp-cta-copy">
		<div class="wrap">
			<div class="cta-panel"<?php echo dq_reveal_attr( 'scale' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>>
				<?php if ( '' !== $g['title'] ) : ?><h2><?php echo esc_html( $g['title'] ); ?></h2><?php endif; ?>
				<?php foreach ( $g['intro'] as $dq_p ) : ?><p><?php echo dq_inline_html( $dq_p ); // phpcs:ignore WordPress.Security.EscapeOutput ?></p><?php endforeach; ?>
				<div class="dynamiq-cta"><a href="<?php echo esc_url( $href ); ?>"><?php echo esc_html( $label ); ?> <span class="arr" aria-hidden="true">→</span></a></div>
			</div>
		</div>
	</section>
	<?php
	return ob_get_clean();
}
