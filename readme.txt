=== Sitemap From Menu ===
Contributors: mayuroza
Tags: sitemap, menu, seo, shortcode
Requires at least: 5.0
Tested up to: 6.5
Requires PHP: 7.0
Stable tag: 1.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Generate a custom sitemap from a WordPress navigation menu with optional additional page links.

== Description ==

**Sitemap From Menu** allows you to generate a clean sitemap page using your site's existing navigation menu. You can also include additional pages by passing their IDs. Perfect for SEO and site indexing.

**Features:**
- Select a menu from the admin panel.
- Optionally add extra page IDs.
- Use a shortcode `[csfm]` to show the sitemap.
- Pass menu name and extra pages directly via shortcode like `[csfm menu="Header Menu" pages="12,34,56"]`.

== Installation ==

1. Upload the plugin folder to `/wp-content/plugins/`.
2. Activate the plugin via the Plugins menu in WordPress.
3. Go to **Settings > Custom Sitemap From Menu** to select the menu and optionally add extra pages.
4. Create a new page and insert the shortcode `[csfm]` or `[csfm menu="Your Menu" pages="1,2,3"]`.

== Shortcode Usage ==

- `[csfm]` – Uses admin-configured menu and pages.
- `[csfm menu="Header Menu"]` – Override with menu name.
- `[csfm pages="12,34,56"]` – Add specific pages by ID.
- `[csfm menu="Footer" pages="99,100"]` – Combine both.

== Screenshots ==

1. Admin settings screen to select menu and add page IDs.
2. Sitemap rendered on frontend.

== Changelog ==

= 1.1 =
* Added shortcode attributes support for `menu` and `pages`.
* Improved output sanitization and security.

= 1.0 =
* Initial release.

== License ==

This plugin is licensed under the GPLv2 or later.
