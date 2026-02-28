<?php
namespace SitemapFromMenu;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Shortcode logic for rendering the HTML sitemap.
 */
class Shortcode {

	/**
	 * Initialize the shortcode.
	 */
	public function init() {
		add_shortcode( 'sitemap_from_menu', [ $this, 'render_shortcode' ] );
	}

	/**
	 * Renders the shortcode output via Renderer.
	 *
	 * @param array $atts Shortcode attributes.
	 * @return string Re-rendered HTML or cached HTML sitemap.
	 */
	public function render_shortcode( $atts ) {
		$settings = get_option( 'sfm_settings', [] );

		$enable_shortcode = isset( $settings['sfm_enable_shortcode'] ) ? $settings['sfm_enable_shortcode'] : 1;
		if ( ! $enable_shortcode ) {
			return '';
		}

		$args = shortcode_atts( [
			'menu_id'         => 0,
			'include_nested'  => true,
			'include_desc'    => false,
			'heading_text'    => '',
			'heading_level'   => 'h2',
			'container_class' => '',
		], $atts );

		$renderer = new Renderer();
		return $renderer->render( $args );
	}
}
