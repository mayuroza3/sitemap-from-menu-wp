<?php
	/*
	Plugin Name: Sitemap From Menu
	Plugin URI:  
	Description: Make simple sitemap page from selecting menu 
	Version:     1.0
	Author:      Mayur Oza
	Author URI:  https://www.facebook.com/mayuroza57
	License:     
	License URI: 
	Text Domain: sitefrommenu
	Domain Path: 
	*/
	add_action( 'admin_menu', 'add_csfm_options_page' );
	function add_csfm_options_page() {
		add_options_page(
			'Custom Sitemap From Menu',
			'Custom Sitemap From Menu',
			'manage_options',
			'csfm-options-page',
			'display_csfm_options_page'
		);
	}
	
	function display_csfm_options_page() {
		echo '<h2>Custom Sitemap From Menu Options</h2>';
		echo '<form method="post" action="options.php">';
		echo "<p><strong>To create custom sitemap from menu, Follow this steps:</strong> <br></p>
	<ol class='data-backend'>
	<li>Select Menu for creating Sitemap and Add extra page ids seperated by , for adding more pages except menu items. </li>
	<li>Create new page with name Sitemap</li>
	<li>Add Shortcode '[csfm]' without quotes in page and save</li>
	</ol>	
	<p class=''>Voila !!! Your Custom Sitemap page generated !!!!</p>" ;
		do_settings_sections( 'csfm-options-page' );
		settings_fields( 'csfm-settings' );
		submit_button();
		echo '</form>';
	}
	
	add_action( 'admin_init', 'csfm_admin_init_one' );
	function csfm_admin_init_one() {
		add_settings_section(
			'csfm-settings-section-one',      
			'Menu Selection',         
			'display_csfm_settings_message',  
			'csfm-options-page'               
		);
	
		add_settings_field(
			'csfm-input-field',        
			'Select Menu for Sitemap Generation',        
			'render_csfm_input_field',  
			'csfm-options-page',        
			'csfm-settings-section-one' 
		);
	
		register_setting(
			'csfm-settings',    
			'csfm-input-field'    
		);
	}
	
	function display_csfm_settings_message() {
		echo "This displays the settings message.";
	}
	
	function render_csfm_input_field() {
	
		$input = get_option( 'csfm-input-field' );
		$select_data  = "";
		?>
			<select class="form-control" id="csfm-input-field" name="csfm-input-field">
			<?php 
				echo "<option value='NA'>-----</option>";	 
				$array1 =  (get_terms( 'nav_menu', array( 'hide_empty' => false ) ) );
				foreach ( $array1 as $array_inner1 ) {
					if( $input == $array_inner1->name){
						echo " <option value='".$array_inner1->name."' Selected> ".$array_inner1->name."</option>";
					}
					else{
						echo "<option value='".$array_inner1->name."'>".$array_inner1->name."</option>";
					}
				}
				
				 ?>
			</select>
			<?php
	}
	
	add_action( 'admin_init', 'csfm_admin_init_two' );
	function csfm_admin_init_two() {
	add_settings_section(
	'csfm-settings-section-two',
	'Extra pages',
	'display_another_csfm_settings_message',
	'csfm-options-page'
	);
	add_settings_field(
	'csfm-input-field-two',
	'Extra pages for sitemap',
	'render_csfm_input_field_two',
	'csfm-options-page',
	'csfm-settings-section-two'
	);
	register_setting(
	'csfm-settings',
	'csfm-input-field-two'
	);
	}
	function display_another_csfm_settings_message() {
	echo "Enter Manually page id seperated by , to add in sitemap";
	}
	function render_csfm_input_field_two() {
	$input = get_option( 'csfm-input-field-two' );
	echo '<input type="text" id="csfm-input-field-two" name="csfm-input-field-two" value="' . $input . '" />';
	}
	function wp_first_shortcode(){
	
	$pages =  explode(",",get_option( 'csfm-input-field-two'));
	//print_r ($pages);
	echo "<ul class='sitemap-class'>";
		$menu_items  =  wp_get_nav_menu_items( get_option( 'csfm-input-field' ) );
		foreach ($menu_items as $menu_item){
		//print_r ($menu_item);echo "<br>";
		if(get_page_link($menu_item)!="")
		echo "
		<li><a href='".$menu_item->url."' class='stemap-link' > ".$menu_item->title."</a></li>
		";
		else{
		echo "
		<li><a href='".get_page_url($menu_item)."' class='stemap-link' > ".get_the_title($menu_item)."</a></li>
		";
		} 
		}
		foreach($pages as $pageid){
		echo "
		<li><a href='".get_page_link($pageid)."' class='stemap-link' > ".get_the_title($pageid)."</a></li>
		";
		}
		echo "</ul>";
		//var_dump( wp_get_nav_menu_items(get_option( 'csfm-input-field' )));
		/* 
		foreach ($menu_items as $menu_item){
		//print_r ($menu_item);echo "<br>";
		if(get_page_link($menu_item)!="")
		echo "
		<li><a href='".get_page_link($menu_item)."' class='stemap-link' > ".get_the_title($menu_item)."</a></li>
		";
		} */
		//echo get_option( 'csfm-input-field' );
		}
		add_shortcode('csfm', 'wp_first_shortcode');
		add_filter( 'plugin_action_links', 'ttt_wpmdr_add_action_plugin', 10, 5 );
		function ttt_wpmdr_add_action_plugin( $actions, $plugin_file ) 
		{
			static $plugin;
			if (!isset($plugin))
			$plugin = plugin_basename(__FILE__);
			if ($plugin == $plugin_file) {
				$settings = array('settings' => '<a href="options-general.php?page=csfm-options-page">' . __('Settings', 'General') . '</a>');
				$site_link = array('support' => '<a href="mailto:mayuroza3@gmail.com" target="_blank">Support</a>');
				$actions = array_merge($settings, $actions);
				$actions = array_merge($site_link, $actions);
			}
			return $actions;
		}