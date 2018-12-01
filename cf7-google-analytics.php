<?php
/**
 * Plugin Name: Contact Form 7 Google Analytics Integration
 * Plugin URI: https://andrewrminion.com/contact-form-7-google-analytics/
 * Description: Adds Google Analytics Event Tracking to all Contact Form 7 forms.
 * Tags: contact form, contact form 7, cf7, contactform7, google analytics, ga, universal, forms, form, track, tracking, event, events, goal, goals
 * Version: 1.8.3
 * Author: AndrewRMinion Design
 * Author URI: https://www.andrewrminion.com
 *
 * @package WordPress
 * @subpackage CF7_Google_Analytics
 */

/** Don't allow calling this file directly */
if ( ! function_exists( 'add_filter' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	exit();
}

/** Define the plugin file */
if ( ! defined( 'CF7GA_PLUGIN_FILE' ) ) {
	define( 'CF7GA_PLUGIN_FILE', __FILE__ );
}

/** Include the main class. */
if ( ! class_exists( 'CF7_Google_Analytics' ) ) {
	include_once dirname( __FILE__ ) . '/inc/class-cf7-google-analytics.php';
	new CF7_Google_Analytics();
}
