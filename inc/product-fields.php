<?php
/**
 * Extra product data: "Made in" region taxonomy, origin city, size,
 * and an emoji + colour on each product category.
 *
 * @package Made_In_Germany
 */

defined( 'ABSPATH' ) || exit;

/**
 * Register the "Made in" region taxonomy.
 */
function mig_register_region_taxonomy() {
	register_taxonomy(
		'mig_region',
		array( 'product' ),
		array(
			'labels'            => array(
				'name'          => __( 'Made in regions', 'made-in-germany' ),
				'singular_name' => __( 'Region', 'made-in-germany' ),
				'menu_name'     => __( 'Made in regions', 'made-in-germany' ),
				'add_new_item'  => __( 'Add region', 'made-in-germany' ),
				'search_items'  => __( 'Search regions', 'made-in-germany' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'made-in' ),
		)
	);
}
add_action( 'init', 'mig_register_region_taxonomy' );

/**
 * Origin city and size fields in the product General tab.
 */
function mig_product_general_fields() {
	echo '<div class="options_group">';
	woocommerce_wp_text_input(
		array(
			'id'          => '_mig_origin',
			'label'       => __( 'Made in (city)', 'made-in-germany' ),
			'placeholder' => 'Hamburg',
			'desc_tip'    => true,
			'description' => __( 'German town or city shown on the product card and page.', 'made-in-germany' ),
		)
	);
	woocommerce_wp_text_input(
		array(
			'id'          => '_mig_brand',
			'label'       => __( 'Maker / brand', 'made-in-germany' ),
			'placeholder' => 'Schwarzwald Kakao',
		)
	);
	woocommerce_wp_text_input(
		array(
			'id'          => '_mig_size',
			'label'       => __( 'Size / pack', 'made-in-germany' ),
			'placeholder' => '100 g',
		)
	);
	woocommerce_wp_text_input(
		array(
			'id'          => '_mig_emoji',
			'label'       => __( 'Emoji (when no photo)', 'made-in-germany' ),
			'placeholder' => '🍫',
			'desc_tip'    => true,
			'description' => __( 'Shown on a coloured tile until you upload a product image.', 'made-in-germany' ),
		)
	);
	echo '</div>';
}
add_action( 'woocommerce_product_options_general_product_data', 'mig_product_general_fields' );

/**
 * Save product fields.
 *
 * @param int $post_id Product ID.
 */
function mig_save_product_fields( $post_id ) {
	foreach ( array( '_mig_origin', '_mig_brand', '_mig_size', '_mig_emoji' ) as $key ) {
		if ( isset( $_POST[ $key ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification -- WooCommerce verifies the product form nonce.
			update_post_meta( $post_id, $key, sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) ); // phpcs:ignore WordPress.Security.NonceVerification
		}
	}
}
add_action( 'woocommerce_process_product_meta', 'mig_save_product_fields' );

/**
 * Category emoji and colour fields (add screen).
 */
function mig_cat_add_fields() {
	?>
	<div class="form-field">
		<label for="mig_icon"><?php esc_html_e( 'Emoji icon', 'made-in-germany' ); ?></label>
		<input type="text" name="mig_icon" id="mig_icon" placeholder="🍫" style="width:6em">
		<p><?php esc_html_e( 'Shown on the home page tiles and shop filters.', 'made-in-germany' ); ?></p>
	</div>
	<div class="form-field">
		<label for="mig_color"><?php esc_html_e( 'Tile colour', 'made-in-germany' ); ?></label>
		<input type="color" name="mig_color" id="mig_color" value="#DD0000">
	</div>
	<?php
}
add_action( 'product_cat_add_form_fields', 'mig_cat_add_fields' );

/**
 * Category emoji and colour fields (edit screen).
 *
 * @param WP_Term $term Term.
 */
function mig_cat_edit_fields( $term ) {
	$s = mig_cat_style( $term );
	?>
	<tr class="form-field">
		<th scope="row"><label for="mig_icon"><?php esc_html_e( 'Emoji icon', 'made-in-germany' ); ?></label></th>
		<td><input type="text" name="mig_icon" id="mig_icon" value="<?php echo esc_attr( get_term_meta( $term->term_id, 'mig_icon', true ) ); ?>" style="width:6em"></td>
	</tr>
	<tr class="form-field">
		<th scope="row"><label for="mig_color"><?php esc_html_e( 'Tile colour', 'made-in-germany' ); ?></label></th>
		<td><input type="color" name="mig_color" id="mig_color" value="<?php echo esc_attr( $s['color'] ); ?>"></td>
	</tr>
	<?php
}
add_action( 'product_cat_edit_form_fields', 'mig_cat_edit_fields' );

/**
 * Save category fields.
 *
 * @param int $term_id Term ID.
 */
function mig_cat_save_fields( $term_id ) {
	if ( ! current_user_can( 'manage_product_terms' ) ) {
		return;
	}
	// phpcs:disable WordPress.Security.NonceVerification -- core verifies the term form nonce.
	if ( isset( $_POST['mig_icon'] ) ) {
		update_term_meta( $term_id, 'mig_icon', sanitize_text_field( wp_unslash( $_POST['mig_icon'] ) ) );
	}
	if ( isset( $_POST['mig_color'] ) ) {
		$c = sanitize_hex_color( wp_unslash( $_POST['mig_color'] ) );
		if ( $c ) {
			update_term_meta( $term_id, 'mig_color', $c );
		}
	}
	// phpcs:enable
}
add_action( 'created_product_cat', 'mig_cat_save_fields' );
add_action( 'edited_product_cat', 'mig_cat_save_fields' );
