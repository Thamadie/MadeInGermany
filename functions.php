<?php
/**
 * Made in Germany theme functions.
 *
 * @package Made_In_Germany
 */

defined( 'ABSPATH' ) || exit;

define( 'MIG_VERSION', '1.0.0' );
define( 'MIG_DIR', get_template_directory() );
define( 'MIG_URI', get_template_directory_uri() );

require MIG_DIR . '/inc/template-tags.php';
require MIG_DIR . '/inc/customizer.php';

if ( class_exists( 'WooCommerce' ) ) {
	require MIG_DIR . '/inc/woocommerce.php';
	require MIG_DIR . '/inc/product-fields.php';
	require MIG_DIR . '/inc/shop-filters.php';
	require MIG_DIR . '/inc/demo-import.php';
}

/**
 * Theme setup.
 */
function mig_setup() {
	load_theme_textdomain( 'made-in-germany', MIG_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'html5', array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' ) );
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 80,
			'width'       => 80,
			'flex-width'  => true,
			'flex-height' => true,
		)
	);

	add_theme_support(
		'woocommerce',
		array(
			'thumbnail_image_width' => 600,
			'single_image_width'    => 900,
			'product_grid'          => array(
				'default_rows'    => 4,
				'min_rows'        => 1,
				'default_columns' => 3,
				'min_columns'     => 2,
				'max_columns'     => 4,
			),
		)
	);
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );

	register_nav_menus(
		array(
			'primary'     => __( 'Header menu', 'made-in-germany' ),
			'footer_help' => __( 'Footer help menu', 'made-in-germany' ),
		)
	);
}
add_action( 'after_setup_theme', 'mig_setup' );

/**
 * Styles and scripts.
 */
function mig_assets() {
	wp_enqueue_style(
		'mig-fonts',
		'https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@400;500;600;700;800&display=swap',
		array(),
		null
	);
	wp_enqueue_style( 'mig-style', get_stylesheet_uri(), array( 'mig-fonts' ), MIG_VERSION );

	$deps = array( 'jquery' );
	if ( class_exists( 'WooCommerce' ) ) {
		// Needed on every page: the header cart count and drawer update through fragments,
		// and product cards on the home page use AJAX add to cart.
		wp_enqueue_script( 'wc-cart-fragments' );
		wp_enqueue_script( 'wc-add-to-cart' );
		$deps[] = 'wc-add-to-cart';
	}
	wp_enqueue_script( 'mig-main', MIG_URI . '/assets/js/main.js', $deps, MIG_VERSION, true );

	wp_localize_script(
		'mig-main',
		'MIG',
		array(
			'ajaxUrl' => admin_url( 'admin-ajax.php' ),
			'nonce'   => wp_create_nonce( 'mig-cart' ),
			'shopUrl' => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ),
			'i18n'    => array(
				'added'      => __( 'added to cart', 'made-in-germany' ),
				'saved'      => __( 'Saved to your list', 'made-in-germany' ),
				'unsaved'    => __( 'Removed from saved', 'made-in-germany' ),
				'subscribed' => __( 'Thanks for subscribing. Check your inbox for your welcome code.', 'made-in-germany' ),
				'badEmail'   => __( 'Enter a valid email address', 'made-in-germany' ),
				'results'    => __( 'results', 'made-in-germany' ),
			),
		)
	);

	if ( is_singular() && comments_open() && get_option( 'thread_comments' ) ) {
		wp_enqueue_script( 'comment-reply' );
	}
}
add_action( 'wp_enqueue_scripts', 'mig_assets' );

/**
 * Apply the visitor's saved dark-mode choice before first paint. Light is the default.
 */
function mig_theme_boot() {
	echo "<script>try{if(localStorage.getItem('mig_theme')==='dark')document.documentElement.dataset.mig='dark'}catch(e){}</script>\n";
}
add_action( 'wp_head', 'mig_theme_boot', 1 );

/**
 * Preconnect to Google Fonts.
 *
 * @param array  $urls URLs.
 * @param string $relation Relation type.
 * @return array
 */
function mig_resource_hints( $urls, $relation ) {
	if ( 'preconnect' === $relation ) {
		$urls[] = array( 'href' => 'https://fonts.gstatic.com', 'crossorigin' );
		$urls[] = 'https://fonts.googleapis.com';
	}
	return $urls;
}
add_filter( 'wp_resource_hints', 'mig_resource_hints', 10, 2 );

/**
 * Remind admins to install WooCommerce and import the demo products.
 */
function mig_admin_notice() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	$screen = get_current_screen();
	if ( $screen && 'appearance_page_mig-demo' === $screen->id ) {
		return;
	}
	if ( ! class_exists( 'WooCommerce' ) ) {
		echo '<div class="notice notice-warning"><p><strong>Made in Germany:</strong> ' . esc_html__( 'This theme is built for WooCommerce. Install and activate WooCommerce to turn on the shop, cart and checkout.', 'made-in-germany' ) . '</p></div>';
		return;
	}
	if ( ! get_option( 'mig_demo_imported' ) && ! get_user_meta( get_current_user_id(), 'mig_hide_demo_notice', true ) ) {
		$url = admin_url( 'themes.php?page=mig-demo' );
		echo '<div class="notice notice-info"><p><strong>Made in Germany:</strong> ' . esc_html__( 'Want to see the store with products in it? Import the 50 demo products, categories and regions in one click.', 'made-in-germany' ) . ' <a class="button button-primary" href="' . esc_url( $url ) . '">' . esc_html__( 'Open demo import', 'made-in-germany' ) . '</a></p></div>';
	}
}
add_action( 'admin_notices', 'mig_admin_notice' );
