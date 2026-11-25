<?php
/**
 * Shop filters: read the filter URL, apply it to the product query,
 * and collect the numbers the filter panel shows.
 *
 * URL parameters:
 *   cats=slug,slug        product categories (also cats[]=slug)
 *   made_in=slug,slug     "Made in" regions
 *   min_price, max_price  handled by WooCommerce itself
 *   min_rating=4.5        average rating at least
 *   on_sale=1 new=1 best=1 (featured products)
 *   saved=1&ids=1,2,3     the visitor's saved list (kept in their browser)
 *   orderby               WooCommerce sorting
 *
 * @package Made_In_Germany
 */

defined( 'ABSPATH' ) || exit;

/**
 * Clean the price parameters before WooCommerce reads them:
 * "2,000" becomes 2000 and empty values are dropped (an empty max would hide everything).
 */
function mig_clean_price_params() {
	foreach ( array( 'min_price', 'max_price' ) as $k ) {
		if ( isset( $_GET[ $k ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$v = preg_replace( '/[^\d.]/', '', (string) wp_unslash( $_GET[ $k ] ) ); // phpcs:ignore WordPress.Security.NonceVerification
			if ( '' === $v ) {
				unset( $_GET[ $k ] );
			} else {
				$_GET[ $k ] = $v;
			}
		}
	}
}
add_action( 'init', 'mig_clean_price_params', 0 );

/**
 * Current filter state from the URL.
 *
 * @return array
 */
function mig_filter_state() {
	static $state = null;
	if ( null !== $state ) {
		return $state;
	}
	// phpcs:disable WordPress.Security.NonceVerification -- read-only catalogue filters.
	$g    = wp_unslash( $_GET );
	$list = function ( $v ) {
		if ( is_array( $v ) ) {
			$v = implode( ',', $v );
		}
		return array_values( array_unique( array_filter( array_map( 'sanitize_title', explode( ',', (string) $v ) ) ) ) );
	};
	$num = function ( $k ) use ( $g ) {
		return isset( $g[ $k ] ) && '' !== $g[ $k ] ? (int) $g[ $k ] : null;
	};
	$state = array(
		'cats'       => isset( $g['cats'] ) ? $list( $g['cats'] ) : array(),
		'made_in'    => isset( $g['made_in'] ) ? $list( $g['made_in'] ) : array(),
		'min_price'  => $num( 'min_price' ),
		'max_price'  => $num( 'max_price' ),
		'min_rating' => isset( $g['min_rating'] ) ? min( 5, max( 0, (float) $g['min_rating'] ) ) : 0,
		'on_sale'    => ! empty( $g['on_sale'] ),
		'new'        => ! empty( $g['new'] ),
		'best'       => ! empty( $g['best'] ),
		'saved'      => ! empty( $g['saved'] ),
		'ids'        => isset( $g['ids'] ) ? array_values( array_filter( array_map( 'absint', explode( ',', (string) $g['ids'] ) ) ) ) : array(),
		's'          => isset( $g['s'] ) ? sanitize_text_field( $g['s'] ) : '',
		'orderby'    => isset( $g['orderby'] ) ? sanitize_key( $g['orderby'] ) : '',
	);
	// phpcs:enable
	return $state;
}

/**
 * Apply the filters to WooCommerce's main product query.
 *
 * @param WP_Query $q Query.
 */
function mig_apply_filters( $q ) {
	$s = mig_filter_state();

	$tax = (array) $q->get( 'tax_query' );
	if ( $s['cats'] ) {
		$tax[] = array(
			'taxonomy' => 'product_cat',
			'field'    => 'slug',
			'terms'    => $s['cats'],
		);
	}
	if ( $s['made_in'] ) {
		$tax[] = array(
			'taxonomy' => 'mig_region',
			'field'    => 'slug',
			'terms'    => $s['made_in'],
		);
	}
	$q->set( 'tax_query', $tax );

	if ( $s['min_rating'] > 0 ) {
		$meta   = (array) $q->get( 'meta_query' );
		$meta[] = array(
			'key'     => '_wc_average_rating',
			'value'   => $s['min_rating'],
			'compare' => '>=',
			'type'    => 'DECIMAL(3,2)',
		);
		$q->set( 'meta_query', $meta );
	}

	$in       = $q->get( 'post__in' ) ? array_map( 'absint', (array) $q->get( 'post__in' ) ) : null;
	$restrict = function ( $ids ) use ( &$in ) {
		$ids = array_map( 'absint', (array) $ids );
		$in  = null === $in ? $ids : array_values( array_intersect( $in, $ids ) );
	};
	if ( $s['on_sale'] ) {
		$restrict( wc_get_product_ids_on_sale() );
	}
	if ( $s['best'] ) {
		$restrict( wc_get_featured_product_ids() );
	}
	if ( $s['saved'] ) {
		$restrict( $s['ids'] );
	}
	if ( null !== $in ) {
		$q->set( 'post__in', $in ? $in : array( 0 ) );
	}

	if ( $s['new'] ) {
		$q->set( 'date_query', array( array( 'after' => absint( mig_opt( 'new_days' ) ) . ' days ago' ) ) );
	}
}
add_action( 'woocommerce_product_query', 'mig_apply_filters' );

/**
 * Count published, visible products matching extra query args. Cached.
 *
 * @param array $args WP_Query args.
 * @return int
 */
function mig_count_products( $args ) {
	$key   = 'mig_cnt_' . md5( wp_json_encode( $args ) );
	$count = get_transient( $key );
	if ( false === $count ) {
		$q     = new WP_Query(
			array_merge(
				array(
					'post_type'      => 'product',
					'post_status'    => 'publish',
					'fields'         => 'ids',
					'posts_per_page' => 1,
					'no_found_rows'  => false,
					'tax_query'      => array( // phpcs:ignore WordPress.DB.SlowDBQuery
						array(
							'taxonomy' => 'product_visibility',
							'field'    => 'name',
							'terms'    => array( 'exclude-from-catalog' ),
							'operator' => 'NOT IN',
						),
					),
				),
				$args
			)
		);
		$count = (int) $q->found_posts;
		set_transient( $key, $count, HOUR_IN_SECONDS );
	}
	return (int) $count;
}

/**
 * Data for the filter panel: categories, regions, price spread and offer counts.
 *
 * @return array
 */
function mig_filter_data() {
	$data = get_transient( 'mig_filter_data' );
	if ( is_array( $data ) ) {
		return $data;
	}
	global $wpdb;

	$cats = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'parent'     => 0,
			'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
		)
	);
	$regions = get_terms(
		array(
			'taxonomy'   => 'mig_region',
			'hide_empty' => true,
		)
	);

	$prices = $wpdb->get_col( // phpcs:ignore WordPress.DB.DirectDatabaseQuery
		"SELECT l.min_price FROM {$wpdb->wc_product_meta_lookup} l
		 INNER JOIN {$wpdb->posts} p ON p.ID = l.product_id
		 WHERE p.post_type = 'product' AND p.post_status = 'publish' AND l.min_price > 0"
	);
	$prices = array_map( 'floatval', $prices );

	$data = array(
		'cats'    => is_wp_error( $cats ) ? array() : array_map(
			function ( $t ) {
				$st = mig_cat_style( $t );
				return array(
					'slug'  => $t->slug,
					'name'  => $t->name,
					'count' => (int) $t->count,
					'icon'  => $st['icon'],
				);
			},
			$cats
		),
		'regions' => is_wp_error( $regions ) ? array() : array_map(
			function ( $t ) {
				return array(
					'slug'  => $t->slug,
					'name'  => $t->name,
					'count' => (int) $t->count,
				);
			},
			$regions
		),
		'prices'  => $prices,
		'min'     => $prices ? floor( min( $prices ) ) : 0,
		'max'     => $prices ? ceil( max( $prices ) ) : 0,
		'offers'  => array(
			'on_sale' => mig_count_products( array( 'post__in' => array_merge( array( 0 ), wc_get_product_ids_on_sale() ) ) ),
			'new'     => mig_count_products( array( 'date_query' => array( array( 'after' => absint( mig_opt( 'new_days' ) ) . ' days ago' ) ) ) ),
			'best'    => mig_count_products( array( 'post__in' => array_merge( array( 0 ), wc_get_featured_product_ids() ) ) ),
		),
	);
	set_transient( 'mig_filter_data', $data, 6 * HOUR_IN_SECONDS );
	return $data;
}

