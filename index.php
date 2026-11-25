<?php
/**
 * Blog, archives and search results.
 *
 * @package Made_In_Germany
 */

defined( 'ABSPATH' ) || exit;
get_header();

if ( is_home() && ! is_front_page() ) {
	$title = single_post_title( '', false );
} elseif ( is_search() ) {
	/* translators: %s: search words */
	$title = sprintf( __( 'Results for “%s”', 'made-in-germany' ), get_search_query() );
} elseif ( is_archive() ) {
	$title = wp_strip_all_tags( get_the_archive_title() );
} else {
	$title = __( 'News', 'made-in-germany' );
}
mig_page_hero( $title, __( 'Journal', 'made-in-germany' ) );
?>
<div class="wrap page-body">
	<?php if ( have_posts() ) : ?>
		<div class="post-grid">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'post-card' ); ?>>
					<?php if ( has_post_thumbnail() ) : ?>
						<a class="post-img" href="<?php the_permalink(); ?>" tabindex="-1" aria-hidden="true"><?php the_post_thumbnail( 'medium_large' ); ?></a>
					<?php endif; ?>
					<div class="post-info">
						<span class="brand"><?php echo esc_html( get_the_date() ); ?></span>
						<h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<?php the_excerpt(); ?>
					</div>
				</article>
			<?php endwhile; ?>
		</div>
		<?php the_posts_pagination( array( 'mid_size' => 1 ) ); ?>
	<?php else : ?>
		<div class="empty"><b><?php esc_html_e( 'Nothing here yet', 'made-in-germany' ); ?></b><?php esc_html_e( 'Try a different search.', 'made-in-germany' ); ?></div>
	<?php endif; ?>
</div>
<?php
get_footer();
