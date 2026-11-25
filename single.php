<?php
/**
 * Single blog post.
 *
 * @package Made_In_Germany
 */

defined( 'ABSPATH' ) || exit;
get_header();

while ( have_posts() ) :
	the_post();
	mig_page_hero( get_the_title(), get_the_date() );
	?>
	<div class="wrap page-body">
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'entry narrow' ); ?>>
			<?php if ( has_post_thumbnail() ) : ?>
				<div class="entry-img"><?php the_post_thumbnail( 'large' ); ?></div>
			<?php endif; ?>
			<?php
			the_content();
			wp_link_pages();
			?>
		</article>
		<?php
		the_post_navigation();
		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
		?>
	</div>
	<?php
endwhile;

get_footer();
