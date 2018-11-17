=== BMLT Versions ===

Contributors: pjaudiomv
Tags: bmlt, meeting list
Tested up to: 5.0.0
Stable tag: 1.1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Displays download links and versions for latest BMLT Releases simply add [bmlt_versions] shortcode to your page.

SHORTCODE
[bmlt_versions]

**Attributes:** root_server, wordpress, drupal, basic, crouton, bread, yap
Adds ability to hide certain releases, default is to show all or 1.

Ex. [bmlt_versions drupal="0"] would not display drupal link.

MORE INFORMATION

<a href="https://github.com/pjaudiomv/bmlt-versions" target="_blank">https://github.com/pjaudiomv/bmlt-versions</a>

== Installation ==

This section describes how to install the plugin and get it working.

1. Upload the entire BMLT Versions Plugin folder to the /wp-content/plugins/ directory
2. Activate the plugin through the Plugins menu in WordPress
3. Add [bmlt_versions] shortcode to your Wordpress page/post.


== Changelog ==

= 1.1.1 =

* Changed urls for moved repos.

= 1.1.0 =

* Added shortcode attributes to be able to only display certain releases.
* Use github api for yap.

= 1.0.2 =

* Added Yap

= 1.0.1 =

* Added Bread

= 1.0.0 =

* Initial Release
