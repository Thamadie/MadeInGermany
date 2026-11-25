<?php
/**
 * Not found.
 *
 * @package Made_In_Germany
 */

defined( 'ABSPATH' ) || exit;
get_header();
mig_page_hero( __( 'Page not found', 'made-in-germany' ), '404 · Nicht gefunden' );
?>
<div class="wrap page-body">
	<div class="empty">
		<b><?php esc_html_e( 'This page has moved or never existed', 'made-in-germany' ); ?></b>
		<?php esc_html_e( 'Search the shop or head back to the home page.', 'made-in-germany' ); ?>
		<div class="cta-row" style="justify-content:center;margin-top:18px">
			<a class="btn btn-primary" href="<?php echo esc_url( function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/' ) ); ?>"><?php esc_html_e( 'Go to the shop', 'made-in-germany' ); ?></a>
			<a class="btn btn-ghost" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'Home', 'made-in-germany' ); ?></a>
		</div>
	</div>
</div>
<?php
get_footer();
