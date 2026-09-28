<?php
/**
 * Author profile pages (/author/<nicename>/): the "Author Page" design — photo hero with a
 * circular portrait, name + LinkedIn, About beside Recognitions / Education cards, Areas of
 * Expertise icon cards, and two "Other Relevant Professional Information" cards.
 *
 * Everything is edited on Users → Profile. Fields are plain text with a light line syntax so
 * the SEO team can paste copy without touching code:
 *   Recognitions / Education:  "Title | detail"  → bold title, en-dash detail
 *                              "Label:"          → a bold label line
 *                              "- text"          → a bullet under the line above
 *   Professional information:  "## Card heading" starts a card, "- text" a bullet inside it
 *   Areas of expertise:        one per line (an icon is picked from the wording)
 *
 * @package dynamiqes
 */

/** LinkedIn as a native contact method (shows under Contact Info on the profile screen). */
add_filter( 'user_contactmethods', function ( $methods ) {
	$methods['linkedin'] = __( 'LinkedIn URL', 'dynamiqes' );
	return $methods;
} );

/** Field definitions: key => [ label, type, help ]. */
function dq_author_fields() {
	return array(
		'dq_role'         => array( __( 'Role / position', 'dynamiqes' ), 'text', __( 'Shown in search results and social previews, e.g. "Business Leader for SAP Business One".', 'dynamiqes' ) ),
		'dq_photo'        => array( __( 'Profile photo URL', 'dynamiqes' ), 'url', __( 'Media Library URL of the portrait. Square works best; it is shown in a circle. Empty = the user avatar.', 'dynamiqes' ) ),
		'dq_hero_bg'      => array( __( 'Hero background photo URL', 'dynamiqes' ), 'url', __( 'Wide photo behind the portrait. Empty = the theme default.', 'dynamiqes' ) ),
		'dq_recognitions' => array( __( 'Formal industry recognitions', 'dynamiqes' ), 'textarea', __( 'One per line. "Title | detail" for a certificate, "Label:" for a bold label, "- text" for a bullet.', 'dynamiqes' ) ),
		'dq_education'    => array( __( 'Education & background', 'dynamiqes' ), 'textarea', __( 'Same syntax, e.g. "Studied at Xavier University | Ateneo de Cagayan".', 'dynamiqes' ) ),
		'dq_expertise'    => array( __( 'Areas of expertise', 'dynamiqes' ), 'textarea', __( 'One per line. Three to six items look best.', 'dynamiqes' ) ),
		'dq_professional' => array( __( 'Other relevant professional information', 'dynamiqes' ), 'textarea', __( '"## Heading" starts a card (e.g. Professional Experience & Affiliations), "- text" adds a bullet to it.', 'dynamiqes' ) ),
	);
}

/** Profile screen: the fields, under their own heading. */
function dq_author_profile_fields( $user ) {
	if ( ! current_user_can( 'edit_user', $user->ID ) ) {
		return;
	}
	echo '<h2>' . esc_html__( 'Author page (DynamIQ theme)', 'dynamiqes' ) . '</h2>';
	echo '<p class="description">' . esc_html__( 'Fills the public author page. The "Biographical Info" above is the About text. Leave everything empty to keep the author page hidden from search engines.', 'dynamiqes' ) . '</p>';
	echo '<table class="form-table" role="presentation">';
	foreach ( dq_author_fields() as $key => $f ) {
		$val = get_user_meta( $user->ID, $key, true );
		echo '<tr><th><label for="' . esc_attr( $key ) . '">' . esc_html( $f[0] ) . '</label></th><td>';
		if ( 'textarea' === $f[1] ) {
			echo '<textarea name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" rows="6" class="large-text code">' . esc_textarea( $val ) . '</textarea>';
		} else {
			echo '<input type="' . ( 'url' === $f[1] ? 'url' : 'text' ) . '" name="' . esc_attr( $key ) . '" id="' . esc_attr( $key ) . '" value="' . esc_attr( $val ) . '" class="regular-text">';
		}
		echo '<p class="description">' . esc_html( $f[2] ) . '</p></td></tr>';
	}
	echo '</table>';
}
add_action( 'show_user_profile', 'dq_author_profile_fields' );
add_action( 'edit_user_profile', 'dq_author_profile_fields' );

