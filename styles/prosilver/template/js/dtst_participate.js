var dtst = {};

(function($) {  // Avoid conflicts with other libraries

'use strict';

dtst.countCharacters = function($element) {
	var length = $element.val().trim().length;
	$element.next('span.pagination').text(length + '/255');
};

$(function() {
	$('#dtst_participants_view').on('click', function() {
		$('#dtst_attendees_list').toggle('slow');
	});

	$('#phpbb_confirm, #dtst_reason_form, #dtst_manage').on('keyup', '[name$="[dtst_action_reason]"], #dtst_reason', function() {
		dtst.countCharacters($(this));
	});

	$('#dtst_new_replies i.fa-times, #dtst_new_replies a').on('click', function() {
		// Hide the notification
		$(this).parent().hide(500);

		// Remove the url parameter
		phpbb.history.replaceUrl(window.location.toString().replace(/&dtst_p=\d+/, ''));
	});
});

phpbb.addAjaxCallback('dtst_participate', function(r) {
	/* If we are not managing the user list */
	if (!r.S_DTST_MANAGE) {
		/* Update the button text, title and class */
		$('#dtst_participate_button').toggleClass('dtst-button-green dtst-button-red').attr('title', r.DTST_BUTTON_TEXT).children('span').text(r.DTST_BUTTON_TEXT);

		/* Update the button icon */
		$('#dtst_participate_button').children('i').toggleClass('fa-user-plus fa-user-times');

		/* Update the user status */
		$('#dtst_user_status').text(r.DTST_USER_STATUS);
	}

	/* Iterate over the statuses */
	$.each(r.DTST_DATA, function(index, item) {
		/* Update the count */
		$('[name="' + item.template_block + '_count"]').text(item.user_count);

		/* Update the list */
		$('[name="' + item.template_block + '_list"]').html(item.user_list);
	});

	/* Close the AJAX pop up automatically after 3 second */
	phpbb.closeDarkenWrapper(3000);
});

})(jQuery); // Avoid conflicts with other libraries
