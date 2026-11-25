<?php
/**
 * One-click demo import: categories, regions, 50 products,
 * and optional Sri Lanka store settings (currency, delivery, payments).
 *
 * Appearance → Made in Germany demo.
 *
 * @package Made_In_Germany
 */

defined( 'ABSPATH' ) || exit;

/**
 * Admin menu entry.
 */
function mig_demo_menu() {
	add_theme_page(
		__( 'Made in Germany demo', 'made-in-germany' ),
		__( 'Made in Germany demo', 'made-in-germany' ),
		'manage_woocommerce',
		'mig-demo',
		'mig_demo_page'
	);
}
add_action( 'admin_menu', 'mig_demo_menu' );

/**
 * Admin page.
 */
function mig_demo_page() {
	$msg = '';
	if ( isset( $_POST['mig_demo_action'] ) && check_admin_referer( 'mig_demo' ) && current_user_can( 'manage_woocommerce' ) ) {
		$action = sanitize_key( wp_unslash( $_POST['mig_demo_action'] ) );
		if ( 'import' === $action ) {
			$r   = mig_demo_import( ! empty( $_POST['mig_store_settings'] ) );
			/* translators: 1: products created, 2: products skipped */
			$msg = sprintf( __( 'Done. %1$d products created, %2$d already existed.', 'made-in-germany' ), $r['created'], $r['skipped'] );
		} elseif ( 'remove' === $action ) {
			$n = mig_demo_remove();
			/* translators: %d: products deleted */
			$msg = sprintf( __( '%d demo products moved to the bin.', 'made-in-germany' ), $n );
		}
	}
	$demo_count = count( mig_demo_product_ids() );
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'Made in Germany demo content', 'made-in-germany' ); ?></h1>
		<?php if ( $msg ) : ?>
			<div class="notice notice-success"><p><?php echo esc_html( $msg ); ?> <a href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'View the shop', 'made-in-germany' ); ?></a></p></div>
		<?php endif; ?>
		<p style="max-width:62em"><?php esc_html_e( 'Creates 12 product categories (with emoji icons and colours), 6 German "Made in" regions and 50 example products with prices in rupees, sale prices, ratings and origin details. Products show an emoji tile until you upload real photos. Importing again skips products that already exist (matched by SKU).', 'made-in-germany' ); ?></p>
		<form method="post">
			<?php wp_nonce_field( 'mig_demo' ); ?>
			<p><label><input type="checkbox" name="mig_store_settings" value="1" checked> <?php esc_html_e( 'Also set up the store for Sri Lanka: currency LKR with no decimals, store country Sri Lanka, a Sri Lanka delivery zone (Rs 450 flat rate, free over the threshold in the Customizer), and turn on cash on delivery and bank transfer.', 'made-in-germany' ); ?></label></p>
			<p>
				<button class="button button-primary button-hero" name="mig_demo_action" value="import"><?php esc_html_e( 'Import demo products', 'made-in-germany' ); ?></button>
			</p>
		</form>
		<?php if ( $demo_count ) : ?>
			<hr>
			<form method="post">
				<?php wp_nonce_field( 'mig_demo' ); ?>
				<p>
					<?php
					/* translators: %d: number of demo products */
					printf( esc_html__( '%d demo products are in your store.', 'made-in-germany' ), (int) $demo_count );
					?>
					<button class="button" name="mig_demo_action" value="remove"><?php esc_html_e( 'Remove demo products', 'made-in-germany' ); ?></button>
				</p>
			</form>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * IDs of products created by the importer.
 *
 * @return int[]
 */
