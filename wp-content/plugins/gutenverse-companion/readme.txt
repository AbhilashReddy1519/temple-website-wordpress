=== Gutenverse Companion ===
Contributors: jegstudio
Tags: themes, basic, template, block, editor
Tested up to: 6.7
Requires PHP: 7.0
Stable tag: 2.0.2
License: GPLv3
License URI: https://www.gnu.org/licenses/gpl-3.0.html

Companion plugin for Gutenverse base themes

== DESCRIPTION ==

A companion plugin designed specifically to enhance and extend the functionality of Gutenverse base themes. This plugin integrates seamlessly with the base themes, providing additional features, customization options, and advanced tools to optimize the overall user experience and streamline the development process.

== Use of 3rd-Party Services ==

To improve the user experience, **Gutenverse Companion** may use the following third-party service:

=== Gutenverse Companion List Service ===

This service is used to fetch the list of companion demos for the Gutenverse theme. It allows users to view and select from various demo templates compatible with the theme they are using. The service retrieves a list of demos based on the current theme, enabling users to browse and install demos according to their preferences.

- **Service Endpoint:** `https://gutenverse.com/wp-json/gutenverse-server/v4/companion/list`
- **Purpose:** To fetch the companion demo list for the user’s current theme.
- **Usage:** The returned demo list helps users quickly find and install demos suitable for their selected theme, streamlining the process of setting up a new site or changing the look and feel of an existing one.
- **Circumstance:** This service is typically used within the **Companion Import Wizard** on the WordPress dashboard, where users can select and import demos directly from the interface based on the theme they have installed.
- **Data Sent:** The service only sends information about the **Gutenverse theme** currently in use by the user. No additional data is transmitted, ensuring that only relevant demos for that specific theme are returned.

This data is securely sent to our server at [gutenverse.com](https://gutenverse.com/). Rest assured, no information is transmitted automatically without your explicit consent.

If you’d like more details about Gutenverse, you can check out the terms and conditions [here!](https://gutenverse.com/terms-and-conditions/)

== COMMUNITY ==

Join our communities and share your experiences with us.

= OUR COMMUNITIES =

- <a href="https://www.facebook.com/groups/gutenversecommunity" target="_blank" rel="">Join the Facebook Group!</a>
- <a href="https://twitter.com/gutenverse" target="_blank" rel="">Follow us on Twitter!</a>
- <a href="https://www.instagram.com/gutenverse/" target="_blank" rel="">Follow us on Instagram!</a>
- <a href="https://www.tiktok.com/@gutenverse/" target="_blank" rel="">Check our TikTok!</a>

== REPOSOTORY ==

Check out our repository at <a href="https://github.com/Jegstudio/gutenverse-companion" target="_blank" rel="norefferer">GitHub</a>.
You're welcome to report bugs or request features.

== Installation ==

Installing Gutenverse Companion is a breeze. Follow one of the two methods below:

= From the WordPress Plugin Dashboard: =
1. Go to the WordPress plugin dashboard and search for "Gutenverse Companion."
2. Click on the "Install Now" button next to the Gutenverse Companion plugin.
3. Once installed, activate the plugin from your WordPress Dashboard.

= Using the Upload Method: =
1. Download the Gutenverse Companion plugin as a zip file from the official website.
2. In your WordPress Dashboard, navigate to "Plugins" and click on "Add New."
3. Select the "Upload Plugin" button and choose the Gutenverse Companion zip file.
4. Click on "Install Now" and activate the plugin after installation.

For additional information and assistance, please visit our extensive <a href="https://gutenverse.com/docs/" target="_blank" rel="">Documentation</a> section.

== Changelog ==
= 2.0.2 =
* Fix dashboard for themeforest themes

= 2.0.1 =
* Improve UI, show which current demo is active
* Dequeue default style, when other demo is active

= 2.0.0 =
* Update companion mechanism for multipurpose themes

= 1.0.5 =
* Update companion mechanism for single themes

= 1.0.4 =
* Add filter to check base theme

= 1.0.3 =
* Fix template empty

= 1.0.2 =
* Fix companion not working
* Fix notice for base theme

= 1.0.1 =
* Add new mode for single themes

= 1.0.0 =
* Initial Release