<?php
/**
 * Product card used in the shop, home page favourites, related products and upsells.
 *
 * Overrides woocommerce/templates/content-product.php
 *
 * @package Made_In_Germany
 * @version 9.4.0
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

$pid    = $product->get_id();
$link   = apply_filters( 'woocommerce_loop_product_link', get_the_permalink(), $product );
$brand  = get_post_meta( $pid, '_mig_brand', true );
$origin = get_post_meta( $pid, '_mig_origin', true );
$meta   = implode( ' · ', array_filter( array( $brand, $origin ) ) );
$rating = (float) $product->get_average_rating();
$count  = (int) $product->get_review_count();
$index  = (int) wc_get_loop_prop( 'loop' );
$simple = $product->is_type( 'simple' ) && $product->is_purchasable() && $product->is_in_stock();
$name   = $product->get_name();
?>
<article <?php wc_product_class( 'card', $product ); ?> style="--h:<?php echo esc_attr( mig_product_hue( $product ) ); ?>;animation-delay:<?php echo (int) min( $index, 12 ) * 40; ?>ms">
	<a class="thumb" href="<?php echo esc_url( $link ); ?>" aria-label="<?php echo esc_attr( $name ); ?>">
		<?php mig_product_visual( $product ); ?>
		<span class="shine"></span>
		<?php echo mig_product_badge( $product ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
	</a>
	<button class="heart" type="button" data-wish-id="<?php echo (int) $pid; ?>" aria-pressed="false" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: product */ __( 'Save %s', 'made-in-germany' ), $name ) ); ?>"><?php echo mig_icon( 'heart' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
	<div class="info">
		<?php if ( $meta ) : ?><span class="brand"><?php echo esc_html( $meta ); ?></span><?php endif; ?>
		<h3 class="pname"><a href="<?php echo esc_url( $link ); ?>"><?php echo esc_html( $name ); ?></a></h3>
		<?php if ( $product->get_short_description() ) : ?>
			<p class="pdesc"><?php echo esc_html( wp_trim_words( wp_strip_all_tags( $product->get_short_description() ), 24 ) ); ?></p>
		<?php endif; ?>
		<?php if ( $rating > 0 ) : ?>
			<span class="rating"><span class="stars" aria-hidden="true"><?php echo esc_html( str_repeat( '★', (int) round( $rating ) ) . str_repeat( '☆', 5 - (int) round( $rating ) ) ); ?></span><?php echo esc_html( number_format_i18n( $rating, 1 ) ); ?> <span class="mono">(<?php echo (int) $count; ?>)</span></span>
		<?php endif; ?>
		<div class="buy">
			<div class="price"><?php echo $product->get_price_html(); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
			<?php if ( $simple ) : ?>
				<a href="<?php echo esc_url( $product->add_to_cart_url() ); ?>" data-quantity="1" data-product_id="<?php echo (int) $pid; ?>" data-product_sku="<?php echo esc_attr( $product->get_sku() ); ?>" class="add add_to_cart_button ajax_add_to_cart product_type_simple" rel="nofollow" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: product */ __( 'Add %s to cart', 'made-in-germany' ), $name ) ); ?>"><?php echo mig_icon( 'plus' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
			<?php else : ?>
				<a href="<?php echo esc_url( $link ); ?>" class="add add-view" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: product */ __( 'View options for %s', 'made-in-germany' ), $name ) ); ?>"><?php echo mig_icon( 'eye' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
			<?php endif; ?>
		</div>
	</div>
</article>
