document.addEventListener('wpcf7submit', function(event) {
    cf7GASendTrackingEvent(event.detail.contactFormId);
}, false );

/**
 * Send tracking data
 * @param {string} formId WP post ID for the CF7 form
 */
function cf7GASendTrackingEvent(formId) {
    // universal Google Analytics tracking code
    // Google Analytics Dashboard for WordpPress (GADWP) (new)
    if ( typeof ga !== "undefined" ) {
        ga( "send", "event", "Contact Form", "Sent", "Form ID " + formId );
    }

    // classic Google Analytics default code
    if ( typeof _gaq !== "undefined" ) {
        _gaq.push([ "_trackEvent", "Contact Form", "Sent", "Form ID " + formId ]);
    }

    // Monster Insights
    if ( typeof __gaTracker !== "undefined" ) {
        __gaTracker( "send", "event", "Contact Form", "Sent", "Form ID " + formId );
    }
}