/**
 * Clear cached filter numbers when products or terms change.
 */
function mig_flush_filter_cache() {
	global $wpdb;
	delete_transient( 'mig_filter_data' );
	$wpdb->query( "DELETE FROM {$wpdb->options} WHERE option_name LIKE '\_transient\_mig\_cnt\_%' OR option_name LIKE '\_transient\_timeout\_mig\_cnt\_%'" ); // phpcs:ignore WordPress.DB.DirectDatabaseQuery
}
add_action( 'woocommerce_update_product', 'mig_flush_filter_cache' );
add_action( 'woocommerce_new_product', 'mig_flush_filter_cache' );
add_action( 'delete_post', 'mig_flush_filter_cache' );
add_action( 'edited_product_cat', 'mig_flush_filter_cache' );
add_action( 'created_product_cat', 'mig_flush_filter_cache' );
add_action( 'edited_mig_region', 'mig_flush_filter_cache' );
add_action( 'created_mig_region', 'mig_flush_filter_cache' );
add_action( 'woocommerce_scheduled_sales', 'mig_flush_filter_cache', 20 );

/**
 * Where the filter form submits: the shop page, or product search when searching.
 *
 * @return string
 */
function mig_filter_action() {
	$s = mig_filter_state();
	return $s['s'] ? home_url( '/' ) : wc_get_page_permalink( 'shop' );
}

/**
 * Build a shop URL from the current filters with some values changed.
 *
 * @param array $changes Values to override.
 * @return string
 */
