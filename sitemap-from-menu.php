<?php
/**
 * Plugin Name:       Sitemap From Menu
 * Plugin URI:        https://github.com/mayuroza3/sitemap-from-menu-wp
 * Description:       Generates a front-end HTML sitemap using a selected WordPress navigation menu.
 * Version:           2.2.0
 * Requires at least: 5.6
 * Tested up to:      7.0
 * Requires PHP:      7.4
 * Author:            Mayur Oza
 * Author URI:        https://www.mayuroza.com
 * License:           GPL v2 or later
 * License URI:       https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:       sitemap-from-menu
 * Domain Path:       /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Define Plugin Constants.
define( 'SFM_VERSION', '2.2.0' );
define( 'SFM_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'SFM_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'SFM_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );

// Require core classes.
require_once SFM_PLUGIN_DIR . 'includes/class-plugin.php';
require_once SFM_PLUGIN_DIR . 'includes/class-admin.php';
require_once SFM_PLUGIN_DIR . 'includes/class-frontend.php';
require_once SFM_PLUGIN_DIR . 'includes/class-renderer.php';
require_once SFM_PLUGIN_DIR . 'includes/class-shortcode.php';
require_once SFM_PLUGIN_DIR . 'includes/class-block.php';

// Initialize the plugin.
function sfm_run_plugin() {
	$plugin = \SitemapFromMenu\Plugin::get_instance();
	$plugin->init();
}
sfm_run_plugin();
