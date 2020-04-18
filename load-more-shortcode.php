<?php 
/**
 * Plugin Name: WP Load more shortcode. 
 * Description: Load more button for post.
 * Author: Sandeep jain
 * Version:1.0
 * License: GPL
 * 
 * @package   best_plugins
 */

define( 'PLUGIN_URL', plugin_dir_url( __FILE__ ) );

if ( ! function_exists( 'bp_load_more_shortcode' ) ) {
	/**
	 * BP load more shortcode.
	 */
	function bp_load_more_shortcode() {
		global $wp_query;
		if ( $wp_query->max_num_pages > 1 ) {
			$bp_output  = wp_nonce_field( 'bp_load_more_check', 'bp_load_more' );
			$bp_output .= '<div class="bp_load_more">More posts</div>';
			return $bp_output;
		}
	}
	add_shortcode( 'bp_load_more', 'bp_load_more_shortcode' );
}

add_action(
	'wp_enqueue_scripts',
	function() {
			wp_register_style(
				'bp_loadmore_css',
				PLUGIN_URL . 'css/bp_loadmore.css',
				'',
				'18042020.1',
				''
			);
			wp_register_script(
				'bp_loadmore_js',
				PLUGIN_URL . 'js/bp_loadmore.js',
				array( 'jquery' ),
				'18042020.1',
				true
			);
			wp_enqueue_style( 'bp_loadmore_css' );
			wp_enqueue_script( 'bp_loadmore_js' );
			global $wp_query;
			wp_localize_script(
				'bp_loadmore_js',
				'bp_loadmore_params',
				array(
					'ajaxurl'      => site_url() . '/wp-admin/admin-ajax.php', // WordPress AJAX.
					'posts'        => wp_json_encode( $wp_query->query_vars ), // everything about your loop is here.
					'current_page' => get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1,
					'max_page'     => $wp_query->max_num_pages,
				) 
			);
	}
);
if ( ! function_exists( 'bp_loadmore_ajax_handler' ) ) {
	/**
	 * BP loadmore ajax handler.
	 */
	function bp_loadmore_ajax_handler() {
		// check nonce security.
		if ( isset( $_POST['bpsecurity'] ) && false !== wp_verify_nonce( sanitize_key( wp_unslash( $_POST['bpsecurity'] ) ), 'bp_load_more_check' ) ) { // WPCS: Input var okay.
			// prepare our arguments for the query.
			$args                = isset( $_POST['query'] ) ? json_decode( sanitize_key( wp_unslash( $_POST['query'] ) ), true ) : '';// WPCS: Input var okay.
			$args['paged']       = isset( $_POST['page'] ) ? sanitize_key( wp_unslash( $_POST['page'] ) ) + 1 : 2; // WPCS: Input var okay.
			$args['post_status'] = 'publish';
			$cache_group         = 'bp_cache_group';
			$cache_key           = md5( 'bp_cache_' . $args['paged'] );
			$cache_name          = sanitize_key( $cache_key );
			$bp_posts            = wp_cache_get( $cache_name, $cache_group );
			if ( empty( $bp_posts ) ) {
				$query = new WP_Query( $args );
				if ( $query->have_posts() ) :
					// run the loop.
					while ( $query->have_posts() ) :
						$query->the_post();
						$bp_posts .= '<article id="post">';
						$bp_posts .= '<header class="entry-header">';
						$bp_posts .= '<h2 class="entry-title">';
						$bp_posts .= sprintf( '<a href ="%s">%s</a>', esc_url( get_permalink() ), esc_attr( get_the_title() ) );
						$bp_posts .= '</h2>';
						$bp_posts .= '</header>';
						if ( has_post_thumbnail() ) :
							$bp_posts .= '<div class="post-thumbnail">';
							$bp_posts .= sprintf( '<a href="%s">%s</a>', esc_url( get_permalink() ), esc_attr( get_title() ) );
							$bp_posts .= '</div>';
						endif;
						$bp_posts .= '<div class="entry-content">';
						$bp_posts .= get_the_excerpt();
						$bp_posts .= '</div>';
						$bp_posts .= '</article>';
					endwhile;
					wp_cache_set( $cache_name, $bp_posts, $cache_group, 300 );
				endif;
			}
			echo wp_kses_post( $bp_posts );
		}
		die; // here we exit the script and even no wp_reset_query() required!.
	}
	add_action( 'wp_ajax_loadmore', 'bp_loadmore_ajax_handler' ); // wp_ajax_action.
	add_action( 'wp_ajax_nopriv_loadmore', 'bp_loadmore_ajax_handler' ); // wp_ajax_nopriv_action.
}
