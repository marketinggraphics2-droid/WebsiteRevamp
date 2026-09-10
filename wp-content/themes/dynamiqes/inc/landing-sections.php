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
				'text'  => array(),
				'list'  => '',
			);
			continue;
		}
		if ( 'figure' === $name || 'img' === $name ) {
			$img = 'img' === $name ? $node : $xp->query( './/img', $node )->item( 0 );
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
			|| '' !== $g['media'] || '' !== $g['faq'] || '' !== $g['cta'] || $g['extra'];
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

/** Client-logo strip, reusing the home page's marquee. */
function dq_lp_logos() {
	$out = '<div class="trust-marq"><div class="trust-track">';
	foreach ( array( false, true ) as $dup ) {
		foreach ( dq_trust_logos() as $l ) {
			$out .= '<img src="' . esc_url( dq_asset( $l[1] ) ) . '" alt="' . ( $dup ? '' : esc_attr( $l[0] ) ) . '"'
				. ( $dup ? ' aria-hidden="true"' : '' ) . ' loading="lazy" height="40">';
		}
	}
	return $out . '</div></div>';
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
	if ( $eyebrow && '' !== $g['title'] ) {
		$out .= '<span class="eyebrow">' . esc_html( $eyebrow ) . '</span>';
	}
	if ( '' !== $g['title'] ) {
		$out .= '<h2>' . esc_html( $g['title'] ) . '</h2>';
	}
	foreach ( $g['intro'] as $p ) {
		$out .= '<p>' . dq_inline_html( $p ) . '</p>';
	}
	return $out . '</div>';
}

/** The H3 groups as cards. */
function dq_lp_cards( array $items ) {
	$out = '<div class="feature-grid">';
	foreach ( $items as $i => $it ) {
		$out .= '<article class="feature-group"' . dq_reveal_attr( '', min( $i, 5 ) * 60 ) . '>';
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
		$out .= '<span class="trust-badge">' . dq_trust_icon( $it['title'] ) . '</span>';
		$out .= '<h3>' . esc_html( $it['title'] ) . '</h3>';
		$out .= '</li>';
	}
	return $out . '</ul>';
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
				<div class="lp-cta-links">
					<div class="dynamiq-cta"><a href="<?php echo esc_url( dq_book_demo_url() ); ?>"><?php esc_html_e( 'Get Your Free Business Analysis', 'dynamiqes' ); ?> <span class="arr" aria-hidden="true">&rarr;</span></a></div>
					<?php if ( ! empty( $contact['phone1'] ) ) : ?>
					<p class="cta-phone"><a href="<?php echo esc_attr( dq_tel( $contact['phone1'] ) ); ?>"><?php echo dq_icon_phone(); // phpcs:ignore WordPress.Security.EscapeOutput ?> <?php echo esc_html( $contact['phone1'] ); ?></a></p>
					<?php endif; ?>
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
	if ( ! $groups ) {
		return array( '', false );
	}
	$out     = '';
	$has_cta = false;
	$ctas    = 0;
	$band    = 0; // alternating section ground, for some vertical rhythm
	$splits  = 0; // alternating image side

	foreach ( $groups as $g ) {
		$variant = dq_lp_variant( $g );

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
		$out .= '<section class="lp-section lp-copy' . $alt . '"><div class="wrap">'
			. dq_lp_head( $g, $eyebrow ) . $media . dq_lp_lists( $g['lists'] ) . $extra
			. '</div></section>';
	}

	return array( $out, $has_cta );
}
