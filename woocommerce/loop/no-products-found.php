<?php
/**
 * Shown when filters match nothing.
 *
 * Overrides woocommerce/templates/loop/no-products-found.php
 *
 * @package Made_In_Germany
 * @version 7.8.0
 */

defined( 'ABSPATH' ) || exit;
$state = function_exists( 'mig_filter_state' ) ? mig_filter_state() : array( 'saved' => false );
?>
<div class="empty woocommerce-no-products-found">
	<?php if ( ! empty( $state['saved'] ) ) : ?>
		<b><?php esc_html_e( 'Nothing saved yet', 'made-in-germany' ); ?></b>
		<?php esc_html_e( 'Tap the heart on any product to save it here.', 'made-in-germany' ); ?>
	<?php else : ?>
		<b><?php esc_html_e( 'No matching products', 'made-in-germany' ); ?></b>
		<?php esc_html_e( 'Remove a filter or two, or search for something like “chocolate”.', 'made-in-germany' ); ?>
	<?php endif; ?>
	<div style="margin-top:16px"><a class="btn btn-ghost" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'Clear all filters', 'made-in-germany' ); ?></a></div>
</div>
