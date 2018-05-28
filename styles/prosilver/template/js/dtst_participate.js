(function($) {  // Avoid conflicts with other libraries

	'use strict';

	$(function() {
		$('#dtst_participants_view').on('click', function() {
			$('#dtst_attendees_list').toggle('slow');
		});
	});

	phpbb.addAjaxCallback('dtst_participate', function(r) {
		/* Update the button text, title and class */
		$('#dtst_participate_button').toggleClass('dtst-button-green dtst-button-red').attr('title', r.DTST_BUTTON_TEXT).children('span').text(r.DTST_BUTTON_TEXT);

		/* Update the button icon */
		$('#dtst_participate_button').children('i').toggleClass('fa-user-plus fa-user-times');

		/* Update the user status */
		$('#dtst_user_status').text(r.DTST_USER_STATUS);

		/* Update the participants count */
		$('[name="dtst_participants_count"]').text(r.DTST_PARTICIPANTS_COUNT);

		/* Update the participants list */
		$('#dtst_participants_list').html(r.DTST_PARTICIPANTS_LIST);

		/* Update the withdrawals count */
		$('[name="dtst_withdrawals_count"]').text(r.DTST_WITHDRAWALS_COUNT);

		/* Update the withdrawals list */
		$('#dtst_withdrawals_list').html(r.DTST_WITHDRAWALS_LIST);

		/* Close the AJAX pop up automatically after 1 second */
		phpbb.closeDarkenWrapper(1000);
	});

})(jQuery); // Avoid conflicts with other libraries
