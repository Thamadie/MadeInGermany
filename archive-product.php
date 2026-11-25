<?php
/**
 * Shop, product category and product search pages, with the filter panel.
 *
 * Overrides woocommerce/templates/archive-product.php
 *
 * @package Made_In_Germany
 * @version 8.6.0
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' );

$state = mig_filter_state();
$data  = mig_filter_data();
$total = (int) wc_get_loop_prop( 'total' );
$shop  = wc_get_page_permalink( 'shop' );

// On a category page, that category counts as selected.
$sel_cats = $state['cats'];
if ( is_product_category() ) {
	$sel_cats[] = get_queried_object()->slug;
	$sel_cats   = array_unique( $sel_cats );
}

$active_count = count( $sel_cats ) + count( $state['made_in'] ) + ( null !== $state['min_price'] || null !== $state['max_price'] ? 1 : 0 )
	+ ( $state['min_rating'] ? 1 : 0 ) + (int) $state['on_sale'] + (int) $state['new'] + (int) $state['best'] + (int) $state['saved'] + ( $state['s'] ? 1 : 0 );

// Price slider bounds and histogram (log scale, so cheap and expensive items both get room).
$pmin  = max( 1, (float) $data['min'] );
$pmax  = max( $pmin + 1, (float) $data['max'] );
$lmin  = log( $pmin );
$lmax  = log( $pmax );
$bins  = array_fill( 0, 24, 0 );
foreach ( $data['prices'] as $p ) {
	$i = (int) floor( ( log( max( $pmin, $p ) ) - $lmin ) / ( $lmax - $lmin ) * 24 );
	$bins[ min( 23, max( 0, $i ) ) ]++;
}
$bmax    = max( 1, max( $bins ) );
$presets = array(
	array( __( 'Under 2k', 'made-in-germany' ), null, 2000 ),
	array( __( '2k – 10k', 'made-in-germany' ), 2000, 10000 ),
	array( __( '10k – 50k', 'made-in-germany' ), 10000, 50000 ),
	array( __( '50k +', 'made-in-germany' ), 50000, null ),
);
$rates   = array( 0, 4.5, 4.7, 4.8 );
$orderby = array(
	'menu_order' => __( 'Most popular', 'made-in-germany' ),
	'popularity' => __( 'Best selling', 'made-in-germany' ),
	'rating'     => __( 'Top rated', 'made-in-germany' ),
	'date'       => __( 'Newest', 'made-in-germany' ),
	'price'      => __( 'Price: low to high', 'made-in-germany' ),
	'price-desc' => __( 'Price: high to low', 'made-in-germany' ),
);
$cur_order = $state['orderby'] ? $state['orderby'] : 'menu_order';
?>

<div class="shop-hero" id="shopHero">
	<div class="wrap">
		<?php woocommerce_breadcrumb(); ?>
		<h1 id="shopTitle"><span><?php echo esc_html( mig_shop_title() ); ?></span></h1>
		<p id="shopSub">
			<?php
			/* translators: %d: number of products */
			printf( esc_html( _n( '%d genuine German product · prices include duty & VAT', '%d genuine German products · prices include duty & VAT', $total, 'made-in-germany' ) ), (int) $total );
			?>
		</p>
		<?php if ( $data['cats'] ) : ?>
			<div class="quick" aria-label="<?php esc_attr_e( 'Quick category filter', 'made-in-germany' ); ?>">
				<a class="qc<?php echo $sel_cats ? '' : ' on'; ?>" href="<?php echo esc_url( $shop ); ?>"><i>🇩🇪</i><?php esc_html_e( 'All', 'made-in-germany' ); ?></a>
				<?php foreach ( $data['cats'] as $c ) : ?>
					<a class="qc<?php echo ( 1 === count( $sel_cats ) && $sel_cats[0] === $c['slug'] ) ? ' on' : ''; ?>" href="<?php echo esc_url( add_query_arg( 'cats', $c['slug'], $shop ) ); ?>"><i><?php echo esc_html( $c['icon'] ); ?></i><?php echo esc_html( $c['name'] ); ?></a>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</div>