function mig_filter_url( $changes = array() ) {
	$s    = array_merge( mig_filter_state(), $changes );
	$args = array();
	if ( $s['s'] ) {
		$args['s']         = $s['s'];
		$args['post_type'] = 'product';
	}
	if ( $s['cats'] ) {
		$args['cats'] = implode( ',', $s['cats'] );
	}
	if ( $s['made_in'] ) {
		$args['made_in'] = implode( ',', $s['made_in'] );
	}
	if ( null !== $s['min_price'] ) {
		$args['min_price'] = $s['min_price'];
	}
	if ( null !== $s['max_price'] ) {
		$args['max_price'] = $s['max_price'];
	}
	if ( $s['min_rating'] ) {
		$args['min_rating'] = $s['min_rating'];
	}
	foreach ( array( 'on_sale', 'new', 'best' ) as $k ) {
		if ( $s[ $k ] ) {
			$args[ $k ] = 1;
		}
	}
	if ( $s['saved'] ) {
		$args['saved'] = 1;
		$args['ids']   = implode( ',', $s['ids'] );
	}
	if ( $s['orderby'] ) {
		$args['orderby'] = $s['orderby'];
	}
	$base = $s['s'] ? home_url( '/' ) : wc_get_page_permalink( 'shop' );
	return add_query_arg( array_map( 'rawurlencode', $args ), $base );
}

/**
 * Removable "active filter" chips.
 *
 * @return array[] Each: [label, url].
 */
function mig_active_chips() {
	$s     = mig_filter_state();
	$chips = array();
	$names = wp_list_pluck( mig_filter_data()['cats'], 'name', 'slug' );
	$regs  = wp_list_pluck( mig_filter_data()['regions'], 'name', 'slug' );

	if ( $s['s'] ) {
		/* translators: %s: search words */
		$chips[] = array( sprintf( __( 'Search: %s', 'made-in-germany' ), $s['s'] ), mig_filter_url( array( 's' => '' ) ) );
	}
	foreach ( $s['cats'] as $c ) {
		$chips[] = array( isset( $names[ $c ] ) ? $names[ $c ] : $c, mig_filter_url( array( 'cats' => array_values( array_diff( $s['cats'], array( $c ) ) ) ) ) );
	}
	if ( null !== $s['min_price'] || null !== $s['max_price'] ) {
		$label   = ( null !== $s['min_price'] ? mig_short_price( $s['min_price'] ) : 'Rs 0' ) . ' – ' . ( null !== $s['max_price'] ? mig_short_price( $s['max_price'] ) : __( 'any', 'made-in-germany' ) );
		$chips[] = array( $label, mig_filter_url( array( 'min_price' => null, 'max_price' => null ) ) );
	}
	if ( $s['min_rating'] ) {
		/* translators: %s: rating */
		$chips[] = array( sprintf( __( '%s★ & up', 'made-in-germany' ), $s['min_rating'] ), mig_filter_url( array( 'min_rating' => 0 ) ) );
	}
	$flags = array(
		'on_sale' => __( 'On sale', 'made-in-germany' ),
		'new'     => __( 'New arrivals', 'made-in-germany' ),
		'best'    => __( 'Bestsellers', 'made-in-germany' ),
		'saved'   => __( 'Saved items', 'made-in-germany' ),
	);
	foreach ( $flags as $k => $label ) {
		if ( $s[ $k ] ) {
			$chips[] = array( $label, mig_filter_url( array( $k => false ) ) );
		}
	}
	foreach ( $s['made_in'] as $r ) {
		/* translators: %s: region */
		$chips[] = array( sprintf( __( 'Made in %s', 'made-in-germany' ), isset( $regs[ $r ] ) ? $regs[ $r ] : $r ), mig_filter_url( array( 'made_in' => array_values( array_diff( $s['made_in'], array( $r ) ) ) ) ) );
	}
	return $chips;
}

/**
 * Short rupee label, e.g. Rs 2k.
 *
 * @param float $n Amount.
 * @return string
 */
function mig_short_price( $n ) {
	return $n >= 1000 ? 'Rs ' . rtrim( rtrim( number_format( $n / 1000, 1 ), '0' ), '.' ) . 'k' : 'Rs ' . (int) $n;
}

/**
 * Heading for the current shop view.
 *
 * @return string
 */
function mig_shop_title() {
	$s     = mig_filter_state();
	$names = wp_list_pluck( mig_filter_data()['cats'], 'name', 'slug' );
	if ( $s['s'] ) {
		return '“' . $s['s'] . '”';
	}
	if ( is_product_taxonomy() ) {
		return single_term_title( '', false );
	}
	if ( $s['saved'] ) {
		return __( 'Saved items', 'made-in-germany' );
	}
	if ( 1 === count( $s['cats'] ) && isset( $names[ $s['cats'][0] ] ) ) {
		return $names[ $s['cats'][0] ];
	}
	if ( $s['on_sale'] ) {
		return __( 'Deals', 'made-in-germany' );
	}
	return __( 'All products', 'made-in-germany' );
}
