<?php
/**
 * Custom post types (products, testimonials, inquiries) and their meta boxes.
 *
 * @package dynamiqes
 */

defined( 'ABSPATH' ) || exit;

add_action( 'init', function () {
	register_post_type( 'dq_product', array(
		'labels'          => array(
			'name'               => __( 'Products', 'dynamiqes' ),
			'singular_name'      => __( 'Product', 'dynamiqes' ),
			'add_new_item'       => __( 'Add New Product', 'dynamiqes' ),
			'edit_item'          => __( 'Edit Product', 'dynamiqes' ),
			'all_items'          => __( 'All Products', 'dynamiqes' ),
			'menu_name'          => __( 'Products', 'dynamiqes' ),
		),
		'public'          => true,
		'has_archive'     => 'products',
		'rewrite'         => array( 'slug' => 'products', 'with_front' => false ),
		'menu_icon'       => 'dashicons-screenoptions',
		'menu_position'   => 5,
		'supports'        => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes', 'revisions' ),
		'show_in_rest'    => true,
		'hierarchical'    => false,
	) );

	register_post_type( 'dq_testimonial', array(
		'labels'        => array(
			'name'          => __( 'Testimonials', 'dynamiqes' ),
			'singular_name' => __( 'Testimonial', 'dynamiqes' ),
			'add_new_item'  => __( 'Add New Testimonial', 'dynamiqes' ),
			'edit_item'     => __( 'Edit Testimonial', 'dynamiqes' ),
		),
		'public'        => false,
		'show_ui'       => true,
		'show_in_rest'  => true,
		'menu_icon'     => 'dashicons-format-quote',
		'menu_position' => 6,
		'supports'      => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
	) );

	register_post_type( 'dq_inquiry', array(
		'labels'          => array(
			'name'          => __( 'Inquiries', 'dynamiqes' ),
			'singular_name' => __( 'Inquiry', 'dynamiqes' ),
		),
		'public'          => false,
		'show_ui'         => true,
		'menu_icon'       => 'dashicons-email-alt',
		'menu_position'   => 7,
		'supports'        => array( 'title', 'editor' ),
		'capability_type' => 'post',
		'map_meta_cap'    => true,
		'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
	) );
} );

/* ------------------------------------------------------------------ */
/* Meta boxes                                                          */
/* ------------------------------------------------------------------ */

add_action( 'add_meta_boxes', function () {
	add_meta_box( 'dq_product_details', __( 'Product details', 'dynamiqes' ), 'dq_product_meta_box', 'dq_product', 'normal', 'high' );
	add_meta_box( 'dq_testimonial_details', __( 'Testimonial details', 'dynamiqes' ), 'dq_testimonial_meta_box', 'dq_testimonial', 'normal', 'high' );
	add_meta_box( 'dq_post_thumb_url', __( 'Image URL fallback', 'dynamiqes' ), 'dq_thumb_url_meta_box', 'post', 'side' );
} );

/** Render a text/textarea/image control. */
function dq_render_field( $name, $def, $value ) {
	list( $type, $label, $desc ) = $def;
	echo '<div class="dq-field"><label for="' . esc_attr( $name ) . '">' . esc_html( $label ) . '</label>';
	if ( 'image' === $type ) {
		$src = dq_asset( $value );
		echo '<div class="dq-image-field"><input type="text" id="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '" placeholder="assets/… or https://…">';
		echo '<button type="button" class="button dq-pick-image">' . esc_html__( 'Select', 'dynamiqes' ) . '</button> <button type="button" class="button dq-clear-image">' . esc_html__( 'Clear', 'dynamiqes' ) . '</button>';
		echo '<img class="dq-image-preview" src="' . esc_url( $src ) . '" alt="" ' . ( $src ? '' : 'style="display:none"' ) . '></div>';
	} elseif ( 'text' === $type ) {
		echo '<input type="text" id="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '" value="' . esc_attr( $value ) . '">';
	} else {
		echo '<textarea id="' . esc_attr( $name ) . '" name="' . esc_attr( $name ) . '">' . esc_textarea( $value ) . '</textarea>';
	}
	if ( $desc ) {
		echo '<p class="desc">' . esc_html( $desc ) . '</p>';
	}
	echo '</div>';
}

function dq_product_meta_box( $post ) {
	wp_nonce_field( 'dq_product_save', 'dq_product_nonce' );
	$key      = get_post_meta( $post->ID, '_dq_product_key', true );
	$defaults = dq_product_defaults();
	echo '<div class="dq-fields">';
	echo '<div class="dq-field"><label for="_dq_product_key">' . esc_html__( 'Catalogue key', 'dynamiqes' ) . '</label><select name="_dq_product_key" id="_dq_product_key"><option value="">' . esc_html__( '— custom product —', 'dynamiqes' ) . '</option>';
	foreach ( $defaults as $k => $d ) {
		echo '<option value="' . esc_attr( $k ) . '" ' . selected( $key, $k, false ) . '>' . esc_html( $d['name'] . ' (' . $k . ')' ) . '</option>';
	}
	echo '</select><p class="desc">' . esc_html__( 'Links this post to the built-in defaults. Empty fields below fall back to the defaults for this key.', 'dynamiqes' ) . '</p></div>';
	foreach ( dq_product_field_map() as $field => $def ) {
		$value = get_post_meta( $post->ID, '_dq_' . $field, true );
		if ( '' === $value && $key && isset( $defaults[ $key ][ $field ] ) ) {
			$d = $defaults[ $key ][ $field ];
			if ( 'lines' === $def[0] ) {
				$value = dq_lines_to_text( $d );
			} elseif ( 'features' === $def[0] ) {
				$value = dq_features_to_text( $d );
			} elseif ( 'faqs' === $def[0] ) {
				$value = dq_faqs_to_text( $d );
			} else {
				$value = $d;
			}
		}
		dq_render_field( '_dq_' . $field, $def, $value );
	}
	echo '</div>';
}

