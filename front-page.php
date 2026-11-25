<?php
/**
 * Home page.
 *
 * @package Made_In_Germany
 */

defined( 'ABSPATH' ) || exit;
get_header();

$wc   = class_exists( 'WooCommerce' );
$shop = $wc ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
?>

<div class="hero">
	<div class="wrap hero-grid">
		<div>
			<div class="eyebrow fade-in"><?php echo esc_html( mig_opt( 'hero_eyebrow' ) ); ?></div>
			<h1>
				<span class="line"><span><?php echo esc_html( mig_opt( 'hero_line1' ) ); ?></span></span>
				<span class="line"><span><?php echo esc_html( mig_opt( 'hero_line2' ) ); ?></span></span>
				<span class="line"><span><?php echo esc_html( mig_opt( 'hero_line3' ) ); ?> <span class="gold"><?php echo esc_html( mig_opt( 'hero_highlight' ) ); ?></span></span></span>
			</h1>
			<p class="lede"><?php echo esc_html( mig_opt( 'hero_text' ) ); ?></p>
			<div class="cta-row">
				<a class="btn btn-primary" href="<?php echo esc_url( $shop ); ?>"><?php esc_html_e( 'Shop now', 'made-in-germany' ); ?> <?php echo mig_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
				<a class="btn btn-ghost" href="#categories"><?php esc_html_e( 'Browse categories', 'made-in-germany' ); ?></a>
			</div>
			<div class="stats">
				<?php
				for ( $i = 1; $i <= 3; $i++ ) {
					$num = mig_opt( "stat{$i}_num" );
					if ( '' === $num ) {
						continue;
					}
					printf(
						'<div><b class="mono" data-count="%1$s" data-suffix="%2$s">%3$s%2$s</b><span>%4$s</span></div>',
						esc_attr( (int) $num ),
						esc_attr( mig_opt( "stat{$i}_suffix" ) ),
						esc_html( number_format_i18n( (int) $num ) ),
						esc_html( mig_opt( "stat{$i}_label" ) )
					);
				}
				?>
			</div>
		</div>
		<div class="stage" aria-hidden="true">
			<div class="band b1"></div><div class="band b2"></div><div class="band b3"></div>
			<div class="float f1">🍫<small>Schokolade</small></div>
			<div class="float f2">🌹<small>Parfum</small></div>
			<div class="float f3">💻<small>Laptops</small></div>
			<div class="float f4">👟<small>Schuhe</small></div>
			<div class="seal"><span><em>Qualität</em>Made in<br>Germany</span></div>
		</div>
	</div>
</div>

