<?php
/**
 * Small template helpers and icons.
 *
 * @package Made_In_Germany
 */

defined( 'ABSPATH' ) || exit;

/**
 * Inline SVG icons.
 *
 * @param string $name Icon name.
 * @return string
 */
function mig_icon( $name ) {
	$icons = array(
		'search'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-3.5-3.5"/></svg>',
		'moon'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><path d="M21 12.8A9 9 0 1 1 11.2 3a7 7 0 0 0 9.8 9.8z"/></svg>',
		'heart'   => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 21s-7.5-4.6-9.5-9.2C1.2 8.6 3.3 5 6.8 5c2.1 0 3.4 1.1 5.2 3 1.8-1.9 3.1-3 5.2-3 3.5 0 5.6 3.6 4.3 6.8C19.5 16.4 12 21 12 21z"/></svg>',
		'heart-o' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linejoin="round" aria-hidden="true"><path d="M12 21s-7.5-4.6-9.5-9.2C1.2 8.6 3.3 5 6.8 5c2.1 0 3.4 1.1 5.2 3 1.8-1.9 3.1-3 5.2-3 3.5 0 5.6 3.6 4.3 6.8C19.5 16.4 12 21 12 21z"/></svg>',
		'cart'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 4h2l2.4 11.2a2 2 0 0 0 2 1.6h7.9a2 2 0 0 0 2-1.5L21 8H6"/><circle cx="10" cy="20.5" r="1.3"/><circle cx="17.5" cy="20.5" r="1.3"/></svg>',
		'user'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><circle cx="12" cy="8" r="4"/><path d="M4 21c1.5-4 4.5-6 8-6s6.5 2 8 6"/></svg>',
		'arrow'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" aria-hidden="true"><path d="M5 12h14M13 6l6 6-6 6"/></svg>',
		'plus'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" aria-hidden="true"><path d="M12 5v14M5 12h14"/></svg>',
		'check'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>',
		'tick'    => '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12.5l4.5 4.5L19 7.5"/></svg>',
		'close'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M6 6l12 12M18 6 6 18"/></svg>',
		'eye'     => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/><circle cx="12" cy="12" r="3"/></svg>',
		'filter'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" aria-hidden="true"><path d="M4 6h10M18 6h2M4 12h4M12 12h8M4 18h12"/><circle cx="16" cy="6" r="2"/><circle cx="10" cy="12" r="2"/><circle cx="18" cy="18" r="2"/></svg>',
		'grid'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="4" y="4" width="6.5" height="6.5" rx="1.5"/><rect x="13.5" y="4" width="6.5" height="6.5" rx="1.5"/><rect x="4" y="13.5" width="6.5" height="6.5" rx="1.5"/><rect x="13.5" y="13.5" width="6.5" height="6.5" rx="1.5"/></svg>',
		'list'    => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true"><rect x="4" y="4.5" width="5" height="5" rx="1.2"/><rect x="4" y="14.5" width="5" height="5" rx="1.2"/><path d="M12 6h8M12 9h5M12 16h8M12 19h5"/></svg>',
		'chat'    => '<svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M12 2a10 10 0 0 0-8.6 15.1L2 22l5-1.3A10 10 0 1 0 12 2zm5.3 14.1c-.2.6-1.3 1.2-1.8 1.2-.5.1-1 .2-3.3-.7-2.8-1.1-4.6-4-4.7-4.2-.1-.2-1.1-1.5-1.1-2.8 0-1.3.7-2 1-2.3.2-.3.5-.3.7-.3h.5c.2 0 .4 0 .6.5l.8 2c.1.2.1.3 0 .5l-.4.6-.3.4c-.1.1-.3.3-.1.6.2.3.8 1.3 1.7 2.1 1.2 1 2.1 1.3 2.4 1.5.3.1.5.1.6-.1l.9-1c.2-.3.4-.2.6-.1l1.9.9c.3.1.5.2.5.3.1.2.1.7-.1 1.3z"/></svg>',
	);
	return isset( $icons[ $name ] ) ? $icons[ $name ] : '';
}