function dq_author_profile_save( $user_id ) {
	if ( ! current_user_can( 'edit_user', $user_id ) ) {
		return;
	}
	foreach ( dq_author_fields() as $key => $f ) {
		if ( ! isset( $_POST[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Missing -- core verifies the profile nonce
			continue;
		}
		$raw = wp_unslash( $_POST[ $key ] ); // phpcs:ignore WordPress.Security.NonceVerification.Missing
		if ( 'url' === $f[1] ) {
			$val = esc_url_raw( $raw );
		} elseif ( 'textarea' === $f[1] ) {
			$val = sanitize_textarea_field( $raw );
		} else {
			$val = sanitize_text_field( $raw );
		}
		if ( '' === $val ) {
			delete_user_meta( $user_id, $key );
		} else {
			update_user_meta( $user_id, $key, $val );
		}
	}
}
add_action( 'personal_options_update', 'dq_author_profile_save' );
add_action( 'edit_user_profile_update', 'dq_author_profile_save' );

/** Parse "Title | detail" / "Label:" / "- bullet" lines into blocks for a card. */
function dq_author_parse_lines( $text ) {
	$out = array();
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $text ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		if ( 0 === strpos( $line, '- ' ) ) {
			$n = count( $out );
			if ( $n ) {
				$out[ $n - 1 ]['items'][] = substr( $line, 2 );
			} else {
				$out[] = array( 'title' => '', 'detail' => '', 'items' => array( substr( $line, 2 ) ) );
			}
			continue;
		}
		$parts = array_map( 'trim', explode( '|', $line, 2 ) );
		$out[] = array( 'title' => $parts[0], 'detail' => isset( $parts[1] ) ? $parts[1] : '', 'items' => array() );
	}
	return $out;
}

/** Parse "## Heading" + "- bullet" lines into cards. */
function dq_author_parse_cards( $text ) {
	$cards = array();
	$cur   = null;
	foreach ( preg_split( '/\r\n|\r|\n/', (string) $text ) as $line ) {
		$line = trim( $line );
		if ( '' === $line ) {
			continue;
		}
		if ( preg_match( '/^##\s*(.+)$/', $line, $m ) ) {
			if ( $cur ) {
				$cards[] = $cur;
			}
			$cur = array( 'title' => $m[1], 'items' => array(), 'text' => array() );
			continue;
		}
		if ( ! $cur ) {
			$cur = array( 'title' => '', 'items' => array(), 'text' => array() );
		}
		if ( 0 === strpos( $line, '- ' ) ) {
			$cur['items'][] = substr( $line, 2 );
		} else {
			$cur['text'][] = $line;
		}
	}
	if ( $cur ) {
		$cards[] = $cur;
	}
	return $cards;
}

/** Everything the author template needs, normalised. */
function dq_author_profile( $user_id ) {
	$user = get_userdata( $user_id );
	if ( ! $user ) {
		return null;
	}
	$photo = get_user_meta( $user_id, 'dq_photo', true );
	$bg    = get_user_meta( $user_id, 'dq_hero_bg', true );
	$about = preg_split( '/(\r\n|\r|\n)\s*(\r\n|\r|\n)/', (string) get_the_author_meta( 'description', $user_id ) );
	return array(
		'id'           => $user_id,
		'name'         => $user->display_name,
		'role'         => get_user_meta( $user_id, 'dq_role', true ),
		'linkedin'     => get_user_meta( $user_id, 'linkedin', true ),
		'photo'        => $photo ? $photo : get_avatar_url( $user_id, array( 'size' => 600 ) ),
		'hero_bg'      => $bg ? $bg : DQ_URI . '/assets/video/hero-poster.jpg',
		'about'        => array_values( array_filter( array_map( 'trim', (array) $about ) ) ),
		'recognitions' => dq_author_parse_lines( get_user_meta( $user_id, 'dq_recognitions', true ) ),
		'education'    => dq_author_parse_lines( get_user_meta( $user_id, 'dq_education', true ) ),
		'expertise'    => array_values( array_filter( array_map( 'trim', preg_split( '/\r\n|\r|\n/', (string) get_user_meta( $user_id, 'dq_expertise', true ) ) ) ) ),
		'professional' => dq_author_parse_cards( get_user_meta( $user_id, 'dq_professional', true ) ),
	);
}

/** An author "has a page" once a bio or any profile field is filled. */
function dq_author_has_profile( $user_id ) {
	if ( '' !== trim( (string) get_the_author_meta( 'description', $user_id ) ) ) {
		return true;
	}
	foreach ( array_keys( dq_author_fields() ) as $key ) {
		if ( '' !== trim( (string) get_user_meta( $user_id, $key, true ) ) ) {
			return true;
		}
	}
	return false;
}

/**
 * The "By Author" byline for the current post's meta row (single hero, Blogs / News & Events
 * cards). Always a link to the author's page: author.php renders any account (sections whose
 * fields are empty stay hidden), and inc/seo.php keeps a page with no profile out of the index.
 */
/**
 * The site's default author (Customizer → Authors → Default author), used only for a post that
 * has no author of its own. Unset, there is none: every post is credited to its own account
 * (user decision 2026-09-23: the site already has its authors; nobody is credited by default).
 */
function dq_default_author_id() {
	$id = (int) get_theme_mod( 'dq_default_author', 0 );
	return ( $id && get_userdata( $id ) ) ? $id : 0;
}

/** Who a post is credited to: its own author when that account still exists, else the default author. */
function dq_post_author_id() {
	$own = (int) get_post_field( 'post_author', get_the_ID() );
	if ( $own && get_userdata( $own ) ) {
		return $own;
	}
	return dq_default_author_id();
}

/** New or re-saved posts with no author get the default author, so the archive counts line up too. */
add_filter( 'wp_insert_post_data', function ( $data ) {
	if ( 'post' === $data['post_type'] && empty( $data['post_author'] ) && function_exists( 'dq_default_author_id' ) ) {
		$id = dq_default_author_id();
		if ( $id ) {
			$data['post_author'] = $id;
		}
	}
	return $data;
} );

function dq_post_byline() {
	$user_id = dq_post_author_id();
	$name    = $user_id ? get_the_author_meta( 'display_name', $user_id ) : '';
	if ( ! $user_id || '' === trim( (string) $name ) ) {
		return;
	}
	/* translators: %s: author name */
	$label = sprintf( __( 'By %s', 'dynamiqes' ), $name );
	echo '<a class="news-date post-author" href="' . esc_url( get_author_posts_url( $user_id ) ) . '" rel="author">' . esc_html( $label ) . '</a>';
}

/** Author pages with a profile are real content: let them be indexed (inc/seo.php noindexes bare author archives). */
add_filter( 'wp_robots', function ( $robots ) {
	if ( is_author() && dq_author_has_profile( get_queried_object_id() ) ) {
		unset( $robots['noindex'] );
		$robots['index']  = true;
		$robots['follow'] = true;
	}
	return $robots;
}, 20 );

add_filter( 'body_class', function ( $classes ) {
	if ( is_author() ) {
		$classes[] = 'sub-page';
		$classes[] = 'author-page';
	}
	return $classes;
} );

/** Render one recognitions / education card body. */
function dq_author_lines_html( array $lines ) {
	$h = '';
	foreach ( $lines as $l ) {
		if ( '' !== $l['title'] ) {
			$h .= '<p class="author-line"><strong>' . esc_html( $l['title'] ) . '</strong>';
			if ( '' !== $l['detail'] ) {
				$h .= ' <span class="author-line-detail">&ndash; ' . esc_html( $l['detail'] ) . '</span>';
			}
			$h .= '</p>';
		}
		if ( $l['items'] ) {
			$h .= '<ul class="author-bullets">';
			foreach ( $l['items'] as $it ) {
				$h .= '<li>' . esc_html( $it ) . '</li>';
			}
			$h .= '</ul>';
		}
	}
	return $h;
}

/** Icon for an expertise card, picked from its wording (reuses the landing-page icon set). */
function dq_author_expertise_icon( $title ) {
	/* The three icons drawn in the Author Page design (assets/author/, cut from the design PDF)
	   cover the sales / leadership / consulting wording; other topics fall back to product icons. */
	$design = array(
		'/sales|channel|partner|ecosystem|revenue/i'          => 'expertise-sales',
		'/leader|manage|team|director/i'                       => 'expertise-leadership',
		'/consult|digital|transform|strateg|advis/i'           => 'expertise-consulting',
	);
	foreach ( $design as $rx => $icon ) {
		if ( preg_match( $rx, (string) $title ) ) {
			return DQ_URI . '/assets/author/' . $icon . '.png';
		}
	}
	$map = array(
		'/erp|sap|implement|system/i'                          => 'sect-six-integrates',
		'/financ|account|audit|tax/i'                          => 'sect-five-accounting',
		'/supply|logistic|inventory|warehouse|distribut/i'     => 'sect-seven-distribution',
		'/market|brand|content|seo/i'                          => 'sect-five-project',
		'/develop|engineer|software|code|technolog/i'          => 'sect-seven-engineering',
	);
	foreach ( $map as $rx => $icon ) {
		if ( preg_match( $rx, (string) $title ) ) {
			return DQ_URI . '/assets/products/icons/' . $icon . '.png';
		}
	}
	return DQ_URI . '/assets/products/icons/sect-four-complete.png';
}
