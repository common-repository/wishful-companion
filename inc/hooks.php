<?php
class Wishful_Companion_Toolset_Hooks {

	private $hook_suffix;

	private $theme_author = 'wishfulthemes';

	public static function instance() {

		static $instance = null;

		if ( null === $instance ) {
			$instance = new self();
		}

		return $instance;
	}

	public function __construct() {}

	public function import_menu() {
		if ( ! class_exists( 'Advanced_Import' ) ) {
			$this->hook_suffix[] = add_theme_page( esc_html__( 'Demo Import ', 'wishful-companion' ), esc_html__( 'Demo Import', 'wishful-companion' ), 'manage_options', 'advanced-import', array( $this, 'demo_import_screen' ) );
		}
	}

	public function enqueue_styles( $hook_suffix ) {
		if ( ! is_array( $this->hook_suffix ) || ! in_array( $hook_suffix, $this->hook_suffix ) ) {
			return;
		}
		wp_enqueue_style( WISHFUL_COMPANION_PLUGIN_NAME, WISHFUL_COMPANION_PLUGIN_URL . 'inc/assets/toolset.css', array( 'wp-admin', 'dashicons' ), WISHFUL_COMPANION_CURRENT_VERSION, 'all' );
	}

	public function enqueue_scripts( $hook_suffix ) {
		if ( ! is_array( $this->hook_suffix ) || ! in_array( $hook_suffix, $this->hook_suffix ) ) {
			return;
		}

		wp_enqueue_script( WISHFUL_COMPANION_PLUGIN_NAME, WISHFUL_COMPANION_PLUGIN_URL . 'inc/assets/toolset.js', array( 'jquery' ), WISHFUL_COMPANION_CURRENT_VERSION, true );
		wp_localize_script(
			WISHFUL_COMPANION_PLUGIN_NAME,
			'wishful_companion',
			array(
				'btn_text' => esc_html__( 'Processing...', 'wishful-companion' ),
				'nonce'    => wp_create_nonce( 'wishful_companion_nonce' ),
			)
		);
	}

	public function demo_import_screen() {
		?>
		<div id="ads-notice">
			<div class="ads-container">
				<img class="ads-screenshot" src="<?php echo esc_url( wishful_companion_get_theme_screenshot() ); ?>" />
				<div class="ads-notice">
					<h2>
						<?php
						printf(
							esc_html__( 'Welcome! Thank you for choosing %1$s! To get started with ready-made starter site templates. Install the Advanced Import plugin and install Demo Starter Site within a single click', 'wishful-companion' ),
							'<strong>' . wp_get_theme()->get( 'Name' ) . '</strong>'
						);
						?>
					</h2>

					<p class="plugin-install-notice"><?php esc_html_e( 'Clicking the button below will install and activate the Advanced Import plugin.', 'wishful-companion' ); ?></p>

					<a class="ads-gsm-btn button button-primary button-hero" href="#" data-name="" data-slug="" aria-label="<?php esc_html_e( 'Get started with the Theme', 'wishful-companion' ); ?>">
						<?php esc_html_e( 'Get Started', 'wishful-companion' ); ?>
					</a>
				</div>
			</div>
		</div>
		<?php

	}

	public function install_advanced_import() {

		check_ajax_referer( 'wishful_companion_nonce', 'security' );

		$slug   = 'advanced-import';
		$plugin = 'advanced-import/advanced-import.php';

		$status             = array(
			'install' => 'plugin',
			'slug'    => sanitize_key( wp_unslash( $slug ) ),
		);
		$status['redirect'] = admin_url( '/themes.php?page=advanced-import&browse=all&at-gsm-hide-notice=welcome' );

		if ( is_plugin_active_for_network( $plugin ) || is_plugin_active( $plugin ) ) {
			// Plugin is activated
			wp_send_json_success( $status );
		}

		if ( ! current_user_can( 'install_plugins' ) ) {
			$status['errorMessage'] = __( 'Sorry, you are not allowed to install plugins on this site.', 'wishful-companion' );
			wp_send_json_error( $status );
		}

		include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		include_once ABSPATH . 'wp-admin/includes/plugin-install.php';

		// Looks like a plugin is installed, but not active.
		if ( file_exists( WP_PLUGIN_DIR . '/' . $slug ) ) {
			$plugin_data          = get_plugin_data( WP_PLUGIN_DIR . '/' . $plugin );
			$status['plugin']     = $plugin;
			$status['pluginName'] = $plugin_data['Name'];

			if ( current_user_can( 'activate_plugin', $plugin ) && is_plugin_inactive( $plugin ) ) {
				$result = activate_plugin( $plugin );

				if ( is_wp_error( $result ) ) {
					$status['errorCode']    = $result->get_error_code();
					$status['errorMessage'] = $result->get_error_message();
					wp_send_json_error( $status );
				}

				wp_send_json_success( $status );
			}
		}

		$api = plugins_api(
			'plugin_information',
			array(
				'slug'   => sanitize_key( wp_unslash( $slug ) ),
				'fields' => array(
					'sections' => false,
				),
			)
		);

		if ( is_wp_error( $api ) ) {
			$status['errorMessage'] = $api->get_error_message();
			wp_send_json_error( $status );
		}

		$status['pluginName'] = $api->name;

		$skin     = new WP_Ajax_Upgrader_Skin();
		$upgrader = new Plugin_Upgrader( $skin );
		$result   = $upgrader->install( $api->download_link );

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG ) {
			$status['debug'] = $skin->get_upgrade_messages();
		}

