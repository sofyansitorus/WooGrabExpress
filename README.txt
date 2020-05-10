=== WooGrabExpress ===
Contributors: sofyansitorus
Tags: woocommerce-shipping,ojek-shipping,grabexpress,grab-shipping
Donate link: https://www.buymeacoffee.com/sofyansitorus?utm_source=woograbexpress_plugin_page&utm_medium=referral
Requires at least: 4.8
Tested up to: 5.4.1
Requires PHP: 5.6
Stable tag: 1.4.0
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt

WooCommerce per kilometer shipping rates calculator for GrabExpress delivery service from Grab Indonesia.

== Description ==
WooCommerce per kilometer shipping rates calculator for GrabExpress delivery service from Grab Indonesia.

Please note that this plugin is not using the official Grab Indonesia API. This plugin just estimates the distance matrix using Google Maps Distance Matrix API and then calculating the cost using the rates defined in the settings.

= Features =

* Automatically split shipping for multiple items into several drivers if the package size exceeded package weight and dimensions limitation.
* Set shipping cost per kilometer.
* Set minimum cost that will be billed to the customer.
* Set maximum cost that will be billed to the customer.
* Set minimum shipping distances that allowed to use the courier.
* Set maximum shipping distances that allowed to use the courier.
* Set maximum package weight and dimensions that allowed to use the courier.
* Set shipping origin info by location coordinates.
* Set travel mode: Driving, Walking, Bicycling.
* Set route restrictions: Avoid Tolls, Avoid Highways, Avoid Ferries, Avoid Indoor.
* Show distance info to the customer for transparency.

= Dependencies =

This plugin requires Google API Key and also need to have the following APIs services enabled: Distance Matrix API, Maps JavaScript API, Geocoding API, Places API.

Please visit the link below to go to the Google API Console to create API Key and to enable the API services:

