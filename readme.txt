=== Server Monitor ===
Contributors:      vendocrat, AlexanderPoellmann
Donate link:       http://vendocr.at/donate/
Tags:              server, server monitor, server monitoring, monitoring, administration, uptime
Requires at least: 3.5
Tested up to:      4.1
Stable tag:        0.2.1
License:           GNU General Public License v3.0
License URI:       http://www.gnu.org/licenses/gpl-3.0.html

Adds three simple widgets to your WordPress Dashboard displaying fundamental info about your server and installation.

== Description ==

Our Server Monitor plugin adds thre simple widgets to your WordPress Dashboard displaying general info about your server, PHP, your database and your WordPress installation.

We've kept the plugin as simple as possible and therefore made no settings available. But, as not all of you may need all of the information made available via this handy plugin, we've splitted it into three widgets. And as you know, you can simply hide them from the Options tab in your WordPress dashboard! Just click "Options" on the top right corner of your browser window and untick the widgets you don't need.

Oh, and for the speed junkies (like us), all data will be stored and served via a transient. This way the plugin will have no impact on your dashboard loading time!

Widget #1: **General**

*   Host Name
*   Server IP
*   Server Path
*   Server Load
*   Uptime
*   Server Info (Software)

Widget #2: **PHP & Database**

*   PHP Version
*   PHP Post Max Size
*   PHP Time Limit
*   PHP Max Input Vars
*   MySQL Version
*   Database Size

Widget #3: **System Status**

*   WordPress Version
*   Multisite?
*   Active Plugins
*   Memory Limit
*   Max Upload Size
*   Debug Mode
*   Language
*   Timezone

= Contributions =

Contributions are warmly welcome via [GitHub](https://github.com/vendocrat/WordPress-Server-Monitor).

= Translations =

Translations included:

*   English
*   German
*   Italian
*   Greek (thanks to [Anestis Samourkasidis](https://wordpress.org/support/profile/samourkasidis))

All our plugins are fully localized/translateable by default and include a .pot-file! Please contact us via [Twitter](https://twitter.com/vendocrat) or hit us on [GitHub](https://github.com/vendocrat/WordPress-Server-Monitor), if you have a translation you want to contribute!

= We'd love to hear from you! =

Follow us on [Twitter](https://twitter.com/vendocrat), like us on [Facebook](https://www.facebook.com/vendocrat), circle us on [Google+](https://plus.google.com/+vendocrat) or fork us on [GitHub](https://github.com/vendocrat)!

== Installation ==

1. Upload 'server-monitor' to the '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Done! Now visit your Dashboard to see the widgets.

== Screenshots ==

1. Widget #1
1. Widget #2
1. Widget #3

== Changelog ==

= 0.1.0 =
Initial release.

= 0.1.1 =
Fixed l10n bug (text domain not loaded).

= 0.1.2 =
Save data in transient to reduce server load.

= 0.2.0 =
Minor fixes/enhancements, added Greek translation (thanks to Anestis Samourkasidis).

= 0.2.1 =
Updated readme files and screenshots.