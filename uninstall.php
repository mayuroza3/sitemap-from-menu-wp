<?php
/**
 * Fired when the plugin is uninstalled.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Delete plugin options
delete_option( 'sfm_settings' );

// Delete plugin transients
delete_transient( 'sfm_sitemap_html' );