		if ( is_wp_error( $result ) ) {
			$status['errorCode']    = $result->get_error_code();
			$status['errorMessage'] = $result->get_error_message();
			wp_send_json_error( $status );
		} elseif ( is_wp_error( $skin->result ) ) {
			$status['errorCode']    = $skin->result->get_error_code();
			$status['errorMessage'] = $skin->result->get_error_message();
			wp_send_json_error( $status );
		} elseif ( $skin->get_errors()->get_error_code() ) {
			$status['errorMessage'] = $skin->get_error_messages();
			wp_send_json_error( $status );
		} elseif ( is_null( $result ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			WP_Filesystem();
			global $wp_filesystem;

			$status['errorCode']    = 'unable_to_connect_to_filesystem';
			$status['errorMessage'] = __( 'Unable to connect to the filesystem. Please confirm your credentials.', 'wishful-companion' );

			// Pass through the error from WP_Filesystem if one was raised.
			if ( $wp_filesystem instanceof WP_Filesystem_Base && is_wp_error( $wp_filesystem->errors ) && $wp_filesystem->errors->get_error_code() ) {
				$status['errorMessage'] = esc_html( $wp_filesystem->errors->get_error_message() );
			}

			wp_send_json_error( $status );
		}

		$install_status = install_plugin_install_status( $api );

		if ( current_user_can( 'activate_plugin', $install_status['file'] ) && is_plugin_inactive( $install_status['file'] ) ) {
			$result = activate_plugin( $install_status['file'] );

			if ( is_wp_error( $result ) ) {
				$status['errorCode']    = $result->get_error_code();
				$status['errorMessage'] = $result->get_error_message();
				wp_send_json_error( $status );
			}
		}

		wp_send_json_success( $status );

	}

