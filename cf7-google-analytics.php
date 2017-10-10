<?php
/*
Plugin Name: Contact Form 7 Google Analytics Integration
Plugin URI: https://andrewrminion.com/contact-form-7-google-analytics/
Description: Adds Google Analytics Event Tracking to all Contact Form 7 forms.
Tags: contact form, contact form 7, cf7, contactform7, google analytics, ga, universal, forms, form, track, tracking, event, events, goal, goals
Version: 1.4.0
Author: AndrewRMinion Design
Author URI: https://www.andrewrminion.com
*/

// don't allow calling this file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Send Google Analytics tracking events when form is successfully submitted
 * @param  array $items  return from CF7
 * @param  array $result WPCF7 data about status, message, etc.
 * @return array modified array to return to the browser
 */
function wpcf7_ga_tracking( $items, $result ) {
    // continue only if using an older WPCF7 without DOM events
    if ( WPCF7_VERSION <= 4.7 ) {
        $form = WPCF7_ContactForm::get_current();

        if ( 'mail_sent' === $result['status'] ) {
            if ( ! isset( $items['onSentOk'] ) ) {
                $items['onSentOk'] = array();
            }

            $items['onSentOk'][] = sprintf(
                'if ( typeof gtag !== "undefined" ) {
                    gtag( "event", "Contact Form", {"event_action": "Sent", "event_label": "%1$s"} );
                }
                if ( typeof ga !== "undefined" ) {
                    ga( "send", "event", "Contact Form", "Sent", "%1$s" );
                }
                if ( typeof _gaq !== "undefined" ) {
                    _gaq.push([ "_trackEvent", "Contact Form", "Sent", "%1$s" ]);
                }
                if ( typeof __gaTracker !== "undefined" ) {
                    __gaTracker( "send", "event", "Contact Form", "Sent", "%1$s" );
                }',
                esc_js( $form->title() )
            );
        }
    }

    return $items;
}
add_filter( 'wpcf7_ajax_json_echo', 'wpcf7_ga_tracking', 10, 2 );

/**
 * Enqueue script for DOM events
 */
function wpcf7_ga_assets() {
    wp_enqueue_script( 'wpcf7-ga-events', plugin_dir_url( __FILE__ ) . 'js/cf7-google-analytics.min.js', array( 'contact-form-7' ), NULL, true );
}
add_action( 'wp_enqueue_scripts', 'wpcf7_ga_assets' );
