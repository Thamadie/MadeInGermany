<?php
/**
 * Pages, including Cart, Checkout and My account.
 *
 * @package Made_In_Germany
 */

defined( 'ABSPATH' ) || exit;
get_header();

while ( have_posts() ) :
	the_post();
	$eyebrow = '';
	if ( function_exists( 'is_cart' ) ) {
		if ( is_cart() ) {
			$eyebrow = __( 'Warenkorb · Step 1 of 2', 'made-in-germany' );
		} elseif ( is_checkout() && ! is_order_received_page() ) {
			$eyebrow = __( 'Kasse · Step 2 of 2', 'made-in-germany' );
		} elseif ( is_account_page() ) {
			$eyebrow = __( 'Mein Konto', 'made-in-germany' );
		}
	}
	mig_page_hero( get_the_title(), $eyebrow );
	?>
	<div class="wrap page-body">
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry' ); ?>>
			<?php
			the_content();
			wp_link_pages();
			?>
		</article>
		<?php
		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
		?>
	</div>
	<?php
endwhile;

get_footer();
