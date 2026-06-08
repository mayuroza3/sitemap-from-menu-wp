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

		// Register Elementor Widget.
		add_action( 'elementor/widgets/register', [ $this, 'register_elementor_widget' ] );
	}

	/**
	 * Register Sitemap Elementor Widget.
	 *
	 * @param \Elementor\Widgets_Manager $widgets_manager Elementor widgets manager.
	 */
	public function register_elementor_widget( $widgets_manager ) {
		require_once SFM_PLUGIN_DIR . 'includes/class-elementor-widget.php';
		$widgets_manager->register( new ElementorWidget() );
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
