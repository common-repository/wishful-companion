<?php
/**
 * Demos
 *
 * @package Wishful_Blog_Extra_Demos
 * @category Core
 * @author WishfulThemes
 */
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

// Start Class
if (!class_exists('Wishful_Blog_Extra_Demos')) {

    class Wishful_Blog_Extra_Demos {

        /**
         * Start things up
         */
        public function __construct() {

            // Return if not in admin
            if (!is_admin() || is_customize_preview()) {
                return;
            }

            // Import demos page
            if (version_compare(PHP_VERSION, '5.4', '>=')) {
                require_once( WISHFUL_COMPANION_PATH . '/includes/panel/classes/importers/class-helpers.php' );
                require_once( WISHFUL_COMPANION_PATH . '/includes/panel/classes/class-install-demos.php' );
            }

            // Start things
            add_action('admin_init', array($this, 'init'));

            // Demos scripts
            add_action('admin_enqueue_scripts', array($this, 'scripts'));

            // Allows xml uploads
            add_filter('upload_mimes', array($this, 'allow_xml_uploads'));

            // Demos popup
            add_action('admin_footer', array($this, 'popup'));
        }

        /**
         * Register the AJAX methods
         *
         * @since 1.0.0
         */
        public function init() {

            // Demos popup ajax
            add_action('wp_ajax_wishful_blog_ajax_get_demo_data', array($this, 'ajax_demo_data'));
            add_action('wp_ajax_wishful_blog_ajax_required_plugins_activate', array($this, 'ajax_required_plugins_activate'));

            // Get data to import
            add_action('wp_ajax_wishful_blog_ajax_get_import_data', array($this, 'ajax_get_import_data'));

            // Import XML file
            add_action('wp_ajax_wishful_blog_ajax_import_xml', array($this, 'ajax_import_xml'));

            // Import customizer settings
            add_action('wp_ajax_wishful_blog_ajax_import_theme_settings', array($this, 'ajax_import_theme_settings'));

            // Import widgets
            add_action('wp_ajax_wishful_blog_ajax_import_widgets', array($this, 'ajax_import_widgets'));

            // After import
            add_action('wp_ajax_wishful_blog_after_import', array($this, 'ajax_after_import'));
        }

        /**
         * Load scripts
         *
         * @since 1.4.5
         */
        public static function scripts($hook_suffix) {

            if ('appearance_page_wishful-companion-panel-install-demos' == $hook_suffix) {

                // CSS
                wp_enqueue_style('wishful-blog-demos-style', plugins_url('/assets/css/demos.css', __FILE__));

                // JS
                wp_enqueue_script('wishful-blog-demos-js', plugins_url('/assets/js/demos.js', __FILE__), array('jquery', 'wp-util', 'updates'), '1.0', true);

                wp_localize_script('wishful-blog-demos-js', 'wishfulblogDemos', array(
                    'ajaxurl' => admin_url('admin-ajax.php'),
                    'demo_data_nonce' => wp_create_nonce('get-demo-data'),
                    'wishful_blog_import_data_nonce' => wp_create_nonce('wishful_blog_import_data_nonce'),
                    'content_importing_error' => esc_html__('There was a problem during the importing process resulting in the following error from your server:', 'wishful-companion'),
                    'button_activating' => esc_html__('Activating', 'wishful-companion') . '&hellip;',
                    'button_active' => esc_html__('Active', 'wishful-companion'),
                ));
            }

            wp_enqueue_style('wishful-blog-notices', plugins_url('/assets/css/notify.css', __FILE__));
        }

        /**
         * Allows xml uploads so we can import from server
         *
         * @since 1.0.0
         */
        public function allow_xml_uploads($mimes) {
            $mimes = array_merge($mimes, array(
                'xml' => 'application/xml'
            ));
            return $mimes;
        }

        /**
         * Get demos data to add them in the Demo Import and Pro Demos plugins
         *
         * @since 1.4.5
         */
        public static function get_demos_data() {
            $theme = wp_get_theme();

            $theme_slug = get_stylesheet();

            $is_child_theme = (bool) $theme->parent();

            // Demos url
            $demo_url = 'https://demo.wishfulthemes.com/';
            $import_url = $demo_url . 'demo-import/' . $theme_slug . '/';

            if( $is_child_theme ) {

                $parent = $theme->parent();

                $theme_slug = $parent->get( 'TextDomain' );

                $import_url = $demo_url . 'demo-import/' . $theme_slug . '/';

                $data = array(
                    'child-free-one' => array(
                        'demo_name' => 'Child : Travel',
                        'demo_slug' => $theme_slug,
                        'demo_url' => $demo_url,
                        'categories' => array('Free', 'Travel', 'Child'),
                        'xml_file' => $import_url . 'child-free-demo/one/content.xml',
                        'theme_settings' => $import_url . 'child-free-demo/one/customizer.dat',
                        'widgets_file' => $import_url . 'child-free-demo/one/widgets.wie',
                        'screenshot' => $import_url . 'child-free-demo/one/screenshot.png',
                        'demo_template' => 'child-free-demo-one',
                        'home_title' => '',
                        'blog_title' => '',
                        'posts_to_show' => '4',
                        'main_nav_name' => 'Main Menu',
                        'main_nav_id' => 'menu-1',
                        'top_nav_name' => '',
                        'top_nav_id' => null,
                        'footer_nav_name' => '',
                        'footer_nav_id' => null,
                        'elementor_width' => '',
                        'is_shop' => false,
                        'woo_image_size' => '',
                        'woo_thumb_size' => '',
                        'woo_crop_width' => '',
                        'woo_crop_height' => '',
                        'required_plugins' => array(
                            'free' => array(
                                array(
                                    'slug' => 'wishful-companion',
                                    'init' => 'wishful-companion/wishful-companion.php',
                                    'name' => 'Wishful Companion',
                                ),
                            ),
                            'premium' => array(),
                        ),
                    ),
                );

            } else {

                $data = array(
                    'free-one' => array(
                        'demo_name' => 'Free : General',
                        'demo_slug' => $theme_slug,
                        'demo_url' => $demo_url,
                        'categories' => array('Free', 'Fashion', 'Travel', 'Food', 'Photography', 'WooCommerce'),
                        'xml_file' => $import_url . 'free-demo/one/content.xml',
                        'theme_settings' => $import_url . 'free-demo/one/customizer.dat',
                        'widgets_file' => $import_url . 'free-demo/one/widgets.wie',
                        'screenshot' => $import_url . 'free-demo/one/screenshot.jpg',
                        'demo_template' => 'free-demo-one',
                        'home_title' => '',
                        'blog_title' => '',
                        'posts_to_show' => '6',
                        'main_nav_name' => 'Main Menu',
                        'main_nav_id' => 'menu-1',
                        'top_nav_name' => '',
                        'top_nav_id' => null,
                        'footer_nav_name' => '',
                        'footer_nav_id' => null,
                        'elementor_width' => '',
                        'is_shop' => true,
                        'woo_image_size' => '600',
                        'woo_thumb_size' => '300',
                        'woo_crop_width' => '1',
                        'woo_crop_height' => '1',
                        'required_plugins' => array(
                            'free' => array(
                                array(
                                    'slug' => 'wishful-companion',
                                    'init' => 'wishful-companion/wishful-companion.php',
                                    'name' => 'Wishful Companion',
                                ),
                                array(
                                    'slug' => 'woocommerce',
                                    'init' => 'woocommerce/woocommerce.php',
                                    'name' => 'WooCommerce',
                                ),
                            ),
                            'premium' => array(),
                        ),
                    ),
                    'free-two' => array(
                        'demo_name' => 'Free : Food',
                        'demo_slug' => $theme_slug,
                        'demo_url' => $demo_url,
                        'categories' => array('Free', 'WooCommerce', 'Food'),
                        'xml_file' => $import_url . 'free-demo/two/content.xml',
                        'theme_settings' => $import_url . 'free-demo/two/customizer.dat',
                        'widgets_file' => $import_url . 'free-demo/two/widgets.wie',
                        'screenshot' => $import_url . 'free-demo/two/screenshot.jpg',
                        'demo_template' => 'free-demo-two',
                        'home_title' => '',
                        'blog_title' => '',
                        'posts_to_show' => '8',
                        'main_nav_name' => 'Main Menu',
                        'main_nav_id' => 'menu-1',
                        'top_nav_name' => '',
                        'top_nav_id' => null,
                        'footer_nav_name' => '',
                        'footer_nav_id' => null,
                        'elementor_width' => '',
                        'is_shop' => true,
                        'woo_image_size' => '600',
                        'woo_thumb_size' => '300',
                        'woo_crop_width' => '1',
                        'woo_crop_height' => '1',
                        'required_plugins' => array(
                            'free' => array(
                                array(
                                    'slug' => 'wishful-companion',
                                    'init' => 'wishful-companion/wishful-companion.php',
                                    'name' => 'Wishful Companion',
                                ),
                                array(
                                    'slug' => 'woocommerce',
                                    'init' => 'woocommerce/woocommerce.php',
                                    'name' => 'WooCommerce',
                                ),
                            ),
                            'premium' => array(),
                        ),
                    ),
                    'free-three' => array(
                        'demo_name' => 'Free : Fashion',
                        'demo_slug' => $theme_slug,
                        'demo_url' => $demo_url,
                        'categories' => array('Free', 'Fashion'),
                        'xml_file' => $import_url . 'free-demo/three/content.xml',
                        'theme_settings' => $import_url . 'free-demo/three/customizer.dat',
                        'widgets_file' => $import_url . 'free-demo/three/widgets.wie',
                        'screenshot' => $import_url . 'free-demo/three/screenshot.jpg',
                        'demo_template' => 'free-demo-three',
                        'home_title' => '',
                        'blog_title' => '',
                        'posts_to_show' => '8',
                        'main_nav_name' => 'Main Menu',
                        'main_nav_id' => 'menu-1',
                        'top_nav_name' => '',
                        'top_nav_id' => null,
                        'footer_nav_name' => '',
                        'footer_nav_id' => null,
                        'elementor_width' => '',
                        'is_shop' => false,
                        'woo_image_size' => '',
                        'woo_thumb_size' => '',
                        'woo_crop_width' => '',
                        'woo_crop_height' => '',
                        'required_plugins' => array(
                            'free' => array(
                                array(
                                    'slug' => 'wishful-companion',
                                    'init' => 'wishful-companion/wishful-companion.php',
                                    'name' => 'Wishful Companion',
                                ),
                            ),
                            'premium' => array(),
                        ),
                    ),
                    'free-four' => array(
                        'demo_name' => 'Free : Travel',
                        'demo_slug' => $theme_slug,
                        'demo_url' => $demo_url,
                        'categories' => array('Free', 'Travel'),
                        'xml_file' => $import_url . 'free-demo/four/content.xml',
                        'theme_settings' => $import_url . 'free-demo/four/customizer.dat',
                        'widgets_file' => $import_url . 'free-demo/four/widgets.wie',
                        'screenshot' => $import_url . 'free-demo/four/screenshot.jpg',
                        'demo_template' => 'free-demo-four',
                        'home_title' => '',
                        'blog_title' => '',
                        'posts_to_show' => '6',
                        'main_nav_name' => 'Main Menu',
                        'main_nav_id' => 'menu-1',
                        'top_nav_name' => '',
                        'top_nav_id' => null,
                        'footer_nav_name' => '',
                        'footer_nav_id' => null,
                        'elementor_width' => '',
                        'is_shop' => false,
                        'woo_image_size' => '',
                        'woo_thumb_size' => '',
                        'woo_crop_width' => '',
                        'woo_crop_height' => '',
                        'required_plugins' => array(
                            'free' => array(
                                array(
                                    'slug' => 'wishful-companion',
                                    'init' => 'wishful-companion/wishful-companion.php',
                                    'name' => 'Wishful Companion',
                                ),
                            ),
                            'premium' => array(),
                        ),
                    ),
                    'free-five' => array(
                        'demo_name' => 'Free : Photography',
                        'demo_slug' => $theme_slug,
                        'demo_url' => $demo_url,
                        'categories' => array('Free', 'Photography'),
                        'xml_file' => $import_url . 'free-demo/five/content.xml',
                        'theme_settings' => $import_url . 'free-demo/five/customizer.dat',
                        'widgets_file' => $import_url . 'free-demo/five/widgets.wie',
                        'screenshot' => $import_url . 'free-demo/five/screenshot.jpg',
                        'demo_template' => 'free-demo-five',
                        'home_title' => '',
                        'blog_title' => '',
                        'posts_to_show' => '10',
                        'main_nav_name' => 'Main Menu',
                        'main_nav_id' => 'menu-1',
                        'top_nav_name' => '',
                        'top_nav_id' => null,
                        'footer_nav_name' => '',
                        'footer_nav_id' => null,
                        'elementor_width' => '',
                        'is_shop' => false,
                        'woo_image_size' => '',
                        'woo_thumb_size' => '',
                        'woo_crop_width' => '',
                        'woo_crop_height' => '',
                        'required_plugins' => array(
                            'free' => array(
                                array(
                                    'slug' => 'wishful-companion',
                                    'init' => 'wishful-companion/wishful-companion.php',
                                    'name' => 'Wishful Companion',
                                ),
                            ),
                            'premium' => array(),
                        ),
                    ),
                );

            }

            // Return
            return apply_filters('wishful_blog_demos_data', $data);
        }

        /**
         * Get the category list of all categories used in the predefined demo imports array.
         *
         * @since 1.4.5
         */
        public static function get_demo_all_categories($demo_imports) {
            $categories = array();

            foreach ($demo_imports as $item) {
                if (!empty($item['categories']) && is_array($item['categories'])) {
                    foreach ($item['categories'] as $category) {
                        $categories[sanitize_key($category)] = $category;
                    }
                }
            }

            if (empty($categories)) {
                return false;
            }

            return $categories;
        }

        /**
         * Return the concatenated string of demo import item categories.
         * These should be separated by comma and sanitized properly.
         *
         * @since 1.4.5
         */
        public static function get_demo_item_categories($item) {
            $sanitized_categories = array();

            if (isset($item['categories'])) {
                foreach ($item['categories'] as $category) {
                    $sanitized_categories[] = sanitize_key($category);
                }
            }

            if (!empty($sanitized_categories)) {
                return implode(',', $sanitized_categories);
            }

            return false;
        }

        /**
         * Demos popup
         *
         * @since 1.4.5
         */
        public static function popup() {
            global $pagenow;
            if (isset($_GET['page'])) {
                // Display on the demos pages
                if (( 'themes.php' == $pagenow && 'wishful-companion-panel-install-demos' == $_GET['page'])) {
                    ?>

                    <div id="wishful-blog-demo-popup-wrap">
                        <div class="wishful-blog-demo-popup-container">
                            <div class="wishful-blog-demo-popup-content-wrap">
                                <div class="wishful-blog-demo-popup-content-inner">
                                    <a href="#" class="wishful-blog-demo-popup-close">×</a>
                                    <div id="wishful-blog-demo-popup-content"></div>
                                </div>
                            </div>
                        </div>
                        <div class="wishful-blog-demo-popup-overlay"></div>
                    </div>

                    <?php
                }
            }
        }

        /**
         * Demos popup ajax.
         *
         * @since 1.4.5
         */
        public static function ajax_demo_data() {

            if (!current_user_can('manage_options') || !wp_verify_nonce($_GET['demo_data_nonce'], 'get-demo-data')) {
                die('This action was stopped for security purposes.');
            }

            // Database reset url
            if (is_plugin_active('wordpress-database-reset/wp-reset.php')) {
                $plugin_link = admin_url('tools.php?page=database-reset');
            } else {
                $plugin_link = admin_url('plugin-install.php?s=WordPress+Database+Reset&tab=search');
            }

            // Get all demos
            $demos = self::get_demos_data();

            // Get selected demo
            if (isset($_GET['demo_name'])) {
                $demo = sanitize_text_field(wp_unslash($_GET['demo_name']));
            }

            // Get required plugins
            $plugins = $demos[$demo]['required_plugins'];

            // Get free plugins
            $free = $plugins['free'];

            // Get premium plugins
            $premium = $plugins['premium'];
            ?>

            <div id="wishful-blog-demo-plugins">

                <h2 class="title"><?php echo sprintf(esc_html__('Import the %1$s demo', 'wishful-companion'), esc_attr($demos[$demo]['demo_name'])); ?></h2>

                <div class="wishful-blog-popup-text">

                    <p><?php
                        echo
                        sprintf(
                                esc_html__('Importing demo data allow you to quickly edit everything instead of creating content from scratch. It is recommended uploading sample data on a fresh WordPress install to prevent conflicts with your current content. You can use this plugin to reset your site if needed: %1$sWordpress Database Reset%2$s.', 'wishful-companion'),
                                '<a href="' . esc_url($plugin_link) . '" target="_blank">',
                                '</a>'
                        );
                        ?></p>

                    <div class="wishful-blog-required-plugins-wrap">
                        <h3><?php esc_html_e('Required Plugins', 'wishful-companion'); ?></h3>
                        <p><?php esc_html_e('For your site to look exactly like this demo, the plugins below need to be activated.', 'wishful-companion'); ?></p>
                        <div class="wishful-blog-required-plugins oe-plugin-installer">
                            <?php
                            self::required_plugins($free, 'free');
                            self::required_plugins($premium, 'premium');
                            ?>
                        </div>
                    </div>

                </div>
                <?php if ( !defined('WISHFULBLOG_PRO_CURRENT_VERSION') && isset( $premium['0']['slug'] ) && $premium['0']['slug'] == 'wishfulblog-pro' ) { ?>
                    <div class="wishful-blog-button wishful-blog-plugins-pro">
                        <a href="https://www.wishfulthemes.com/themes/<?php echo esc_attr( $premium['0']['slug'] ); ?>/" target="_blank" >
                            <?php esc_html_e('Install and activate Wishful Blog PRO', 'wishful-companion'); ?>
                        </a>
                    </div>
                <?php } elseif (defined('WISHFULBLOG_PRO_CURRENT_VERSION') && !defined('ACTIVATED_LICENSE_PRO') && isset( $premium['0']['slug'] ) && $premium['0']['slug'] == 'wishfulblog-pro') { ?>
                    <div class="wishful-blog-button wishful-blog-plugins-pro">
                        <a href="<?php echo esc_url(network_admin_url('admin.php?page=wishfulblog-pro-license-page')) ?>" >
                            <?php esc_html_e('Activate Wishful Blog PRO license', 'wishful-companion'); ?>
                        </a>
                    </div>
                <?php } else { ?>
                    <div class="wishful-blog-button wishful-blog-plugins-next">
                        <a href="#">
                            <?php esc_html_e('Go to the next step', 'wishful-companion'); ?>
                        </a>
                    </div>
                <?php } ?>


            </div>

            <form method="post" id="wishful-blog-demo-import-form">

                <input id="wishful_blog_import_demo" type="hidden" name="wishful_blog_import_demo" value="<?php echo esc_attr($demo); ?>" />

                <div class="wishful-blog-demo-import-form-types">

                    <h2 class="title"><?php esc_html_e('Select what you want to import:', 'wishful-companion'); ?></h2>

                    <ul class="wishful-blog-popup-text">
                        <li>
                            <label for="wishful_blog_import_xml">
                                <input id="wishful_blog_import_xml" type="checkbox" name="wishful_blog_import_xml" checked="checked" />
                                <strong><?php esc_html_e('Import XML Data', 'wishful-companion'); ?></strong> (<?php esc_html_e('pages, posts, images, menus, etc...', 'wishful-companion'); ?>)
                            </label>
                        </li>

                        <li>
                            <label for="wishful_blog_theme_settings">
                                <input id="wishful_blog_theme_settings" type="checkbox" name="wishful_blog_theme_settings" checked="checked" />
                                <strong><?php esc_html_e('Import Customizer Settings', 'wishful-companion'); ?></strong>
                            </label>
                        </li>

                        <li>
                            <label for="wishful_blog_import_widgets">
                                <input id="wishful_blog_import_widgets" type="checkbox" name="wishful_blog_import_widgets" checked="checked" />
                                <strong><?php esc_html_e('Import Widgets', 'wishful-companion'); ?></strong>
                            </label>
                        </li>
                    </ul>

                </div>

                <?php wp_nonce_field('wishful_blog_import_demo_data_nonce', 'wishful_blog_import_demo_data_nonce'); ?>
                <input type="submit" name="submit" class="wishful-blog-button wishful-blog-import" value="<?php esc_html_e('Install this demo', 'wishful-companion'); ?>"  />

            </form>

            <div class="wishful-blog-loader">
                <h2 class="title"><?php esc_html_e('The import process could take some time, please be patient', 'wishful-companion'); ?></h2>
                <div class="wishful-blog-import-status wishful-blog-popup-text"></div>
            </div>

            <div class="wishful-blog-last">
                <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52"><circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"></circle><path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"></path></svg>
                <h3><?php esc_html_e('Demo Imported!', 'wishful-companion'); ?></h3>
                <a href="<?php echo esc_url(get_home_url()); ?>" target="_blank"><?php esc_html_e('See the result', 'wishful-companion'); ?></a>
            </div>

            <?php
            die();
        }

        /**
         * Required plugins.
         *
         * @since 1.4.5
         */
        public static function required_plugins($plugins, $return) {
            if ( ! empty( $plugins ) && ! empty( $return ) ) {
                foreach ($plugins as $key => $plugin) {
    
                    $api = array(
                        'slug' => isset($plugin['slug']) ? $plugin['slug'] : '',
                        'init' => isset($plugin['init']) ? $plugin['init'] : '',
                        'name' => isset($plugin['name']) ? $plugin['name'] : '',
                    );
    
                    if (!is_wp_error($api)) { // confirm error free
                        // Installed but Inactive.
                        if (file_exists(WP_PLUGIN_DIR . '/' . $plugin['init']) && is_plugin_inactive($plugin['init'])) {
    
                            $button_classes = 'button activate-now button-primary';
                            $button_text = esc_html__('Activate', 'wishful-companion');
    
                            // Not Installed.
                        } elseif (!file_exists(WP_PLUGIN_DIR . '/' . $plugin['init'])) {
    
                            $button_classes = 'button install-now';
                            $button_text = esc_html__('Install Now', 'wishful-companion');
    
                            // Active.
                        } else {
                            $button_classes = 'button disabled';
                            $button_text = esc_html__('Activated', 'wishful-companion');
                        }
                        ?>
    
                        <div class="wishful-blog-plugin wishful-blog-clr wishful-blog-plugin-<?php echo esc_attr($api['slug']); ?>" data-slug="<?php echo esc_attr($api['slug']); ?>" data-init="<?php echo esc_attr($api['init']); ?>">
                            <h2><?php echo esc_html($api['name']); ?></h2>
    
                            <?php
                            // If premium plugins and not installed
                            if ('premium' == $return && !file_exists(WP_PLUGIN_DIR . '/' . $plugin['init'])) {
                                ?>
                                <a class="button" href="https://wishfulthemes.com/themes/<?php echo esc_attr($api['slug']); ?>/" target="_blank"><?php esc_html_e('Get This Addon', 'wishful-companion'); ?></a>
                            <?php } else { ?>
                                <button class="<?php echo esc_attr($button_classes); ?>" data-init="<?php echo esc_attr($api['init']); ?>" data-slug="<?php echo esc_attr($api['slug']); ?>" data-name="<?php echo esc_attr($api['name']); ?>"><?php echo esc_html($button_text); ?></button>
                            <?php } ?>
                        </div>
    
                        <?php
                    }
                }
            }
        }

        /**
         * Required plugins activate
         *
         * @since 1.4.5
         */
        public function ajax_required_plugins_activate() {

            if (!current_user_can('install_plugins') || !isset($_POST['init']) || !$_POST['init']) {
                wp_send_json_error(
                        array(
                            'success' => false,
                            'message' => __('No plugin specified', 'wishful-companion'),
                        )
                );
            }

            $plugin_init = ( isset($_POST['init']) ) ? esc_attr($_POST['init']) : '';
            $activate = activate_plugin($plugin_init, '', false, true);

            if (is_wp_error($activate)) {
                wp_send_json_error(
                        array(
                            'success' => false,
                            'message' => $activate->get_error_message(),
                        )
                );
            }

            wp_send_json_success(
                    array(
                        'success' => true,
                        'message' => __('Plugin Successfully Activated', 'wishful-companion'),
                    )
            );
        }

        /**
         * Returns an array containing all the importable content
         *
         * @since 1.4.5
         */
        public function ajax_get_import_data() {
            if (!current_user_can('manage_options')) {
                die('This action was stopped for security purposes.');
            }
            check_ajax_referer('wishful_blog_import_data_nonce', 'security');

            echo json_encode(
                    array(
                        array(
                            'input_name' => 'wishful_blog_import_xml',
                            'action' => 'wishful_blog_ajax_import_xml',
                            'method' => 'ajax_import_xml',
                            'loader' => esc_html__('Importing XML Data', 'wishful-companion')
                        ),
                        array(
                            'input_name' => 'wishful_blog_theme_settings',
                            'action' => 'wishful_blog_ajax_import_theme_settings',
                            'method' => 'ajax_import_theme_settings',
                            'loader' => esc_html__('Importing Customizer Settings', 'wishful-companion')
                        ),
                        array(
                            'input_name' => 'wishful_blog_import_widgets',
                            'action' => 'wishful_blog_ajax_import_widgets',
                            'method' => 'ajax_import_widgets',
                            'loader' => esc_html__('Importing Widgets', 'wishful-companion')
                        ),
                    )
            );

            die();
        }

        /**
         * Import XML file
         *
         * @since 1.4.5
         */
        public function ajax_import_xml() {
            if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wishful_blog_import_demo_data_nonce'], 'wishful_blog_import_demo_data_nonce')) {
                die('This action was stopped for security purposes.');
            }

            // Get the selected demo
            if (isset($_POST['wishful_blog_import_demo'])) {
                $demo_type = sanitize_text_field(wp_unslash($_POST['wishful_blog_import_demo']));
            }

            // Get demos data
            $demo = Wishful_Blog_Extra_Demos::get_demos_data()[$demo_type];

            // Content file
            $xml_file = isset($demo['xml_file']) ? $demo['xml_file'] : '';

            $sample_page = get_page_by_title('Sample Page', OBJECT, 'page');
            $helloWorld_post = get_page_by_title('Hello world!', OBJECT, 'post');

            // Delete the default post and page by forcfully
            if( is_object( $sample_page ) ) {
                wp_delete_post( $sample_page->ID, true );
            }

            //force to delete helloworld default post
            if( is_object( $helloWorld_post ) ) {
                wp_delete_post( $helloWorld_post->ID, true );
            }

            $sidebars_widgets = get_option( 'sidebars_widgets' );

            foreach( $sidebars_widgets as $keys => $values ) {

                if( is_array( $values ) ) {

                    foreach( $values as $key_val => $val ) {

                        $sidebars_widgets[$keys][$key_val] = null;

                        update_option( 'sidebars_widgets', $sidebars_widgets );
                    }
                }
            }

            // Import Posts, Pages, Images, Menus.
            $result = $this->process_xml($xml_file);

            if (is_wp_error($result)) {
                echo json_encode($result->errors);
            } else {
                echo 'successful import';
            }

            die();
        }

        /**
         * Import customizer settings
         *
         * @since 1.4.5
         */
        public function ajax_import_theme_settings() {
            if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wishful_blog_import_demo_data_nonce'], 'wishful_blog_import_demo_data_nonce')) {
                die('This action was stopped for security purposes.');
            }

            // Include settings importer
            include WISHFUL_COMPANION_PATH . 'includes/panel/classes/importers/class-settings-importer.php';

            // Get the selected demo
            if (isset($_POST['wishful_blog_import_demo'])) {
                $demo_type = sanitize_text_field(wp_unslash($_POST['wishful_blog_import_demo']));
            }

            // Get demos data
            $demo = Wishful_Blog_Extra_Demos::get_demos_data()[$demo_type];

            // Settings file
            $theme_settings = isset($demo['theme_settings']) ? $demo['theme_settings'] : '';

            // Import settings.
            $settings_importer = new wishful_blog_Settings_Importer();
            $result = $settings_importer->process_import_file($theme_settings);

            if (is_wp_error($result)) {
                echo json_encode($result->errors);
            } else {
                echo 'successful import';
            }

            die();
        }

        /**
         * Import widgets
         *
         * @since 1.4.5
         */
        public function ajax_import_widgets() {
            if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wishful_blog_import_demo_data_nonce'], 'wishful_blog_import_demo_data_nonce')) {
                die('This action was stopped for security purposes.');
            }

            // Include widget importer
            include WISHFUL_COMPANION_PATH . 'includes/panel/classes/importers/class-widget-importer.php';

            // Get the selected demo
            if (isset($_POST['wishful_blog_import_demo'])) {
                $demo_type = sanitize_text_field(wp_unslash($_POST['wishful_blog_import_demo']));
            }

            // Get demos data
            $demo = Wishful_Blog_Extra_Demos::get_demos_data()[$demo_type];

            // Widgets file
            $widgets_file = isset($demo['widgets_file']) ? $demo['widgets_file'] : '';

            // Import settings.
            $widgets_importer = new Wishful_Blog_Widget_Importer();
            $result = $widgets_importer->process_import_file($widgets_file);

            if (is_wp_error($result)) {
                echo json_encode($result->errors);
            } else {
                echo 'successful import';
            }

            die();
        }

        /**
         * After import
         *
         * @since 1.4.5
         */
        public function ajax_after_import() {
            if (!current_user_can('manage_options') || !wp_verify_nonce($_POST['wishful_blog_import_demo_data_nonce'], 'wishful_blog_import_demo_data_nonce')) {
                die('This action was stopped for security purposes.');
            }

            // If XML file is imported
            if ($_POST['wishful_blog_import_is_xml'] === 'true') {

                // Get the selected demo
                if (isset($_POST['wishful_blog_import_demo'])) {
                    $demo_type = sanitize_text_field(wp_unslash($_POST['wishful_blog_import_demo']));
                }

                // Get demos data
                $demo = Wishful_Blog_Extra_Demos::get_demos_data()[$demo_type];

                // Current demo template
                $demo_template = isset($demo['demo_template']) ? $demo['demo_template'] : '';

                // Current demo slug
                $demo_slug = isset($demo['demo_slug']) ? $demo['demo_slug'] : '';

                // Elementor width setting
                $elementor_width = isset($demo['elementor_width']) ? $demo['elementor_width'] : '';

                // Reading settings
                $homepage_title = isset($demo['home_title']) ? $demo['home_title'] : '';
                $blog_title = isset($demo['blog_title']) ? $demo['blog_title'] : '';

                // Posts to show on the blog page
                $posts_to_show = isset($demo['posts_to_show']) ? $demo['posts_to_show'] : '';

                // If shop demo
                $shop_demo = isset($demo['is_shop']) ? $demo['is_shop'] : false;

                // Product image size
                $image_size = isset($demo['woo_image_size']) ? $demo['woo_image_size'] : '';
                $thumbnail_size = isset($demo['woo_thumb_size']) ? $demo['woo_thumb_size'] : '';
                $crop_width = isset($demo['woo_crop_width']) ? $demo['woo_crop_width'] : '';
                $crop_height = isset($demo['woo_crop_height']) ? $demo['woo_crop_height'] : '';

                // Assign WooCommerce pages if WooCommerce Exists
                if (class_exists('WooCommerce') && true == $shop_demo) {

                    $woopages = array(
                        'woocommerce_shop_page_id' => 'Shop',
                        'woocommerce_cart_page_id' => 'Cart',
                        'woocommerce_checkout_page_id' => 'Checkout',
                        'woocommerce_pay_page_id' => 'Checkout &#8594; Pay',
                        'woocommerce_thanks_page_id' => 'Order Received',
                        'woocommerce_myaccount_page_id' => 'My Account',
                        'woocommerce_edit_address_page_id' => 'Edit My Address',
                        'woocommerce_view_order_page_id' => 'View Order',
                        'woocommerce_change_password_page_id' => 'Change Password',
                        'woocommerce_logout_page_id' => 'Logout',
                        'woocommerce_lost_password_page_id' => 'Lost Password'
                    );

                    foreach ($woopages as $woo_page_name => $woo_page_title) {

                        $woopage = get_page_by_title($woo_page_title);
                        if (isset($woopage) && $woopage->ID) {
                            update_option($woo_page_name, $woopage->ID);
                        }
                    }

                    // We no longer need to install pages
                    delete_option('_wc_needs_pages');
                    delete_transient('_wc_activation_redirect');

                    // Get products image size
                    update_option('woocommerce_single_image_width', $image_size);
                    update_option('woocommerce_thumbnail_image_width', $thumbnail_size);
                    update_option('woocommerce_thumbnail_cropping', 'custom');
                    update_option('woocommerce_thumbnail_cropping_custom_width', $crop_width);
                    update_option('woocommerce_thumbnail_cropping_custom_height', $crop_height);
                }

                //navigation menu name and id
                $main_nav_name    = isset($demo['main_nav_name']) ? $demo['main_nav_name'] : 'Main Menu';
                $main_nav_id      = isset($demo['main_nav_id']) ? $demo['main_nav_id'] : '';
                $top_nav_name     = isset($demo['top_nav_name']) ? $demo['top_nav_name'] : 'Top Menu';
                $top_nav_id       = isset($demo['top_nav_id']) ? $demo['top_nav_id'] : '';
                $footer_nav_name  = isset($demo['footer_nav_name']) ? $demo['footer_nav_name'] : 'Footer Menu';
                $footer_nav_id    = isset($demo['footer_nav_id']) ? $demo['footer_nav_id'] : '';

                // Set imported menus to registered theme locations
                $main_menu  	= get_term_by('name', $main_nav_name, 'nav_menu');
                $top_menu       = get_term_by('name', $top_nav_name, 'nav_menu');
                $footer_menu    = get_term_by('name', $footer_nav_name, 'nav_menu');

                set_theme_mod(
                    'nav_menu_locations',
                    array(
                        $main_nav_id   => $main_menu->term_id,
                        $top_nav_id   => $top_menu->term_id,
                        $footer_nav_id => $footer_menu->term_id,
                    )
                );

                if( !empty( $demo_slug ) ) {

                    switch( $demo_slug ) {

                        case 'wishful-blog':

                            if( !empty( $demo_template ) ) {

                                switch( $demo_template ) {

                                    case 'free-demo-one':

                                        $travel_category = get_term_by( 'slug', 'travel', 'category' );
                                        $travel_category_id = $travel_category->term_id;

                                        set_theme_mod( 'wishful_blog_banner_posts_categories', $travel_category_id );
                                        break;

                                    case 'free-demo-two':

                                        $fruits_category = get_term_by( 'slug', 'fruits', 'category' );
                                        $fruits_category_id = $fruits_category->term_id;

                                        set_theme_mod( 'wishful_blog_banner_posts_categories', $fruits_category_id );
                                        break;

                                    case 'free-demo-three':

                                        set_theme_mod( 'wishful_blog_banner_posts_categories', 0 );

                                        $author_widget = get_option( 'widget_wishful_blog_author_widget' );

                                        $author_page_one = get_page_by_title( 'Beauty Andy', OBJECT, 'page' );
                                        $author_page_two = get_page_by_title( 'About Fashion', OBJECT, 'page' );

                                        $author_widget[1]['author_page'] = $author_page_one->ID;
                                        $author_widget[2]['author_page'] = $author_page_two->ID;

                                        update_option( 'widget_wishful_blog_author_widget', $author_widget );
                                        break;

                                    case 'free-demo-four':

                                        $weekend_category = get_term_by( 'slug', 'weekend-break', 'category' );
                                        $weekend_category_id = $weekend_category->term_id;

                                        set_theme_mod( 'wishful_blog_banner_posts_categories', $weekend_category_id );

                                        $author_widget = get_option( 'widget_wishful_blog_author_widget' );

                                        $author_page_one = get_page_by_title( 'Beauty Andy', OBJECT, 'page' );

                                        $author_widget[1]['author_page'] = $author_page_one->ID;

                                        update_option( 'widget_wishful_blog_author_widget', $author_widget );
                                        break;

                                    case 'free-demo-five':

                                        $nature_category = get_term_by( 'slug', 'nature', 'category' );
                                        $nature_category_id = $nature_category->term_id;

                                        set_theme_mod( 'wishful_blog_banner_posts_categories', $nature_category_id );
                                        break;

                                    case 'child-free-demo-one':

                                        $refreshment_category = get_term_by( 'slug', 'refreshment', 'category' );
                                        $refreshment_category_id = $refreshment_category->term_id;

                                        set_theme_mod( 'wishful_blog_banner_posts_categories', $refreshment_category_id );

                                        $banner_image = get_page_by_title( 'banner', OBJECT, 'attachment' );
                                        $banner_image_url = wp_get_attachment_url( $banner_image->ID );

                                        set_theme_mod( 'wishful_travel_banner_hero_image', $banner_image_url );
                                        break;

                                    case 'pro-demo-one':

                                        $asia_category = get_term_by( 'slug', 'asia', 'category' );
                                        $asia_category_id = $asia_category->term_id;

                                        set_theme_mod( 'wishful_blog_banner_posts_categories', $asia_category_id );

                                        $pro_ad_widget = get_option( 'widget_wishfulblog_pro_ad_widget' );

                                        $sidebar_ad_image = get_page_by_title( 'wishful-sidebar', OBJECT, 'attachment' );
                                        $sidebar_ad_image_url = wp_get_attachment_url( $sidebar_ad_image->ID );

                                        $full_ad_image = get_page_by_title( 'full-size', OBJECT, 'attachment' );
                                        $full_ad_image_url = wp_get_attachment_url( $full_ad_image->ID );

                                        $half_ad_image = get_page_by_title( 'ad1', OBJECT, 'attachment' );
                                        $half_ad_image_url = wp_get_attachment_url( $half_ad_image->ID );

                                        $pro_ad_widget[1]['image_url'] = $sidebar_ad_image_url;
                                        $pro_ad_widget[2]['image_url'] = $full_ad_image_url;
                                        $pro_ad_widget[3]['image_url'] = $half_ad_image_url;
                                        $pro_ad_widget[4]['image_url'] = $full_ad_image_url;
                                        $pro_ad_widget[5]['image_url'] = $full_ad_image_url;
                                        $pro_ad_widget[6]['image_url'] = $full_ad_image_url;
                                        $pro_ad_widget[7]['image_url'] = $full_ad_image_url;

                                        update_option( 'widget_wishfulblog_pro_ad_widget', $pro_ad_widget );

                                        break;

                                    case 'pro-demo-two':

                                        $vintage_category = get_term_by( 'slug', 'vintage', 'category' );
                                        $vintage_category_id = $vintage_category->term_id;

                                        set_theme_mod( 'wishful_blog_banner_posts_categories', $vintage_category_id );

                                        $pro_ad_widget = get_option( 'widget_wishfulblog_pro_ad_widget' );

                                        $sidebar_ad_image = get_page_by_title( 'side-ad3', OBJECT, 'attachment' );
                                        $sidebar_ad_image_url = wp_get_attachment_url( $sidebar_ad_image->ID );

                                        $full_ad_image = get_page_by_title( 'full-size', OBJECT, 'attachment' );
                                        $full_ad_image_url = wp_get_attachment_url( $full_ad_image->ID );

                                        $half_ad_image = get_page_by_title( 'wishful-ad2', OBJECT, 'attachment' );
                                        $half_ad_image_url = wp_get_attachment_url( $half_ad_image->ID );

                                        $pro_ad_widget[1]['image_url'] = $sidebar_ad_image_url;
                                        $pro_ad_widget[2]['image_url'] = $full_ad_image_url;
                                        $pro_ad_widget[3]['image_url'] = $full_ad_image_url;
                                        $pro_ad_widget[4]['image_url'] = $half_ad_image_url;
                                        $pro_ad_widget[5]['image_url'] = $full_ad_image_url;
                                        $pro_ad_widget[6]['image_url'] = $full_ad_image_url;

                                        update_option( 'widget_wishfulblog_pro_ad_widget', $pro_ad_widget );

                                        break;

                                    case 'pro-demo-three':

                                        $fashion_category = get_term_by( 'slug', 'fashion', 'category' );
                                        $fashion_category_id = $fashion_category->term_id;

                                        set_theme_mod( 'wishful_blog_banner_posts_categories', $fashion_category_id );

                                        $nature_category = get_term_by( 'slug', 'nature', 'category' );
                                        $nature_category_id = $nature_category->term_id;
                                        $food_category = get_term_by( 'slug', 'food', 'category' );
                                        $food_category_id = $food_category->term_id;

                                        $post_widget_one = get_option( 'widget_wishful_blog_post_widget_layout_one' );

                                        $post_widget_one[1]['select_cat'] = $nature_category_id;
                                        $post_widget_one[2]['select_cat'] = $food_category_id;

                                        update_option( 'widget_wishful_blog_post_widget_layout_one', $post_widget_one );

                                        $travel_category = get_term_by( 'slug', 'travel', 'category' );
                                        $travel_category_id = $travel_category->term_id;

                                        $post_widget_two = get_option( 'widget_wishful_blog_post_widget_layout_two' );

                                        $post_widget_two[1]['select_cat'] = $travel_category_id;
                                        $post_widget_two[2]['select_cat'] = $travel_category_id;

                                        update_option( 'widget_wishful_blog_post_widget_layout_two', $post_widget_two );

                                        $pro_ad_widget = get_option( 'widget_wishfulblog_pro_ad_widget' );

                                        $sidebar_ad_image = get_page_by_title( 'wishful-side-ad3', OBJECT, 'attachment' );
                                        $sidebar_ad_image_url = wp_get_attachment_url( $sidebar_ad_image->ID );

                                        $full_ad_image = get_page_by_title( 'full-size', OBJECT, 'attachment' );
                                        $full_ad_image_url = wp_get_attachment_url( $full_ad_image->ID );

                                        $pro_ad_widget[1]['image_url'] = $sidebar_ad_image_url;
                                        $pro_ad_widget[2]['image_url'] = $full_ad_image_url;
                                        $pro_ad_widget[3]['image_url'] = $full_ad_image_url;
                                        $pro_ad_widget[4]['image_url'] = $full_ad_image_url;
                                        $pro_ad_widget[5]['image_url'] = $full_ad_image_url;
                                        $pro_ad_widget[6]['image_url'] = $full_ad_image_url;
                                        $pro_ad_widget[7]['image_url'] = $full_ad_image_url;

                                        update_option( 'widget_wishfulblog_pro_ad_widget', $pro_ad_widget );

                                        //get attachment ids by attachment name
                                        $cat_image1 = get_page_by_title( '59', OBJECT, 'attachment' );
                                        $cat_image1_id = $cat_image1->ID;

                                        $cat_image2 = get_page_by_title( '9', OBJECT, 'attachment' );
                                        $cat_image2_id = $cat_image2->ID;

                                        $cat_image3 = get_page_by_title( '58', OBJECT, 'attachment' );
                                        $cat_image3_id = $cat_image3->ID;

                                        $cat_image4 = get_page_by_title( '59', OBJECT, 'attachment' );
                                        $cat_image4_id = $cat_image4->ID;

                                        $cat_image5 = get_page_by_title( '46', OBJECT, 'attachment' );
                                        $cat_image5_id = $cat_image5->ID;

                                        $cat_image_array = array(
                                            $cat_image1_id,
                                            $cat_image2_id,
                                            $cat_image3_id,
                                            $cat_image4_id,
                                            $cat_image5_id,
                                        );

                                        $pro_cat_widget = get_option( 'widget_wishfulblog_pro_category_widget' );

                                        $pro_cat_widget_cats = $pro_cat_widget[1]['categories'];

                                        foreach( $pro_cat_widget_cats as $widget_key => $widget_value ) {

                                            foreach( $cat_image_array as $cat_image_key => $cat_image_value ) {

                                                $category_term = get_term_by( 'slug', $widget_value, 'category' );
                                                $category_term_id = $category_term->term_id;

                                                if( $widget_key == $cat_image_key ) {

                                                    update_term_meta( $category_term_id, 'wishfulblog-pro-category-image-id', $cat_image_value );
                                                }
                                            }
                                        }

                                        break;

                                    case 'pro-demo-four':

                                        $drinks_category = get_term_by( 'slug', 'drinks', 'category' );
                                        $drinks_category_id = $drinks_category->term_id;

                                        set_theme_mod( 'wishful_blog_banner_posts_categories', $drinks_category_id );

                                        $pro_ad_widget = get_option( 'widget_wishfulblog_pro_ad_widget' );

                                        $sidebar_ad_image = get_page_by_title( 'sidebar1', OBJECT, 'attachment' );
                                        $sidebar_ad_image_url = wp_get_attachment_url( $sidebar_ad_image->ID );

                                        $full_ad_image = get_page_by_title( 'full-size', OBJECT, 'attachment' );
                                        $full_ad_image_url = wp_get_attachment_url( $full_ad_image->ID );

                                        $pro_ad_widget[1]['image_url'] = $sidebar_ad_image_url;
                                        $pro_ad_widget[2]['image_url'] = $full_ad_image_url;

                                        update_option( 'widget_wishfulblog_pro_ad_widget', $pro_ad_widget );

                                        $dessert_category = get_term_by( 'slug', 'dessert', 'category' );
                                        $dessert_category_id = $dessert_category->term_id;

                                        $homepage_post_widget = get_option( 'widget_wishful_blog_homepage_post_widget' );

                                        $homepage_post_widget[1]['select_cat'] = $dessert_category_id;

                                        update_option( 'widget_wishful_blog_homepage_post_widget', $homepage_post_widget );

                                        break;

                                    default:
                                        //nothing to do
                                }
                            }
                            break;

                        default:
                            //nothing to do
                    }
                }

                // Disable Elementor default settings
                //update_option( 'elementor_disable_color_schemes', 'yes' );
                //update_option( 'elementor_disable_typography_schemes', 'yes' );
                if (!empty($elementor_width)) {
                    update_option('elementor_container_width', $elementor_width);
                }

                // Assign front page and posts page (blog page).
                $home_page = get_page_by_title($homepage_title);
                $blog_page = get_page_by_title($blog_title);

                update_option('show_on_front', 'page');

                if (is_object($home_page)) {
                    update_option('page_on_front', $home_page->ID);
                }

                if (is_object($blog_page)) {
                    update_option('page_for_posts', $blog_page->ID);
                }

                // Posts to show on the blog page
                if (!empty($posts_to_show)) {
                    update_option('posts_per_page', $posts_to_show);
                }

                flush_rewrite_rules();

            }

            die();
        }

        /**
         * Import XML data
         *
         * @since 1.0.0
         */
        public function process_xml($file) {

            $response = wishful_blog_Demos_Helpers::get_remote($file);

            // No sample data found
            if ($response === false) {
                return new WP_Error('xml_import_error', __('Can not retrieve sample data xml file. The server may be down at the moment please try again later. If you still have issues contact the theme developer for assistance.', 'wishful-companion'));
            }

            // Write sample data content to temp xml file
            $temp_xml = WISHFUL_COMPANION_PATH . 'includes/panel/classes/importers/temp.xml';
            file_put_contents($temp_xml, $response);

            // Set temp xml to attachment url for use
            $attachment_url = $temp_xml;

            // If file exists lets import it
            if (file_exists($attachment_url)) {
                $this->import_xml($attachment_url);
            } else {
                // Import file can't be imported - we should die here since this is core for most people.
                return new WP_Error('xml_import_error', __('The xml import file could not be accessed. Please try again or contact the theme developer.', 'wishful-companion'));
            }
        }

        /**
         * Import XML file
         *
         * @since 1.0.0
         */
        private function import_xml($file) {

            // Make sure importers constant is defined
            if (!defined('WP_LOAD_IMPORTERS')) {
                define('WP_LOAD_IMPORTERS', true);
            }

            // Import file location
            $import_file = ABSPATH . 'wp-admin/includes/import.php';

            // Include import file
            if (!file_exists($import_file)) {
                return;
            }

            // Include import file
            require_once( $import_file );

            // Define error var
            $importer_error = false;

            if (!class_exists('WP_Importer')) {
                $class_wp_importer = ABSPATH . 'wp-admin/includes/class-wp-importer.php';

                if (file_exists($class_wp_importer)) {
                    require_once $class_wp_importer;
                } else {
                    $importer_error = __('Can not retrieve class-wp-importer.php', 'wishful-companion');
                }
            }

            if (!class_exists('WP_Import')) {
                $class_wp_import = WISHFUL_COMPANION_PATH . 'includes/panel/classes/importers/class-wordpress-importer.php';

                if (file_exists($class_wp_import)) {
                    require_once $class_wp_import;
                } else {
                    $importer_error = __('Can not retrieve wordpress-importer.php', 'wishful-companion');
                }
            }

            // Display error
            if ($importer_error) {
                return new WP_Error('xml_import_error', $importer_error);
            } else {

                // No error, lets import things...
                if (!is_file($file)) {
                    $importer_error = __('Sample data file appears corrupt or can not be accessed.', 'wishful-companion');
                    return new WP_Error('xml_import_error', $importer_error);
                } else {
                    $importer = new WP_Import();
                    $importer->fetch_attachments = true;
                    $importer->import($file);

                    // Clear sample data content from temp xml file
                    $temp_xml = WISHFUL_COMPANION_PATH . 'includes/panel/classes/importers/temp.xml';
                    file_put_contents($temp_xml, '');
                }
            }
        }

    }

}
new Wishful_Blog_Extra_Demos();
