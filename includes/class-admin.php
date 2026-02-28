<?php
namespace SitemapFromMenu;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Admin logic and settings page.
 */
class Admin {

	/**
	 * Initialize the admin actions.
	 */
	public function init() {
		add_action( 'admin_menu', [ $this, 'add_settings_page' ] );
		add_action( 'admin_init', [ $this, 'register_settings' ] );
		add_action( 'wp_update_nav_menu', [ $this, 'clear_sitemap_transient' ] );
	}

	/**
	 * Adds the settings page under 'Settings'.
	 */
	public function add_settings_page() {
		add_options_page(
			esc_html__( 'Sitemap From Menu Settings', 'sitemap-from-menu' ),
			esc_html__( 'Sitemap From Menu', 'sitemap-from-menu' ),
			'manage_options',
			'sitemap-from-menu',
			[ $this, 'render_settings_page' ]
		);
	}

	/**
	 * Registers the settings, sections, and fields.
	 */
	public function register_settings() {
		register_setting(
			'sfm_settings_group',
			'sfm_settings',
			[
				'sanitize_callback' => [ $this, 'sanitize_settings' ],
				'default'           => [],
			]
		);

		add_settings_section(
			'sfm_general_section',
			esc_html__( 'General Settings', 'sitemap-from-menu' ),
			[ $this, 'render_general_section' ],
			'sitemap-from-menu'
		);

		add_settings_field(
			'sfm_menu_id',
			esc_html__( 'Select Menu', 'sitemap-from-menu' ),
			[ $this, 'render_menu_dropdown' ],
			'sitemap-from-menu',
			'sfm_general_section'
		);

		add_settings_field(
			'sfm_include_nested',
			esc_html__( 'Include Nested Items', 'sitemap-from-menu' ),
			[ $this, 'render_checkbox_field' ],
			'sitemap-from-menu',
			'sfm_general_section',
			[
				'key'   => 'sfm_include_nested',
				'label' => esc_html__( 'Include multi-level children items', 'sitemap-from-menu' ),
				'default' => 1,
			]
		);

		add_settings_field(
			'sfm_include_desc',
			esc_html__( 'Include Descriptions', 'sitemap-from-menu' ),
			[ $this, 'render_checkbox_field' ],
			'sitemap-from-menu',
			'sfm_general_section',
			[
				'key'   => 'sfm_include_desc',
				'label' => esc_html__( 'Show menu item descriptions under links', 'sitemap-from-menu' ),
				'default' => 0,
			]
		);

		add_settings_field(
			'sfm_append_pages',
			esc_html__( 'Append Extra Pages', 'sitemap-from-menu' ),
			[ $this, 'render_text_field' ],
			'sitemap-from-menu',
			'sfm_general_section',
			[
				'key'         => 'sfm_append_pages',
				'description' => esc_html__( 'Comma-separated post/page IDs to append at the end of the sitemap.', 'sitemap-from-menu' ),
			]
		);

		add_settings_field(
			'sfm_container_class',
			esc_html__( 'Wrapper Container Class', 'sitemap-from-menu' ),
			[ $this, 'render_text_field' ],
			'sitemap-from-menu',
			'sfm_general_section',
			[
				'key'     => 'sfm_container_class',
				'default' => 'sfm-sitemap',
			]
		);

		add_settings_field(
			'sfm_enable_shortcode',
			esc_html__( 'Enable Shortcode', 'sitemap-from-menu' ),
			[ $this, 'render_checkbox_field' ],
			'sitemap-from-menu',
			'sfm_general_section',
			[
				'key'     => 'sfm_enable_shortcode',
				'label'   => esc_html__( 'Enable the [sitemap_from_menu] shortcode', 'sitemap-from-menu' ),
				'default' => 1,
			]
		);
	}

