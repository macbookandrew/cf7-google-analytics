=== Contact Form 7 Google Analytics ===
Contributors: macbookandrew
Tags: contact form, contact form 7, cf7, contactform7, google analytics, ga, universal, forms, form, track, tracking, event, events, goal, goals, analytics
Donate link: https://cash.me/$AndrewRMinionDesign
Requires at least: 4.3
Tested up to: 6.3.0
Stable tag: 1.8.10
License: GPL2

Adds Google Analytics Event Tracking to all Contact Form 7 forms.

**Note**: once you are using Google Analytics v4, this plugin may no longer be necessary. See [Enhanced event measurement documentation](https://support.google.com/analytics/answer/9216061) for information on how to enable event measurement in Google Analytics v4.

== Description ==
Adds Google Analytics Event Tracking to all Contact Form 7 forms sitewide, using “Contact Form” as the Event Category, the Contact Form 7 event as the Event Action, and the form name as the Event Label.

Supports the most popular Google Analytics plugins, including the following:

- [Google Analytics by MonsterInsights](https://wordpress.org/plugins/google-analytics-for-wordpress/), formerly “Google Analytics by Yoast”
- [Google Analytics Dashboard for WP](https://wordpress.org/plugins/google-analytics-dashboard-for-wp/) by Alin Marcu/ExactMetrics
- [Google Analytics](https://wordpress.org/plugins/googleanalytics/) by Kevin Sylvestre
- [Google Analytics](https://wordpress.org/plugins/pc-google-analytics/) by Praveen Chauhan
- [Analytics Tracker](https://wordpress.org/plugins/analytics-tracker/) by Valeriu Tihai
- [Enhanced Ecommerce Google Analytics Plugin for WooCommerce](https://wordpress.org/plugins/enhanced-e-commerce-for-woocommerce-store/) by Tatvic
- The default Google Analytics code copied from the Analytics admin panel (both the newer `gtag.js` and the older universal `analytics.js`)
- Google Tag Manager (using `gtm.js`) (see additional setup instructions in the FAQ section)
- Any other plugin using `gtag`, `ga`, `_gaq`, or `__gaTracker` as the Javascript function
- To add others, [open a pull request](https://github.com/macbookandrew/cf7-google-analytics)

== Installation ==
1. Install and activate the plugin
1. Enable the actions you would like to send (if you skip this step, it will send all available actions).
1. Enjoy!

== Frequently Asked Questions ==

= What about GDPR (General Data Protection Regulation)? =

This plugin does not collect or send any personal information or form submissions to Google Analytics. The only form-related information sent is the name of the form and the submission result (invalid, spam, mail sent, mail failed, and sent).

Google Analytics does collect more information including, but not limited to, the following:

- Page name, URL, and language
- Screen and window size
- The user’s IP address

For more information, refer to [Google’s compliance information](https://privacy.google.com/businesses/compliance/#?modal_active=none).

Please also note that Contact Form 7 and other plugins may collect, process, or store user data.

= Where will events show up? =

Check your statistics in Google Analytics under *Behavior > Events* or under *Real-Time > Events*.

= What events will be shown? =

This depends on the settings you choose.

- **Invalid** - Fires when an Ajax form submission has completed successfully, but mail hasn’t been sent because there are fields with invalid input.
- **Spam** - Fires when an Ajax form submission has completed successfully, but mail hasn’t been sent because a possible spam activity has been detected.
- **Mail Sent** - Fires when an Ajax form submission has completed successfully, and mail has been sent.
- **Mail Failed** - Fires when an Ajax form submission has completed successfully, but it has failed in sending mail.
- **Sent** - Fires when an Ajax form submission has completed successfully, regardless of other incidents.
	- This is the only event available if you have Contact Form 7 version 4.7 or earlier.
	- This is the only original event sent by this plugin. I recommend enabling the other four and disabling this event, unless you need it for old goals you had set up from several years ago.

= How do I set a goal in Google Analytics? =

Note: these instructions are provided for reference and may become out of date if Google Analytics changes their feature set or labels.

1. Click on “Admin” in your Google Analytics account menubar
1. In the right-most column (“View”), click on “Goals”
1. Click on the “+ New Goal” button
1. Set the Goal description
	1. Enter a name for the goal (I suggest “Contact Forms”)
	1. Choose the “Event” radio button and click “Continue”
1. Set the Goal details
	1. Set the “Category Equals to” field to “Contact Form 7”
	1. Set the “Action Equals to” field to the event you wish to track ([see above](https://wordpress.org/plugins/cf7-google-analytics/#what%20events%20will%20be%20shown%3F) for a list of events)
	1. If you want to define goals for specific form, enter the name of your form in the “Label” field
	1. Click the “Save” button

= How do I use this with Google Tag Manager (gtm.js)? =

1. In your Google Tag Manager workspace, add a new Trigger.
	1. Choose trigger type: “Other/Custom Event”
	1. Set “Event name” to “Contact Form 7”
	1. Set “This trigger fires on” to “Some Custom Events”
	1. Set the dropdowns to “Event contains Contact Form 7”
		- ![Settings screenshot](https://raw.githubusercontent.com/macbookandrew/cf7-google-analytics/master/assets/gtm-trigger.png)
	1. Save the trigger
1. In your Google Tag Manager workspace, add a new Tag.
	1. Choose tag type: “Universal Analytics”
	1. Change “Track Type” to “Event”
	1. Set “Category” to “Contact Form 7”
	1. Set “Label” to “{{Event}}”
		- ![Settings screenshot](https://raw.githubusercontent.com/macbookandrew/cf7-google-analytics/master/assets/gtm-tag.png)
	1. Click in the “Triggering” box and choose the trigger you set up above.
1. Save and publish your changes.

== Changelog ==

= 1.8.10 =
- Fix issue with minified JS

= 1.8.9 =
- Fix undefined index issue in new installations

= 1.8.8 =
- Update tested-up-to version and automatic deployment.

= 1.8.7 =
- Fix typo in old tracking code for GTM.

= 1.8.6 =
- Fix typo in upgrade notes.

= 1.8.5 =
- Fix a bug with GTM and older CF7 versions.
- Drop “Contact Form” event labels for some integrations, leaving just “Contact Form 7” as the event label.

= 1.8.4 =
- Fix a bug sending “Contact Form” instead of “Contact Form 7” as the event label for certain configurations.

= 1.8.3 =
- Fix a bug sending the form ID instead of name to Google Analytics.

= 1.8.2 =
- Cache form titles and IDs for better performance.
- Remove 1.7.0 admin upgrade notices.

= 1.8.1 =
- Fix a bug causing events not to send due to upgrade logic.
- Set default options if user has not selected events to send.

= 1.8.0 =
- Add options to enable/disable the available event actions.

= 1.7.5 =
- Update readme with goal tracking instructions

= 1.7.4 =
- Update readme with GDPR notes

= 1.7.3 =
- Add [`gtm.js` setup instructions](https://github.com/macbookandrew/cf7-google-analytics#how-do-i-use-this-with-google-tag-manager-gtmjs)
- Update plugin coding standards

= 1.7.2 =
- Fix error with undefined constant

= 1.7.1 =
- Fix error with admin notice on PHP < 5.0

= 1.7.0 =
- Add support for all CF7 DOM events. Please [see this note for more detail](https://github.com/macbookandrew/cf7-google-analytics#what-events-will-be-shown).

= 1.6.1 =
- Add support for PHP < 5.3

= 1.6.0 =
- Add support for Google Tag Manager

= 1.5.0 =
- Add support for sending the Contact Form 7 form name instead of just the form ID

= 1.4.1 =
- Update suppoprt for gtag.js custom events

= 1.4.0 =
- Add support for the Global Site Tag (gtag.js)

= 1.3.0 =
- Update to use new DOM events in Contact Form 7 v4.8

= 1.2.2 =
- Fix JS issue if `ga` is undefined

= 1.2.1 =
- Fix PHP undefined index issue

= 1.2 =
- Fix issue where any manually-specified items were being deleted

= 1.1 =
- Add support for Google Analytics by Yoast

= 1.0 =
- First stable version
