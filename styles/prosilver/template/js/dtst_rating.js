(function($) { // Avoid conflicts with other libraries

	'use strict';

	$(function() {
		$("[data-rateyo-rating]").each(function() {
			$(this).rateYo({
				readOnly: true,
				maxValue: 100, // The Maximum value you want the rating to end with.
				numStars: 10,
				starWidth: "15px",
				normalFill: "#696969", // DimGray, the background color for the un-rated part of a star.
				multiColor: {
					"startColor": "#F0E68C", // Kaki
					"endColor": "#FFA500" // Orange - https://www.rapidtables.com/web/color/Gold_Color.html
				}
			});
		});
	});

})(jQuery); // Avoid conflicts with other libraries
