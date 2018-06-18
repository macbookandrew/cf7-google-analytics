/**
 * Contact Form 7 Google Analytics
 *
 * @package WordPress
 * @subpackage CF7_Google_Analytics
 */

'use strict';

/**
 * Send tracking data
 *
 * @param {string} formId     WP post ID for the CF7 form.
 * @param {string} eventLabel CF7 custom DOM event label
 *
 * @returns {void} Fires third-party functions.
 */
function cf7GASendTrackingEvent(formId, eventLabel) {
	var formLabel = '';

	// Set name of CF7 form.
	if (typeof cf7FormIDs === 'object') {
		formLabel = cf7FormIDs['ID_' + formId];
	} else {
		formLabel = 'Form ID ' + formId;
	}

	// Global Site Tag (gtag.js).
	if (typeof gtag !== 'undefined') {
		gtag('event', 'contact_form_7', {
			event_category: 'Contact Form 7',
			event_action: eventLabel,
			event_label: formLabel
		});
	}

	// Google Tag Manager (gtm.js).
	if (typeof dataLayer !== 'undefined') {
		dataLayer.push({
			event: 'Contact Form 7',
			event_action: eventLabel,
			event_label: formLabel
		});
	}

	// Universal Google Analytics tracking code (analytics.js).
	// Google Analytics Dashboard for WordpPress (GADWP).
	if (typeof ga !== 'undefined') {
		ga('send', 'event', 'Contact Form', eventLabel, formLabel);
	}

	// Classic Google Analytics default code.
	if (typeof _gaq !== 'undefined') {
		_gaq.push(['_trackEvent', 'Contact Form', eventLabel, formLabel]);
	}

	// Monster Insights.
	if (typeof __gaTracker !== 'undefined') {
		__gaTracker('send', 'event', 'Contact Form', eventLabel, formLabel);
	}
}

/** See https://contactform7.com/dom-events/ */

/** Invalid: Fires when an Ajax form submission has completed successfully, but mail hasn’t been sent because there are fields with invalid input */
document.addEventListener('wpcf7invalid', function(event) {
	cf7GASendTrackingEvent(event.detail.contactFormId, 'Invalid');
}, false);

/** Spam: Fires when an Ajax form submission has completed successfully, but mail hasn’t been sent because a possible spam activity has been detected */
document.addEventListener('wpcf7spam', function(event) {
	cf7GASendTrackingEvent(event.detail.contactFormId, 'Spam');
}, false);

/** Mail Sent: Fires when an Ajax form submission has completed successfully, and mail has been sent */
document.addEventListener('wpcf7mailsent', function(event) {
	cf7GASendTrackingEvent(event.detail.contactFormId, 'Mail Sent');
}, false);

/** Mail Failed: Fires when an Ajax form submission has completed successfully, but it has failed in sending mail */
document.addEventListener('wpcf7mailfailed', function(event) {
	cf7GASendTrackingEvent(event.detail.contactFormId, 'Mail Failed');
}, false);

/** Submit: Fires when an Ajax form submission has completed successfully, regardless of other incidents */
document.addEventListener('wpcf7submit', function(event) {
	cf7GASendTrackingEvent(event.detail.contactFormId, 'Sent'); // possibly misleading; change to “Submit”?
}, false);
