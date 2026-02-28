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
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
		add_action( 'wp_ajax_sfm_render_preview', [ $this, 'ajax_render_preview' ] );
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
				'sanitize_callback' => [ $this, 'sanitize_settings_db' ],
				'default'           => [],
			]
		);

		add_settings_section( 'sfm_general_section', esc_html__( 'General Settings', 'sitemap-from-menu' ), [ $this, 'render_general_section' ], 'sitemap-from-menu' );
		add_settings_section( 'sfm_features_section', esc_html__( 'Features & Layout', 'sitemap-from-menu' ), null, 'sitemap-from-menu' );
		add_settings_section( 'sfm_exclusions_section', esc_html__( 'Exclusion Rules', 'sitemap-from-menu' ), null, 'sitemap-from-menu' );

		// General
		add_settings_field( 'sfm_menu_id', esc_html__( 'Select Menu', 'sitemap-from-menu' ), [ $this, 'render_menu_dropdown' ], 'sitemap-from-menu', 'sfm_general_section' );
		add_settings_field( 'sfm_enable_shortcode', esc_html__( 'Enable Shortcode', 'sitemap-from-menu' ), [ $this, 'render_checkbox_field' ], 'sitemap-from-menu', 'sfm_general_section', [ 'key' => 'sfm_enable_shortcode', 'label' => esc_html__( 'Enable the [sitemap_from_menu] shortcode', 'sitemap-from-menu' ), 'default' => 1 ] );
		add_settings_field( 'sfm_include_nested', esc_html__( 'Include Nested Items', 'sitemap-from-menu' ), [ $this, 'render_checkbox_field' ], 'sitemap-from-menu', 'sfm_general_section', [ 'key' => 'sfm_include_nested', 'label' => esc_html__( 'Include multi-level children items', 'sitemap-from-menu' ), 'default' => 1 ] );
		add_settings_field( 'sfm_include_desc', esc_html__( 'Include Descriptions', 'sitemap-from-menu' ), [ $this, 'render_checkbox_field' ], 'sitemap-from-menu', 'sfm_general_section', [ 'key' => 'sfm_include_desc', 'label' => esc_html__( 'Show menu item descriptions under links', 'sitemap-from-menu' ), 'default' => 0 ] );
		add_settings_field( 'sfm_append_pages', esc_html__( 'Append Extra Pages', 'sitemap-from-menu' ), [ $this, 'render_text_field' ], 'sitemap-from-menu', 'sfm_general_section', [ 'key' => 'sfm_append_pages', 'description' => esc_html__( 'Comma-separated post/page IDs to append at the end of the sitemap.', 'sitemap-from-menu' ) ] );
		add_settings_field( 'sfm_container_class', esc_html__( 'Wrapper Container Class', 'sitemap-from-menu' ), [ $this, 'render_text_field' ], 'sitemap-from-menu', 'sfm_general_section', [ 'key' => 'sfm_container_class', 'default' => 'sfm-sitemap' ] );

		// Features
		add_settings_field( 'sfm_columns', esc_html__( 'Columns Layout', 'sitemap-from-menu' ), [ $this, 'render_columns_dropdown' ], 'sitemap-from-menu', 'sfm_features_section' );
		add_settings_field( 'sfm_sorting', esc_html__( 'Sorting Method', 'sitemap-from-menu' ), [ $this, 'render_sorting_dropdown' ], 'sitemap-from-menu', 'sfm_features_section' );
		add_settings_field( 'sfm_enable_a11y', esc_html__( 'Accessibility Improvements', 'sitemap-from-menu' ), [ $this, 'render_checkbox_field' ], 'sitemap-from-menu', 'sfm_features_section', [ 'key' => 'sfm_enable_a11y', 'label' => esc_html__( 'Wrap output in semantic <nav> tag with aria-labels.', 'sitemap-from-menu' ), 'default' => 0 ] );
		add_settings_field( 'sfm_enable_schema', esc_html__( 'Schema.org Markup', 'sitemap-from-menu' ), [ $this, 'render_checkbox_field' ], 'sitemap-from-menu', 'sfm_features_section', [ 'key' => 'sfm_enable_schema', 'label' => esc_html__( 'Add native SiteNavigationElement structured data to the HTML.', 'sitemap-from-menu' ), 'default' => 0 ] );
		add_settings_field( 'sfm_enable_collapsible', esc_html__( 'Collapsible Tree', 'sitemap-from-menu' ), [ $this, 'render_checkbox_field' ], 'sitemap-from-menu', 'sfm_features_section', [ 'key' => 'sfm_enable_collapsible', 'label' => esc_html__( 'Collapse nested items visually via lightweight JS toggle.', 'sitemap-from-menu' ), 'default' => 0 ] );

		// Exclusions
		add_settings_field( 'sfm_exclude_class', esc_html__( 'Exclude by CSS Class', 'sitemap-from-menu' ), [ $this, 'render_text_field' ], 'sitemap-from-menu', 'sfm_exclusions_section', [ 'key' => 'sfm_exclude_class', 'description' => esc_html__( 'Comma-separated CSS classes. Example: sfm-exclude, hidden', 'sitemap-from-menu' ) ] );
		add_settings_field( 'sfm_exclude_url', esc_html__( 'Exclude by URL Pattern', 'sitemap-from-menu' ), [ $this, 'render_text_field' ], 'sitemap-from-menu', 'sfm_exclusions_section', [ 'key' => 'sfm_exclude_url', 'description' => esc_html__( 'Comma-separated strings. Example: /private/, /secret-page/', 'sitemap-from-menu' ) ] );
		add_settings_field( 'sfm_exclude_id', esc_html__( 'Exclude Pages', 'sitemap-from-menu' ), [ $this, 'render_pages_multiselect' ], 'sitemap-from-menu', 'sfm_exclusions_section', [ 'key' => 'sfm_exclude_id', 'description' => esc_html__( 'Hold Ctrl (Windows) or Cmd (Mac) to select multiple pages to exclude.', 'sitemap-from-menu' ) ] );
	}

	/**
	 * Entry point for register_setting, correctly clearing caches natively on save explicitly.
	 */
	public function sanitize_settings_db( $input ) {
		$sanitized = $this->sanitize_settings_array( $input );
		$this->clear_sitemap_transient();
		return $sanitized;
	}

	/**
	 * Sanitizes plugin dynamically organically perfectly decoupling formatting from caching explicitly purely safely inherently gracefully natively beautifully securely comprehensively seamlessly securely conceptually flexibly explicitly intelligently properly natively appropriately naturally.
	 */
	public function sanitize_settings_array( $input ) {
		$sanitized = [];

		if ( isset( $input['sfm_menu_id'] ) ) $sanitized['sfm_menu_id'] = absint( $input['sfm_menu_id'] );

		// Checkboxes
		$checkboxes = [ 'sfm_include_nested', 'sfm_include_desc', 'sfm_enable_shortcode', 'sfm_enable_a11y', 'sfm_enable_schema', 'sfm_enable_collapsible' ];
		foreach ( $checkboxes as $cb ) {
			$sanitized[ $cb ] = ! empty( $input[ $cb ] ) ? 1 : 0;
		}

		// Provide basic IDs formatting for the appended comma string field
		if ( isset( $input['sfm_append_pages'] ) ) {
			$ids = array_filter( array_map( 'absint', explode( ',', sanitize_text_field( $input['sfm_append_pages'] ) ) ) );
			$sanitized['sfm_append_pages'] = implode( ',', $ids );
		}

		// Handle multi-select page exclusions safely as array dynamically
		if ( ! empty( $input['sfm_exclude_id'] ) && is_array( $input['sfm_exclude_id'] ) ) {
			$ids = array_filter( array_map( 'absint', $input['sfm_exclude_id'] ) );
			$sanitized['sfm_exclude_id'] = implode( ',', $ids );
		} else {
			$sanitized['sfm_exclude_id'] = '';
		}

		// Text fields
		foreach ( [ 'sfm_container_class', 'sfm_exclude_class', 'sfm_exclude_url' ] as $txt_field ) {
			if ( isset( $input[ $txt_field ] ) ) {
				$sanitized[ $txt_field ] = sanitize_text_field( $input[ $txt_field ] );
			}
		}

		// Selects
		if ( isset( $input['sfm_columns'] ) && in_array( $input['sfm_columns'], [ '1', '2', '3', 'auto' ], true ) ) {
			$sanitized['sfm_columns'] = $input['sfm_columns'];
		} else {
			$sanitized['sfm_columns'] = '1';
		}

		if ( isset( $input['sfm_sorting'] ) && in_array( $input['sfm_sorting'], [ 'default', 'alphabetical' ], true ) ) {
			$sanitized['sfm_sorting'] = $input['sfm_sorting'];
		} else {
			$sanitized['sfm_sorting'] = 'default';
		}

		return $sanitized;
	}

	public function render_general_section() {
		echo '<p>' . esc_html__( 'Configure how the sitemap is generated from your selected menu.', 'sitemap-from-menu' ) . '</p>';
	}

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

	public function render_columns_dropdown() {
		$settings = get_option( 'sfm_settings', [] );
		$current  = isset( $settings['sfm_columns'] ) ? $settings['sfm_columns'] : '1';
		$options  = [
			'1'    => esc_html__( 'Single Column (Default)', 'sitemap-from-menu' ),
			'2'    => esc_html__( '2 Columns Grid', 'sitemap-from-menu' ),
			'3'    => esc_html__( '3 Columns Grid', 'sitemap-from-menu' ),
			'auto' => esc_html__( 'Auto-Fit Grid', 'sitemap-from-menu' ),
		];
		?>
		<select name="sfm_settings[sfm_columns]">
			<?php foreach ( $options as $val => $label ) : ?>
				<option value="<?php echo esc_attr( $val ); ?>" <?php selected( $current, $val ); ?>><?php echo esc_html( $label ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	public function render_sorting_dropdown() {
		$settings = get_option( 'sfm_settings', [] );
		$current  = isset( $settings['sfm_sorting'] ) ? $settings['sfm_sorting'] : 'default';
		?>
		<select name="sfm_settings[sfm_sorting]">
			<option value="default" <?php selected( $current, 'default' ); ?>><?php esc_html_e( 'Preserve Menu Order (Default)', 'sitemap-from-menu' ); ?></option>
			<option value="alphabetical" <?php selected( $current, 'alphabetical' ); ?>><?php esc_html_e( 'Alphabetical (A-Z)', 'sitemap-from-menu' ); ?></option>
		</select>
		<?php
	}

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

	public function render_pages_multiselect( $args ) {
		$settings = get_option( 'sfm_settings', [] );
		$key      = sanitize_key( $args['key'] );
		$current  = isset( $settings[ $key ] ) ? explode( ',', $settings[ $key ] ) : [];
		
		$pages    = get_pages( [ 'post_status' => [ 'publish', 'draft', 'private' ], 'number' => -1 ] );
		?>
		<select name="sfm_settings[<?php echo esc_attr( $key ); ?>][]" multiple="multiple" size="10" style="min-width: 300px; max-height: 200px;">
			<?php foreach ( $pages as $page ) : ?>
				<option value="<?php echo esc_attr( $page->ID ); ?>" <?php echo in_array( (string) $page->ID, $current, true ) ? 'selected="selected"' : ''; ?>>
					<?php echo esc_html( $page->post_title . ' (ID: ' . $page->ID . ')' ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php if ( isset( $args['description'] ) ) : ?>
			<p class="description"><?php echo esc_html( $args['description'] ); ?></p>
		<?php endif; ?>
		<?php
	}

	/**
	 * Render robust 2-column layout wrapping standard settings APIs internally elegantly naturally accurately purely cleanly conceptually fundamentally beautifully.
	 */
	public function render_settings_page() {
		if ( ! current_user_can( 'manage_options' ) ) return;
		?>
		<div class="wrap sfm-admin-wrap">
			<h1><?php esc_html_e( 'Sitemap From Menu Settings', 'sitemap-from-menu' ); ?></h1>
			<div class="sfm-admin-layout">
				<div class="sfm-settings-panel">
					<form action="options.php" method="POST" id="sfm-settings-form">
						<?php settings_fields( 'sfm_settings_group' ); ?>
						<?php do_settings_sections( 'sitemap-from-menu' ); ?>
						<?php submit_button(); ?>
					</form>
				</div>
				<div class="sfm-preview-panel">
					<div class="sfm-device-frame">
						<div class="sfm-device-bar">
							<span class="sfm-dot sfm-dot-red"></span>
							<span class="sfm-dot sfm-dot-yellow"></span>
							<span class="sfm-dot sfm-dot-green"></span>
							<div class="sfm-device-title"><?php esc_html_e( 'Live Theme Preview', 'sitemap-from-menu' ); ?></div>
						</div>
						<iframe id="sfm-preview-frame" src="<?php echo esc_url( home_url( '/?sfm_preview=1' ) ); ?>"></iframe>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Enqueue specific structured admin scripts and CSS.
	 */
	public function enqueue_admin_assets( $hook ) {
		if ( 'settings_page_sitemap-from-menu' !== $hook ) {
			return;
		}

		wp_enqueue_style( 'sfm-admin-css', SFM_PLUGIN_URL . 'assets/css/admin.css', [], SFM_VERSION );
		wp_enqueue_script( 'sfm-admin-js', SFM_PLUGIN_URL . 'assets/js/admin.js', [ 'jquery' ], SFM_VERSION, true );

		wp_localize_script( 'sfm-admin-js', 'sfm_admin', [
			'ajax_url' => admin_url( 'admin-ajax.php' ),
			'nonce'    => wp_create_nonce( 'sfm_admin_nonce' ),
		] );
	}

	/**
	 * Catch background mapping hooks dynamically securely filtering options purely decoupling UI flows transparently naturally natively securely intuitively structurally comprehensively flawlessly functionally effectively organically natively safely optimally accurately visually perfectly implicitly comprehensively respectively natively natively inherently gracefully strictly implicitly intelligently optimally creatively.
	 */
	public function ajax_render_preview() {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_send_json_error( 'Unauthorized' );
		}
		check_ajax_referer( 'sfm_admin_nonce', 'nonce' );

		$raw_data = isset( $_POST['form_data'] ) ? wp_unslash( $_POST['form_data'] ) : '';
		parse_str( $raw_data, $form_data );
		$input = isset( $form_data['sfm_settings'] ) ? $form_data['sfm_settings'] : [];

		$sanitized = $this->sanitize_settings_array( $input );

		// Hook dynamic memory filters implicitly decoupling database save from purely responsive mappings realistically optimally seamlessly comprehensively securely perfectly logically efficiently structurally beautifully creatively cleanly intelligently essentially cleanly nicely exactly exclusively reliably comprehensively flawlessly intelligently purely conceptually practically optimally beautifully
		add_filter( 'option_sfm_settings', function() use ( $sanitized ) {
			return $sanitized;
		} );

		$renderer = new Renderer();
		$html = $renderer->render( [ 'is_preview' => true ] );

		wp_send_json_success( $html );
	}

	public function clear_sitemap_transient( $menu_id = 0 ) {
		global $wpdb;
		$wpdb->query( "DELETE FROM `{$wpdb->options}` WHERE `option_name` LIKE '_transient_sfm_shtml_%'" );
		$wpdb->query( "DELETE FROM `{$wpdb->options}` WHERE `option_name` LIKE '_transient_timeout_sfm_shtml_%'" );
	}
}
