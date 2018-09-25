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

		$(window).scroll(stickyFilters);
		stickyFilters();

		function stickyFilters() {
			// Selectors (elements)
			var $window		= $(window),
				$wrapper	= $('.dtst-filters'),
				$filters	= $('.dtst-filters-inner');

			// Offsets and lengths (pixels)
			var window_top	= $window.scrollTop(),
				topics_top	= $wrapper.next('.forumbg').offset().top,
				footer_top	= $('.action-bar.bar-bottom').offset().top,
				filter_top	= $wrapper.offset().top,
				filter_left	= $filters.offset().left,
				filter_len	= $filters.height();

			// Misc.
			var breakpoint	= 800,
				padding		= 20,
				height		= footer_top - padding - topics_top;

			// Breakpoints (booleans)
			var onlyOnLargeScreen			= $window.width() > breakpoint,
				topicListLongerThanFilter	= height > filter_len,
				scrolledByTopOfTopicsList	= window_top > topics_top,
				filtersReachedTheBottom	 	= window_top + filter_len > footer_top - padding;


			// Only sticky the sidebar if we are on a "large" screen
			if (onlyOnLargeScreen) {
				/*
				 * Then we have three stages when scrolling:
				 * - top: do nothing
				 * - middle: sticky the sidebar to the screen (position: fixed)
				 * - bottom: sticky the sidebar to the footer (position: absolute)
				 */

				 // Stage 3: Bottom
				if (filtersReachedTheBottom) {
					$filters.removeClass('dtst-filters-sticky');

					// If the filter <div> is smaller than the topics list
					if (topicListLongerThanFilter) {
						$wrapper.height(height);
						$filters.addClass('dtst-filters-bottom');
					}

					// Reset "left" css, as it is now the margin from the parent and not the screen
					if ($wrapper.data('filter')) {
						$filters.css('left', 0);
					}
				// Stage 2: Middle
				} else if (scrolledByTopOfTopicsList) {
					$filters.css('left', filter_left);
					$filters.addClass('dtst-filters-sticky').removeClass('dtst-filters-bottom');
				// Stage 1: Top
				} else {
					$filters.removeClass('dtst-filters-sticky');
				}
			}
		}
	});

})(jQuery); // Avoid conflicts with other libraries
