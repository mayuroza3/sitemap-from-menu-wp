=== Sitemap From Menu ===
Contributors: mayuroza3
Tags: html sitemap, menu sitemap, navigation sitemap, sitemap generator, gutenberg block
Requires at least: 5.6
Tested up to: 7.0
Requires PHP: 7.4
Stable tag: 2.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Generates a beautifully optimized front-end HTML sitemap using a selected WordPress navigation menu.

== Description ==

Sitemap From Menu allows you to effortlessly generate a clean, semantic HTML frontend sitemap simply by selecting any WordPress natural navigation menu. With Native Block Editor support, robust A11y enhancements, and Enterprise-ready configurations, creating a fully extensible frontend sitemap has never been easier.

**Features:**
*   **Native Gutenberg Block:** Add and configure the sitemap visually straight from your block editor.
*   Generate a clean, hierarchical HTML sitemap natively tied to your menu tree.
*   **Theme-Compatible Output:** Works flawlessly with any theme (No forced inline CSS).
*   **Collapsible Trees:** Add interactive JS toggles seamlessly to expand/collapse multi-level nesting cleanly.
*   **Columns Grid Layout:** Choose between Single Column, 2 Columns, 3 Columns, or Auto-Grid interfaces natively inside settings.
*   **Accessibility Ready:** (Optional) Wrap endpoints explicitly inside semantic `<nav>` structures holding valid Aria properties.
*   **Schema.org Rich Data:** (Optional) Wrap native nodes inside `SiteNavigationElement` configurations to elevate Search Engine optimization capabilities intrinsically without bloating footprints.
*   **Robust Exclusions:** Filter unwanted entries by CSS classes, Page IDs, or URL strings securely natively.
*   **Sort Functionality:** Toggle between hierarchical presentation or flat A-Z alphabetical rendering intuitively.
*   Fully Translatable and i18n compliant codebase built using standard OOP patterns.

== Installation ==

1. Upload the `sitemap-from-menu` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Navigate to **Settings -> Sitemap From Menu** to configure the plugin globally.
4. Go to any Page/Post Editor and add the native **Sitemap From Menu** Block directly securely.
5. Alternatively, use the `[sitemap_from_menu]` shortcode naturally exactly as needed anywhere sequentially natively.

== FAQ ==

= Do I need a specific theme to use this? =
No, the plugin outputs clean, semantic unordered lists mimicking standard HTML formats inheriting theme fonts inherently elegantly.

= Is the shortcode block editor compatible? =
Not only does the shortcode work gracefully implicitly inside older Classic environments, but the plugin strictly includes a fully Native Server-Rendered Block perfectly compliant with modern environments.

= Can I append pages not in the menu? =
Yes! Via the plugin settings page securely passing comma-separated Page/Post IDs appends them explicitly safely.

= Does this compete with XML Sitemaps? =
Not at all. This solely renders an HTML interface for humans to interact cleanly. Your SEO plugin still securely handles XML transmissions separately gracefully natively.

== Screenshots ==

1. screenshot-1.png - The Admin Settings Page featuring menu selector, column layouts, and exclusion rules.
2. screenshot-2.png - Clean, naturally generated front-end HTML sitemap mapping dynamic multi-column menu lists.

== Changelog ==

= 2.2.0 =
* Improved compatibility with the latest WordPress 7.0.
* Versioned transient cache keys for reliable clearing under external object caching (Redis/Memcached).
* Cleaned up and polished code comments.

= 2.1.0 =
* Introduced Native Gutenberg Block.
* Added Accessibility (A11y) wrappers with semantic landmarks.
* Introduced Schema.org SiteNavigationElement markup.
* Added Collapsible Interactive Menu Trees with lightweight JS toggle.
* Added CSS Grid configuration options (Columns, Grids).
* Added URL String, Page ID, and CSS Class Exclusion constraints.
* Added Alphabetical sorting filters.

= 2.0.0 =
* Complete structural modernization.
* Implemented clean Object-Oriented PHP architecture.
* Integrated WordPress Settings API for the configuration dashboard.
* Switched to single-array options and transient caching.

= 1.0.0 =
* Initial release.
