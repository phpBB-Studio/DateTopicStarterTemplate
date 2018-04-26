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
	});

})(jQuery); // Avoid conflicts with other libraries
