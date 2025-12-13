<?php
/*
Plugin Name: Sitemap From Menu
Plugin URI:  https://github.com/mayuroza/sitemap-from-menu
Description: Generates a simple sitemap page from selected menu and additional pages.
Version:     1.1.0
Author:      Mayur Oza
Author URI:  https://www.facebook.com/mayuroza57
License:     GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: sitefrommenu
Requires at least: 5.0
Tested up to: 6.7
Requires PHP: 7.4
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CSFM_VERSION', '1.1.0' );

// Admin Menu.
add_action( 'admin_menu', 'csfm_add_options_page' );
function csfm_add_options_page() {
	add_options_page(
		__( 'Custom Sitemap From Menu', 'sitefrommenu' ),
		__( 'Custom Sitemap From Menu', 'sitefrommenu' ),
		'manage_options',
		'csfm-options-page',
		'csfm_display_options_page'
	);
}

function csfm_display_options_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}
	?>
	<div class="wrap">
		<h2><?php esc_html_e( 'Custom Sitemap From Menu Options', 'sitefrommenu' ); ?></h2>
		<form method="post" action="options.php">
			<?php
			settings_fields( 'csfm-settings' );
			do_settings_sections( 'csfm-options-page' );
			submit_button();
			?>
		</form>
	</div>
	<?php
}

// Settings Init.
add_action( 'admin_init', 'csfm_admin_settings_init' );
function csfm_admin_settings_init() {

	// Menu Select Section.
	add_settings_section(
		'csfm_settings_section_menu',
		__( 'Menu Selection', 'sitefrommenu' ),
		function () {
			echo '<p>' . esc_html__( 'Select a menu for generating sitemap.', 'sitefrommenu' ) . '</p>';
		},
		'csfm-options-page'
	);

	add_settings_field(
		'csfm_menu_select',
		__( 'Select Menu', 'sitefrommenu' ),
		'csfm_menu_dropdown_render',
		'csfm-options-page',
		'csfm_settings_section_menu'
	);

	// Store menu ID, sanitize as integer.
	register_setting( 'csfm-settings', 'csfm_menu_select', 'absint' );

	// Extra Page IDs Section.
	add_settings_section(
		'csfm_settings_section_pages',
		__( 'Extra Pages', 'sitefrommenu' ),
		function () {
			echo '<p>' . esc_html__( 'Comma-separated Page IDs to include in sitemap.', 'sitefrommenu' ) . '</p>';
		},
		'csfm-options-page'
	);

	add_settings_field(
		'csfm_extra_pages',
		__( 'Extra Page IDs', 'sitefrommenu' ),
		'csfm_extra_pages_render',
		'csfm-options-page',
		'csfm_settings_section_pages'
	);

	register_setting( 'csfm-settings', 'csfm_extra_pages', 'sanitize_text_field' );
}

function csfm_menu_dropdown_render() {
	$selected_menu = (int) get_option( 'csfm_menu_select', 0 );

	$menus = get_terms(
		array(
			'taxonomy'   => 'nav_menu',
			'hide_empty' => false,
		)
	);

	?>
	<select name="csfm_menu_select" id="csfm_menu_select">
		<option value="0"><?php esc_html_e( '-- Select Menu --', 'sitefrommenu' ); ?></option>
		<?php
		if ( ! is_wp_error( $menus ) && ! empty( $menus ) ) :
			foreach ( $menus as $menu ) :
				?>
				<option value="<?php echo esc_attr( $menu->term_id ); ?>" <?php selected( $selected_menu, $menu->term_id ); ?>>
					<?php echo esc_html( $menu->name ); ?>
				</option>
				<?php
			endforeach;
		endif;
		?>
	</select>
	<?php
}

function csfm_extra_pages_render() {
	$extra_pages = get_option( 'csfm_extra_pages', '' );
	?>
	<input
		type="text"
		name="csfm_extra_pages"
		id="csfm_extra_pages"
		value="<?php echo esc_attr( $extra_pages ); ?>"
		class="regular-text"
	/>
	<?php
}

// Shortcode Output.
function csfm_render_sitemap( $atts = array() ) {

	// Allow override via shortcode attributes.
	$atts = shortcode_atts(
		array(
			'menu'  => '', // menu ID, slug, or name.
			'pages' => '', // comma-separated page IDs.
		),
		$atts,
		'csfm'
	);

	$menu_id   = 0;
	$extra_ids = array();

	// Determine menu ID.
	if ( ! empty( $atts['menu'] ) ) {
		$menu_obj = wp_get_nav_menu_object( $atts['menu'] );
		if ( $menu_obj && ! is_wp_error( $menu_obj ) ) {
			$menu_id = (int) $menu_obj->term_id;
		}
	} else {
		$menu_id = (int) get_option( 'csfm_menu_select', 0 );
	}

	// Determine extra pages.
	$extra_ids_raw = ! empty( $atts['pages'] ) ? $atts['pages'] : get_option( 'csfm_extra_pages', '' );

	if ( ! empty( $extra_ids_raw ) ) {
		foreach ( explode( ',', $extra_ids_raw ) as $page_id ) {
			$page_id = absint( trim( $page_id ) );
			if ( $page_id > 0 ) {
				$extra_ids[] = $page_id;
			}
		}
	}

	$output = '<ul class="csfm-sitemap">';

	// Menu items.
	if ( $menu_id > 0 ) {
		$items = wp_get_nav_menu_items( $menu_id );
		if ( ! empty( $items ) && ! is_wp_error( $items ) ) {
			foreach ( $items as $item ) {
				$url   = ! empty( $item->url ) ? $item->url : '';
				$title = ! empty( $item->title ) ? $item->title : '';

				if ( $url && $title ) {
					$output .= sprintf(
						'<li><a href="%1$s">%2$s</a></li>',
						esc_url( $url ),
						esc_html( $title )
					);
				}
			}
		}
	}

	// Extra pages.
	if ( ! empty( $extra_ids ) ) {
		foreach ( $extra_ids as $page_id ) {
			if ( 'publish' === get_post_status( $page_id ) ) {
				$output .= sprintf(
					'<li><a href="%1$s">%2$s</a></li>',
					esc_url( get_permalink( $page_id ) ),
					esc_html( get_the_title( $page_id ) )
				);
			}
		}
	}

	$output .= '</ul>';

	return $output;
}
add_shortcode( 'csfm', 'csfm_render_sitemap' );

// Plugin Action Links.
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'csfm_plugin_action_links' );
function csfm_plugin_action_links( $links ) {
	$settings_url = admin_url( 'options-general.php?page=csfm-options-page' );

	$settings_link = sprintf(
		'<a href="%1$s">%2$s</a>',
		esc_url( $settings_url ),
		esc_html__( 'Settings', 'sitefrommenu' )
	);

	$support_link = sprintf(
		'<a href="%1$s">%2$s</a>',
		esc_url( 'mailto:mayuroza3@gmail.com' ),
		esc_html__( 'Support', 'sitefrommenu' )
	);

	array_unshift( $links, $settings_link, $support_link );

	return $links;
}
