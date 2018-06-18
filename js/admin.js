/**
 * CF7 Google Analytics admin functions.
 */

/* global ajaxurl, jQuery */

'use strict';

(function($) {
	$(document).ready(function() {

		/** Handle dismissable notices */
		$(document).on('click', '.cf7-ga-notice-170 .notice-dismiss', function() {

			$.ajax({
				url: ajaxurl,
				data: {
					action: 'cf7_ga_dismiss_notice_170'
				}
			});

		});
	});
}(jQuery));