<div class="wrap shop-layout">
	<aside class="filters" id="filters" aria-label="<?php esc_attr_e( 'Product filters', 'made-in-germany' ); ?>">
		<form id="filterForm" method="get" action="<?php echo esc_url( mig_filter_action() ); ?>">
			<div class="f-head">
				<h3><?php esc_html_e( 'Filters', 'made-in-germany' ); ?></h3>
				<a class="clear-link" href="<?php echo esc_url( $shop ); ?>"><?php esc_html_e( 'Clear all', 'made-in-germany' ); ?></a>
				<button class="icon-btn f-close" type="button" data-close aria-label="<?php esc_attr_e( 'Close filters', 'made-in-germany' ); ?>"><?php echo mig_icon( 'close' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
			</div>
			<div class="f-scroll">
				<?php if ( $state['s'] ) : ?>
					<input type="hidden" name="s" value="<?php echo esc_attr( $state['s'] ); ?>">
					<input type="hidden" name="post_type" value="product">
				<?php endif; ?>

				<?php if ( $data['cats'] ) : ?>
					<details class="f-group" open>
						<summary><?php esc_html_e( 'Category', 'made-in-germany' ); ?><?php echo $sel_cats ? '<em>' . esc_html( sprintf( /* translators: %d: count */ __( '%d selected', 'made-in-germany' ), count( $sel_cats ) ) ) . '</em>' : ''; ?></summary>
						<div class="f-body">
							<?php foreach ( $data['cats'] as $c ) : ?>
								<label class="f-check">
									<input type="checkbox" name="cats[]" value="<?php echo esc_attr( $c['slug'] ); ?>" <?php checked( in_array( $c['slug'], $sel_cats, true ) ); ?>>
									<span class="box"><?php echo mig_icon( 'tick' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
									<span class="ic" aria-hidden="true"><?php echo esc_html( $c['icon'] ); ?></span><?php echo esc_html( $c['name'] ); ?>
									<span class="n"><?php echo (int) $c['count']; ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</details>
				<?php endif; ?>

				<?php if ( $data['prices'] ) : ?>
					<details class="f-group" open>
						<summary><?php esc_html_e( 'Price', 'made-in-germany' ); ?> <em id="sumPrice"></em></summary>
						<div class="f-body price-filter" data-min="<?php echo esc_attr( $pmin ); ?>" data-max="<?php echo esc_attr( $pmax ); ?>">
							<div class="hist" id="hist" aria-hidden="true">
								<?php foreach ( $bins as $b ) : ?>
									<i style="height:<?php echo esc_attr( $b ? round( 18 + 82 * $b / $bmax ) : 4 ); ?>%"></i>
								<?php endforeach; ?>
							</div>
							<div class="dual">
								<div class="rail"></div><div class="fill" id="pFill"></div>
								<input type="range" id="pLo" min="0" max="1000" step="1" value="0" aria-label="<?php esc_attr_e( 'Minimum price', 'made-in-germany' ); ?>">
								<input type="range" id="pHi" min="0" max="1000" step="1" value="1000" aria-label="<?php esc_attr_e( 'Maximum price', 'made-in-germany' ); ?>">
							</div>
							<div class="p-inputs">
								<label for="pMinIn"><span>Rs</span><input id="pMinIn" name="min_price" inputmode="numeric" placeholder="<?php esc_attr_e( 'Min', 'made-in-germany' ); ?>" value="<?php echo null !== $state['min_price'] ? esc_attr( $state['min_price'] ) : ''; ?>" aria-label="<?php esc_attr_e( 'Minimum price in rupees', 'made-in-germany' ); ?>"></label>
								<b>–</b>
								<label for="pMaxIn"><span>Rs</span><input id="pMaxIn" name="max_price" inputmode="numeric" placeholder="<?php esc_attr_e( 'Max', 'made-in-germany' ); ?>" value="<?php echo null !== $state['max_price'] ? esc_attr( $state['max_price'] ) : ''; ?>" aria-label="<?php esc_attr_e( 'Maximum price in rupees', 'made-in-germany' ); ?>"></label>
							</div>
							<div class="presets">
								<?php
								foreach ( $presets as $p ) :
									$on = $state['min_price'] === $p[1] && $state['max_price'] === $p[2];
									?>
									<a class="pill<?php echo $on ? ' on' : ''; ?>" data-min="<?php echo esc_attr( (string) $p[1] ); ?>" data-max="<?php echo esc_attr( (string) $p[2] ); ?>" href="<?php echo esc_url( mig_filter_url( $on ? array( 'min_price' => null, 'max_price' => null ) : array( 'min_price' => $p[1], 'max_price' => $p[2] ) ) ); ?>"><?php echo esc_html( $p[0] ); ?></a>
								<?php endforeach; ?>
							</div>
						</div>
					</details>
				<?php endif; ?>

				<details class="f-group" open>
					<summary><?php esc_html_e( 'Customer rating', 'made-in-germany' ); ?></summary>
					<div class="f-body">
						<div class="seg" role="radiogroup" aria-label="<?php esc_attr_e( 'Minimum rating', 'made-in-germany' ); ?>">
							<span class="knob" style="transform:translateX(<?php echo (int) max( 0, array_search( (float) $state['min_rating'], array_map( 'floatval', $rates ), true ) ) * 100; ?>%)"></span>
							<?php foreach ( $rates as $r ) : ?>
								<label class="<?php echo (float) $state['min_rating'] === (float) $r ? 'on' : ''; ?>">
									<input type="radio" name="min_rating" value="<?php echo $r ? esc_attr( $r ) : ''; ?>" <?php checked( (float) $state['min_rating'], (float) $r ); ?>>
									<?php echo $r ? '<span>★</span> ' . esc_html( $r ) . '+' : esc_html__( 'Any', 'made-in-germany' ); ?>
								</label>
							<?php endforeach; ?>
						</div>
					</div>
				</details>

				<details class="f-group" open>
					<summary><?php esc_html_e( 'Offers', 'made-in-germany' ); ?></summary>
					<div class="f-body">
						<?php
						$offers = array(
							'on_sale' => array( __( 'On sale', 'made-in-germany' ), $data['offers']['on_sale'] ),
							'new'     => array( __( 'New arrivals', 'made-in-germany' ), $data['offers']['new'] ),
							'best'    => array( __( 'Bestsellers', 'made-in-germany' ), $data['offers']['best'] ),
							'saved'   => array( __( 'Saved items ♥', 'made-in-germany' ), null ),
						);
						foreach ( $offers as $k => $o ) :
							?>
							<label class="f-toggle">
								<span><?php echo esc_html( $o[0] ); ?> <span class="n"<?php echo null === $o[1] ? ' id="savedN"' : ''; ?>><?php echo null === $o[1] ? '' : (int) $o[1]; ?></span></span>
								<input type="checkbox" name="<?php echo esc_attr( $k ); ?>" value="1" <?php checked( $state[ $k ] ); ?>>
								<span class="tr"></span>
							</label>
						<?php endforeach; ?>
						<input type="hidden" name="ids" id="savedIds" value="<?php echo esc_attr( implode( ',', $state['ids'] ) ); ?>">
					</div>
				</details>

				<?php if ( $data['regions'] ) : ?>
					<details class="f-group" open>
						<summary><?php esc_html_e( 'Made in', 'made-in-germany' ); ?><?php echo $state['made_in'] ? '<em>' . esc_html( sprintf( /* translators: %d: count */ __( '%d selected', 'made-in-germany' ), count( $state['made_in'] ) ) ) . '</em>' : ''; ?></summary>
						<div class="f-body f-tags">
							<?php foreach ( $data['regions'] as $r ) : ?>
								<label class="pill">
									<input type="checkbox" name="made_in[]" value="<?php echo esc_attr( $r['slug'] ); ?>" <?php checked( in_array( $r['slug'], $state['made_in'], true ) ); ?>>
									<?php echo esc_html( $r['name'] ); ?> <small><?php echo (int) $r['count']; ?></small>
								</label>
							<?php endforeach; ?>
						</div>
					</details>
				<?php endif; ?>

				<noscript><p><button class="btn btn-primary btn-block" type="submit"><?php esc_html_e( 'Apply filters', 'made-in-germany' ); ?></button></p></noscript>
			</div>
			<div class="f-foot">
				<button class="btn btn-primary btn-block" type="button" data-close id="applyBtn">
					<?php
					/* translators: %d: results */
					printf( esc_html( _n( 'Show %d result', 'Show %d results', $total, 'made-in-germany' ) ), (int) $total );
					?>
				</button>
			</div>
		</form>
	</aside>

	<div class="shop-main" id="shopResults" data-total="<?php echo (int) $total; ?>" data-active="<?php echo (int) $active_count; ?>">
		<div class="shop-bar">
			<button class="btn-filter" type="button" id="openFilters"><?php echo mig_icon( 'filter' ); // phpcs:ignore WordPress.Security.EscapeOutput ?><?php esc_html_e( 'Filters', 'made-in-germany' ); ?> <span class="cnt" id="fCount"<?php echo $active_count ? '' : ' hidden'; ?>><?php echo (int) $active_count; ?></span></button>
			<div class="result-count"><b><?php echo (int) $total; ?></b> <?php echo esc_html( _n( 'result', 'results', $total, 'made-in-germany' ) ); ?></div>
			<select class="select" name="orderby" form="filterForm" aria-label="<?php esc_attr_e( 'Sort products', 'made-in-germany' ); ?>">
				<?php foreach ( $orderby as $k => $label ) : ?>
					<option value="<?php echo esc_attr( 'menu_order' === $k ? '' : $k ); ?>" <?php selected( $cur_order, $k ); ?>><?php echo esc_html( $label ); ?></option>
				<?php endforeach; ?>
			</select>
			<div class="view-toggle" role="group" aria-label="<?php esc_attr_e( 'Layout', 'made-in-germany' ); ?>">
				<button type="button" data-view="grid" class="on" aria-label="<?php esc_attr_e( 'Grid view', 'made-in-germany' ); ?>"><?php echo mig_icon( 'grid' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
				<button type="button" data-view="list" aria-label="<?php esc_attr_e( 'List view', 'made-in-germany' ); ?>"><?php echo mig_icon( 'list' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
			</div>
		</div>

		<?php
		$chips = mig_active_chips();
		if ( is_product_category() ) {
			array_unshift( $chips, array( single_term_title( '', false ), $shop ) );
		}
		if ( $chips ) :
			?>
			<div class="active">
				<?php foreach ( $chips as $chip ) : ?>
					<a class="achip" href="<?php echo esc_url( $chip[1] ); ?>" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: filter */ __( 'Remove filter %s', 'made-in-germany' ), $chip[0] ) ); ?>"><?php echo esc_html( $chip[0] ); ?><span aria-hidden="true">×</span></a>
				<?php endforeach; ?>
				<?php if ( count( $chips ) > 1 ) : ?>
					<a class="clear-link" href="<?php echo esc_url( $shop ); ?>"><?php esc_html_e( 'Clear all', 'made-in-germany' ); ?></a>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<?php
		if ( woocommerce_product_loop() ) {
			do_action( 'woocommerce_before_shop_loop' );
			woocommerce_product_loop_start();
			if ( wc_get_loop_prop( 'total' ) ) {
				while ( have_posts() ) {
					the_post();
					do_action( 'woocommerce_shop_loop' );
					wc_get_template_part( 'content', 'product' );
				}
			}
			woocommerce_product_loop_end();
			do_action( 'woocommerce_after_shop_loop' );
		} else {
			do_action( 'woocommerce_no_products_found' );
		}
		?>
	</div>
</div>

<?php
get_footer( 'shop' );
