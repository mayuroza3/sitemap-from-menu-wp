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
	 * Renders the shortcode output.
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

		$menu_id = isset( $settings['sfm_menu_id'] ) ? absint( $settings['sfm_menu_id'] ) : 0;
		if ( ! $menu_id ) {
			return '<p>' . esc_html__( 'Sitemap From Menu: No menu selected in settings.', 'sitemap-from-menu' ) . '</p>';
		}

		$transient_key = 'sfm_sitemap_html';
		$cached_html   = get_transient( $transient_key );
		if ( false !== $cached_html ) {
			return $cached_html;
		}

		$menu_items = wp_get_nav_menu_items( $menu_id );
		if ( empty( $menu_items ) ) {
			return '<p>' . esc_html__( 'Sitemap From Menu: Selected menu is empty.', 'sitemap-from-menu' ) . '</p>';
		}

		$include_nested = isset( $settings['sfm_include_nested'] ) ? $settings['sfm_include_nested'] : 1;
		$include_desc   = isset( $settings['sfm_include_desc'] ) ? $settings['sfm_include_desc'] : 0;
		$append_pages   = isset( $settings['sfm_append_pages'] ) ? $settings['sfm_append_pages'] : '';
		$container_cls  = ! empty( $settings['sfm_container_class'] ) ? $settings['sfm_container_class'] : 'sfm-sitemap';

		$menu_tree = $this->build_menu_tree( $menu_items );

		ob_start();

		echo '<div class="' . esc_attr( $container_cls ) . '">';
		echo '<ul>';
		$this->render_menu_tree( $menu_tree, $include_nested, $include_desc );
		
		if ( ! empty( $append_pages ) ) {
			$page_ids = explode( ',', $append_pages );
			foreach ( $page_ids as $page_id ) {
				$page_id = absint( trim( $page_id ) );
				if ( $page_id > 0 ) {
					$post = get_post( $page_id );
					if ( $post && 'publish' === $post->post_status ) {
						echo '<li><a href="' . esc_url( get_permalink( $post->ID ) ) . '">' . esc_html( get_the_title( $post->ID ) ) . '</a></li>';
					}
				}
			}
		}

		echo '</ul>';
		echo '</div>';

		$html = ob_get_clean();

		set_transient( $transient_key, $html, 12 * HOUR_IN_SECONDS );

		return $html;
	}

	/**
	 * Build a tree array of menu items.
	 * 
	 * @param array $menu_items Array of menu items objects natively from WordPress.
	 * @return array Array of menu items organized by parent-child tree structure.
	 */
	private function build_menu_tree( $menu_items ) {
		$tree = [];
		$items_by_id = [];

		foreach ( $menu_items as $item ) {
			$item->sfm_children = [];
			$items_by_id[ $item->ID ] = $item;
		}

		foreach ( $menu_items as $item ) {
			if ( $item->menu_item_parent && isset( $items_by_id[ $item->menu_item_parent ] ) ) {
				$items_by_id[ $item->menu_item_parent ]->sfm_children[] = $item;
			} else {
				$tree[] = $item;
			}
		}

		return $tree;
	}

	/**
	 * Recursively render menu items as an unordered HTML list.
	 * 
	 * @param array $menu_items List of root menu elements to parse.
	 * @param bool  $include_nested Settings configuration to check.
	 * @param bool  $include_desc Settings configuration to check.
	 */
	private function render_menu_tree( $menu_items, $include_nested, $include_desc ) {
		foreach ( $menu_items as $item ) {
			$url   = ! empty( $item->url ) ? $item->url : '#';
			$title = ! empty( $item->title ) ? $item->title : '';
			$desc  = ! empty( $item->description ) ? $item->description : '';

			echo '<li>';
			echo '<a href="' . esc_url( $url ) . '">' . esc_html( $title ) . '</a>';

			if ( $include_desc && ! empty( $desc ) ) {
				echo '<span class="sfm-desc">' . esc_html( $desc ) . '</span>';
			}

			if ( $include_nested && ! empty( $item->sfm_children ) ) {
				echo '<ul>';
				$this->render_menu_tree( $item->sfm_children, $include_nested, $include_desc );
				echo '</ul>';
			}

			echo '</li>';
		}
	}
}
