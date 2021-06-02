<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Function to unique number value
 * 
 * @package WP News and Scrolling Widgets
 * @since 1.0.0
 */
function wpnw_get_unique() {
	static $unique = 0;
	$unique++;

	// For Elementor & Beaver Builder
	if( ( defined('ELEMENTOR_PLUGIN_BASE') && isset( $_POST['action'] ) && $_POST['action'] == 'elementor_ajax' )
	|| ( class_exists('FLBuilderModel') && ! empty( $_POST['fl_builder_data']['action'] ) ) ) {
		$unique = current_time('timestamp') . '-' . rand();
	}

	return $unique;
}

/**
 * Function to content words limit
 * 
 * @package WP News and Scrolling Widgets
 * @since 1.0.0
 */
function wpnw_limit_words( $post_id = null, $content = '', $word_length = '55', $more = '...' ) {

	$has_excerpt  = false;
	$word_length    = ! empty( $word_length ) ? $word_length : '55';

	// If post id is passed
	if( ! empty( $post_id ) ) {
		if (has_excerpt($post_id)) {
			$has_excerpt    = true;
			$content        = get_the_excerpt();
		} else {
			$content = ! empty( $content ) ? $content : get_the_content();
		}
	}

	if( ! empty( $content ) && ( ! $has_excerpt ) ) {
		$content = strip_shortcodes( $content ); // Strip shortcodes
		$content = wp_trim_words( $content, $word_length, $more );
	}

	return $content;
}

/**
 * Sanitize Multiple HTML class
 * 
 * @package WP News and Widget - Masonry Layout
 * @since 2.1.4
 */
function wpnw_sanitize_html_classes($classes, $sep = " ") {
	$return = "";

	if( $classes && ! is_array( $classes ) ) {
		$classes = explode($sep, $classes);
	}

	if( ! empty( $classes ) ) {
		foreach($classes as $class) {
			$return .= sanitize_html_class($class) . " ";
		}
		$return = trim( $return );
	}

	return $return;
}

/**
 * Function to news pagination
 * 
 * @package WP News and Scrolling Widgets
 * @since 1.0.0
 */
function wpnw_news_pagination($args = array()){    

	$big				= 999999999; // need an unlikely integer
	$page_links_temp	= array();	
	$pagination_type	= isset( $args['pagination_type'] ) ? $args['pagination_type'] : 'numeric';
	$multi_page			= ! empty( $args['multi_page'] ) 	? 1 : 0;

	$paging = array(
		'base' 		=> str_replace( $big, '%#%', esc_url( get_pagenum_link( $big ) ) ),
		'format' 	=> '?paged=%#%',
		'current' 	=> max( 1, $args['paged'] ),
		'total' 	=> $args['total'],
		'prev_next'	=> true,
		'prev_text'	=> '&laquo; '.__('Previous', 'sp-news-and-widget'),
		'next_text'	=> __('Next', 'sp-news-and-widget').' &raquo;',
	);

	// If pagination is prev-next and shortcode is placed in single post
	if( $multi_page ) {
		$paging['type']		= ( $pagination_type == 'prev-next' ) ? 'array' : 'plain';
		$paging['base']		= esc_url_raw( add_query_arg( 'news_page', '%#%', false ) );
		$paging['format']	= '?news_page=%#%';
	}

	$page_links = paginate_links( apply_filters( 'wpnw_pro_paging_args', $paging ) );

	// For single post shortcode we just fetch the prev-next link
	if( $multi_page && $pagination_type == 'prev-next' && $page_links && is_array( $page_links ) ) {

		foreach ( $page_links as $page_link_key => $page_link ) {
			if( strpos( $page_link, 'next page-numbers') !== false || strpos( $page_link, 'prev page-numbers') !== false ) {
				$page_links_temp[ $page_link_key ] = $page_link;
			}
		}
		return join( "\n", $page_links_temp );
	}

	return $page_links;

}

/**
 * Sanitize number value and return fallback value if it is blank
 * 
 * @package WP News and Scrolling Widgets
 * @since 4.3
 */
function wpnw_clean_number( $var, $fallback = null, $type = 'int' ) {

	if ( $type == 'number' ) {
		$data = intval( $var );
	} else {
		$data = absint( $var );
	}

	return ( empty( $data ) && isset( $fallback ) ) ? $fallback : $data;
}

/**
 * Clean variables using sanitize_text_field. Arrays are cleaned recursively.
 * Non-scalar values are ignored.
 * 
 * @package WP News and Scrolling Widgets
 * @since 4.3
 */
function wpnw_clean( $var ) {
	if ( is_array( $var ) ) {
		return array_map( 'wpnw_clean', $var );
	} else {
		$data = is_scalar( $var ) ? sanitize_text_field( $var ) : $var;
		return wp_unslash($data);
	}
}