<?php
if ( $wc ) :
	$cats = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => true,
			'parent'     => 0,
			'number'     => 12,
			'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
		)
	);
	if ( ! is_wp_error( $cats ) && $cats ) :
		?>
		<section id="categories">
			<div class="wrap">
				<div class="sec-head reveal">
					<div>
						<div class="eyebrow">
							<?php
							/* translators: %d: number of departments */
							printf( esc_html__( '%d departments', 'made-in-germany' ), count( $cats ) );
							?>
						</div>
						<h2><?php echo wp_kses( __( 'Shop by <em>category</em>', 'made-in-germany' ), array( 'em' => array() ) ); ?></h2>
					</div>
					<p><?php esc_html_e( 'From Black Forest chocolate to Bavarian-engineered laptops. Pick a category to start shopping.', 'made-in-germany' ); ?></p>
				</div>
				<div class="cats">
					<?php
					foreach ( $cats as $i => $c ) :
						$st = mig_cat_style( $c );
						?>
						<a class="cat" href="<?php echo esc_url( add_query_arg( 'cats', $c->slug, $shop ) ); ?>" style="animation-delay:<?php echo (int) $i * 40; ?>ms">
							<span class="ic" aria-hidden="true"><?php echo esc_html( $st['icon'] ); ?></span>
							<b><?php echo esc_html( $c->name ); ?></b>
							<span>
								<?php
								/* translators: %d: product count */
								printf( esc_html( _n( '%d item', '%d items', $c->count, 'made-in-germany' ) ), (int) $c->count );
								?>
							</span>
						</a>
					<?php endforeach; ?>
				</div>
			</div>
		</section>
	<?php endif; ?>

	<?php if ( mig_opt( 'deal_show' ) ) : ?>
		<?php
		$deal_args = array( 'on_sale' => 1 );
		if ( mig_opt( 'deal_cats' ) ) {
			$deal_args['cats'] = preg_replace( '/\s+/', '', mig_opt( 'deal_cats' ) );
		}
		?>
		<div class="wrap reveal">
			<div class="deal">
				<div>
					<div class="eyebrow on-dark"><?php echo esc_html( mig_opt( 'deal_eyebrow' ) ); ?></div>
					<h3><?php echo wp_kses( mig_opt( 'deal_title' ), array( 'em' => array() ) ); ?></h3>
					<p><?php echo esc_html( mig_opt( 'deal_text' ) ); ?></p>
					<a class="btn btn-primary" href="<?php echo esc_url( add_query_arg( $deal_args, $shop ) ); ?>"><?php esc_html_e( 'Grab the deal', 'made-in-germany' ); ?> <?php echo mig_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
				</div>
				<div class="timer" aria-label="<?php esc_attr_e( 'Time left on today\'s deal', 'made-in-germany' ); ?>">
					<div><b id="th">00</b><span><?php esc_html_e( 'Hours', 'made-in-germany' ); ?></span></div>
					<div><b id="tm">00</b><span><?php esc_html_e( 'Minutes', 'made-in-germany' ); ?></span></div>
					<div><b id="ts">00</b><span><?php esc_html_e( 'Seconds', 'made-in-germany' ); ?></span></div>
				</div>
			</div>
		</div>
	<?php endif; ?>

	<?php
	$fav = new WP_Query(
		array(
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'posts_per_page'      => 8,
			'ignore_sticky_posts' => true,
			'meta_key'            => 'total_sales', // phpcs:ignore WordPress.DB.SlowDBQuery
			'orderby'             => array(
				'meta_value_num' => 'DESC',
				'date'           => 'DESC',
			),
			'tax_query'           => WC()->query->get_tax_query(), // phpcs:ignore WordPress.DB.SlowDBQuery -- hides products set to hidden.
		)
	);
	if ( $fav->have_posts() ) :
		?>
		<section id="featured">
			<div class="wrap">
				<div class="sec-head reveal">
					<div>
						<div class="eyebrow"><?php esc_html_e( 'Meistverkauft · Bestsellers', 'made-in-germany' ); ?></div>
						<h2><?php echo wp_kses( __( 'Customer <em>favourites</em>', 'made-in-germany' ), array( 'em' => array() ) ); ?></h2>
					</div>
					<a class="btn btn-ghost" href="<?php echo esc_url( $shop ); ?>"><?php esc_html_e( 'View all products', 'made-in-germany' ); ?> <?php echo mig_icon( 'arrow' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></a>
				</div>
				<?php
				wc_set_loop_prop( 'name', 'favourites' );
				woocommerce_product_loop_start();
				while ( $fav->have_posts() ) {
					$fav->the_post();
					wc_get_template_part( 'content', 'product' );
				}
				woocommerce_product_loop_end();
				wp_reset_postdata();
				?>
			</div>
		</section>
	<?php endif; ?>
<?php endif; ?>

<section class="pt0">
	<div class="wrap">
		<div class="sec-head reveal">
			<div>
				<div class="eyebrow"><?php esc_html_e( 'Warum wir · Why us', 'made-in-germany' ); ?></div>
				<h2><?php echo wp_kses( __( 'German quality, <em>Sri Lankan</em> service', 'made-in-germany' ), array( 'em' => array() ) ); ?></h2>
			</div>
		</div>
		<div class="why">
			<div class="reveal" style="--c:var(--schwarz)"><span class="k">01 · ECHT</span><b><?php esc_html_e( '100% authentic', 'made-in-germany' ); ?></b><p><?php esc_html_e( 'Every item is sourced from licensed German distributors and arrives with its import invoice.', 'made-in-germany' ); ?></p></div>
			<div class="reveal" style="--c:var(--rot)"><span class="k">02 · ZOLL</span><b><?php esc_html_e( 'Duty included', 'made-in-germany' ); ?></b><p><?php esc_html_e( 'Prices include Sri Lankan customs duty and VAT. What you see is what you pay.', 'made-in-germany' ); ?></p></div>
			<div class="reveal" style="--c:var(--gold)"><span class="k">03 · FRISCH</span><b><?php esc_html_e( 'Climate-stored', 'made-in-germany' ); ?></b><p><?php esc_html_e( 'Chocolate and cosmetics are kept in our air-conditioned Colombo warehouse.', 'made-in-germany' ); ?></p></div>
			<div class="reveal" style="--c:var(--schwarz)"><span class="k">04 · BAR</span><b><?php esc_html_e( 'Cash on delivery', 'made-in-germany' ); ?></b><p><?php esc_html_e( 'Pay with cash, card, or bank transfer. Easy 7-day returns on unopened goods.', 'made-in-germany' ); ?></p></div>
		</div>
	</div>
</section>

<section class="pt0">
	<div class="wrap">
		<div class="sec-head reveal">
			<div>
				<div class="eyebrow"><?php esc_html_e( 'Von Deutschland zu Ihnen', 'made-in-germany' ); ?></div>
				<h2><?php echo wp_kses( __( 'How your order <em>travels</em>', 'made-in-germany' ), array( 'em' => array() ) ); ?></h2>
			</div>
		</div>
		<div class="route reveal">
			<div class="st" style="--c:var(--schwarz)"><div class="dot">DE</div><b><?php esc_html_e( 'Sourced in Germany', 'made-in-germany' ); ?></b><span><?php esc_html_e( 'Hamburg, Munich & Frankfurt suppliers', 'made-in-germany' ); ?></span></div>
			<div class="st" style="--c:var(--rot)"><div class="dot">✈</div><b><?php esc_html_e( 'Air & sea freight', 'made-in-germany' ); ?></b><span><?php esc_html_e( 'Sealed batches to Colombo port', 'made-in-germany' ); ?></span></div>
			<div class="st" style="--c:var(--gold)"><div class="dot">LK</div><b><?php esc_html_e( 'Customs cleared', 'made-in-germany' ); ?></b><span><?php esc_html_e( 'Inspected and stored in Colombo 03', 'made-in-germany' ); ?></span></div>
			<div class="st" style="--c:var(--schwarz)"><div class="dot">🏠</div><b><?php esc_html_e( 'At your door', 'made-in-germany' ); ?></b><span><?php esc_html_e( '1–2 days Colombo, 2–4 days island-wide', 'made-in-germany' ); ?></span></div>
		</div>
	</div>
</section>

<section class="pt0">
	<div class="wrap reveal">
		<div class="news">
			<div>
				<h3><?php echo esc_html( mig_opt( 'news_title' ) ); ?></h3>
				<p><?php echo esc_html( mig_opt( 'news_text' ) ); ?></p>
			</div>
			<?php if ( mig_opt( 'news_shortcode' ) ) : ?>
				<div class="news-plugin"><?php echo do_shortcode( mig_opt( 'news_shortcode' ) ); ?></div>
			<?php else : ?>
				<form id="newsForm" novalidate>
					<input id="newsEmail" type="email" placeholder="you@example.lk" aria-label="<?php esc_attr_e( 'Email address', 'made-in-germany' ); ?>" required>
					<button type="submit"><?php esc_html_e( 'Subscribe', 'made-in-germany' ); ?></button>
				</form>
			<?php endif; ?>
		</div>
	</div>
</section>

<?php
get_footer();
