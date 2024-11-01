<?php

class Wishful_Companion_Toolset {

    private $theme_author = 'wishfulthemes';


	public static function instance() {

		static $instance = null;

		if ( null === $instance ) {
			$instance = new Wishful_Companion_Toolset;
		}

		return $instance;
	}

	public function run() {
        $this->load_dependencies();

        if ( wishful_companion_get_current_theme_author() == $this->theme_author ) {
            $this->hooks();
        }

	}

    private function load_dependencies() {

        require_once WISHFUL_COMPANION_PATH . 'inc/functions.php';
        require_once WISHFUL_COMPANION_PATH . 'inc/hooks.php';

    }

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.2
	 * @access   private
	 */
	private function hooks() {

		$plugin_admin = wishful_companion_hooks();
        add_filter( 'advanced_import_demo_lists', array( $plugin_admin, 'add_demo_lists' ), 10, 1 );
        add_filter( 'admin_menu', array( $plugin_admin, 'import_menu' ), 10, 1 );
        add_filter( 'wp_ajax_wishful_companion_getting_started', array( $plugin_admin, 'install_advanced_import' ), 10, 1 );
        add_filter( 'admin_enqueue_scripts', array( $plugin_admin, 'enqueue_styles' ), 10, 1 );
        add_filter( 'admin_enqueue_scripts', array( $plugin_admin, 'enqueue_scripts' ), 10, 1 );

        /*Replace terms and post ids*/
        add_action( 'advanced_import_replace_term_ids', array( $plugin_admin, 'replace_term_ids' ), 20 );
        add_action( 'advanced_import_replace_post_ids', array( $plugin_admin, 'replace_post_ids' ), 20 );
    }

}


/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.2
 */
if( !function_exists( 'Wishful_Companion_Toolset')){

    function wishful_companion() {

        return Wishful_Companion_Toolset::instance();
    }
    wishful_companion()->run();
}