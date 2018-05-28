(function($) { // Avoid conflicts with other libraries

	'use strict';

	$(function() {
		/*
		 * jQuery datepicker by fengyuanchen
		 * https://github.com/fengyuanchen/datepicker
		 */
		$('#dtst_date').datepicker({
			// The dateformat to output
			autoShow: false,
			autoHide: true,
			autoPick: false,
			inline: true,
			format: 'dd-mm-yyyy',
			weekStart: 1,
			yearFirst: false,
		});

		$('#dtst_date_reset').on('click', function(event) {
			// We have to prevent the default action when clicking a <button>
			event.preventDefault();

			// Reset the datepicker
			$('#dtst_date').datepicker('setDate', '').val('');
		});
	});

})(jQuery); // Avoid conflicts with other libraries
