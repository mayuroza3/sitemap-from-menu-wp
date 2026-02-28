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
		add_action( 'template_redirect', [ $this, 'render_sitemap_preview' ] );
	}

	/**
	 * Conditionally enqueue the stylesheet and scripts if shortcode or block is used.
	 */
	public function enqueue_assets() {
		// Only enqueue on front-end endpoints or preview endpoint
		if ( ! is_singular() && ! isset( $_GET['sfm_preview'] ) ) {
			return;
		}

		$post = get_post();
		$has_shortcode = $post ? has_shortcode( $post->post_content, 'sitemap_from_menu' ) : false;
		$has_block     = $post ? has_block( 'sitemap-from-menu/block', $post ) : false;
		$is_preview    = isset( $_GET['sfm_preview'] ) && current_user_can( 'manage_options' );

		if ( $has_shortcode || $has_block || $is_preview ) {
			wp_enqueue_style(
				'sitemap-from-menu',
				SFM_PLUGIN_URL . 'assets/css/sitemap-from-menu.css',
				[],
				SFM_VERSION
			);

			$settings = get_option( 'sfm_settings', [] );
			if ( ! empty( $settings['sfm_enable_collapsible'] ) || $is_preview ) {
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

	/**
	 * Renders live preview iframe content mapping normally via theme contexts smoothly.
	 */
	public function render_sitemap_preview() {
		if ( isset( $_GET['sfm_preview'] ) && current_user_can( 'manage_options' ) ) {
			status_header( 200 );
			?>
			<!DOCTYPE html>
			<html <?php language_attributes(); ?>>
			<head>
				<meta charset="<?php bloginfo( 'charset' ); ?>">
				<meta name="viewport" content="width=device-width, initial-scale=1">
				<?php wp_head(); ?>
				<style>
					body { padding: 4% 8%; background: #fff; }
					.sfm-preview-header { border-bottom: 2px solid #eee; padding-bottom: 20px; margin-bottom: 30px; font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif; }
					.sfm-preview-header h1 { font-size: 24px; margin: 0 0 10px; color: #1d2327; }
					.sfm-preview-header p { font-size: 14px; margin: 0; color: #50575e; }
				</style>
			</head>
			<body <?php body_class(); ?>>
				<div class="sfm-preview-header">
					<h1><?php esc_html_e( 'Theme Preview Area', 'sitemap-from-menu' ); ?></h1>
					<p><?php esc_html_e( 'This iframe loads your actual front-end active theme CSS to ensure 100% rendering accuracy natively.', 'sitemap-from-menu' ); ?></p>
				</div>
				<main id="sfm-preview-container">
					<?php 
					$renderer = new Renderer();
					// Provide an empty array. Renderer intrinsically parses get_option internally mapping the initial fetch smoothly.
					echo $renderer->render( [ 'is_preview' => true ] ); 
					?>
				</main>
				<?php wp_footer(); ?>
				<script>
					window.addEventListener('message', function(event) {
						if ( event.data && event.data.sfm_html ) {
							document.getElementById('sfm-preview-container').innerHTML = event.data.sfm_html;
							// Re-init collapsible if inherently present
							if ( typeof sfm_init_collapsible === 'function' ) {
								sfm_init_collapsible();
							}
						}
					});
				</script>
			</body>
			</html>
			<?php
			exit;
		}
	}
}
