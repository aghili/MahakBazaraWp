(function( $ ) {
	'use strict';
//todo: automatic update method , Didnt stop
	function refresh_update_processbar(value) {
		var elem = document.getElementById("update_progress_bar");
		var width = ((value * 100) / all_records).toFixed(1);
		//var id = setInterval(frame, 10);
		//function frame() {
		//	if (width >= 100) {
		//		clearInterval(id);
		//	} else {
		//		width++;
		$(elem).animate({width: width + '%'}, 100);
		elem.innerHTML = width * 1 + '%';
		//	}
		//}
	}
	/**
	 * All of the code for your admin-facing JavaScript source
	 * should reside in this file.
	 *
	 * Note: It has been assumed you will write jQuery code here, so the
	 * $ function reference has been prepared for usage within the scope
	 * of this function.
	 *
	 * This enables you to define handlers, for when the DOM is ready:
	 *
	 * $(function() {
	 *
	 * });
	 *
	 * When the window is loaded:
	 *
	 * $( window ).load(function() {
	 *
	 * });
	 *
	 * ...and/or other possibilities.
	 *
	 * Ideally, it is not considered best practise to attach more than a
	 * single DOM-ready or window-load handler for a particular page.
	 * Although scripts in the WordPress core, Plugins and Themes may be
	 * practising this, we should strive to set a better example in our own work.
	 */


	$(window).load(function () {
		var server_name = window.location.origin;

		function set_log_rows(value) {
			var html = '';

			$.each(value, function (key, row) {
				html += ' <tr class=""> <td class="" >' + row.id + '</td><td class="" >' + row.synced_by + '</td><td class="" >' + new Date(row.start_time * 1000) + '</td><td class="" >' + row.count_product + '</td><td class="" >' + row.count_customer + '</td><td class="" >' + row.duration + ' Seconds</td></tr>';
			});

			$('#log-list').html(html);
		}

		function result_sync_log() {
			$.ajax({
				url: server_name + '/?rest_route=/wpmbt/v1/get-sync-log-list',
				success: function (result) {
					set_log_rows(result.data);
				}
			});
		}

		function add_result(parent) {
			$('<tr><td></td></tr>').prependTo('#table-' + parent + '-body');
			return $('#table-' + parent + '-body')[0].firstChild.firstChild;
		}

		var update_timer = null;
		var update_count = 1;

		function update_record_result(result_panel) {
			$.ajax({
				url: server_name + '/?rest_route=/wpmbt/v1/get-update-info',
				success: function (result) {
					var data = result.result;
					var product_count = data.count_products;
					var customer_count = data.count_customers;
					update_count = all_records - (parseInt(product_count) + parseInt(customer_count));
					refresh_update_processbar(update_count);
					var text = '';
					if (product_count > 0 || customer_count > 0) {
						text += 'count of ';
						if (product_count > 0) {
							text += '<strong>' + product_count + '</strong> product ';
							if (customer_count > 0)
								text += 'and '
						}
						if (customer_count > 0)
							text += ' <strong>' + customer_count + '</strong> user left for update'
					} else
						text = ' website is up to date.'
					$(result_panel).html('update <strong class="notice-success">done</strong>,' + text + ' .');
					$("#lbl_btn_update").text(data.time_format);
				}, error: function (xhr) {
					$(result_panel).html('Update process ended with <strong class="notice-error">Error</strong> .');
					$("#lbl_btn_update").text(xhr.error);
				}
			});
			//result_sync_log();
		}

		function request_engine_update(result_panel) {
			$(result_panel).html('Updating process <strong class="notice-info">start</strong> .');
			$.ajax({
				url: server_name + '/?rest_route=/wpmbt/v1/hit-update_records',
				success: function (result) {
					if (update_timer != null) {
						clearInterval(update_timer);
						update_timer = null;
					}
					update_record_result(result_panel);
					var data = result.result;
					var product_count = data.count_products;
					var customer_count = data.count_customers;
					var update_records = parseInt(product_count, 10) + parseInt(customer_count, 10);

					if (update_records != 0)
						create_update_request();
				}, error: function (xhr) {
					update_record_result(result_panel);
					if (update_timer == null)
						update_timer = setInterval(create_update_request(), 100);
				}
			});
		}

		function create_update_request() {
			var result_row = add_result('update');
			request_engine_update(result_row);
		}

		function request_engine_sync(result_panel) {
			$(result_panel).html('Sync process <strong class="notice-info">start</strong> .');
			$.ajax({
				url: server_name + '/?rest_route=/wpmbt/v1/hit-sync',
				success: function (result) {
					var data = result.result;
					var product_count = data.count_products;
					var customer_count = data.count_customers;
					all_records = parseInt(product_count, 10) + parseInt(customer_count, 10);
					//update_count = 1;
					$(result_panel).html('syncing <strong class="notice-success">done</strong>,receive <strong>' + product_count + '</strong> product and <strong>' + customer_count + '</strong> user.');
					$("#lbl_btn_sync").text(data.time_format);
					result_sync_log();
				}, error: function (xhr) {
					$(result_panel).html('Sync process ended with <strong class="notice-error">Error</strong> .');
					$("#lbl_btn_sync").text(xhr.error);
					result_sync_log();
				}
			});
		}

		$("#btn_sync").on("click", function () {
			var result_row = add_result('sync');
			request_engine_sync(result_row);
		});

		$("#btn_update_records").on("click", function () {
			update_count = 1;
			create_update_request();
		});
		$(".btn-collapse").on('click', function (e) {
			var id = this.hash;
			$(id + '-select').show();
			//$(this).hide();
			//alert(e.id);
		});
		$(".btn-accept").on('click', function (e) {
			var id = this.hash;
			$(id + '-select').hide();
			$(id + '-value-hid').val($(id + '-value').val());
			$(id + '-display').html('').append($(id + '-value-hid').val());
			//$(this).hide();
			//alert(e.id);
		});
		result_sync_log();
	});

})( jQuery );
