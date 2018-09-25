(function($) {  // Avoid conflicts with other libraries

	'use strict';

	phpbb.addAjaxCallback('dtst_reputation', function(r) {
		// Make sure the callback is successful
		if (r.DTST_SUCCESS) {
			// If we are marking an attendee as a "no show"
			if (r.DTST_NO_SHOW) {
				// Hide the "no show"-button
				$(this).hide();
				// Find the "good conduct" and "bad conduct" buttons
				$(this).parents('.dtst-rep-user').find('.button').each(function() {
					// Remove any classes that indicated the attendee received reputation by this user
					$(this).removeClass('dtst-rep-up dtst-repped-up dtst-rep-down dtst-repped-down');
					// Remove the "href" and the "data-ajax".
					$(this).removeAttr('href data-ajax');
					// Add the disabled classes
					$(this).addClass('dtst-rep-disabled');
				});
				// Show the "no show"-icon
				$(this).parents('.dtst-rep-user').find('.dtst-rep-noshow-icon').show();
			} else {
				// Update classes for this button
				$(this).toggleClass(r.DTST_CLASS);
				// Remove classes from the other button
				$(this).parents('.dtst-rep-buttons').find('.button').not(this).removeClass('dtst-repped-up dtst-repped-down');
				// Update counts
				$('#dtst_rep_count_up').text(r.DTST_COUNT_UP);
				$('#dtst_rep_count_down').text(r.DTST_COUNT_DOWN);
			}
		}
	});

})(jQuery); // Avoid conflicts with other libraries
