<?php
/**
 * Site header.
 *
 * @package Made_In_Germany
 */

defined( 'ABSPATH' ) || exit;
$wc = class_exists( 'WooCommerce' );
?><!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="skip-link" href="#content"><?php esc_html_e( 'Skip to content', 'made-in-germany' ); ?></a>

<?php mig_flag(); ?>
<?php
$lines = array_filter( array_map( 'trim', explode( "\n", (string) mig_opt( 'announcements' ) ) ) );
if ( $lines ) :
	$items = '';
	foreach ( $lines as $l ) {
		$items .= '<span>' . wp_kses( $l, array( 'b' => array(), 'strong' => array() ) ) . '</span>';
	}
	?>
	<div class="announce" aria-label="<?php esc_attr_e( 'Store announcements', 'made-in-germany' ); ?>">
		<div class="track"><?php echo $items . $items; // phpcs:ignore WordPress.Security.EscapeOutput -- built from wp_kses above. ?></div>
	</div>
<?php endif; ?>

<header class="top">
	<div class="wrap nav">
		<?php mig_logo(); ?>
		<nav class="links" aria-label="<?php esc_attr_e( 'Main', 'made-in-germany' ); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'menu',
					'depth'          => 1,
					'fallback_cb'    => 'mig_menu_fallback',
				)
			);
			?>
		</nav>
		<form class="search" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>">
			<label class="screen-reader-text" for="q"><?php esc_html_e( 'Search products', 'made-in-germany' ); ?></label>
			<input id="q" type="search" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search chocolates, perfumes, laptops…', 'made-in-germany' ); ?>" autocomplete="off">
			<?php if ( $wc ) : ?><input type="hidden" name="post_type" value="product"><?php endif; ?>
			<button type="submit" aria-label="<?php esc_attr_e( 'Search', 'made-in-germany' ); ?>"><?php echo mig_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
		</form>
		<div class="actions">
			<button class="icon-btn" id="themeBtn" type="button" aria-label="<?php esc_attr_e( 'Switch light or dark theme', 'made-in-germany' ); ?>"><?php echo mig_icon( 'moon' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
			<?php if ( $wc ) : ?>
				<a class="icon-btn" id="wishBtn" href="<?php echo esc_url( add_query_arg( 'saved', '1', wc_get_page_permalink( 'shop' ) ) ); ?>" aria-label="<?php esc_attr_e( 'Saved items', 'made-in-germany' ); ?>">
					<?php echo mig_icon( 'heart-o' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<span class="badge" id="wishCount" hidden>0</span>
				</a>
				<a class="icon-btn hide-sm" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>" aria-label="<?php esc_attr_e( 'My account', 'made-in-germany' ); ?>"><?php echo mig_icon( 'user' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
				<a class="icon-btn" id="cartBtn" href="<?php echo esc_url( wc_get_cart_url() ); ?>" aria-label="<?php esc_attr_e( 'Open cart', 'made-in-germany' ); ?>">
					<?php echo mig_icon( 'cart' ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
					<?php echo mig_cart_count_html(); // phpcs:ignore WordPress.Security.EscapeOutput ?>
				</a>
			<?php endif; ?>
		</div>
	</div>
</header>

<main id="content">
