/**
 * Load more script.
 *
 * @package best_plugins
 */

jQuery(
	function($){ // use jQuery code inside this to avoid "$ is not defined" error.
		$( '.bp_load_more' ).click(
			function(e){
				var button = $( this ),
				data       = {
					'action': 'loadmore',
					'query': bp_loadmore_params.posts, // that's how we get params from wp_localize_script() function.
					'page' : bp_loadmore_params.current_page,
					'bpsecurity' : $( '#bp_load_more' ).val(),
				};
 
				$.ajax(
					{ 
						url : bp_loadmore_params.ajaxurl, // AJAX handler.
						data : data,
						type : 'POST',
						beforeSend : function ( xhr ) {
							button.text( 'Loading...' );
						},
						success : function( data ){
							if ( data ) { 
								button.text( 'More posts' ).prev().before( data ); // insert new posts.
								bp_loadmore_params.current_page++;
 
								if ( bp_loadmore_params.current_page == bp_loadmore_params.max_page ) { 
									button.remove(); // if last page, remove the button.
								}
							} else {
								button.remove(); // if no data, remove the button as well.
							}
						}
					}
				);
			}
		);
	}
);
