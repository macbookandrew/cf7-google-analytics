# Contact Form 7 Google Analytics #
**Contributors:** macbookandrew  
**Tags:** contact form, contact form 7, cf7, contactform7, google analytics, ga, universal, forms, form, track, tracking, event, events, goal, goals  
**Donate link:** https://cash.me/$AndrewRMinionDesign  
**Requires at least:** 4.3  
**Tested up to:** 4.6  
**Stable tag:** 1.2.1  
**License:** GPL2  

Adds Google Analytics Event Tracking to all Contact Form 7 forms.

## Description ##
Adds Google Analytics Event Tracking to all Contact Form 7 forms sitewide, using “Contact Form” as the Event Category, “Send” as the Event Action, and the form name as the Event Label.

## Installation ##
1. Install the plugin
1. Check your statistics in Google Analytics under Behavior > Events or under Real-Time > Events
1. Optionally, to set up a goal, follow these steps:
    1. Click on “Admin” in your Google Analytics account menubar
    1. In the right-most column (“View”), click on “Goals”
    1. Click on the “+ New Goal” button
    1. Choose the “Template” radio button (selected by default) and click “Continue”
    1. Enter a name for the goal (I suggest “Contact Forms”)
    1. Choose the “Event” radio button and click “Continue”
    1. Set “Category Equals to Contact Form”
    1. Set “Action Equals to Send”
    1. Optionally add a label if you want to define a goal for one specific form
    1. Click the “Save” button

## Changelog ##

### 1.2.1 ###
 - Fix PHP undefined index issue

### 1.2 ###
 - Fix issue where any manually-specified items were being deleted

### 1.1 ###
 - Add support for Google Analytics by Yoast

### 1.0 ###
 - First stable version
