var dtst = {};

(function($) { // Avoid conflicts with other libraries

	'use strict';

	$(function() {
		var $form = $('#mcp_dtst_recent');

		$form.find('input').on('change', function() {
			$form.submit();
		});

		$form.find('textarea').on('blur', function() {
			$form.submit();
		});

		$('.dtst-mcp-textarea-search').on('click', function(e) {
			e.preventDefault();

			var url = $(this).attr('href');

			var win = window.open(url.replace(/&amp;/g, '&'), '_usersearch', 'height=570,resizable=yes,scrollbars=yes, width=760');
			var winTimer = window.setInterval(function()
			{
				if (win.closed !== false)
				{
					window.clearInterval(winTimer);
					$form.submit();
				}
			}, 200);
		});

		$('.dtst-mcp-th').click(function(){
			var table = $(this).parents('table').eq(0).find('tbody');
			var rows = table.find('tr').toArray().sort(dtst.comparer($(this).index()));
			this.asc = !this.asc;
			if (!this.asc) {
				rows = rows.reverse();
			}
			for (var i = 0; i < rows.length; i++) {
				table.append(rows[i]);
			}
		});
	});

	dtst.comparer = function(index) {
		return function(a, b) {
			var valA = dtst.getCellValue(a, index), valB = dtst.getCellValue(b, index);
			return $.isNumeric(valA) && $.isNumeric(valB) ? valA - valB : valA.toString().localeCompare(valB);
		}
	};

	dtst.getCellValue = function(row, index) {
		return $(row).children('td').eq(index).text();
	};

	phpbb.addAjaxCallback('mcp_dtst_recent', function(response) {
		var $panel	= $('#mcp_dtst_recent_list');

		$panel.replaceWith(response);
	});

})(jQuery); // Avoid conflicts with other libraries
