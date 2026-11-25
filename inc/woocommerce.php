<?php
/**
 * WooCommerce integration: wrappers, cart drawer, Sri Lanka checkout tweaks.
 *
 * @package Made_In_Germany
 */

defined( 'ABSPATH' ) || exit;

/* -------------------------------------------------------------------------
 * Layout wrappers
 * ---------------------------------------------------------------------- */

remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );

add_action(
	'woocommerce_before_main_content',
	function () {
		echo '<div class="wrap wc-wrap">';
	},
	10
);
add_action(
	'woocommerce_after_main_content',
	function () {
		echo '</div>';
	},
	10
);

// The shop template prints its own count, sorting and breadcrumb.
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_result_count', 20 );
remove_action( 'woocommerce_before_shop_loop', 'woocommerce_catalog_ordering', 30 );

add_filter(
	'woocommerce_breadcrumb_defaults',
	function ( $args ) {
		$args['delimiter']   = '<span>/</span>';
		$args['wrap_before'] = '<nav class="crumbs" aria-label="' . esc_attr__( 'Breadcrumb', 'made-in-germany' ) . '">';
		$args['wrap_after']  = '</nav>';
		$args['home']        = __( 'Home', 'made-in-germany' );
		return $args;
	}
);

add_filter(
	'loop_shop_per_page',
	function () {
		return 12;
	}
);
add_filter(
	'loop_shop_columns',
	function () {
		return 3;
	}
);

add_filter(
	'woocommerce_output_related_products_args',
	function ( $args ) {
		$args['posts_per_page'] = 4;
		$args['columns']        = 4;
		return $args;
	}
);

/* -------------------------------------------------------------------------
 * Money and labels
 * ---------------------------------------------------------------------- */

add_filter(
	'woocommerce_currency_symbol',
	function ( $symbol, $currency ) {
		return 'LKR' === $currency ? 'Rs' : $symbol;
	},
	10,
	2
);

/**
 * Percentage saved on a product, 0 when not on sale.
 *
 * @param WC_Product $product Product.
 * @return int
 */
function mig_discount( $product ) {
	if ( ! $product->is_on_sale() ) {
		return 0;
	}
	if ( $product->is_type( 'variable' ) ) {
		$max = 0;
		foreach ( $product->get_children() as $child_id ) {
			$child = wc_get_product( $child_id );
			if ( $child && $child->is_on_sale() && (float) $child->get_regular_price() > 0 ) {
				$max = max( $max, 1 - (float) $child->get_sale_price() / (float) $child->get_regular_price() );
			}
		}
		return (int) round( $max * 100 );
	}
	$regular = (float) $product->get_regular_price();
	$sale    = (float) $product->get_sale_price();
	return $regular > 0 ? (int) round( ( 1 - $sale / $regular ) * 100 ) : 0;
}

/**
 * Whether a product counts as new.
 *
 * @param WC_Product $product Product.
 * @return bool
 */
function mig_is_new( $product ) {
	$days    = (int) mig_opt( 'new_days' );
	$created = $product->get_date_created();
	return $days > 0 && $created && $created->getTimestamp() > time() - $days * DAY_IN_SECONDS;
}

/**
 * Label shown on a product card: sale %, Bestseller (featured) or New.
 *
 * @param WC_Product $product Product.
 * @return string
 */
function mig_product_badge( $product ) {
	$d = mig_discount( $product );
	if ( $d > 0 ) {
		return '<span class="tag sale">−' . $d . '%</span>';
	}
	if ( $product->is_featured() ) {
		return '<span class="tag">' . esc_html__( 'Bestseller', 'made-in-germany' ) . '</span>';
	}
	if ( mig_is_new( $product ) ) {
		return '<span class="tag new">' . esc_html__( 'New', 'made-in-germany' ) . '</span>';
	}
	if ( ! $product->is_in_stock() ) {
		return '<span class="tag">' . esc_html__( 'Sold out', 'made-in-germany' ) . '</span>';
	}
	return '';
}

add_filter(
	'woocommerce_sale_flash',
	function ( $html, $post, $product ) {
		$d = mig_discount( $product );
		return '<span class="onsale tag sale">' . ( $d ? '−' . $d . '%' : esc_html__( 'Sale', 'made-in-germany' ) ) . '</span>';
	},
	10,
	3
);

/**
 * Product image, or the category emoji on a tinted tile when there is no photo.
 *
 * @param WC_Product $product Product.
 * @param string     $size Image size.
 */
