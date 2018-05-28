(function($) { // Avoid conflicts with other libraries

	'use strict';

	$(function() {
		/*
		 * jQuery datepicker by fengyuanchen
		 * https://github.com/fengyuanchen/datepicker
		 */
		$('.dtst-date').datepicker({
			// The dateformat to output
			autoShow: false,
			autoHide: true,
			autoPick: false,
			inline: false,
			format: 'dd-mm-yyyy',
			weekStart: 1,
			yearFirst: false,
		});

		// Define our date elements
		let $date_after		= $('#dtst_date_after'),
			$date_before	= $('#dtst_date_before');

		// On selecting a 'after' date
		$date_after.on('pick.datepicker', function(e) {
			// Set a min date for the before date picker
			$date_before.datepicker('setStartDate', e.date);

			// Check if the selected date is not higher than the before date
			if ($date_before.val() && e.date > $date_before.datepicker('getDate')) {
				// If so, empty the before date
				$date_before.val('');
			}
		});

		// On selecting a 'before' date
		$date_before.on('pick.datepicker', function(e) {
			// Set a max date for the after date picker
			$date_after.datepicker('setEndDate', e.date);

			// Check if the selected date is not lower than the after date
			if ($date_after.val() && e.date < $date_after.datepicker('getDate')) {
				// If so, empty the after date
				$date_after.val('');
			}
		});

		$('.dtst-date-reset').on('click', function(e) {
			$(this).next('.dtst-date').val('');
			autoReloadForm();
		});

		$('.dtst-date-reset').hover(
			function() {
				$(this).children('i').addClass('fa-spin');
			}, function() {
				$(this).children('i').removeClass('fa-spin');
			}
		);

		/*
		 * jQuery range by nitinhayaran
		 * https://github.com/nitinhayaran/jRange
		 */
		$('#dtst_age').jRange({
			from: 0,
			to: 99,
			step: 1,
			scale: [0,25,50,75,99],
			format: '%s',
			width: 150,
			showLabels: true,
			isRange : true,
			theme: 'theme-blue',
		});

		/*
		 * jQuery range by nitinhayaran
		 * https://github.com/nitinhayaran/jRange
		 */
		$('#dtst_participants').jRange({
			from: 0,
			to: 999,
			step: 1,
			scale: [0,250,500,750,999],
			format: '%s',
			width: 150,
			showLabels: true,
			isRange : true,
			theme: 'theme-blue',
		});

		/*
		 * On selecting a filter, submit the form if so chosen
		 */
		$('[name="dtst_type[]"], [name="dtst_location[]"], .dtst-date').on('change', autoReloadForm);

		function autoReloadForm() {
			let $form = $('#dtst_filters');

			if ($form.data('dtst-auto-reload')) {
				$form.submit();
			}
		}
	});

})(jQuery); // Avoid conflicts with other libraries