	public function add_demo_lists( $current_demo_list ) {

		if ( wishful_companion_get_current_theme_author() != $this->theme_author ) {
			return $current_demo_list;
		}

		$theme_slug = wishful_companion_get_current_theme_slug();

		switch ( $theme_slug ) :
			case 'raise-mag':
				$templates = array(
					array(
						'title'          => __( 'Main Demo', 'wishful-companion' ), /*Title*/
						'is_pro'         => false, /*Premium*/
						'type'           => 'normal',
						'author'         => __( 'WishfulThemes', 'wishful-companion' ), /*Author Name*/
						'keywords'       => array( 'main', 'demo' ), /*Search keyword*/
						'categories'     => array( 'magazine' ), /*Categories*/
						'template_url'   => array(
							'content' => WISHFUL_COMPANION_PLUGIN_URL . 'inc/demo/raise-mag/demo-one/content.json',
							'options' => WISHFUL_COMPANION_PLUGIN_URL . 'inc/demo/raise-mag/demo-one/options.json',
							'widgets' => WISHFUL_COMPANION_PLUGIN_URL . 'inc/demo/raise-mag/demo-one/widgets.json',
						),
						'screenshot_url' => WISHFUL_COMPANION_PLUGIN_URL . 'inc/demo/raise-mag/demo-one/screenshot.png', /*Screenshot of block*/
						'demo_url'       => 'https://demo.wishfulthemes.com/raise-mag/free-demo-one/', /*Demo Url*/
						'plugins'        => array(
							array(
								'name' => 'Gutentor',
								'slug' => 'gutentor',
							),
						),
					),
					array(
						'title'          => __( 'Gutentor Demo One', 'wishful-companion' ), /*Title*/
						'is_pro'         => false, /*Premium*/
						'type'           => 'gutentor',
						'author'         => __( 'Wishfulthemes', 'wishful-companion' ), /*Author Name*/
						'keywords'       => array( 'main', 'demo' ), /*Search keyword*/
						'categories'     => array( 'gutenberg' ), /*Categories*/
						'template_url'   => array(
							'content' => WISHFUL_COMPANION_PLUGIN_URL . 'inc/demo/raise-mag/demo-two/content.json',
							'options' => WISHFUL_COMPANION_PLUGIN_URL . 'inc/demo/raise-mag/demo-two/options.json',
							'widgets' => WISHFUL_COMPANION_PLUGIN_URL . 'inc/demo/raise-mag/demo-two/widgets.json',
						),
						'screenshot_url' => WISHFUL_COMPANION_PLUGIN_URL . 'inc/demo/raise-mag/demo-two/screenshot.png', /*Screenshot of block*/
						'demo_url'       => 'https://demo.wishfulthemes.com/raise-mag/free-demo-two/', /*Demo Url*/
						'plugins'        => array(
							array(
								'name' => 'Gutentor',
								'slug' => 'gutentor',
							),
						),
					),
					array(
						'title'          => __( 'Gutentor Demo Two', 'wishful-companion' ), /*Title*/
						'is_pro'         => false, /*Premium*/
						'type'           => 'gutentor',
						'author'         => __( 'Wishfulthemes', 'wishful-companion' ), /*Author Name*/
						'keywords'       => array( 'main', 'demo' ), /*Search keyword*/
						'categories'     => array( 'gutenberg', 'magazine' ), /*Categories*/
						'template_url'   => array(
							'content' => WISHFUL_COMPANION_PLUGIN_URL . 'inc/demo/raise-mag/demo-three/content.json',
							'options' => WISHFUL_COMPANION_PLUGIN_URL . 'inc/demo/raise-mag/demo-three/options.json',
							'widgets' => WISHFUL_COMPANION_PLUGIN_URL . 'inc/demo/raise-mag/demo-three/widgets.json',
						),
						'screenshot_url' => WISHFUL_COMPANION_PLUGIN_URL . 'inc/demo/raise-mag/demo-three/screenshot.png', /*Screenshot of block*/
						'demo_url'       => 'https://demo.wishfulthemes.com/raise-mag/free-demo-three/', /*Demo Url*/
						'plugins'        => array(
							array(
								'name' => 'Gutentor',
								'slug' => 'gutentor',
							),
						),
					),
				);
				break;

			case 'trending-mag':
				$templates = array(
					array(
						'title'          => __( 'Main Demo', 'wishful-companion' ), /*Title*/
						'is_pro'         => false, /*Premium*/
						'type'           => 'normal',
						'author'         => __( 'WishfulThemes', 'wishful-companion' ), /*Author Name*/
						'keywords'       => array( 'main', 'demo' ), /*Search keyword*/
						'categories'     => array( 'magazine' ), /*Categories*/
						'template_url'   => array(
							'content' => WISHFUL_COMPANION_PLUGIN_URL . 'inc/demo/trending-mag/demo-one/content.json',
							'options' => WISHFUL_COMPANION_PLUGIN_URL . 'inc/demo/trending-mag/demo-one/options.json',
							'widgets' => WISHFUL_COMPANION_PLUGIN_URL . 'inc/demo/trending-mag/demo-one/widgets.json',
						),
						'screenshot_url' => WISHFUL_COMPANION_PLUGIN_URL . 'inc/demo/trending-mag/demo-one/screenshot.png', /*Screenshot of block*/
						'demo_url'       => 'https://demo.wishfulthemes.com/trending-mag/free-demo-one/', /*Demo Url*/
						'plugins'        => array(
							array(
								'name' => 'Wishful Ad Manager',
								'slug' => 'wishful-ad-manager',
							),
						),
					),
					array(
						'title'          => __( 'Pro Demo One', 'wishful-companion' ), /*Title*/
						'is_pro'         => true, /*Premium*/
						'type'           => 'normal',
						'pro_url'        => 'https://www.wishfulthemes.com/themes/trending-mag/', /*Premium version/Pricing Url*/
						'author'         => __( 'WishfulThemes', 'wishful-companion' ), /*Author Name*/
						'keywords'       => array( 'main', 'demo', 'premium', 'pro' ), /*Search keyword*/
						'categories'     => array( 'magazine' ), /*Categories*/
						'template_url'   => array(
							'content' => WISHFUL_COMPANION_PLUGIN_URL . 'inc/demo/trending-mag/pro-demo-one/content.json',
							'options' => WISHFUL_COMPANION_PLUGIN_URL . 'inc/demo/trending-mag/pro-demo-one/options.json',
							'widgets' => WISHFUL_COMPANION_PLUGIN_URL . 'inc/demo/trending-mag/pro-demo-one/widgets.json',
						),
						'screenshot_url' => WISHFUL_COMPANION_PLUGIN_URL . 'inc/demo/trending-mag/demo-one/screenshot.png', /*Screenshot of block*/
						'demo_url'       => 'https://demo.wishfulthemes.com/trending-mag/pro-demo-one/', /*Demo Url*/
						'plugins'        => array(
							array(
								'name' => 'Wishful Ad Manager',
								'slug' => 'wishful-ad-manager',
							),
						),
					),
					array(
						'title'          => __( 'Pro Demo Two', 'wishful-companion' ), /*Title*/
						'is_pro'         => true, /*Premium*/
						'type'           => 'normal',
						'pro_url'        => 'https://www.wishfulthemes.com/themes/trending-mag/', /*Premium version/Pricing Url*/
						'author'         => __( 'WishfulThemes', 'wishful-companion' ), /*Author Name*/
						'keywords'       => array( 'main', 'demo', 'premium', 'pro' ), /*Search keyword*/
						'categories'     => array( 'magazine' ), /*Categories*/
						'template_url'   => array(
							'content' => WISHFUL_COMPANION_PLUGIN_URL . 'inc/demo/trending-mag/pro-demo-two/content.json',
							'options' => WISHFUL_COMPANION_PLUGIN_URL . 'inc/demo/trending-mag/pro-demo-two/options.json',
							'widgets' => WISHFUL_COMPANION_PLUGIN_URL . 'inc/demo/trending-mag/pro-demo-two/widgets.json',
						),
						'screenshot_url' => WISHFUL_COMPANION_PLUGIN_URL . 'inc/demo/trending-mag/demo-one/screenshot.png', /*Screenshot of block*/
						'demo_url'       => 'https://demo.wishfulthemes.com/trending-mag/pro-demo-two/', /*Demo Url*/
						'plugins'        => array(
							array(
								'name' => 'Wishful Ad Manager',
								'slug' => 'wishful-ad-manager',
							),
						),
					),
					array(
						'title'          => __( 'Pro Demo Three', 'wishful-companion' ), /*Title*/
						'is_pro'         => true, /*Premium*/
						'type'           => 'normal',
						'pro_url'        => 'https://www.wishfulthemes.com/themes/trending-mag/', /*Premium version/Pricing Url*/
						'author'         => __( 'WishfulThemes', 'wishful-companion' ), /*Author Name*/
						'keywords'       => array( 'main', 'demo', 'premium', 'pro' ), /*Search keyword*/
						'categories'     => array( 'magazine' ), /*Categories*/
						'template_url'   => array(
							'content' => WISHFUL_COMPANION_PLUGIN_URL . 'inc/demo/trending-mag/pro-demo-three/content.json',
							'options' => WISHFUL_COMPANION_PLUGIN_URL . 'inc/demo/trending-mag/pro-demo-three/options.json',
							'widgets' => WISHFUL_COMPANION_PLUGIN_URL . 'inc/demo/trending-mag/pro-demo-three/widgets.json',
						),
						'screenshot_url' => WISHFUL_COMPANION_PLUGIN_URL . 'inc/demo/trending-mag/demo-one/screenshot.png', /*Screenshot of block*/
						'demo_url'       => 'https://demo.wishfulthemes.com/trending-mag/pro-demo-three/', /*Demo Url*/
						'plugins'        => array(
							array(
								'name' => 'Wishful Ad Manager',
								'slug' => 'wishful-ad-manager',
							),
						),
					),
				);
				break;

			default:
				$templates = array();
				break;
		endswitch;

		return array_merge( $current_demo_list, $templates );

	}