function mig_demo_product_ids() {
	return get_posts(
		array(
			'post_type'      => 'product',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'meta_key'       => '_mig_demo', // phpcs:ignore WordPress.DB.SlowDBQuery
			'meta_value'     => '1', // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);
}

/**
 * Get or create a term.
 *
 * @param string $name Name.
 * @param string $tax Taxonomy.
 * @param string $slug Slug.
 * @return int
 */
function mig_demo_term( $name, $tax, $slug = '' ) {
	$slug = $slug ? $slug : sanitize_title( $name );
	$t    = get_term_by( 'slug', $slug, $tax );
	if ( $t ) {
		return (int) $t->term_id;
	}
	$r = wp_insert_term( $name, $tax, array( 'slug' => $slug ) );
	return is_wp_error( $r ) ? 0 : (int) $r['term_id'];
}

/**
 * Run the import.
 *
 * @param bool $store_settings Also configure currency, shipping and payments.
 * @return array{created:int,skipped:int}
 */
function mig_demo_import( $store_settings ) {
	if ( function_exists( 'set_time_limit' ) ) {
		set_time_limit( 300 );
	}
	$data = require MIG_DIR . '/inc/demo-data.php';

	$cat_ids = array();
	foreach ( $data['categories'] as $i => $c ) {
		$id = mig_demo_term( $c['name'], 'product_cat', $c['slug'] );
		update_term_meta( $id, 'mig_icon', $c['icon'] );
		update_term_meta( $id, 'mig_color', $c['color'] );
		update_term_meta( $id, 'order', $i );
		$cat_ids[ $c['slug'] ] = $id;
	}
	$reg_ids = array();
	foreach ( $data['regions'] as $r ) {
		$reg_ids[ $r ] = mig_demo_term( $r, 'mig_region' );
	}

	$created = 0;
	$skipped = 0;
	$total   = count( $data['products'] );
	foreach ( $data['products'] as $i => $p ) {
		if ( wc_get_product_id_by_sku( $p['sku'] ) ) {
			++$skipped;
			continue;
		}
		$product = new WC_Product_Simple();
		$product->set_name( $p['name'] );
		$product->set_status( 'publish' );
		$product->set_sku( $p['sku'] );
		$product->set_regular_price( (string) ( $p['old'] ? $p['old'] : $p['price'] ) );
		if ( $p['old'] ) {
			$product->set_sale_price( (string) $p['price'] );
		}
		$product->set_short_description( $p['desc'] );
		$product->set_description( $p['desc'] . "\n\n" . sprintf( 'Made in %1$s, %2$s. Imported directly from Germany and stored in our Colombo warehouse.', $p['origin'], $p['region'] ) );
		$product->set_category_ids( array( $cat_ids[ $p['cat'] ] ) );
		$product->set_featured( 'Bestseller' === $p['tag'] );
		$product->set_stock_status( 'instock' );
		$product->set_reviews_allowed( true );
		// "New" products are dated today; the rest are spread over the past months so they don't count as new.
		$ts = 'New' === $p['tag'] ? time() - $i * HOUR_IN_SECONDS : time() - ( 60 + $total - $i ) * DAY_IN_SECONDS;
		$product->set_date_created( $ts );
		$product->update_meta_data( '_mig_demo', '1' );
		$product->update_meta_data( '_mig_origin', $p['origin'] );
		$product->update_meta_data( '_mig_brand', $p['brand'] );
		$product->update_meta_data( '_mig_size', $p['size'] );
		$product->update_meta_data( '_mig_emoji', $p['emoji'] );
		$product->set_average_rating( $p['rating'] );
		$product->set_review_count( $p['reviews'] );
		$product->set_total_sales( $p['reviews'] );
		$id = $product->save();
		update_post_meta( $id, '_wc_average_rating', $p['rating'] );
		update_post_meta( $id, '_wc_review_count', $p['reviews'] );
		wp_set_object_terms( $id, array( $reg_ids[ $p['region'] ] ), 'mig_region' );
		++$created;
	}

	if ( $store_settings ) {
		mig_demo_store_settings();
	}
	update_option( 'mig_demo_imported', 1 );
	if ( function_exists( 'mig_flush_filter_cache' ) ) {
		mig_flush_filter_cache();
	}
	if ( function_exists( 'wc_update_product_lookup_tables_is_running' ) && ! wc_update_product_lookup_tables_is_running() ) {
		wc_update_product_lookup_tables();
	}
	return array(
		'created' => $created,
		'skipped' => $skipped,
	);
}

/**
 * Currency, delivery zone and payment methods for Sri Lanka.
 */
function mig_demo_store_settings() {
	update_option( 'woocommerce_currency', 'LKR' );
	update_option( 'woocommerce_price_num_decimals', 0 );
	update_option( 'woocommerce_currency_pos', 'left_space' );
	update_option( 'woocommerce_default_country', 'LK:CMB' );
	update_option( 'woocommerce_store_city', 'Colombo' );
	update_option( 'woocommerce_enable_ajax_add_to_cart', 'yes' );

	// Delivery zone: only create it once.
	$exists = false;
	foreach ( WC_Shipping_Zones::get_zones() as $z ) {
		if ( 'Sri Lanka' === $z['zone_name'] ) {
			$exists = true;
		}
	}
	if ( ! $exists ) {
		$zone = new WC_Shipping_Zone();
		$zone->set_zone_name( 'Sri Lanka' );
		$zone->add_location( 'LK', 'country' );
		$zone->save();
		$flat = $zone->add_shipping_method( 'flat_rate' );
		update_option(
			'woocommerce_flat_rate_' . $flat . '_settings',
			array(
				'title'      => 'Island-wide delivery',
				'tax_status' => 'none',
				'cost'       => '450',
			)
		);
		$free = $zone->add_shipping_method( 'free_shipping' );
		update_option(
			'woocommerce_free_shipping_' . $free . '_settings',
			array(
				'title'      => 'Free delivery',
				'requires'   => 'min_amount',
				'min_amount' => (string) mig_free_shipping_threshold(),
			)
		);
	}

	$cod = (array) get_option( 'woocommerce_cod_settings', array() );
	update_option(
		'woocommerce_cod_settings',
		array_merge(
			$cod,
			array(
				'enabled'     => 'yes',
				'title'       => 'Cash on delivery',
				'description' => 'Pay the rider in cash or by card when your order arrives.',
			)
		)
	);
	$bacs = (array) get_option( 'woocommerce_bacs_settings', array() );
	update_option(
		'woocommerce_bacs_settings',
		array_merge(
			$bacs,
			array(
				'enabled'     => 'yes',
				'title'       => 'Bank transfer',
				'description' => 'Your order ships once the payment clears. Use your order number as the reference.',
			)
		)
	);
}

/**
 * Move demo products to the bin.
 *
 * @return int
 */
function mig_demo_remove() {
	$ids = mig_demo_product_ids();
	foreach ( $ids as $id ) {
		wp_trash_post( $id );
	}
	delete_option( 'mig_demo_imported' );
	if ( function_exists( 'mig_flush_filter_cache' ) ) {
		mig_flush_filter_cache();
	}
	return count( $ids );
}
