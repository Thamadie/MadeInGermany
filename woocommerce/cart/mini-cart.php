<?php
/**
 * Cart drawer contents. WooCommerce refreshes this through cart fragments.
 *
 * Overrides woocommerce/templates/cart/mini-cart.php
 *
 * @package Made_In_Germany
 * @version 10.0.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_mini_cart' );

if ( WC()->cart && ! WC()->cart->is_empty() ) :
	$subtotal  = (float) WC()->cart->get_displayed_subtotal();
	$threshold = function_exists( 'mig_free_shipping_threshold' ) ? mig_free_shipping_threshold() : 0;
	$left      = max( 0, $threshold - $subtotal );
	$pct       = $threshold > 0 ? min( 100, $subtotal / $threshold * 100 ) : 100;
	?>
	<div class="d-body">
		<?php if ( $threshold > 0 ) : ?>
			<div class="ship-bar">
				<?php if ( $left > 0 ) : ?>
					<?php
					/* translators: %s: amount */
					printf( wp_kses_post( __( 'Add <b class="mono">%s</b> more for <b>free delivery</b>', 'made-in-germany' ) ), wp_kses_post( wc_price( $left ) ) );
					?>
				<?php else : ?>
					<?php echo wp_kses_post( __( '🎉 You\'ve unlocked <b>free island-wide delivery</b>', 'made-in-germany' ) ); ?>
				<?php endif; ?>
				<div class="bar"><i style="width:<?php echo esc_attr( round( $pct ) ); ?>%"></i></div>
			</div>
		<?php endif; ?>

		<ul class="woocommerce-mini-cart cart_list product_list_widget">
			<?php
			do_action( 'woocommerce_before_mini_cart_contents' );
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) :
				$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
				$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );
				if ( ! $_product || ! $_product->exists() || $cart_item['quantity'] <= 0 || ! apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
					continue;
				}
				$name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
				$link      = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
				$parent    = wc_get_product( $product_id );
				$line      = WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] );
				$qty       = (int) $cart_item['quantity'];
				?>
				<li class="line-item woocommerce-mini-cart-item <?php echo esc_attr( apply_filters( 'woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key ) ); ?>" style="--h:<?php echo esc_attr( $parent ? mig_product_hue( $parent ) : '#DD0000' ); ?>">
					<a class="mini" href="<?php echo esc_url( $link ); ?>" tabindex="-1" aria-hidden="true">
						<?php
						if ( $_product->get_image_id() || ! $parent ) {
							echo $_product->get_image( 'woocommerce_gallery_thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput
						} else {
							mig_product_visual( $parent );
						}
						?>
					</a>
					<div>
						<div class="n"><?php echo $link ? '<a href="' . esc_url( $link ) . '">' . wp_kses_post( $name ) . '</a>' : wp_kses_post( $name ); ?></div>
						<div class="p"><?php echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); // phpcs:ignore WordPress.Security.EscapeOutput ?></div>
						<?php echo wc_get_formatted_cart_item_data( $cart_item ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
						<?php if ( $_product->is_sold_individually() ) : ?>
							<div class="p"><?php esc_html_e( 'Qty 1', 'made-in-germany' ); ?></div>
						<?php else : ?>
							<div class="qty">
								<button type="button" data-key="<?php echo esc_attr( $cart_item_key ); ?>" data-qty="<?php echo (int) ( $qty - 1 ); ?>" aria-label="<?php esc_attr_e( 'Decrease quantity', 'made-in-germany' ); ?>">−</button>
								<span><?php echo (int) $qty; ?></span>
								<button type="button" data-key="<?php echo esc_attr( $cart_item_key ); ?>" data-qty="<?php echo (int) ( $qty + 1 ); ?>" aria-label="<?php esc_attr_e( 'Increase quantity', 'made-in-germany' ); ?>">+</button>
							</div>
						<?php endif; ?>
					</div>
					<div class="li-right">
						<div class="mono line-total"><?php echo wp_kses_post( $line ); ?></div>
						<?php
						echo apply_filters( // phpcs:ignore WordPress.Security.EscapeOutput
							'woocommerce_cart_item_remove_link',
							sprintf(
								'<a role="button" href="%s" class="rm remove remove_from_cart_button" aria-label="%s" data-product_id="%s" data-cart_item_key="%s" data-product_sku="%s" data-success_message="%s">%s</a>',
								esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
								/* translators: %s: product name */
								esc_attr( sprintf( __( 'Remove %s from cart', 'made-in-germany' ), wp_strip_all_tags( $name ) ) ),
								esc_attr( $product_id ),
								esc_attr( $cart_item_key ),
								esc_attr( $_product->get_sku() ),
								/* translators: %s: product name */
								esc_attr( sprintf( __( '“%s” has been removed from your cart', 'made-in-germany' ), wp_strip_all_tags( $name ) ) ),
								esc_html__( 'Remove', 'made-in-germany' )
							),
							$cart_item_key
						);
						?>
					</div>
				</li>
			<?php endforeach; ?>
			<?php do_action( 'woocommerce_mini_cart_contents' ); ?>
		</ul>
	</div>

	<div class="d-foot">
		<div class="totals">
			<div><span><?php esc_html_e( 'Subtotal', 'made-in-germany' ); ?></span><span class="mono"><?php echo wp_kses_post( WC()->cart->get_cart_subtotal() ); ?></span></div>
			<div class="muted"><span><?php esc_html_e( 'Delivery', 'made-in-germany' ); ?></span><span><?php esc_html_e( 'Calculated at checkout', 'made-in-germany' ); ?></span></div>
		</div>
		<?php do_action( 'woocommerce_widget_shopping_cart_before_buttons' ); ?>
		<a class="btn btn-primary btn-block" href="<?php echo esc_url( wc_get_checkout_url() ); ?>"><?php esc_html_e( 'Checkout', 'made-in-germany' ); ?> <?php echo mig_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
		<a class="rm view-cart" href="<?php echo esc_url( wc_get_cart_url() ); ?>"><?php esc_html_e( 'View full cart', 'made-in-germany' ); ?></a>
		<?php do_action( 'woocommerce_widget_shopping_cart_after_buttons' ); ?>
	</div>

<?php else : ?>
	<div class="d-body">
		<div class="cart-empty woocommerce-mini-cart__empty-message">
			<div>🛒</div>
			<p><b><?php esc_html_e( 'Your cart is empty.', 'made-in-germany' ); ?></b><br><?php esc_html_e( 'Start with a bestseller like our Alpine milk chocolate.', 'made-in-germany' ); ?></p>
		</div>
	</div>
	<div class="d-foot">
		<a class="btn btn-primary btn-block" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Go to the shop', 'made-in-germany' ); ?></a>
	</div>
<?php endif; ?>

<?php
do_action( 'woocommerce_after_mini_cart' );
