=== Sitemap From Menu ===
Contributors: mayuroza3
Tags: html sitemap, menu sitemap, navigation sitemap, sitemap generator
Requires at least: 5.6
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 2.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Generates a front-end HTML sitemap using a selected WordPress navigation menu.

== Description ==

Sitemap From Menu allows you to effortlessly generate a clean, semantic HTML frontend sitemap simply by selecting any WordPress natural navigation menu. With native shortcode support, customizable output, and zero bloat, creating an accessible frontend sitemap has never been easier.

**Features:**
*   Generate a clean, hierarchical HTML sitemap based on a WordPress menu
*   Output via `[sitemap_from_menu]` shortcode
*   Theme-compatible unstyled semantic HTML (No forced inline CSS)
*   Include multi-level children/nested items (optional)
*   Include menu item descriptions (optional)
*   Append extra posts/pages using post IDs
*   Built using modern WordPress OOP architecture
*   Performance optimized with custom transient caching
*   Fully Translatable and i18n compliant

== Installation ==

1. Upload the `sitemap-from-menu` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to **Settings -> Sitemap From Menu** to configure the plugin.
4. Select the navigation menu you want to use.
5. Use the shortcode `[sitemap_from_menu]` on any page, post, or widget area to display the sitemap.

== FAQ ==

= Do I need a specific theme to use this? =
No, the plugin outputs clean, semantic unordered lists that perfectly inherit your theme's native styling.

= Is the shortcode block editor compatible? =
Yes, you can drop the `[sitemap_from_menu]` shortcode directly into a Shortcode Block and it will render seamlessly over Gutenberg.

= Can I append pages not in the menu? =
Yes! Via the plugin settings page, you can safely pass comma-separated Page/Post IDs to append them to the end of your sitemap.

== Screenshots ==

1. The plugin admin settings panel.
2. The naturally generated front-end sitemap.

== Changelog ==

= 2.0.0 =
* Complete structural modernization.
* Implemented clean Object-Oriented architecture.
* Integrated Settings API for the admin configuration page.
* Switched to single-array options and transient caching for immense performance increases.
* Added HTML sanitization, nonce protection inherently via the Settings API, strict codebase security review.

= 1.0.0 =
* Initial release.
