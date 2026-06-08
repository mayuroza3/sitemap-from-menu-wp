<?php
namespace SitemapFromMenu;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

/**
 * Elementor Sitemap From Menu Widget.
 */
class ElementorWidget extends \Elementor\Widget_Base {

	/**
	 * Get widget name.
	 *
	 * @return string Widget name.
	 */
	public function get_name() {
		return 'sitemap_from_menu';
	}

	/**
	 * Get widget title.
	 *
	 * @return string Widget title.
	 */
	public function get_title() {
		return esc_html__( 'Sitemap From Menu', 'sitemap-from-menu' );
	}

	/**
	 * Get widget icon.
	 *
	 * @return string Widget icon.
	 */
	public function get_icon() {
		return 'eicon-sitemap';
	}

	/**
	 * Get widget categories.
	 *
	 * @return array Widget categories.
	 */
	public function get_categories() {
		return [ 'general' ];
	}

	/**
	 * Register widget controls.
	 */
	protected function register_controls() {

		$this->start_controls_section(
			'section_content',
			[
				'label' => esc_html__( 'Sitemap Settings', 'sitemap-from-menu' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		// Get all menus
		$menus = wp_get_nav_menus();
		$menu_options = [ '0' => esc_html__( '&mdash; Select Menu &mdash;', 'sitemap-from-menu' ) ];
		foreach ( $menus as $menu ) {
			$menu_options[ $menu->term_id ] = esc_html( $menu->name );
		}

		$this->add_control(
			'menu_id',
			[
				'label'   => esc_html__( 'Select Menu', 'sitemap-from-menu' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '0',
				'options' => $menu_options,
			]
		);

		$this->add_control(
			'include_nested',
			[
				'label'        => esc_html__( 'Include Nested Items', 'sitemap-from-menu' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'sitemap-from-menu' ),
				'label_off'    => esc_html__( 'No', 'sitemap-from-menu' ),
				'return_value' => 'yes',
				'default'      => 'yes',
			]
		);

		$this->add_control(
			'include_desc',
			[
				'label'        => esc_html__( 'Include Descriptions', 'sitemap-from-menu' ),
				'type'         => \Elementor\Controls_Manager::SWITCHER,
				'label_on'     => esc_html__( 'Yes', 'sitemap-from-menu' ),
				'label_off'    => esc_html__( 'No', 'sitemap-from-menu' ),
				'return_value' => 'yes',
				'default'      => 'no',
			]
		);

		$this->add_control(
			'columns',
			[
				'label'   => esc_html__( 'Columns Layout', 'sitemap-from-menu' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => '1',
				'options' => [
					'1'    => esc_html__( '1 Column', 'sitemap-from-menu' ),
					'2'    => esc_html__( '2 Columns', 'sitemap-from-menu' ),
					'3'    => esc_html__( '3 Columns', 'sitemap-from-menu' ),
					'auto' => esc_html__( 'Auto-Fit Grid', 'sitemap-from-menu' ),
				],
			]
		);

		$this->add_control(
			'sorting',
			[
				'label'   => esc_html__( 'Sorting Method', 'sitemap-from-menu' ),
				'type'    => \Elementor\Controls_Manager::SELECT,
				'default' => 'default',
				'options' => [
					'default'      => esc_html__( 'Preserve Menu Order', 'sitemap-from-menu' ),
					'alphabetical' => esc_html__( 'Alphabetical (A-Z)', 'sitemap-from-menu' ),
				],
			]
		);

		$this->add_control(
			'container_class',
			[
				'label'   => esc_html__( 'Wrapper Container Class', 'sitemap-from-menu' ),
				'type'    => \Elementor\Controls_Manager::TEXT,
				'default' => 'sfm-sitemap',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_exclusions',
			[
				'label' => esc_html__( 'Exclusion Settings', 'sitemap-from-menu' ),
				'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'exclude_class',
			[
				'label'       => esc_html__( 'Exclude by CSS Class', 'sitemap-from-menu' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => 'sfm-exclude, hidden',
			]
		);

		$this->add_control(
			'exclude_url',
			[
				'label'       => esc_html__( 'Exclude by URL Pattern', 'sitemap-from-menu' ),
				'type'        => \Elementor\Controls_Manager::TEXT,
				'placeholder' => '/private/, /secret-page/',
			]
		);

		// Get all pages for multiselect
		$pages = get_pages( [ 'post_status' => [ 'publish' ], 'number' => -1 ] );
		$page_options = [];
		foreach ( $pages as $page ) {
			$page_options[ $page->ID ] = esc_html( $page->post_title . ' (ID: ' . $page->ID . ')' );
		}

		$this->add_control(
			'exclude_id',
			[
				'label'    => esc_html__( 'Exclude Pages', 'sitemap-from-menu' ),
				'type'     => \Elementor\Controls_Manager::SELECT2,
				'multiple' => true,
				'options'  => $page_options,
			]
		);

		$this->end_controls_section();
	}

	/**
	 * Render widget output on the frontend.
	 */
	protected function render() {
		$settings = $this->get_settings_for_display();

		// Convert Elementor switcher return values ('yes' / '') to boolean/integers
		$args = [
			'menu_id'         => absint( $settings['menu_id'] ),
			'include_nested'  => ( 'yes' === $settings['include_nested'] ) ? 1 : 0,
			'include_desc'    => ( 'yes' === $settings['include_desc'] ) ? 1 : 0,
			'container_class' => sanitize_html_class( $settings['container_class'] ),
		];

		// Overwrite dynamic option values selectively if they are custom-set in Elementor
		add_filter( 'option_sfm_settings', function( $opt ) use ( $settings ) {
			if ( ! is_array( $opt ) ) {
				$opt = [];
			}
			$opt['sfm_columns']       = sanitize_text_field( $settings['columns'] );
			$opt['sfm_sorting']       = sanitize_text_field( $settings['sorting'] );
			$opt['sfm_exclude_class'] = sanitize_text_field( $settings['exclude_class'] );
			$opt['sfm_exclude_url']   = sanitize_text_field( $settings['exclude_url'] );

			if ( ! empty( $settings['exclude_id'] ) && is_array( $settings['exclude_id'] ) ) {
				$opt['sfm_exclude_id'] = implode( ',', array_map( 'absint', $settings['exclude_id'] ) );
			}

			return $opt;
		} );

		$renderer = new Renderer();
		echo $renderer->render( $args );
	}
}
