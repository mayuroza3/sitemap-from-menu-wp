<?php
namespace SitemapFromMenu;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles front-end enqueueing and output requirements.
 */
class Frontend {

	/**
	 * Initialize front-end actions.
	 */
	public function init() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
	}

	/**
	 * Conditionally enqueue the stylesheet and scripts if shortcode or block is used.
	 */
	public function enqueue_assets() {
		if ( ! is_singular() ) {
			return;
		}

		$post = get_post();
		if ( ! $post ) {
			return;
		}

		$has_shortcode = has_shortcode( $post->post_content, 'sitemap_from_menu' );
		$has_block     = has_block( 'sitemap-from-menu/block', $post );

		if ( $has_shortcode || $has_block ) {
			wp_enqueue_style(
				'sitemap-from-menu',
				SFM_PLUGIN_URL . 'assets/css/sitemap-from-menu.css',
				[],
				SFM_VERSION
			);

			$settings = get_option( 'sfm_settings', [] );
			if ( ! empty( $settings['sfm_enable_collapsible'] ) ) {
				wp_enqueue_script(
					'sfm-js',
					SFM_PLUGIN_URL . 'assets/js/sitemap-from-menu.js',
					[],
					SFM_VERSION,
					true
				);
			}
		}
	}
}
