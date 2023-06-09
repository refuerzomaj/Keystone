=== WP Real Estate ===
Contributors: hozyali, 99robots, charliepatel, draftpress
Donate link: http://www.etechy101.com/wp-real-estate-wordpress-plugin
Tags: property listing, wp real estate, wordpress real estate plugin, advanced property search
Requires at least: 4.5
Tested up to: 5.5.1
Stable tag: 5.5.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Specially for real estate agents and people who are willing to list their property listing on their own site.

== Description ==

Full usage video tutorial [How to Setup a Real Estate Website with WordPress](https://www.youtube.com/watch?v=PDkLs5lj9G4)

Great options to list properties on your WordPress site.

Specially for real estate agents and people who are looking to list their property listings.

If you are looking to build a site where you can list property for sale or rent, this is the plugin you need.

Features
------------

* Add Property
* Add multiple property photos
* Advanced property search
* jQuery photo slider in property detailed view
* all property options so you can add any type of property listing
* Google Maps
* Property search widget - can be added in sidebar
* Advanced search widget and custom page also available
* Custom property listing page
* Custom manage-able property types
* Manage the number of property listing per page

Newly added in WP Real Estate 4.0
------------------

* Translation Ready
* I am still looking for help with translation of the text. If you can help, please drop an email to sales@intensewp.com. Thanks
* Responsive layout for property listing and property view pages

NOTE: Existing users who are upgrading to this new version will need to go in settings page and update the property listing page first. And also have to update the property types in order to have the existing property listings work smooth.

If facing 404 not found errors
------------------------------

* If you do all the settings correctly and still see 404 page not found errors, please go to your wp-admin > settings > permalinks and simply press the save button. It will reset your permalinks and make the plugins links active. Pressing the save button does not need to change the actual permalinks configuration.

How to increase the number of photos limit from 10
---------------------------------------------------

* Go to WPRealEstate > Settings
* Change the value of Max Property Photos
* You will be allowed for the same number of photos on add and edit property screens

Create Property Listing page
------------------------------

* Create a normal page in your wordpress website
* Then go to WPRealEstate in admin > settings and select that page for property listing

Create an advanced property search page
---------------------------------------
* Create a normal page in your wordpress website
* In the body editor of the page, add this short code [WPRE_SEARCH]

For support and feedback, please [click here](http://support.intensewp.com "wordpress real estate plugin").

== Installation ==


1. Upload `wprealestate` to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. You should see a Properties tab on left panel

== Frequently Asked Questions ==

= How do I add a property search =

Go to Appearance > Widgets and add the Property Search widget in your sidebar.

= How do I list properties by rent or by sale? =

Add this shortcode [WPRE_LIST_PROPERTIES list_type=sale] or can use list_type=rent. Default is sale

= How do I add an advanced property search widget =

Go to Appearance > Widgets and add the text widget in your sidebar. then add this shortcode in it [WP_RE_ADVANCED_SEARCH]

== Screenshots ==

1. A very detailed view of the property with rotating images
2. Property listing with basic details
3- Add property screen with great additional options and custom post type for better SE ranking

== Changelog ==

= 5.5.2 = 2020-09-14
* Updated to make compatible with WordPress 5.5.1

= 5.5.1 =
* Bugs, Warnings and Notices Fixed
* Updated to make compatible with WordPress 5.2.3

= 5.5 =
* Bugs, Warnings and Notices Fixed
* Updated UI to look cleaner

= 5.4 =
* Redesigned listing and view property pages
* Improved css styles for sidebar search form
* Google property map added (Add api key in settings)

= 5.3 =
* Fixed text domain in some places

= 5.2 =
* Updated language files

= 5.1 =
* Updated localization

= 5.0 =
* Fixed localization error in some areas
* Updated text domain to refer to the original plugin file name
* Tested for 4.8 WordPress
* Added Portuguese (Brazil) translation

= 4.9 =
* Tested for WordPress 4.7.5
* Fixed minor bug of sql limit in property listing

= 4.8 =
* Tested for WordPress 4.7.3
* Fixed a couple of internal urls

= 4.7 =
* Tested for WordPress 4.3.1


* Fixed a couple of internal urls

= 4.5 =
* Tested on WP 4.1. Works perfect

= 4.4 =
* Tested on WP 4.0. Works perfect
* Fixed header redirect error on activation in some browsers

= 4.3 =
* inquiry form validation
* currency dispaly in full property view
* link broken after form submit
* Pagination fixed in property listing short coded pages

= 4.2 =
* French language translation added
* Minor bug fixed in listing template

= 4.1 =
* Responsive layout for property listing and view pages

= 4.0 =
* Translation supported

= 3.7 =
* Fixed the bootstrap css error some people reported.

= 3.6 =
* Displayed rent in property view

= 3.5 =
* Added couple of additional fields in admin area
* Admin details moved under the editor for better viewing
* Added state field
* Add rent price field (not use-able yet)
* Fixed the layout issue on new theme of wordpress

= 3.4 =
* Fixed the empty commas in property location details on view page

= 3.3 =
* Fixed warning messages appearing on property view page

= 3.2 =
* Fixed the page listing in settings to select property page
* Fixed the Navigation in property listing page
* Fixed the warning message on metabox file which displays photo slider on property pages

= 3.1 =
* Shortcode for property listing by rent or sale

= 3.0 =
* Unlimited property photos
* Enable / Disable social sharing buttons on property view
* Hide fields if not entered from admin
* Property ID can be hidden from settings

= 2.8 =
* Small bug fixed. the email message had wrong field labels for phone and email value.

= 2.7 =
* Property listing and detail view hides the items which are not filled by admin
* So if you select Bedroom 'Not Applicable', it will not show on property view and listing

= 2.6 =
* Fixed advanced property search variables
* Added notes in some places

= 2.5 =
* Added important notes on plugin homepage

= 2.4 =
* Added 2 new options in settings page. Enable/Disable sidebar in Property view and listing page

= 2.3 =
* Tested all functions for wordpress 3.7.1

= 2.2 =
* Added new fields in advanced search and customization
* Fixed the search query to work without price fields selected.

= 2.1 =
* A security glitch with ajax scripts has been fixed

= 2.0 =
* Advanced property search section added
* Custom property type has been added. Can be adjusted from wp-admin
* Advanced search form customization
* Property listing page can now be chosen from the settings page
* Property listing number to be set from settings


= 1.3.3 =
* Added sidebar and widgets support in property listing template. so property search widget can be added in listing page

= 1.3.2 =
* Beware! you will have to edit all your property listings to edit facilities again.
* property facilities now using custom taxonomy so its auto searchable and its linked to archive listing.

= 1.3.1 =
* updated options page with jquery saving
* added property listing in rss feeds

= 1.3 =
* fixed slider images path again
* added settings page with 3 options
* edit currency, widget bg color and inquiry email from settings
* fix the css issue in property listing page
* more updates to come soon...

= 1.2.1 =
* fixed slider images path

= 1.2 =
* Currency variable moved in db options
* Fixed buffer function error.

= 1.0.2 =
* added screen shots

= 1.0.1 =
* updated readme only

= 1.0 =
* Current version is a basic one
* Will be adding more features soon
