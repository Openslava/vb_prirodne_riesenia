/* global jQuery, picturefill */
( function( $ ){

	$( function() {
		//DOM ready
		$( document ).on( 'mio_saved_element_setting', function() {
			picturefill();
		} );

	} );

} )( jQuery );