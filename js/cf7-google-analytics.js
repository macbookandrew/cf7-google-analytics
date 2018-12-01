/**
 * Contact Form 7 Google Analytics
 *
 * @package WordPress
 * @subpackage CF7_Google_Analytics
 */

/* global cf7FormIDs, cf7GASendActions, dataLayer, ga, _gaq, __gaTracker, gtag */

'use strict';

/**
 * Send tracking data
 *
 * @param {string} formId     WP post ID for the CF7 form.
 * @param {string} eventLabel CF7 custom DOM event label.
 * @param {string} eventKey   CF7 custom DOM event key.
 *
 * @returns {void} Fires third-party functions.
 */
function cf7GASendTrackingEvent(formId, eventLabel, eventKey) {

	// If there are settings but this eventKey is not enabled in WP settings, bail out now.
	if (Object.keys(cf7GASendActions).length > 0 && ('undefined' === typeof cf7GASendActions[eventKey] || 'true' !== cf7GASendActions[eventKey])) {
		return;
	}

	var formLabel = '';

	// Set name of CF7 form.
	if ('undefined' !== typeof cf7FormIDs) {
		formLabel = cf7FormIDs['ID_' + formId];
	} else {
		formLabel = 'Form ID ' + formId;
	}

	// Global Site Tag (gtag.js).
	if ('undefined' !== typeof gtag) {
		gtag('event', 'contact_form_7', {
			event_category: 'Contact Form 7',
			event_action: eventLabel,
			event_label: formLabel
		});
	}

	// Google Tag Manager (gtm.js).
	if ('undefined' !== typeof dataLayer) {
		dataLayer.push({
			event: 'Contact Form 7',
			event_action: eventLabel,
			event_label: formLabel
		});
	}

	// Universal Google Analytics tracking code (analytics.js).
	// Google Analytics Dashboard for WordpPress (GADWP).
	if ('undefined' !== typeof ga) {
		ga('send', 'event', 'Contact Form', eventLabel, formLabel);
	}

	// Classic Google Analytics default code.
	if ('undefined' !== typeof _gaq) {
		_gaq.push(['_trackEvent', 'Contact Form', eventLabel, formLabel]);
	}

	// Monster Insights.
	if ('undefined' !== typeof __gaTracker) {
		__gaTracker('send', 'event', 'Contact Form', eventLabel, formLabel);
	}
}

/** See https://contactform7.com/dom-events/ */

/** Invalid: Fires when an Ajax form submission has completed successfully, but mail hasn’t been sent because there are fields with invalid input */
document.addEventListener('wpcf7invalid', function(event) {
	cf7GASendTrackingEvent(event.detail.contactFormId, 'Invalid', 'invalid');
}, false);

/** Spam: Fires when an Ajax form submission has completed successfully, but mail hasn’t been sent because a possible spam activity has been detected */
document.addEventListener('wpcf7spam', function(event) {
	cf7GASendTrackingEvent(event.detail.contactFormId, 'Spam', 'spam');
}, false);

/** Mail Sent: Fires when an Ajax form submission has completed successfully, and mail has been sent */
document.addEventListener('wpcf7mailsent', function(event) {
	cf7GASendTrackingEvent(event.detail.contactFormId, 'Mail Sent', 'mail_sent');
}, false);

/** Mail Failed: Fires when an Ajax form submission has completed successfully, but it has failed in sending mail */
document.addEventListener('wpcf7mailfailed', function(event) {
	cf7GASendTrackingEvent(event.detail.contactFormId, 'Mail Failed', 'mail_failed');
}, false);

// FUTURE: add setting to disable wpcf7submit. See https://wordpress.org/support/topic/when-you-will-delete-sent-event/.

/** Submit: Fires when an Ajax form submission has completed successfully, regardless of other incidents */
document.addEventListener('wpcf7submit', function(event) {
	cf7GASendTrackingEvent(event.detail.contactFormId, 'Sent', 'sent');
}, false);
