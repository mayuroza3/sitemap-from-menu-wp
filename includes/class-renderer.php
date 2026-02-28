<?php
namespace SitemapFromMenu;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handles core HTML generation, caching, exclusions, a11y, and parsing.
 */
class Renderer {

	/**
	 * General render entrypoint for block and shortcode.
	 *
	 * @param array $args Attributes provided by shortcode or block controls.
	 * @return string HTML sitemap.
	 */
	public function render( $args = [] ) {
		$settings = get_option( 'sfm_settings', [] );

		$menu_id = isset( $args['menu_id'] ) && '0' !== (string) $args['menu_id'] 
			? absint( $args['menu_id'] ) 
			: ( isset( $settings['sfm_menu_id'] ) ? absint( $settings['sfm_menu_id'] ) : 0 );

		if ( ! $menu_id ) {
			return '<p>' . esc_html__( 'Sitemap From Menu: No menu selected.', 'sitemap-from-menu' ) . '</p>';
		}

		$transient_key = 'sfm_shtml_' . md5( serialize( $args ) . serialize( $settings ) );
		$cached_html   = get_transient( $transient_key );
		if ( false !== $cached_html ) {
			return $cached_html;
		}

		$menu_items = wp_get_nav_menu_items( $menu_id );
		if ( empty( $menu_items ) ) {
			return '<p>' . esc_html__( 'Sitemap From Menu: Selected menu is empty.', 'sitemap-from-menu' ) . '</p>';
		}

		// Map exclusions
		$exc_class = ! empty( $settings['sfm_exclude_class'] ) ? array_map( 'trim', explode( ',', $settings['sfm_exclude_class'] ) ) : [];
		$exc_url   = ! empty( $settings['sfm_exclude_url'] ) ? array_map( 'trim', explode( ',', $settings['sfm_exclude_url'] ) ) : [];
		$exc_id    = ! empty( $settings['sfm_exclude_id'] ) ? array_map( 'absint', explode( ',', $settings['sfm_exclude_id'] ) ) : [];

		$filtered_items = [];
		foreach ( $menu_items as $item ) {

			// ID Filter
			if ( in_array( (int) $item->ID, $exc_id, true ) ) continue;
			if ( in_array( (int) $item->object_id, $exc_id, true ) ) continue;

			// CSS Class Filter
			$has_class = false;
			if ( ! empty( $item->classes ) && is_array( $item->classes ) ) {
				foreach ( $exc_class as $ec ) {
					if ( in_array( $ec, $item->classes, true ) ) {
						$has_class = true;
						break;
					}
				}
			}
			if ( $has_class ) continue;

			// URL Filter
			$has_url = false;
			foreach ( $exc_url as $eu ) {
				if ( ! empty( $item->url ) && false !== strpos( $item->url, $eu ) ) {
					$has_url = true;
					break;
				}
			}
			if ( $has_url ) continue;

			$filtered_items[] = $item;
		}

		// Sort items if needed before building tree.
		// Alphabetical breaks menu depth order natively if not careful.
		$sort_method = isset( $settings['sfm_sorting'] ) ? $settings['sfm_sorting'] : 'default';

		$menu_tree = $this->build_menu_tree( $filtered_items, $sort_method );

		$include_nested = isset( $args['include_nested'] ) ? filter_var( $args['include_nested'], FILTER_VALIDATE_BOOLEAN ) : ( isset( $settings['sfm_include_nested'] ) ? $settings['sfm_include_nested'] : 1 );
		$include_desc   = isset( $args['include_desc'] ) ? filter_var( $args['include_desc'], FILTER_VALIDATE_BOOLEAN ) : ( isset( $settings['sfm_include_desc'] ) ? $settings['sfm_include_desc'] : 0 );
		$append_pages   = isset( $settings['sfm_append_pages'] ) ? $settings['sfm_append_pages'] : '';
		
		$a11y     = ! empty( $settings['sfm_enable_a11y'] );
		$schema   = ! empty( $settings['sfm_enable_schema'] );
		$collapse = ! empty( $settings['sfm_enable_collapsible'] );
		$cols     = isset( $settings['sfm_columns'] ) ? $settings['sfm_columns'] : '1';

		$container_cls  = ! empty( $settings['sfm_container_class'] ) ? $settings['sfm_container_class'] : 'sfm-sitemap';
		if ( ! empty( $args['container_class'] ) ) {
			$container_cls .= ' ' . sanitize_html_class( $args['container_class'] );
		}
		if ( $collapse ) {
			$container_cls .= ' sfm-collapsible-tree';
		}
		if ( '1' !== $cols && in_array( $cols, [ '2', '3', 'auto' ], true ) ) {
			$container_cls .= ' sfm-cols-' . $cols;
		}

		ob_start();

		// Optional Header
		$heading_text = ! empty( $args['heading_text'] ) ? $args['heading_text'] : '';
		if ( ! empty( $heading_text ) ) {
			$default_level = isset( $args['heading_level'] ) ? $args['heading_level'] : 'h2';
			$h_tag = in_array( $default_level, [ 'h2', 'h3', 'h4', 'h5', 'h6' ], true ) ? $default_level : 'h2';
			echo '<' . esc_attr( $h_tag ) . ' class="sfm-heading">' . esc_html( $heading_text ) . '</' . esc_attr( $h_tag ) . '>';
		}

		if ( $a11y ) {
			echo '<nav class="' . esc_attr( trim( $container_cls ) ) . '" aria-label="' . esc_attr__( 'HTML Sitemap', 'sitemap-from-menu' ) . '">';
		} else {
			echo '<div class="' . esc_attr( trim( $container_cls ) ) . '">';
		}

		$ul_attr = $schema ? ' itemscope itemtype="https://schema.org/SiteNavigationElement"' : '';
		echo '<ul' . $ul_attr . '>';

		$this->render_menu_tree( $menu_tree, $include_nested, $include_desc, $schema, $collapse );

		// Appended Pages logic
		if ( ! empty( $append_pages ) ) {
			$page_ids = explode( ',', $append_pages );
			foreach ( $page_ids as $page_id ) {
				$page_id = absint( trim( $page_id ) );
				if ( $page_id > 0 ) {
					$post = get_post( $page_id );
					if ( $post && 'publish' === $post->post_status ) {
						echo '<li><a';
						if ( $schema ) echo ' itemprop="url"';
						echo ' href="' . esc_url( get_permalink( $post->ID ) ) . '">';
						
						if ( $schema ) echo '<span itemprop="name">';
						echo esc_html( get_the_title( $post->ID ) );
						if ( $schema ) echo '</span>';
						
						echo '</a></li>';
					}
				}
			}
		}

		echo '</ul>';

		if ( $a11y ) {
			echo '</nav>';
		} else {
			echo '</div>';
		}

		$html = ob_get_clean();

		set_transient( $transient_key, $html, 12 * HOUR_IN_SECONDS );

		return $html;
	}