[https://console.developers.google.com/apis](https://console.developers.google.com/apis)

= Donation =

If you enjoy using this plugin and find it useful, please consider donating. Your donation will help encourage and support the plugin’s continued development and better user support.

Please use the link below to if you would like to buy me some coffee:

[https://www.buymeacoffee.com/sofyansitorus](https://www.buymeacoffee.com/sofyansitorus?utm_source=woograbexpress_plugin_page&utm_medium=referral)

== Installation ==
= Minimum Requirements =

* WordPress 4.8 or later
* WooCommerce 3.0 or later

= AUTOMATIC INSTALLATION =

Automatic installation is the easiest option as WordPress handles the file transfers itself and you don’t even need to leave your web browser. To do an automatic install of WooGrabExpress, log in to your WordPress admin panel, navigate to the Plugins menu, and click Add New.

In the search field type “WooGrabExpress” and click Search Plugins. You can install it by simply clicking Install Now. After clicking that link you will be asked if you’re sure you want to install the plugin. Click yes and WordPress will automatically complete the installation. After the installation has finished, click the ‘activate plugin’ link.

= MANUAL INSTALLATION =

1. Download the plugin zip file to your computer
1. Go to the WordPress admin panel menu Plugins > Add New
1. Choose upload
1. Upload the plugin zip file, the plugin will now be installed
1. After the installation has finished, click the ‘activate plugin’ link

== Frequently Asked Questions ==

= I see the message "There are no shipping methods available" in the cart/checkout page, what should I do? =

I have no clue what is happening on your server during the WooCommerce doing the shipping calculation, and there are too many possibilities to guess that can cause the shipping method not available. To find out the causes and the solutions, please switch to “ON” for the WooCommerce Shipping Debug Mode setting. Then open your cart/checkout page. You will see a very informative and self-explanatory debug info printed on the cart/checkout page. Please note that this debug info only visible for users that already logged-in/authenticated as an administrator. You must include this debug info in case you are going to create a support ticket related to this issue.

[Click here](https://fast.wistia.net/embed/iframe/9c9008dxnr) for how to switch WooCommerce Shipping Debug Mode.

= I got an error related with the API Key setting, what should I do? =

The error printed in there is coming from the Google API. Click any link printed within the error message to find out the causes and solutions. You may also need to check out the Browser's developer tools console to check if there is a JavaScript error/conflict. You must include this error and or the debug info in case you are going to create a support ticket related to this issue.

= How to set the plugin settings? =

You can set up the plugin setting from the WooCommerce Shipping Zones settings panel. Please [click here](https://fast.wistia.net/embed/iframe/95yiocro6p) for the video tutorial on how to set up the WooCommerce Shipping Zones.

= Where can I get support or report a bug? =

You can create a support ticket at plugin support forum:

* [Plugin Support Forum](https://wordpress.org/plugins/woograbexpress/)

= Can I contribute to developing this plugin? =

I always welcome and encourage contributions to this plugin. Please visit the plugin GitHub repository:

* [Plugin GitHub Repository](https://github.com/sofyansitorus/WooGrabExpress)

== Screenshots ==
1. Settings panel: General Options
2. Settings panel: GrabExpress Service Options
3. Shipping Calculator preview

== Changelog ==

= 1.4.0 =

* Fix - Fixed conflict with other shipping plugins in the cart calculate shipping form.
* Fix - Fixed compatibility issue with Checkout Fields Editor plugin.
* Enhancement - Improved API Key settings UI/UX.
* Enhancement - Improved almost overall admin settings UI/UX.

= 1.3.0 =

* Improvements - Enabled address 1 and address 2 fields in the shipping calculator form.
* Improvements - Added option to set distance slab for per km cost.
* Improvements - Added option to round up the distance.
* Improvements - Added option to use alternate API Key for server-side API request.
* Improvements - Added option to choose origin type data.
* Improvements - Added option to choose the preferred route type.
* Improvements - Improved the multiple driver's calculations.
* Improvements - Improved the UI/UX in the admin area.

= 1.2.4 =

* Improvements - Add new option: Enable Fallback Request.
* Improvements - Add and enhance Map Picker UI/UX.

= 1.2.3 =

* Fix - Remove Maps Place Picker.

= 1.2.2 =

* Fix - Maps picker.

= 1.2.1 =

* Fix - Cleaning up the js code.

= 1.2.0 =

* Improvements - Add "Map Location Picker" for store location setting.

= 1.1.1 =

* Improvements - Add the "Settings" link on the plugins.php page.

= 1.1.0 =

* Improvements - Add new settings field to enable or disable multiple drivers function.
* Fix - A non-numeric value encountered warning.

= 1.0.3 =

* Improvements - Add new filter hooks: woocommerce_woograbexpress_shipping_destination_info.
* Improvements - Add new filter hooks: woocommerce_woograbexpress_shipping_origin_info.

= 1.0.2 =

* Improvement - Tweak settings panel UI and default value.

= 1.0.1 =

* Improvement - Set cost based on driver counts needed.
* Improvement - Add validation for settings field: gmaps_api_key, origin_lat, origin_lng.

= 1.0.0 =

* Feature - Automatically split shipping for multiple items into several drivers if the package size exceeded package weight and dimensions limitation.
* Feature - Set shipping cost per kilometer.
* Feature - Define minimum cost that will be billed to the customer.
* Feature - Define maximum cost that will be billed to the customer.
* Feature - Set minimum shipping distances that allowed to use the courier.
* Feature - Set maximum shipping distances that allowed to use the courier.
* Feature - Set maximum package weight and dimensions that allowed to use the courier.
* Feature - Set shipping origin info by coordinates.
* Feature - Set travel mode: Driving, Walking, Bicycling.
* Feature - Set route restrictions: Avoid Tolls, Avoid Highways, Avoid Ferries, Avoid Indoor.
* Feature - Set visibility distance info to the customer.

== Upgrade Notice ==

= 1.4.0 =
This version includes fixes and improvements. Upgrade immediately is always highly recommended.