/**
 * Three-stripe flag bar.
 */
function mig_flag() {
	echo '<div class="flag" aria-hidden="true"><i></i><i></i><i></i></div>';
}

/**
 * Site logo: the custom logo if set, else the flag mark and site name.
 */
function mig_logo() {
	if ( has_custom_logo() ) {
		the_custom_logo();
		return;
	}
	$desc = get_bloginfo( 'description' );
	printf(
		'<a class="logo" href="%1$s" rel="home"><span class="mark" aria-hidden="true"><i></i><i></i><i></i></span><span><b>%2$s</b>%3$s</span></a>',
		esc_url( home_url( '/' ) ),
		esc_html( get_bloginfo( 'name' ) ),
		$desc ? '<small>' . esc_html( $desc ) . '</small>' : ''
	);
}

/**
 * Emoji icon and colour stored on a product category.
 *
 * @param WP_Term|int|null $term Term.
 * @return array{icon:string,color:string}
 */
function mig_cat_style( $term ) {
	$id    = is_object( $term ) ? $term->term_id : (int) $term;
	$icon  = $id ? get_term_meta( $id, 'mig_icon', true ) : '';
	$color = $id ? get_term_meta( $id, 'mig_color', true ) : '';
	return array(
		'icon'  => $icon ? $icon : '🇩🇪',
		'color' => $color ? $color : '#DD0000',
	);
}

/**
 * Fallback header menu when no menu is assigned.
 */
function mig_menu_fallback() {
	$shop = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
	$is_shop = function_exists( 'is_shop' ) && ( is_shop() || is_product_taxonomy() );
	$deals   = $is_shop && ! empty( $_GET['on_sale'] ); // phpcs:ignore WordPress.Security.NonceVerification
	echo '<ul class="menu">';
	printf( '<li class="%s"><a href="%s">%s</a></li>', is_front_page() ? 'current-menu-item' : '', esc_url( home_url( '/' ) ), esc_html__( 'Home', 'made-in-germany' ) );
	printf( '<li class="%s"><a href="%s">%s</a></li>', $is_shop && ! $deals ? 'current-menu-item' : '', esc_url( $shop ), esc_html__( 'Shop', 'made-in-germany' ) );
	printf( '<li class="%s"><a href="%s">%s</a></li>', $deals ? 'current-menu-item' : '', esc_url( add_query_arg( 'on_sale', '1', $shop ) ), esc_html__( 'Deals', 'made-in-germany' ) );
	echo '</ul>';
}

/**
 * Footer help links fallback.
 */
function mig_help_fallback() {
	echo '<ul>';
	if ( function_exists( 'wc_get_page_permalink' ) ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( wc_get_page_permalink( 'myaccount' ) ), esc_html__( 'My account & orders', 'made-in-germany' ) );
		printf( '<li><a href="%s">%s</a></li>', esc_url( wc_get_cart_url() ), esc_html__( 'Cart', 'made-in-germany' ) );
		printf( '<li><a href="%s">%s</a></li>', esc_url( wc_get_checkout_url() ), esc_html__( 'Checkout', 'made-in-germany' ) );
	}
	$privacy = get_privacy_policy_url();
	if ( $privacy ) {
		printf( '<li><a href="%s">%s</a></li>', esc_url( $privacy ), esc_html__( 'Privacy policy', 'made-in-germany' ) );
	}
	echo '</ul>';
}

/**
 * Page hero used by regular pages, posts and WooCommerce pages.
 *
 * @param string $title Title.
 * @param string $eyebrow Small label.
 */
function mig_page_hero( $title, $eyebrow = '' ) {
	echo '<div class="shop-hero page-hero"><div class="wrap">';
	if ( $eyebrow ) {
		echo '<div class="eyebrow">' . esc_html( $eyebrow ) . '</div>';
	}
	echo '<h1 id="shopTitle"><span>' . esc_html( $title ) . '</span></h1>';
	echo '</div></div>';
}
