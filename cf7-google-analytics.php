<?php
/*
Plugin Name: Contact Form 7 Google Analytics Integration
Plugin URI: https://andrewrminion.com/contact-form-7-google-analytics/
Description: Adds Google Analytics Event Tracking to all Contact Form 7 forms.
Tags: contact form, contact form 7, cf7, contactform7, google analytics, ga, universal, forms, form, track, tracking, event, events, goal, goals
Version: 1.7.1
Author: AndrewRMinion Design
Author URI: https://www.andrewrminion.com
*/

/** don't allow calling this file directly */
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

class CF7_GA {
    public $version = '1.7.1';

    /**
     * Load everything
     */
    public function __construct() {
        /** Enqueue the main JS file */
        add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_assets' ) );

        /** Register backend assets */
        add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_backend_assets' ) );

        /** Add old tracking */
        if ( WPCF7_VERSION <= 4.7 ) {
            add_filter( 'wpcf7_ajax_json_echo', array( $this, 'add_old_tracking' ), 10, 2 );
        }

        /** Add notice about v1.7.0 changes */
        if ( get_option( 'cf7-ga-170-notice-dismissed' ) === false ) {
            add_action( 'admin_notices', array( $this, 'admin_notices' ) );
            add_action( 'admin_enqueue_scripts', function() {
                wp_enqueue_script( 'admin-cf7-ga' );
            } );
            add_action( 'wp_ajax_cf7_ga_dismiss_notice_170', array( $this, 'cf7_ga_dismiss_notice_170' ) );
        }
    }

    /**
     * Send Google Analytics tracking events when form is successfully submitted and mail sent
     * @param  array $items  return from CF7
     * @param  array $result WPCF7 data about status, message, etc.
     * @return array modified array to return to the browser
     */
    function add_old_tracking( $items, $result ) {
        $form = WPCF7_ContactForm::get_current();

        if ( 'mail_sent' === $result['status'] ) {
            if ( ! isset( $items['onSentOk'] ) ) {
                $items['onSentOk'] = array();
            }

            $items['onSentOk'][] = sprintf('
                if ( typeof gtag !== "undefined" ) {
                    gtag( "event", "contact_form_7", {"event_category": "Contact Form 7", "event_action": "Sent", "event_label": "%1$s"} );
                }
                if ( typeof dataLayer !== "undefined" ) {
                    dataLayer.push({ "event": "Contact Form 7", "event_action": "Sent", "event_label": formLabel });
                }
                if ( typeof ga !== "undefined" ) {
                    ga( "send", "event", "Contact Form", "Sent", "%1$s" );
                }
                if ( typeof _gaq !== "undefined" ) {
                    _gaq.push([ "_trackEvent", "Contact Form", "Sent", "%1$s" ]);
                }
                if ( typeof __gaTracker !== "undefined" ) {
                    __gaTracker( "send", "event", "Contact Form", "Sent", "%1$s" );
                }
                ',
                esc_js( $form->title() )
            );
        }

        return $items;
    }

    /**
     * Enqueue script for DOM events
     */
    function enqueue_assets() {
        $form_args = array(
            'post_type'         => 'wpcf7_contact_form',
            'posts_per_page'    => -1,
        );
        $forms_query = get_posts( $form_args );
        $forms = array();

        foreach ( $forms_query as $form ) {
            $forms['ID_' . $form->ID] = $form->post_title;
        }

        wp_enqueue_script( 'wpcf7-ga-events', plugin_dir_url( __FILE__ ) . 'js/cf7-google-analytics.min.js', array( 'contact-form-7' ), $this->version, true );
        wp_add_inline_script( 'wpcf7-ga-events', 'var cf7FormIDs = ' . json_encode( $forms ), 'before' );
    }

    /**
     * Enqueue backend assets
     */
    function enqueue_backend_assets() {
        wp_register_script( 'admin-cf7-ga', plugin_dir_url( __FILE__ ) . 'js/admin.js', array( 'jquery' ), $this->version, true );
    }

    /**
     * Add admin notice about new tracking behavior
     */
    function admin_notices() {
        ?>
        <div class="notice notice-info cf7-ga-notice-170 is-dismissible">
            <h2>Contact Form 7 to Google Analytics Update</h2>
            <p>The tracking behavior has <strong>added more events</strong> since version 1.7.0. It now sends data to Google Analytics about <strong>all</strong> form submission attempts. Here is a list of the events you will begin to see since the upgrade:</p>
            <ul>
                <li><strong>Invalid</strong>: Fires when an Ajax form submission has completed successfully, but mail hasn’t been sent because there are fields with invalid input.
                <li><strong>Spam</strong>: Fires when an Ajax form submission has completed successfully, but mail hasn’t been sent because a possible spam activity has been detected.
                <li><strong>Mail Sent</strong>: Fires when an Ajax form submission has completed successfully, and mail has been sent.
                <li><strong>Mail Failed</strong>: Fires when an Ajax form submission has completed successfully, but it has failed in sending mail.
                <li><strong>Sent</strong>: Fires when an Ajax form submission has completed successfully, regardless of other incidents. (This is the old plugin behavior.)
            </ul>

            <p>Note: you will begin seeing <strong>multiple events</strong> in Google Analytics for each form submission: “Sent” plus one of the other four, depending on what happened on submission.</p>
        </div>
        <?php
    }

    /**
     * Update option for CF7 GA 170 notes
     */
    function cf7_ga_dismiss_notice_170() {
        update_option( 'cf7-ga-170-notice-dismissed', 1, false );
    }
}

$CF7_GA = new CF7_GA();
