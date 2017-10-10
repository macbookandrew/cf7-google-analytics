document.addEventListener('wpcf7submit', function(event) {
    cf7GASendTrackingEvent(event.detail.contactFormId);
}, false );

/**
 * Send tracking data
 * @param {string} formId WP post ID for the CF7 form
 */
function cf7GASendTrackingEvent(formId) {
    // Global Site Tag (gtag.js)
    if ( typeof gtag !== "undefined" ) {
        gtag( "event", "Contact Form", {
            "event_action": "Sent",
            "event_label": "Form ID " + formId
        });
    }

    // universal Google Analytics tracking code (analytics.js)
    // Google Analytics Dashboard for WordpPress (GADWP)
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
