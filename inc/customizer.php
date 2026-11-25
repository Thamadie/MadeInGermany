<?php
/**
 * Customizer settings: store texts, contact details and delivery numbers.
 *
 * @package Made_In_Germany
 */

defined( 'ABSPATH' ) || exit;

/**
 * Default values for every theme option.
 *
 * @return array
 */
function mig_defaults() {
	return array(
		'announcements'    => "<b>Free delivery</b> on orders over Rs 15,000\n<b>Cash on delivery</b> across all 25 districts\n<b>New arrivals</b> from Hamburg every Friday\n<b>100% genuine</b> German imports\n<b>Wholesale</b> pricing for shops & hotels",
		'hero_eyebrow'     => 'Direct import · Frankfurt → Colombo',
		'hero_line1'       => 'Made in',
		'hero_line2'       => 'Germany.',
		'hero_line3'       => 'Delivered in',
		'hero_highlight'   => 'Sri Lanka',
		'hero_text'        => 'Authentic German chocolates, perfumes, skincare, pantry staples, fashion and electronics. Imported in sealed batches, customs-cleared, and delivered to all 25 districts.',
		'stat1_num'        => '1200',
		'stat1_suffix'     => '+',
		'stat1_label'      => 'Products in stock',
		'stat2_num'        => '25',
		'stat2_suffix'     => '',
		'stat2_label'      => 'Districts covered',
		'stat3_num'        => '48',
		'stat3_suffix'     => 'h',
		'stat3_label'      => 'Colombo delivery',
		'deal_show'        => true,
		'deal_eyebrow'     => 'Tagesangebot · Deal of the day',
		'deal_title'       => 'Up to <em>30% off</em> chocolates & pasta',
		'deal_text'        => 'Restocked this week from Hamburg. Prices drop at midnight, while stock lasts.',
		'deal_cats'        => 'chocolates,pasta',
		'free_shipping'    => 15000,
		'new_days'         => 30,
		'news_title'       => 'Get 10% off your first order',
		'news_text'        => 'New arrivals from Germany, restock alerts and member prices. One email a week.',
		'news_shortcode'   => '',
		'footer_about'     => "Sri Lanka's home for genuine German products. Imported, cleared and delivered with care.",
		'address'          => "No. 42, Galle Road,\nColombo 03",
		'phone'            => '+94 11 234 5678',
		'whatsapp'         => '',
		'hours'            => 'Mon–Sat · 9:00–19:00',
		'copyright'        => '© {year} Made in Germany Lanka. All prices in LKR, duty & VAT included.',
	);
}

/**
 * Read a theme option.
 *
 * @param string $key Option key without prefix.
 * @return mixed
 */
function mig_opt( $key ) {
	$d = mig_defaults();
	return get_theme_mod( 'mig_' . $key, isset( $d[ $key ] ) ? $d[ $key ] : '' );
}

/**
 * Register Customizer panel.
 *
 * @param WP_Customize_Manager $wp_customize Manager.
 */
