<?php
/*
Plugin Name: Contact Form 7 Google Analytics Integration
Plugin URI: https://github.com/macbookandrew/cf7-google-analytics
Description: Adds Google Analytics Event Tracking to all Contact Form 7 forms.
Tags: contact form, contact form 7, google analytics, ga, universal, forms, form, track
Version: 1.0
Author: AndrewRMinion Design
Author URI: https://www.andrewrminion.com
*/

// don't allow calling this file directly
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// add GA event tracking to all WPCF7 forms
// thanks to https://github.com/kasparsd/contact-form-7-extras/ for the starter code
function wpcf7_ga_tracking( $items, $result ) {
    $form = WPCF7_ContactForm::get_current();

    if ( 'mail_sent' === $result['status'] ) {
        $items['onSentOk'] = array();

        $items['onSentOk'][] = sprintf(
                'if ( typeof ga == "function" ) {
                    ga( "send", "event", "Contact Form", "Sent", "%1$s" );
                }
                if ( typeof _gaq !== "undefined" ) {
                    _gaq.push([ "_trackEvent", "Contact Form", "Sent", "%1$s" ]);
                }',
                esc_js( $form->title() )
            );
    }
    return $items;
}
add_filter( 'wpcf7_ajax_json_echo', 'wpcf7_ga_tracking', 10, 2 );