add_action( 'save_post_dq_product', function ( $post_id ) {
	if ( ! isset( $_POST['dq_product_nonce'] ) || ! wp_verify_nonce( $_POST['dq_product_nonce'], 'dq_product_save' ) ) {
		return;
	}
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	update_post_meta( $post_id, '_dq_product_key', sanitize_key( $_POST['_dq_product_key'] ?? '' ) );
	foreach ( dq_product_field_map() as $field => $def ) {
		$name = '_dq_' . $field;
		if ( ! isset( $_POST[ $name ] ) ) {
			continue;
		}
		$raw = wp_unslash( $_POST[ $name ] );
		$val = ( 'text' === $def[0] || 'image' === $def[0] ) ? sanitize_text_field( $raw ) : wp_kses_post( $raw );
		if ( '' === trim( $val ) ) {
			delete_post_meta( $post_id, $name );
		} else {
			update_post_meta( $post_id, $name, $val );
		}
	}
} );

function dq_testimonial_meta_box( $post ) {
	wp_nonce_field( 'dq_testimonial_save', 'dq_testimonial_nonce' );
	echo '<div class="dq-fields">';
	dq_render_field( '_dq_logo', array( 'image', __( 'Client logo', 'dynamiqes' ), __( 'Or set a Featured Image; the featured image wins.', 'dynamiqes' ) ), get_post_meta( $post->ID, '_dq_logo', true ) );
	dq_render_field( '_dq_role', array( 'text', __( 'Role / designation', 'dynamiqes' ), __( 'Optional, e.g. Finance and Accounting Head', 'dynamiqes' ) ), get_post_meta( $post->ID, '_dq_role', true ) );
	dq_render_field( '_dq_more_label', array( 'text', __( '"Read more" label', 'dynamiqes' ), __( 'e.g. Read MacroAsia\'s Story', 'dynamiqes' ) ), get_post_meta( $post->ID, '_dq_more_label', true ) );
	dq_render_field( '_dq_link', array( 'text', __( '"Read more" link', 'dynamiqes' ), __( 'Full URL of the case study. Empty = link to the testimonials section.', 'dynamiqes' ) ), get_post_meta( $post->ID, '_dq_link', true ) );
	echo '<p class="desc">' . esc_html__( 'The quote is the post content; the client name is the post title.', 'dynamiqes' ) . '</p></div>';
}

add_action( 'save_post_dq_testimonial', function ( $post_id ) {
	if ( ! isset( $_POST['dq_testimonial_nonce'] ) || ! wp_verify_nonce( $_POST['dq_testimonial_nonce'], 'dq_testimonial_save' ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	foreach ( array( '_dq_logo', '_dq_role', '_dq_more_label', '_dq_link' ) as $name ) {
		$val = sanitize_text_field( wp_unslash( $_POST[ $name ] ?? '' ) );
		if ( '' === $val ) {
			delete_post_meta( $post_id, $name );
		} else {
			update_post_meta( $post_id, $name, $val );
		}
	}
} );

function dq_thumb_url_meta_box( $post ) {
	wp_nonce_field( 'dq_thumb_save', 'dq_thumb_nonce' );
	echo '<div class="dq-fields">';
	dq_render_field( '_dq_thumb', array( 'image', __( 'Image', 'dynamiqes' ), __( 'Used only when no Featured Image is set.', 'dynamiqes' ) ), get_post_meta( $post->ID, '_dq_thumb', true ) );
	echo '</div>';
}
add_action( 'save_post_post', function ( $post_id ) {
	if ( ! isset( $_POST['dq_thumb_nonce'] ) || ! wp_verify_nonce( $_POST['dq_thumb_nonce'], 'dq_thumb_save' ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	$val = sanitize_text_field( wp_unslash( $_POST['_dq_thumb'] ?? '' ) );
	if ( '' === $val ) {
		delete_post_meta( $post_id, '_dq_thumb' );
	} else {
		update_post_meta( $post_id, '_dq_thumb', $val );
	}
} );

/* Admin list columns for products. */
add_filter( 'manage_dq_product_posts_columns', function ( $cols ) {
	$new = array();
	foreach ( $cols as $k => $v ) {
		$new[ $k ] = $v;
		if ( 'title' === $k ) {
			$new['dq_key']  = __( 'Key', 'dynamiqes' );
			$new['dq_url']  = __( 'URL', 'dynamiqes' );
		}
	}
	return $new;
} );
add_action( 'manage_dq_product_posts_custom_column', function ( $col, $post_id ) {
	if ( 'dq_key' === $col ) {
		echo '<code>' . esc_html( get_post_meta( $post_id, '_dq_product_key', true ) ) . '</code>';
	} elseif ( 'dq_url' === $col ) {
		echo '<a href="' . esc_url( get_permalink( $post_id ) ) . '" target="_blank" rel="noopener">' . esc_html( wp_make_link_relative( get_permalink( $post_id ) ) ) . '</a>';
	}
}, 10, 2 );