	/**
	 * Sanitizes plugin settings.
	 *
	 * @param array $input Raw input options.
	 * @return array Sanitized options.
	 */
	public function sanitize_settings( $input ) {
		$sanitized = [];

		if ( isset( $input['sfm_menu_id'] ) ) {
			$sanitized['sfm_menu_id'] = absint( $input['sfm_menu_id'] );
		}

		$sanitized['sfm_include_nested'] = ! empty( $input['sfm_include_nested'] ) ? 1 : 0;
		$sanitized['sfm_include_desc']   = ! empty( $input['sfm_include_desc'] ) ? 1 : 0;
		
		if ( isset( $input['sfm_append_pages'] ) ) {
			$ids = explode( ',', sanitize_text_field( $input['sfm_append_pages'] ) );
			$ids = array_map( 'absint', $ids );
			$ids = array_filter( $ids );
			$sanitized['sfm_append_pages'] = implode( ',', $ids );
		}
		
		if ( isset( $input['sfm_container_class'] ) ) {
			$sanitized['sfm_container_class'] = sanitize_text_field( $input['sfm_container_class'] );
		}

		$sanitized['sfm_enable_shortcode'] = ! empty( $input['sfm_enable_shortcode'] ) ? 1 : 0;

		// Clear cache upon setting save
		$this->clear_sitemap_transient();

		return $sanitized;
	}

	/**
	 * Render for the section above the fields.
	 */
	public function render_general_section() {
		echo '<p>' . esc_html__( 'Configure how the sitemap is generated from your selected menu.', 'sitemap-from-menu' ) . '</p>';
	}

	/**
	 * Renders dropdown to select navigation menus.
	 */
	public function render_menu_dropdown() {
		$settings = get_option( 'sfm_settings', [] );
		$current  = isset( $settings['sfm_menu_id'] ) ? absint( $settings['sfm_menu_id'] ) : 0;
		$menus    = wp_get_nav_menus();
		?>
		<select name="sfm_settings[sfm_menu_id]" id="sfm_menu_id">
			<option value="0"><?php esc_html_e( '&mdash; Select a Menu &mdash;', 'sitemap-from-menu' ); ?></option>
			<?php foreach ( $menus as $menu ) : ?>
				<option value="<?php echo esc_attr( $menu->term_id ); ?>" <?php selected( $current, $menu->term_id ); ?>>
					<?php echo esc_html( $menu->name ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Render checkbox field.
	 *
	 * @param array $args Arguments provided in add_settings_field.
	 */
	public function render_checkbox_field( $args ) {
		$settings = get_option( 'sfm_settings', [] );
		$key      = sanitize_key( $args['key'] );
		$default  = isset( $args['default'] ) ? $args['default'] : 0;
		$current  = isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
		?>
		<label>
			<input type="checkbox" name="sfm_settings[<?php echo esc_attr( $key ); ?>]" value="1" <?php checked( 1, $current ); ?> />
			<?php if ( isset( $args['label'] ) ) : ?>
				<?php echo esc_html( $args['label'] ); ?>
			<?php endif; ?>
		</label>
		<?php
	}

	/**
	 * Render text field.
	 *
	 * @param array $args Arguments provided in add_settings_field.
	 */
	public function render_text_field( $args ) {
		$settings = get_option( 'sfm_settings', [] );
		$key      = sanitize_key( $args['key'] );
		$default  = isset( $args['default'] ) ? $args['default'] : '';
		$current  = isset( $settings[ $key ] ) ? $settings[ $key ] : $default;
		?>
		<input type="text" name="sfm_settings[<?php echo esc_attr( $key ); ?>]" value="<?php echo esc_attr( $current ); ?>" class="regular-text" />
		<?php if ( isset( $args['description'] ) ) : ?>
			<p class="description"><?php echo esc_html( $args['description'] ); ?></p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Output Settings page HTML.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Sitemap From Menu Settings', 'sitemap-from-menu' ); ?></h1>
			<form action="options.php" method="POST">
				<?php settings_fields( 'sfm_settings_group' ); ?>
				<?php do_settings_sections( 'sitemap-from-menu' ); ?>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}

	/**
	 * Clear the sitemap transient cache.
	 * 
	 * @param int $menu_id The menu ID.
	 */
	public function clear_sitemap_transient( $menu_id = 0 ) {
		delete_transient( 'sfm_sitemap_html' );
	}
}