function mig_product_visual( $product, $size = 'woocommerce_thumbnail' ) {
	if ( $product->get_image_id() ) {
		echo $product->get_image( $size, array( 'class' => 'pimg', 'loading' => 'lazy' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
		return;
	}
	$emoji = get_post_meta( $product->get_id(), '_mig_emoji', true );
	if ( ! $emoji ) {
		$cats  = $product->get_category_ids();
		$emoji = mig_cat_style( $cats ? $cats[0] : 0 )['icon'];
	}
	echo '<span class="emo" aria-hidden="true">' . esc_html( $emoji ) . '</span>';
}

/**
 * Category colour for tinted product tiles.
 *
 * @param WC_Product $product Product.
 * @return string
 */
function mig_product_hue( $product ) {
	$cats = $product->get_category_ids();
	return mig_cat_style( $cats ? $cats[0] : 0 )['color'];
}

/* -------------------------------------------------------------------------
 * Single product
 * ---------------------------------------------------------------------- */

/**
 * "Made in" facts under the price.
 */
function mig_single_specs() {
	global $product;
	if ( ! $product ) {
		return;
	}
	$origin  = get_post_meta( $product->get_id(), '_mig_origin', true );
	$size    = get_post_meta( $product->get_id(), '_mig_size', true );
	$regions = wp_get_post_terms( $product->get_id(), 'mig_region', array( 'fields' => 'names' ) );
	$rows    = array();
	if ( $origin || $regions ) {
		$rows[ __( 'Made in', 'made-in-germany' ) ] = trim( implode( ', ', array_filter( array( $origin, $regions ? $regions[0] : '' ) ) ) . ', Germany 🇩🇪', ', ' );
	}
	if ( $size ) {
		$rows[ __( 'Size', 'made-in-germany' ) ] = $size;
	}
	if ( $product->get_sku() ) {
		$rows[ __( 'Article', 'made-in-germany' ) ] = $product->get_sku();
	}
	$rows[ __( 'Delivery', 'made-in-germany' ) ] = __( '1–2 days Colombo · 2–4 days island-wide', 'made-in-germany' );
	echo '<dl class="spec">';
	foreach ( $rows as $k => $v ) {
		echo '<dt>' . esc_html( $k ) . '</dt><dd>' . esc_html( $v ) . '</dd>';
	}
	echo '</dl>';
}
add_action( 'woocommerce_single_product_summary', 'mig_single_specs', 25 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
add_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 45 );

/**
 * Show the emoji tile on single products without images.
 *
 * @param string $html Placeholder HTML.
 * @return string
 */
function mig_single_placeholder( $html ) {
	global $product;
	if ( is_product() && $product && ! $product->get_image_id() ) {
		ob_start();
		echo '<div class="woocommerce-product-gallery__image mig-noimg" style="--h:' . esc_attr( mig_product_hue( $product ) ) . '"><div class="thumb">';
		mig_product_visual( $product );
		echo '</div></div>';
		return ob_get_clean();
	}
	return $html;
}
add_filter(
	'woocommerce_single_product_image_thumbnail_html',
	function ( $html, $id ) {
		return $id ? $html : mig_single_placeholder( $html );
	},
	10,
	2
);

/* -------------------------------------------------------------------------
 * Cart: header count, drawer, quantity AJAX
 * ---------------------------------------------------------------------- */

/**
 * Header cart badge.
 *
 * @return string
 */
function mig_cart_count_html() {
	$n = WC()->cart ? WC()->cart->get_cart_contents_count() : 0;
	return '<span class="badge mig-cart-count"' . ( $n ? '' : ' hidden' ) . '>' . (int) $n . '</span>';
}

add_filter(
	'woocommerce_add_to_cart_fragments',
	function ( $fragments ) {
		$fragments['span.mig-cart-count'] = mig_cart_count_html();
		return $fragments;
	}
);

/**
 * Change a cart line's quantity from the drawer.
 */
function mig_ajax_set_qty() {
	check_ajax_referer( 'mig-cart', 'nonce' );
	$key = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
	$qty = isset( $_POST['qty'] ) ? max( 0, (int) $_POST['qty'] ) : 0;
	if ( $key && WC()->cart && WC()->cart->get_cart_item( $key ) ) {
		WC()->cart->set_quantity( $key, $qty, true );
	}
	WC_AJAX::get_refreshed_fragments();
}
add_action( 'wp_ajax_mig_set_qty', 'mig_ajax_set_qty' );
add_action( 'wp_ajax_nopriv_mig_set_qty', 'mig_ajax_set_qty' );

/**
 * Free-delivery threshold shown in the cart bar.
 *
 * @return float
 */
function mig_free_shipping_threshold() {
	return (float) mig_opt( 'free_shipping' );
}

/* -------------------------------------------------------------------------
 * Sri Lanka checkout
 * ---------------------------------------------------------------------- */

/**
 * The 25 districts, used as "states" for Sri Lankan addresses.
 *
 * @return array
 */
function mig_lk_districts() {
	return array(
		'CMB' => 'Colombo',
		'GMP' => 'Gampaha',
		'KLT' => 'Kalutara',
		'KDY' => 'Kandy',
		'MTL' => 'Matale',
		'NWE' => 'Nuwara Eliya',
		'GLL' => 'Galle',
		'MTR' => 'Matara',
		'HMB' => 'Hambantota',
		'JFN' => 'Jaffna',
		'KLN' => 'Kilinochchi',
		'MNR' => 'Mannar',
		'VNA' => 'Vavuniya',
		'MLT' => 'Mullaitivu',
		'BTC' => 'Batticaloa',
		'AMP' => 'Ampara',
		'TRC' => 'Trincomalee',
		'KRN' => 'Kurunegala',
		'PTL' => 'Puttalam',
		'ANP' => 'Anuradhapura',
		'PLN' => 'Polonnaruwa',
		'BDL' => 'Badulla',
		'MNG' => 'Monaragala',
		'RTN' => 'Ratnapura',
		'KGL' => 'Kegalle',
	);
}

add_filter(
	'woocommerce_states',
	function ( $states ) {
		if ( empty( $states['LK'] ) ) {
			$states['LK'] = mig_lk_districts();
		}
		return $states;
	}
);

add_filter(
	'woocommerce_get_country_locale',
	function ( $locale ) {
		$locale['LK']['state'] = array(
			'label'    => __( 'District', 'made-in-germany' ),
			'required' => true,
		);
		$locale['LK']['postcode'] = array( 'required' => false );
		return $locale;
	}
);

/**
 * Validate Sri Lankan mobile numbers at checkout.
 *
 * @param array    $data Posted data.
 * @param WP_Error $errors Errors.
 */
function mig_validate_lk_phone( $data, $errors ) {
	if ( empty( $data['billing_country'] ) || 'LK' !== $data['billing_country'] || empty( $data['billing_phone'] ) ) {
		return;
	}
	$phone = preg_replace( '/[\s\-()]/', '', $data['billing_phone'] );
	if ( ! preg_match( '/^(?:\+94|0094|94|0)?(?:7\d{8}|[1-9]\d{8})$/', $phone ) ) {
		$errors->add( 'validation', __( 'Enter a Sri Lankan phone number, for example 077 123 4567.', 'made-in-germany' ) );
	}
}
add_action( 'woocommerce_after_checkout_validation', 'mig_validate_lk_phone', 10, 2 );

/**
 * Same phone check for the block checkout (Store API).
 *
 * @param WC_Order        $order Order.
 * @param WP_REST_Request $request Request.
 * @throws \Automattic\WooCommerce\StoreApi\Exceptions\RouteException When the number is not Sri Lankan.
 */
function mig_validate_lk_phone_blocks( $order, $request ) {
	if ( 'LK' !== $order->get_billing_country() || ! $order->get_billing_phone() ) {
		return;
	}
	$errors = new WP_Error();
	mig_validate_lk_phone(
		array(
			'billing_country' => 'LK',
			'billing_phone'   => $order->get_billing_phone(),
		),
		$errors
	);
	if ( $errors->has_errors() && class_exists( '\\Automattic\\WooCommerce\\StoreApi\\Exceptions\\RouteException' ) ) {
		throw new \Automattic\WooCommerce\StoreApi\Exceptions\RouteException( 'mig_invalid_phone', esc_html( $errors->get_error_message() ), 400 );
	}
}
add_action( 'woocommerce_store_api_checkout_update_order_from_request', 'mig_validate_lk_phone_blocks', 10, 2 );

add_filter(
	'woocommerce_checkout_fields',
	function ( $fields ) {
		if ( isset( $fields['billing']['billing_phone'] ) ) {
			$fields['billing']['billing_phone']['placeholder'] = '077 123 4567';
		}
		return $fields;
	}
);
