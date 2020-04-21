=== Stopsign ===
Contributors: joho68
Donate link: https://joho.se
Tags: commute, public transport, time table
Requires at least: 5.4.0
Tested up to: 5.4.0
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Display depature times of public transport at a specific stop using data from Trafiklab.se

== Description ==

This WordPress shortcode plugin uses APIs from Trafiklab.se to display departure times of public transport at a specific stop.

The type of transport can vary depending on the stop chosen for the shortcode.

A few notes about this plugin:

*   You need to register API keys with Trafiklab.se (registration is free)
*   This plugin may work with earlier versions of WordPress
*   I have only tested this plugin on 5.4.0, at the time of this writing
*   This plugin makes use of the allow_url_fopen() PHP function
*   This plugin may create entries in your PHP error log (if active)
*   This plugin contains no tracking code and does not process or collect any information about the visitor

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the contents of the `stopsign` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. As mentioned previously, you need to register with Trafiklab.se and get your API keys. This is free. You need an API key for "Trafiklab ResRobot - Stolptidtabeller (2)", and an API key for "Trafiklab ResRobot - Reseplanerare"

== Usage ==

There are two shortcodes available with this plugin:

[stopsign_shortcode] and [stopsign_shortcode_widget]

The following parameters are supported:

id="<commute stop ID>" (The commute stop ID from Trafiklab.se)
numgroup="0|1"         (Group output)
maxgroup="0-9"         (Maximum group output, default is 3)

For example:

[stopsign_shortcode id="123456" numgroup="0"][/stopsign_shortcode]

== Frequently Asked Questions ==

= How to I make local customizations =

Use the files in the public/templates directory to modify output. There are also .css files that you can tweak to your liking. You can also copy the templates/ folder to wp-content/themes/yourtheme/stopsign. Changes you make to the copied files will not be overwritten.

= Are API calls throttled =

Stopsign makes use of WordPress Transients to prevent unnecessary API calls. No other caching mechanism is used. Transients are deleted upon deactivation and uninstallation of the Stopsign plugin.

= Is the plugin locale aware =

Stopsign uses standard WordPress functionality to handle localization/locale. The native language localization of the plugin is English. It has been translated to Swedish by the author.

= Are there any incompatibilities =

This is a hard question to answer. There are no known incompatibilities that I am aware of. There is no background loading of information.

== Changelog ==

= 1.0.0 =
* Initial release

== Upgrade Notice ==

= 1.0.0 =
Initial release

== Credits ==

The Stopsign WordPress Plugin is based on the WordPress Plugin Boilerplate, as a starting point. The Stopsign WordPress Plugin was written by Joaquim Homrighausen.

The WordPress Plugin Boilerplate was started in 2011 by [Tom McFarlin](http://twitter.com/tommcfarlin/) and has since included a number of great contributions. In March of 2015 the project was handed over by Tom to Devin Vinson.

The current version of the Boilerplate was developed in conjunction with [Josh Eaton](https://twitter.com/jjeaton), [Ulrich Pogson](https://twitter.com/grapplerulrich), and [Brad Vincent](https://twitter.com/themergency).

You can get the WordPress Plugin Boilerplate here: http://wppb.io/
You may also like the WordPress Plugin Boilerplate generator: https://wppb.me/
