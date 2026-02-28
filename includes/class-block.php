<?php
namespace SitemapFromMenu;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles the registration and rendering of the Native Gutenberg Block.
 */
class Block {

	/**
	 * Initialize the block registration hooks.
	 */
	public function init() {
		add_action( 'init', [ $this, 'register_block' ] );
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_block_assets' ] );
	}

	/**
	 * Register the dynamic block.
	 */
	public function register_block() {
		if ( ! function_exists( 'register_block_type' ) ) {
			return;
		}

		register_block_type( 'sitemap-from-menu/block', [
			'api_version'     => 2,
			'render_callback' => [ $this, 'render_block' ],
			'attributes'      => [
				'menu_id'         => [ 'type' => 'string', 'default' => '0' ],
				'include_nested'  => [ 'type' => 'boolean', 'default' => true ],
				'include_desc'    => [ 'type' => 'boolean', 'default' => false ],
				'heading_text'    => [ 'type' => 'string', 'default' => '' ],
				'heading_level'   => [ 'type' => 'string', 'default' => 'h2' ],
				'container_class' => [ 'type' => 'string', 'default' => '' ],
			]
		] );
	}

	/**
	 * Enceueue the block editor JS.
	 */
	public function enqueue_block_assets() {
		wp_enqueue_script(
			'sfm-block-js',
			SFM_PLUGIN_URL . 'assets/js/block.js',
			[ 'wp-blocks', 'wp-element', 'wp-editor', 'wp-components', 'wp-server-side-render' ],
			SFM_VERSION,
			true
		);

		$menus = wp_get_nav_menus();
		$menu_options = [ [ 'label' => esc_html__( '&mdash; Select Option &mdash;', 'sitemap-from-menu' ), 'value' => '0' ] ];
		foreach ( $menus as $menu ) {
			$menu_options[] = [ 'label' => esc_html( $menu->name ), 'value' => (string) $menu->term_id ];
		}

		wp_localize_script( 'sfm-block-js', 'sfm_block_data', [
			'menus' => $menu_options,
		] );
	}

	/**
	 * Render callback for the dynamic server-rendered block.
	 * 
	 * @param array $attributes User set block attributes.
	 * @return string Re-rendered HTML or cached HTML sitemap.
	 */
	public function render_block( $attributes ) {
		$renderer = new Renderer();
		return $renderer->render( $attributes );
	}
}
