<?php
namespace SitemapFromMenu;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Main Plugin Class
 */
class Plugin {

	/**
	 * Single instance of the class
	 *
	 * @var Plugin
	 */
	private static $instance = null;

	/**
	 * Get the singleton instance.
	 *
	 * @return Plugin
	 */
	public static function get_instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Initialize the plugin hookers.
	 */
	public function init() {
		add_action( 'plugins_loaded', [ $this, 'load_textdomain' ] );

		if ( is_admin() ) {
			$admin = new Admin();
			$admin->init();
		}

		$frontend = new Frontend();
		$frontend->init();

		$shortcode = new Shortcode();
		$shortcode->init();

		$block = new Block();
		$block->init();

	}

	/**
	 * Load plugin text domain for translations.
	 */
	public function load_textdomain() {
		load_plugin_textdomain(
			'sitemap-from-menu',
			false,
			dirname( plugin_basename( SFM_PLUGIN_DIR . 'sitemap-from-menu.php' ) ) . '/languages'
		);
	}
}
