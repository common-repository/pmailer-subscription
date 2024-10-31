=== Plugin Name ===
Contributors: Pmailer
Donate link: http://www.pmailer.co.za
Tags:  pmailer, subscription forms, email marketing
Requires at least: 3.0
Tested up to: 3.3.1
Stable tag: 1.4.1
Version: 1.4.1

== Description ==

Allows users to subscribe to  pMailer lists by filling in their email addresses.

== Installation ==

This section describes how to install the plugin and get it working.

1. Unzip our archive and upload the entire `pmailer_importer` directory to your `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Go to Settings and look for "pMailer Importer" in the menu
1. Enter your pMailer API URL and API Key and let the plugin verify it.
1. Select one or more of your lists to have users/comments imported into (select multiple lists by holding down the control key on windows and command key on mac while clicking on a list).
1. Setup form options, the defaults will suffice but customization of form colors and behaviour can be setup.
1. To display the subscription form on your site you will need to navigate to the wordpress widgets page and drag and drop the pmailer subscription widget to any desired widget area.

== Frequently Asked Questions ==

= Can the subscription form widget be styled? =

Yes, there are options to customize the form on the pMailer subscriptions settings page.

= What does the smart form option do? =

A smart form is a subscription form that blurs out a web page and is displayed over the web page content.
The form can be quickly filled in and then is not displayed again. 
The smart form can be customised to only get displayed to a user on your website has been to X amount of pages.
E.g. The smart form can be displayed to users who have visited 3 pages on your site.

= How do I customise the smart form? =
The smart form can be styled by changing the style sheet named subscription_form.css found in pmailer-subscription/css in the plugins folder.

== Screenshots ==

1. Entering your API info
2. Setting your subscription options options 
3. Selecting lists that contacts get subscribed to
4. Smart form demo on a page
5. Subscription form demo on a page 

== Upgrade Notice ==

= 1.3 =
* All is good.

= 1.2 =
* All is good.

= 1.1 =
* All is good.

= 1.0 =
* All is good.

= 0.9 =
* Clear browser cache and delete cookies.

= 0.8 =
* Clear browser cache and delete cookies.

= 0.7 =
* Clear browser cache and delete cookies.

= 0.6 =
* Clear browser cache and delete cookies.

= 0.5 =
* Clear browser cache and delete cookies.

= 0.4 =
* Clear browser cache and delete cookies.

= 0.3 =
* Clear browser cache and delete cookies.

= 0.2 =
* Clear browser cache and delete cookies.

= 0.1 =
* Initial release, no upgrade information available.

== Change Log ==

= 1.4 =
* Check against bad URLs.

= 1.3 =
* Renamed potentially conflicting class names.

= 1.2 =
* Renamed potentially conflicting class names.

= 1.1 =
* Renamed potentially conflicting class names.

= 1.0 =
* Removed deprecated calls.

= 0.9 =
* Fixed a display issue on IE7 smart form.

= 0.8 =
* Form has been styled better and plays nicely with themes.

* Form has been styled better and plays nicely with themes.

= 0.7 =
* Changed styles to be more website friendly, and fixed a cookie path problem.

= 0.6 =
* Updated version number.

= 0.5 =
* Resolved more compatibility issues.

= 0.4 =
* Refactored JS to rely less on jQuery, removed styling options and refactored html styles to rely on currrent theme css.

= 0.3 =
* Defaulted ajax form submision option to off. This was causing to many problems in themes that used old versions of jQuery libraries.

= 0.2 =
* Javascript is less dependent on jQuery. Thus older versions of jQuery can be used.

= 0.1 =
* Initial release, no upgrade information available.

