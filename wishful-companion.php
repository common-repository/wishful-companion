<?php
/**
 * Plugin Name: Wishful Companion
 * Description: Extra features for WishfulThemes
 * Version: 1.1.0
 * Author: WishfulThemes
 * Author URI: https://wishfulthemes.com/
 * License: GPL-2.0+
 * WC requires at least: 3.3.0
 * WC tested up to: 6.5
 *
 * @package wishful-companion
 */

// Exit if accessed directly.

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! function_exists( 'add_action' ) ) {
	die( 'Nothing to do...' );
}

function wish_file_get_contents( $file ) {
	$response_data = file_get_contents( $file );
	if ( empty( $response_data ) || ! $response_data ) {
		$response      = wp_remote_get( $file );
		$response_data = wp_remote_retrieve_body( $response );
	}
	return $response_data;
}

$plugin_data    = get_file_data( __FILE__, array( 'Version' => 'Version' ), false );
$plugin_version = $plugin_data['Version'];
// Define WISHFUL_COMPANION_CURRENT_VERSION.
if ( ! defined( 'WISHFUL_COMPANION_CURRENT_VERSION' ) ) {
	define( 'WISHFUL_COMPANION_CURRENT_VERSION', $plugin_version );
}

// plugin constants
define( 'WISHFUL_COMPANION_PATH', plugin_dir_path( __FILE__ ) );
define( 'WISHFUL_COMPANION_PLUGIN_BASE', plugin_basename( __FILE__ ) );
define( 'WISHFUL_COMPANION_PLUGIN_NAME', 'wishful-companion' );
define( 'WISHFUL_COMPANION_PLUGIN_URL', plugins_url( '/', __FILE__ ) );


add_action( 'plugins_loaded', 'wishful_companion_load_textdomain' );

function wishful_companion_load_textdomain() {
	load_plugin_textdomain( 'wishful-companion', false, basename( dirname( __FILE__ ) ) . '/languages/' );
}

/**
 * Check Elementor plugin
 */
function wishful_companion_check_for_elementor() {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	return is_plugin_active( 'elementor/elementor.php' );
}

/**
 * Check Elementor PRO plugin
 */
function wishful_companion_check_for_elementor_pro() {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	return is_plugin_active( 'elementor-pro/elementor-pro.php' );
}

/**
 * Check Wishfulblog pro PRO plugin
 */
function wishful_companion_check_for_wishful_blog_pro() {
	require_once ABSPATH . 'wp-admin/includes/plugin.php';
	return is_plugin_active( 'wishfulblog-pro/wishfulblog-pro.php' );
}

/**
 * Register Wishfulblog PRO features
 */
if ( ! wishful_companion_check_for_wishful_blog_pro() ) {
	include_once WISHFUL_COMPANION_PATH . 'includes/panel/demos-pro.php';
}
/**
 * Register demo import
 */
$theme = wp_get_theme();
if ( 'Wishful Blog' == $theme->name || 'wishful-blog' == $theme->template ) {
	require_once WISHFUL_COMPANION_PATH . 'includes/panel/demos.php';
	require_once WISHFUL_COMPANION_PATH . 'includes/wizard/wizard.php';
	require_once WISHFUL_COMPANION_PATH . 'includes/notify/notify.php';
} else {

	require WISHFUL_COMPANION_PATH . 'inc/init.php';
}

/**
 * Add Metadata on plugin activation.
 */
function wishful_companion_activate() {
	add_site_option( 'wishful_companion_active_time', time() );
	add_option( 'wishful_blog_plugin_do_activation_redirect', true );
}

register_activation_hook( __FILE__, 'wishful_companion_activate' );

/**
 * Remove Metadata on plugin Deactivation.
 */
function wishful_companion_deactivate() {
	delete_option( 'wishful_companion_active_time' );
}

register_deactivation_hook( __FILE__, 'wishful_companion_deactivate' );

add_action( 'admin_init', 'wishful_companion_plugin_redirect' );

/**
 * Redirect after plugin activation
 */
function wishful_companion_plugin_redirect() {
	$theme = wp_get_theme();
	if ( 'Wishful Blog' == $theme->name || 'wishful-blog' == $theme->template ) {

		if ( get_option( 'wishful_blog_plugin_do_activation_redirect', false ) ) {
			delete_option( 'wishful_blog_plugin_do_activation_redirect' );
			if ( ! is_network_admin() || ! isset( $_GET['activate-multi'] ) ) {
				wp_redirect( 'themes.php?page=wishful-companion-panel-install-demos' );
			}
		}
	} else {

	}

}

