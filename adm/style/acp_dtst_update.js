var dtst = {};

(function($) {

'use strict';

$(function() {
	var $lang	= $('#dtst_pm_isocode'),
		$status	= $('#dtst_pm_status');

	$lang.on('change', function() {
		dtst.updatePm($lang.val(), $status.val());
	});

	$status.on('change', function() {
		dtst.updatePm($lang.val(), $status.val());
	});
});

dtst.updatePm = function(lang, status) {
	var url 		= $('#dtst_pm_update').data('url'),
		$pm_title	= $('#dtst_pm_title'),
		$pm_message	= $('#dtst_pm_message');

	$.ajax({
		// The method: get|post
		method: 'get',
		// The url to send it to
		url: url,
		// The data to send
		data: {
			dtst_pm_isocode: lang,
			dtst_pm_status: status,
		},
		// On success
		success: function(response) {
			// A private message was found
			if (!response.DTST_NO_PM) {
				$pm_title.val(response.DTST_PM_TITLE);
				$pm_message.text(response.DTST_PM_MESSAGE);
			} else {
				// No private message was found
				$pm_title.val('');
				$pm_message.text('');
			}
		},
	});
};

}) (jQuery);