function mig_customize_register( $wp_customize ) {
	$wp_customize->add_panel(
		'mig_panel',
		array(
			'title'    => __( 'Made in Germany', 'made-in-germany' ),
			'priority' => 30,
		)
	);

	$sections = array(
		'mig_header'  => __( 'Announcement bar', 'made-in-germany' ),
		'mig_hero'    => __( 'Home: hero', 'made-in-germany' ),
		'mig_deal'    => __( 'Home: deal of the day', 'made-in-germany' ),
		'mig_shop'    => __( 'Shop & delivery', 'made-in-germany' ),
		'mig_news'    => __( 'Newsletter', 'made-in-germany' ),
		'mig_contact' => __( 'Footer & contact', 'made-in-germany' ),
	);
	foreach ( $sections as $id => $title ) {
		$wp_customize->add_section( $id, array( 'title' => $title, 'panel' => 'mig_panel' ) );
	}

	$html = 'wp_kses_post';
	$text = 'sanitize_text_field';
	$fields = array(
		array( 'announcements', 'mig_header', 'textarea', __( 'Messages (one per line, <b>bold</b> allowed)', 'made-in-germany' ), $html ),
		array( 'hero_eyebrow', 'mig_hero', 'text', __( 'Small label above the headline', 'made-in-germany' ), $text ),
		array( 'hero_line1', 'mig_hero', 'text', __( 'Headline line 1', 'made-in-germany' ), $text ),
		array( 'hero_line2', 'mig_hero', 'text', __( 'Headline line 2 (red)', 'made-in-germany' ), $text ),
		array( 'hero_line3', 'mig_hero', 'text', __( 'Headline line 3', 'made-in-germany' ), $text ),
		array( 'hero_highlight', 'mig_hero', 'text', __( 'Highlighted words (gold underline)', 'made-in-germany' ), $text ),
		array( 'hero_text', 'mig_hero', 'textarea', __( 'Intro paragraph', 'made-in-germany' ), $text ),
		array( 'stat1_num', 'mig_hero', 'text', __( 'Stat 1 number', 'made-in-germany' ), $text ),
		array( 'stat1_suffix', 'mig_hero', 'text', __( 'Stat 1 suffix', 'made-in-germany' ), $text ),
		array( 'stat1_label', 'mig_hero', 'text', __( 'Stat 1 label', 'made-in-germany' ), $text ),
		array( 'stat2_num', 'mig_hero', 'text', __( 'Stat 2 number', 'made-in-germany' ), $text ),
		array( 'stat2_suffix', 'mig_hero', 'text', __( 'Stat 2 suffix', 'made-in-germany' ), $text ),
		array( 'stat2_label', 'mig_hero', 'text', __( 'Stat 2 label', 'made-in-germany' ), $text ),
		array( 'stat3_num', 'mig_hero', 'text', __( 'Stat 3 number', 'made-in-germany' ), $text ),
		array( 'stat3_suffix', 'mig_hero', 'text', __( 'Stat 3 suffix', 'made-in-germany' ), $text ),
		array( 'stat3_label', 'mig_hero', 'text', __( 'Stat 3 label', 'made-in-germany' ), $text ),
		array( 'deal_show', 'mig_deal', 'checkbox', __( 'Show the deal banner', 'made-in-germany' ), 'wp_validate_boolean' ),
		array( 'deal_eyebrow', 'mig_deal', 'text', __( 'Small label', 'made-in-germany' ), $text ),
		array( 'deal_title', 'mig_deal', 'text', __( 'Title (wrap words in <em> to make them gold)', 'made-in-germany' ), $html ),
		array( 'deal_text', 'mig_deal', 'textarea', __( 'Text', 'made-in-germany' ), $text ),
		array( 'deal_cats', 'mig_deal', 'text', __( 'Category slugs the button filters to (comma separated)', 'made-in-germany' ), $text ),
		array( 'free_shipping', 'mig_shop', 'number', __( 'Free delivery threshold in rupees (shown in the cart bar). Set the matching rule in WooCommerce → Settings → Shipping.', 'made-in-germany' ), 'absint' ),
		array( 'new_days', 'mig_shop', 'number', __( 'Show the "New" label for this many days after a product is published', 'made-in-germany' ), 'absint' ),
		array( 'news_title', 'mig_news', 'text', __( 'Title', 'made-in-germany' ), $text ),
		array( 'news_text', 'mig_news', 'textarea', __( 'Text', 'made-in-germany' ), $text ),
		array( 'news_shortcode', 'mig_news', 'text', __( 'Form shortcode from your email plugin (e.g. Mailchimp for WP). Leave empty to show a demo form that does not send anything.', 'made-in-germany' ), $text ),
		array( 'footer_about', 'mig_contact', 'textarea', __( 'About text', 'made-in-germany' ), $text ),
		array( 'address', 'mig_contact', 'textarea', __( 'Address', 'made-in-germany' ), 'sanitize_textarea_field' ),
		array( 'phone', 'mig_contact', 'text', __( 'Phone', 'made-in-germany' ), $text ),
		array( 'whatsapp', 'mig_contact', 'text', __( 'WhatsApp number with country code, digits only (e.g. 94771234567). Adds a chat button.', 'made-in-germany' ), $text ),
		array( 'hours', 'mig_contact', 'text', __( 'Opening hours', 'made-in-germany' ), $text ),
		array( 'copyright', 'mig_contact', 'text', __( 'Copyright line ({year} becomes the current year)', 'made-in-germany' ), $text ),
	);

	$d = mig_defaults();
	foreach ( $fields as $f ) {
		list( $key, $section, $type, $label, $sanitize ) = $f;
		$wp_customize->add_setting(
			'mig_' . $key,
			array(
				'default'           => $d[ $key ],
				'sanitize_callback' => $sanitize,
			)
		);
		$wp_customize->add_control(
			'mig_' . $key,
			array(
				'label'   => $label,
				'section' => $section,
				'type'    => $type,
			)
		);
	}
}
add_action( 'customize_register', 'mig_customize_register' );
