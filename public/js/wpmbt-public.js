(function( $ ) {
	'use strict';

	//function sleep(ms)
	//{
	//	return(
	//			new Promise(function(resolve, reject)
	//			{
	//				setTimeout(function() { resolve(); }, ms);
	//			})
	//	);
	//}

	var server_name = window.location.origin;

	function sync_engine() {

		$.ajax({
			url: server_name + '/?rest_route=/wpmbt/v1/hit-update_records', success: function (result) {
			}, error: function (xhr) {
			}
		});
		//setTimeout(sync_engine(),1000);
	}

	setTimeout(sync_engine(), 10000);
})( jQuery );