	/**
	 * Build tree array structurally.
	 */
	private function build_menu_tree( $menu_items, $sort_method ) {
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

		if ( 'alphabetical' === $sort_method ) {
			$this->sort_tree_recursive( $tree );
		}

		return $tree;
	}

	/**
	 * Recursively sort items alphabetically by title
	 */
	private function sort_tree_recursive( &$tree ) {
		usort( $tree, function( $a, $b ) {
			return strcmp( $a->title, $b->title );
		} );
		foreach ( $tree as &$item ) {
			if ( ! empty( $item->sfm_children ) ) {
				$this->sort_tree_recursive( $item->sfm_children );
			}
		}
	}

	/**
	 * Render the tree list HTML gracefully.
	 */
	private function render_menu_tree( $menu_items, $include_nested, $include_desc, $schema, $collapse ) {
		foreach ( $menu_items as $item ) {
			$url   = ! empty( $item->url ) ? $item->url : '#';
			$title = ! empty( $item->title ) ? $item->title : '';
			$desc  = ! empty( $item->description ) ? $item->description : '';

			$has_children = $include_nested && ! empty( $item->sfm_children );

			echo '<li' . ( $has_children ? ' class="sfm-has-children"' : '' ) . '>';
			
			$a_attr = '';
			if ( $schema ) $a_attr .= ' itemprop="url"';
			// Append target blank if exist
			if ( ! empty( $item->target ) ) {
				$a_attr .= ' target="' . esc_attr( $item->target ) . '" rel="noopener"';
			}

			echo '<a href="' . esc_url( $url ) . '"' . $a_attr . '>';
			if ( $schema ) echo '<span itemprop="name">';
			echo esc_html( $title );
			if ( $schema ) echo '</span>';
			echo '</a>';

			if ( $has_children && $collapse ) {
				echo '<button class="sfm-toggle-btn" aria-expanded="false" aria-label="' . esc_attr__( 'Expand', 'sitemap-from-menu' ) . '">+</button>';
			}

			if ( $include_desc && ! empty( $desc ) ) {
				echo '<span class="sfm-desc">' . esc_html( $desc ) . '</span>';
			}

			if ( $has_children ) {
				echo '<ul>';
				$this->render_menu_tree( $item->sfm_children, $include_nested, $include_desc, $schema, $collapse );
				echo '</ul>';
			}

			echo '</li>';
		}
	}
}
