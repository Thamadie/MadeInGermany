<?php
/**
 * Product grid opening tag.
 *
 * Overrides woocommerce/templates/loop/loop-start.php
 *
 * @package Made_In_Germany
 * @version 3.3.0
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="grid products columns-<?php echo esc_attr( wc_get_loop_prop( 'columns' ) ); ?>" id="<?php echo is_shop() || is_product_taxonomy() || ( is_search() && 'product' === get_query_var( 'post_type' ) ) ? 'grid' : ''; ?>">
