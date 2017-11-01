document.addEventListener('wpcf7submit', function(event) {
    cf7GASendTrackingEvent(event.detail.contactFormId);
}, false );

/**
 * Send tracking data
 * @param {string} formId WP post ID for the CF7 form
 */
function cf7GASendTrackingEvent(formId) {
    // get name of CF7 form
    if (typeof cf7FormIDs === 'object') {
        var formLabel = cf7FormIDs["ID_" + formId];
    } else {
        var formLabel = "Form ID " + formId;
    }

    // Global Site Tag (gtag.js)
    if ( typeof gtag !== "undefined" ) {
        gtag( "event", "contact_form_7", {
            "event_category": "Contact Form 7",
            "event_action": "Sent",
            "event_label": formLabel
        });
    }

    // universal Google Analytics tracking code (analytics.js)
    // Google Analytics Dashboard for WordpPress (GADWP)
    if ( typeof ga !== "undefined" ) {
        ga( "send", "event", "Contact Form", "Sent", formLabel );
    }

    // classic Google Analytics default code
    if ( typeof _gaq !== "undefined" ) {
        _gaq.push([ "_trackEvent", "Contact Form", "Sent", formLabel ]);
    }

    // Monster Insights
    if ( typeof __gaTracker !== "undefined" ) {
        __gaTracker( "send", "event", "Contact Form", "Sent", formLabel );
    }
}
