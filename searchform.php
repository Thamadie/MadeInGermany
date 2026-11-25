<?php
/**
 * Search form.
 *
 * @package Made_In_Germany
 */

defined( 'ABSPATH' ) || exit;
$uid = wp_unique_id( 'search-' );
?>
<form role="search" method="get" class="search search-inline" action="<?php echo esc_url( home_url( '/' ) ); ?>">
	<label class="screen-reader-text" for="<?php echo esc_attr( $uid ); ?>"><?php esc_html_e( 'Search', 'made-in-germany' ); ?></label>
	<input type="search" id="<?php echo esc_attr( $uid ); ?>" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'Search…', 'made-in-germany' ); ?>">
	<button type="submit" aria-label="<?php esc_attr_e( 'Search', 'made-in-germany' ); ?>"><?php echo mig_icon( 'search' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
</form>
