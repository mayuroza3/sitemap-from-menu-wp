# Sitemap From Menu 🗺️

A lightweight WordPress plugin that generates a front‑end HTML sitemap from any selected **navigation menu**, instead of relying on categories or post hierarchies.
Perfect for sites where content is manually curated via menus and you want the sitemap to reflect that exact structure.

---

## ✨ Features

- Uses existing WordPress navigation menus as the sitemap source.
- Supports nested (multi‑level) menus.
- Outputs simple, theme‑friendly `<ul><li>` HTML markup.
- Works independently of SEO plugins (Yoast, Rank Math, etc.). 
- Optionally append extra pages via comma‑separated page IDs.
- Lightweight, no external dependencies.

---

## 📷 Screenshot

![Sitemap From Menu Screenshot](assets/screenshot-1.png)

---

## 📦 Installation

1. Download this repository as a ZIP or clone it into your WordPress installation.  
2. Copy the plugin folder to your `wp-content/plugins/` directory.  
3. In your WordPress dashboard, go to **Plugins → Installed Plugins**.  
4. Activate **Sitemap From Menu**.

---

## 🧠 Usage

### 1. Configure the sitemap source

1. Go to **Appearance → Menus** and create or select the menu you want to use as the sitemap.  
2. Go to **Settings → Custom Sitemap From Menu** in the WordPress admin.  
3. Select the menu you want to use in the **Select Menu** dropdown.  
4. (Optional) Add **Extra Page IDs** as a comma‑separated list (e.g. `12,34,56`) to always include those pages in the sitemap.

### 2. Add the shortcode

Add the shortcode to any page, post, or widget:


### 3. Advanced shortcode options

You can override the settings page values per instance using shortcode attributes:

[csfm menu="footer-menu"]
[csfm pages="10,25,42"]
[csfm menu="main-menu" pages="10,25,42"]



- `menu` can be a menu ID, slug, or name.
- `pages` is a comma‑separated list of page IDs.

If you omit these attributes, the shortcode uses the menu and extra pages configured under **Settings → Custom Sitemap From Menu**.

---

## 🤝 Contributing

Pull requests, bug reports, and feature suggestions are welcome.  
If you encounter issues, please open an issue on GitHub with steps to reproduce and your WordPress/PHP versions.

---

## 👤 Author

Created by [Mayur Oza](https://mayuroza.com)  
Follow on [GitHub](https://github.com/mayuroza3)

---

## 📄 License

This plugin is released under the [GPLv2 or later](https://www.gnu.org/licenses/gpl-2.0.html) license.  
You are free to use, modify, and distribute it under the terms of the GPL.