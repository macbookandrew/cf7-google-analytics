/**
 * CF7 Google Analytics admin functions.
 */

/* global ajaxurl, jQuery */

'use strict';

(function($) {
	$(document).ready(function() {

		/** Handle dismissable notices */
		$(document).on('click', '.cf7-ga-notice .notice-dismiss', function(e) {
			var version = $(this).parents('.cf7-ga-notice').data('version');

			$.ajax({
				url: ajaxurl,
				data: {
					action: 'cf7_ga_dismiss_notice_' + version
				}
			});

		});
	});
}(jQuery));
