jQuery(document).on( "touchstart touchmove mousemove", function() {
	if(typeof jQuery.cookie('BAS_test_mousemoved') === 'undefined') {
		jQuery.cookie("BAS_test_mousemoved", 1, { expires: 30, path: '/' });
	}
}); 