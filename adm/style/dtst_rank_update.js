var dtst = {};

(function($) {

'use strict';

$(function() {
	var $lang	= $('#dtst_rank_isocode'),
		$value	= $('#dtst_rank_value');

	$lang.on('change', function() {
		dtst.updateRank($lang.val(), $value.val());
	});

	$value.on('change', function() {
		dtst.updateRank($lang.val(), $value.val());
	});
});

dtst.updateRank = function(lang, value) {
	var url 					= $('#dtst_ranks_update').data('url'),
		$dtst_rank_title		= $('#dtst_rank_title'),
		$dtst_rank_desc			= $('#dtst_rank_desc'),
		$dtst_rank_bckg			= $('#dtst_rank_bckg'),
		$dtst_rank_bckg_hex		= $('#dtst_rank_bckg_hex'),
		$dtst_rank_text			= $('#dtst_rank_text'),
		$dtst_rank_text_hex		= $('#dtst_rank_text_hex'),
		$dtst_rank_text2		= $('#dtst_rank_text2'),
		$dtst_rank_text2_hex	= $('#dtst_rank_text2_hex'),
		$dtst_rank_bckg2		= $('#dtst_rank_bckg2'),
		$dtst_rank_bckg2_hex	= $('#dtst_rank_bckg2_hex');

	$.ajax({
		// The method: get|post
		method: 'get',
		// The url to send it to
		url: url,
		// The data to send
		data: {
			dtst_rank_isocode: lang,
			dtst_rank_value: value,
		},
		// On success
		success: function(response) {
			// A rank was found
			if (!response.DTST_NO_RANK) {
				$dtst_rank_title.val(response.DTST_RANK_TITLE);
				$dtst_rank_desc.val(response.DTST_RANK_DESC);
				$dtst_rank_bckg.val(response.DTST_RANK_BCKG);
				$dtst_rank_bckg_hex.val(response.DTST_RANK_BCKG);
				$dtst_rank_text.val(response.DTST_RANK_TEXT);
				$dtst_rank_text_hex.val(response.DTST_RANK_TEXT);
				$dtst_rank_text2.val(response.DTST_RANK_TEXT);
				$dtst_rank_text2_hex.val(response.DTST_RANK_TEXT);
				$dtst_rank_bckg2.val(response.DTST_RANK_BCKG);
				$dtst_rank_bckg2_hex.val(response.DTST_RANK_BCKG);
			} else {
				// No rank was found
				$dtst_rank_title.val('');
				$dtst_rank_desc.val('');
				$dtst_rank_bckg.val('');
				$dtst_rank_bckg_hex.val('');
				$dtst_rank_text.val('');
				$dtst_rank_text_hex.val('');
				$dtst_rank_text2.val('');
				$dtst_rank_text2_hex.val('');
				$dtst_rank_bckg2.val('');
				$dtst_rank_bckg2_hex.val('');
			}
		},
	});
};

}) (jQuery);
