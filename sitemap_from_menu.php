<?php
/*
Plugin Name: Sitemap From Menu
Plugin URI:  https://github.com/mayuroza/sitemap-from-menu
Description: Generates a simple sitemap page from selected menu and additional pages.
Version:     1.0.1
Author:      Mayur Oza
Author URI:  https://www.facebook.com/mayuroza57
License:     GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Text Domain: sitefrommenu
*/

if (! defined('ABSPATH')) exit; // Exit if accessed directly

// Admin Menu
add_action('admin_menu', 'csfm_add_options_page');
function csfm_add_options_page()
{
    add_options_page(
        __('Custom Sitemap From Menu', 'sitefrommenu'),
        __('Custom Sitemap From Menu', 'sitefrommenu'),
        'manage_options',
        'csfm-options-page',
        'csfm_display_options_page'
    );
}

function csfm_display_options_page()
{
    if (! current_user_can('manage_options')) {
        return;
    }
?>
    <div class="wrap">
        <h2><?php _e('Custom Sitemap From Menu Options', 'sitefrommenu'); ?></h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('csfm-settings');
            do_settings_sections('csfm-options-page');
            submit_button();
            ?>
        </form>
    </div>
<?php
}

// Settings Init
add_action('admin_init', 'csfm_admin_settings_init');
function csfm_admin_settings_init()
{
    // Menu Select Section
    add_settings_section(
        'csfm_settings_section_menu',
        __('Menu Selection', 'sitefrommenu'),
        function () {
            echo '<p>' . esc_html__('Select a menu for generating sitemap.', 'sitefrommenu') . '</p>';
        },
        'csfm-options-page'
    );

    add_settings_field(
        'csfm_menu_select',
        __('Select Menu', 'sitefrommenu'),
        'csfm_menu_dropdown_render',
        'csfm-options-page',
        'csfm_settings_section_menu'
    );

    register_setting('csfm-settings', 'csfm_menu_select', 'sanitize_text_field');

    // Extra Page IDs Section
    add_settings_section(
        'csfm_settings_section_pages',
        __('Extra Pages', 'sitefrommenu'),
        function () {
            echo '<p>' . esc_html__('Comma-separated Page IDs to include in sitemap.', 'sitefrommenu') . '</p>';
        },
        'csfm-options-page'
    );

    add_settings_field(
        'csfm_extra_pages',
        __('Extra Page IDs', 'sitefrommenu'),
        'csfm_extra_pages_render',
        'csfm-options-page',
        'csfm_settings_section_pages'
    );

    register_setting('csfm-settings', 'csfm_extra_pages', 'sanitize_text_field');
}

function csfm_menu_dropdown_render()
{
    $selected_menu = get_option('csfm_menu_select');
    $menus = get_terms('nav_menu', array('hide_empty' => false));
?>
    <select name="csfm_menu_select" id="csfm_menu_select">
        <option value=""><?php esc_html_e('-- Select Menu --', 'sitefrommenu'); ?></option>
        <?php foreach ($menus as $menu) : ?>
            <option value="<?php echo esc_attr($menu->name); ?>" <?php selected($selected_menu, $menu->name); ?>>
                <?php echo esc_html($menu->name); ?>
            </option>
        <?php endforeach; ?>
    </select>
<?php
}

function csfm_extra_pages_render()
{
    $extra_pages = get_option('csfm_extra_pages', '');
    echo '<input type="text" name="csfm_extra_pages" value="' . esc_attr($extra_pages) . '" class="regular-text">';
}

// Shortcode Output
function csfm_render_sitemap()
{
    $menu_name = get_option('csfm_menu_select');
    $extra_ids = explode(',', get_option('csfm_extra_pages', ''));

    $output = '<ul class="csfm-sitemap">';

    if ($menu_name) {
        $items = wp_get_nav_menu_items($menu_name);
        if ($items) {
            foreach ($items as $item) {
                $output .= sprintf(
                    '<li><a href="%s">%s</a></li>',
                    esc_url($item->url),
                    esc_html($item->title)
                );
            }
        }
    }

    foreach ($extra_ids as $page_id) {
        $page_id = absint(trim($page_id));
        if ($page_id && get_post_status($page_id) === 'publish') {
            $output .= sprintf(
                '<li><a href="%s">%s</a></li>',
                esc_url(get_permalink($page_id)),
                esc_html(get_the_title($page_id))
            );
        }
    }

    $output .= '</ul>';

    return $output;
}
add_shortcode('csfm', 'csfm_render_sitemap');

// Plugin Action Links
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'csfm_plugin_action_links');
function csfm_plugin_action_links($links)
{
    $settings_link = '<a href="options-general.php?page=csfm-options-page">' . esc_html__('Settings', 'sitefrommenu') . '</a>';
    $support_link = '<a href="mailto:mayuroza3@gmail.com">' . esc_html__('Support', 'sitefrommenu') . '</a>';
    array_unshift($links, $settings_link, $support_link);
    return $links;
}