	public function replace_post_ids( $replace_post_ids ) {
		if ( wishful_companion_get_current_theme_author() != $this->theme_author ) {
			return $replace_post_ids;
		}

		$theme_slug = wishful_companion_get_current_theme_slug();

		switch ( $theme_slug ) :
			case 'raise-mag':
				/*Terms IDS*/
				$term_ids = array(
					'raise-mag-select-category',
					'raise-mag-promo-select-category',
				);
				break;

			case 'next-mag':
				/*Terms IDS*/
				$term_ids = array(
					'raise-mag-select-category',
					'raise-mag-promo-select-category',
				);
				break;
			default:
				$term_ids = array();
				break;

			endswitch;

		return array_merge( $replace_post_ids, $term_ids );
	}

	public function replace_term_ids( $replace_term_ids ) {

		if ( wishful_companion_get_current_theme_author() != $this->theme_author ) {
			return $replace_term_ids;
		}

		$theme_slug = wishful_companion_get_current_theme_slug();

		switch ( $theme_slug ) :
			case 'raise-mag':
				/*Terms IDS*/
				$term_ids = array(
					'raise-mag-select-category',
					'raise-mag-promo-select-category',
				);
				break;

			case 'trending-mag':
				/*Terms IDS*/
				$term_ids = array(
					'category',
					'slider_category',
				);
				break;
			default:
				$term_ids = array();
				break;

		endswitch;

		return array_merge( $replace_term_ids, $term_ids );
	}
}

/**
 * Begins execution of the hooks.
 *
 * @since    1.0.0
 */
function wishful_companion_hooks() {
	return Wishful_Companion_Toolset_Hooks::instance();
}
