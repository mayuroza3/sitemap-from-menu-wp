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
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_styles' ] );
	}

	/**
	 * Conditionally enqueue the stylesheet if the shortcode is used.
	 */
	public function enqueue_styles() {
		if ( is_singular() ) {
			$post = get_post();
			if ( $post && has_shortcode( $post->post_content, 'sitemap_from_menu' ) ) {
				wp_enqueue_style(
					'sitemap-from-menu',
					SFM_PLUGIN_URL . 'assets/css/sitemap-from-menu.css',
					[],
					SFM_VERSION
				);
			}
		}
	}
}
