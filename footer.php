<?php
/**
 * Site footer, cart drawer and toast.
 *
 * @package Made_In_Germany
 */

defined( 'ABSPATH' ) || exit;
$wc = class_exists( 'WooCommerce' );
?>
</main>

<footer class="site-footer">
	<?php mig_flag(); ?>
	<div class="wrap cols">
		<div>
			<?php mig_logo(); ?>
			<p class="f-about"><?php echo esc_html( mig_opt( 'footer_about' ) ); ?></p>
		</div>
		<div>
			<h4><?php esc_html_e( 'Shop', 'made-in-germany' ); ?></h4>
			<ul>
				<?php
				if ( $wc ) {
					$cats = get_terms(
						array(
							'taxonomy'   => 'product_cat',
							'hide_empty' => true,
							'parent'     => 0,
							'number'     => 6,
							'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
						)
					);
					if ( ! is_wp_error( $cats ) ) {
						foreach ( $cats as $c ) {
							printf( '<li><a href="%s">%s</a></li>', esc_url( add_query_arg( 'cats', $c->slug, wc_get_page_permalink( 'shop' ) ) ), esc_html( $c->name ) );
						}
					}
				}
				?>
			</ul>
		</div>
		<div>
			<h4><?php esc_html_e( 'Help', 'made-in-germany' ); ?></h4>
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'footer_help',
					'container'      => false,
					'depth'          => 1,
					'fallback_cb'    => 'mig_help_fallback',
				)
			);
			?>
		</div>
		<div>
			<h4><?php esc_html_e( 'Visit us', 'made-in-germany' ); ?></h4>
			<ul>
				<?php if ( mig_opt( 'address' ) ) : ?><li><?php echo nl2br( esc_html( mig_opt( 'address' ) ) ); ?></li><?php endif; ?>
				<?php if ( mig_opt( 'phone' ) ) : ?><li class="mono"><?php echo esc_html( mig_opt( 'phone' ) ); ?></li><?php endif; ?>
				<?php if ( mig_opt( 'hours' ) ) : ?><li><?php echo esc_html( mig_opt( 'hours' ) ); ?></li><?php endif; ?>
			</ul>
		</div>
	</div>
	<div class="wrap bottom">
		<span><?php echo esc_html( str_replace( '{year}', gmdate( 'Y' ), mig_opt( 'copyright' ) ) ); ?></span>
		<div class="pay"><span>VISA</span><span>MASTERCARD</span><span>AMEX</span><span>COD</span><span>BANK TRANSFER</span></div>
	</div>
</footer>

<?php if ( mig_opt( 'whatsapp' ) ) : ?>
	<a class="wa" href="<?php echo esc_url( 'https://wa.me/' . preg_replace( '/\D/', '', mig_opt( 'whatsapp' ) ) ); ?>" target="_blank" rel="noopener" aria-label="<?php esc_attr_e( 'Chat on WhatsApp', 'made-in-germany' ); ?>"><?php echo mig_icon( 'chat' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
<?php endif; ?>

<div class="scrim" id="scrim"></div>

<?php if ( $wc && ! is_cart() && ! is_checkout() ) : ?>
	<aside class="drawer" id="drawer" aria-label="<?php esc_attr_e( 'Shopping cart', 'made-in-germany' ); ?>" aria-hidden="true">
		<div class="d-head">
			<h3><?php esc_html_e( 'Your cart', 'made-in-germany' ); ?></h3>
			<button class="icon-btn" type="button" data-close aria-label="<?php esc_attr_e( 'Close cart', 'made-in-germany' ); ?>"><?php echo mig_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
		</div>
		<div class="widget_shopping_cart_content"><?php woocommerce_mini_cart(); ?></div>
	</aside>
<?php endif; ?>

<div class="toast" id="toast" role="status"><i></i><span id="toastMsg"></span></div>

<?php wp_footer(); ?>
</body>
</html>
