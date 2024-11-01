<?php
/**
 * Theme Wizard
 *
 * @package Wishful_Blog_Extra_Demo_Import
 * @category Core
 * @author WishfulThemes
 */
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('Wishful_Blog_Extra_Demo_Import_Theme_Wizard')):

    // Start Class
    class Wishful_Blog_Extra_Demo_Import_Theme_Wizard
    {

        /**
         * Current step
         *
         * @var string
         */
        private $step = '';

        /**
         * Steps for the setup wizard
         *
         * @var array
         */
        private $steps = array();

        public function __construct()
        {
            $this->includes();
            add_action('admin_menu', array($this, 'add_wishful_blog_wizzard_menu'));
            add_action('admin_init', array($this, 'wishful_blog_wizzard_setup'), 99);
            add_action('wp_loaded', array($this, 'remove_notice'));
            add_action('admin_print_styles', array($this, 'add_notice'));
            add_action("add_second_notice", array($this, "install"));
        }

        public static function install()
        {
            if (!get_option("wishful_blog_wizzard")) {
                update_option("wishful_blog_wizzard", "un-setup");
                (wp_safe_redirect(admin_url('admin.php?page=wishful_blog_setup')));
            } else {
                // first run for automatic message after first 24 hour
                if (!get_option("automatic_2nd_notice")) {
                    update_option("automatic_2nd_notice", "second-time");
                } else {
                    // clear cronjob after second 24 hour
                    wp_clear_scheduled_hook('add_second_notice');
                    delete_option("automatic_2nd_notice");
                    delete_option("2nd_notice");
                    delete_option("wishful_blog_wizzard");
                    wp_safe_redirect(admin_url());
                    exit;
                }
            }
        }

        // clear cronjob when deactivate plugin
        public static function uninstall()
        {
            wp_clear_scheduled_hook('add_second_notice');
            delete_option("automatic_2nd_notice");
            delete_option("2nd_notice");
            delete_option("wishful_blog_wizzard");
        }

        public function remove_notice()
        {
            if (isset($_GET['wishful_blog_wizzard_hide_notice']) && $_GET['wishful_blog_wizzard_hide_notice'] == "install") { // WPCS: input var ok, CSRF ok.
                // when finish install
                delete_option("wishful_blog_wizzard");
                //clear cronjob when finish install
                wp_clear_scheduled_hook('add_second_notice');
                delete_option("2nd_notice");
                if (isset($_GET['show'])) {
                    wp_safe_redirect(home_url());
                    exit;
                }
            } else if (isset($_GET['wishful_blog_wizzard_hide_notice']) && $_GET['wishful_blog_wizzard_hide_notice'] == "2nd_notice") { // WPCS: input var ok, CSRF ok.
                //when skip install
                delete_option("wishful_blog_wizzard");
                if (!get_option("2nd_notice")) {
                    update_option("2nd_notice", "second-time");
                    date_default_timezone_set(get_option('timezone_string'));
                    // set time for next day
                    $new_time_format = time() + (24 * 60 * 60);
                    //add "add_second_notice" cronjob
                    if (!wp_next_scheduled('add_second_notice')) {
                        wp_schedule_event($new_time_format, 'daily', 'add_second_notice');
                    }
                } else {
                    //clear cronjob when skip for second time
                    wp_clear_scheduled_hook('add_second_notice');
                }
                if (isset($_GET['show'])) {
                    wp_safe_redirect(home_url());
                    exit;
                } else {
                    wp_safe_redirect(admin_url());
                    exit;
                }
            }
        }

        public function add_notice()
        {
            if ((get_option("wishful_blog_wizzard") == "un-setup") && (empty($_GET['page']) || 'wishful_blog_setup' !== $_GET['page'])) {
                if (!get_option("2nd_notice") && !get_option("automatic_2nd_notice")) {
                    ?>
                    <div class="updated notice-success wishful-companion-notice">
                        <div class="notice-inner">
                            <div class="notice-content">
                                <p><?php esc_html_e('Are you ready to create an amazing website?', 'wishful-companion'); ?></p>
                                <p class="submit">
                                    <a href="<?php echo esc_url(admin_url('admin.php?page=wishful_blog_setup')); ?>"
                                       class="btn button-primary"><?php esc_html_e('Run the Setup Wizard', 'wishful-companion'); ?></a>
                                    <a class="btn button-secondary"
                                       href="<?php echo esc_url((add_query_arg('wishful_blog_wizzard_hide_notice', '2nd_notice'))); ?>"><?php esc_html_e('Skip setup', 'wishful-companion'); ?></a>
                                </p>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            }
        }

        private function includes()
        {
            require_once(WISHFUL_COMPANION_PATH . '/includes/wizard/classes/QuietSkin.php');
            require_once(WISHFUL_COMPANION_PATH . '/includes/wizard/classes/WizardAjax.php');
        }

        public function add_wishful_blog_wizzard_menu()
        {
            add_dashboard_page('', '', 'manage_options', 'wishful_blog_setup', '');
        }

        public function wishful_blog_wizzard_setup()
        {
            if (!current_user_can('manage_options'))
                return;
            if (empty($_GET['page']) || 'wishful_blog_setup' !== $_GET['page']) { // WPCS: CSRF ok, input var ok.
                return;
            }
            $default_steps = array(
                'welcome' => array(
                    'name' => __('Welcome', 'wishful-companion'),
                    'view' => array($this, 'wishful_blog_welcome'),
                ),
                'demo' => array(
                    'name' => __('Choosing Demo', 'wishful-companion'),
                    'view' => array($this, 'wishful_blog_demo_setup'),
                ),
                'customize' => array(
                    'name' => __('Customize', 'wishful-companion'),
                    'view' => array($this, 'wishful_blog_customize_setup'),
                ),
                'ready' => array(
                    'name' => __('Ready', 'wishful-companion'),
                    'view' => array($this, 'wishful_blog_ready_setup'),
                )
            );
            $this->steps = apply_filters('wishful_blog_setup_wizard_steps', $default_steps);
            $this->step = isset($_GET['step']) ? sanitize_key($_GET['step']) : current(array_keys($this->steps)); // WPCS: CSRF ok, input var ok.
            // CSS
            wp_enqueue_style('wishful-blog-wizard-style', plugins_url('/assets/css/style.css', __FILE__));

            // RTL
            if (is_RTL()) {
                wp_enqueue_style('wishful-blog-wizard-rtl', plugins_url('/assets/css/rtl.css', __FILE__));
            }

            // JS
            wp_enqueue_script('wishful-blog-wizard-js', plugins_url('/assets/js/wizard.js', __FILE__), array('jquery', 'wp-util', 'updates'));

            wp_localize_script('wishful-blog-wizard-js', 'wishfulblogDemos', array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'demo_data_nonce' => wp_create_nonce('get-demo-data'),
                'wishful_blog_import_data_nonce' => wp_create_nonce('wishful_blog_import_data_nonce'),
                'content_importing_error' => esc_html__('There was a problem during the importing process resulting in the following error from your server:', 'wishful-companion'),
                'button_activating' => esc_html__('Activating', 'wishful-companion') . '&hellip;',
                'button_active' => esc_html__('Active', 'wishful-companion'),
            ));

            global $current_screen, $hook_suffix, $wp_locale;
            if (empty($current_screen))
                set_current_screen();
            $admin_body_class = preg_replace('/[^a-z0-9_-]+/i', '-', $hook_suffix);

            ob_start();
            ?>
            <!DOCTYPE html>
            <html <?php language_attributes(); ?>>
            <head>
                <meta name="viewport" content="width=device-width"/>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
                <title><?php esc_html_e('WishfulThemes &rsaquo; Setup Wizard', 'wishful-companion'); ?></title>
                <script type="text/javascript">
                    addLoadEvent = function (func) {
                        if (typeof jQuery != "undefined")
                            jQuery(document).ready(func);
                        else if (typeof wpOnload != 'function') {
                            wpOnload = func;
                        } else {
                            var oldonload = wpOnload;
                            wpOnload = function () {
                                oldonload();
                                func();
                            }
                        }
                    };
                    var ajaxurl = '<?php echo admin_url('admin-ajax.php', 'relative'); ?>',
                        pagenow = '<?php echo $current_screen->id; ?>',
                        typenow = '<?php echo $current_screen->post_type; ?>',
                        adminpage = '<?php echo $admin_body_class; ?>',
                        thousandsSeparator = '<?php echo addslashes($wp_locale->number_format['thousands_sep']); ?>',
                        decimalPoint = '<?php echo addslashes($wp_locale->number_format['decimal_point']); ?>',
                        isRtl = <?php echo (int)is_rtl(); ?>;
                </script>
                <?php
                //include demos script
                wp_print_scripts('wishful-blog-wizard-js');

                //include custom scripts in specifiec steps
                if ($this->step == 'demo' || $this->step == "welcome" || $this->step == 'customize') {
                    wp_print_styles('themes');
                    wp_print_styles('buttons');
                    wp_print_styles('dashboard');
                    wp_print_styles('common');
                }

                if ($this->step == 'customize') {
                    wp_print_styles('media');
                    wp_enqueue_media();
                    wp_enqueue_style('wp-color-picker');
                    wp_enqueue_script('wp-color-picker');
                }

                //add admin styles
                do_action('admin_print_styles');

                do_action('admin_head');
                ?>
            </head>
            <body class="wishful-blog-setup wp-core-ui">
            <?php $logo = '<a href="https://wishfulthemes.com/?utm_source=dash&utm_medium=wizard&utm_campaign=logo">WishfulThemes</a>'; ?>
            <div id="wishful-blog-logo"><?php echo $logo; ?></div>
            <?php
            $this->setup_wizard_steps();
            $this->setup_wizard_content();
            _wp_footer_scripts();
            do_action('admin_footer');
            ?>
            </body>
            </html>
            <?php
            exit;
        }

        /**
         * Output the steps.
         */
        public function setup_wizard_steps()
        {
            $output_steps = $this->steps;
            ?>
            <ol class="wishful-blog-setup-steps">
                <?php
                foreach ($output_steps as $step_key => $step) {
                    $is_completed = array_search($this->step, array_keys($this->steps), true) > array_search($step_key, array_keys($this->steps), true);

                    if ($step_key === $this->step) {
                        ?>
                        <li class="active"><?php echo esc_html($step['name']); ?></li>
                        <?php
                    } elseif ($is_completed) {
                        ?>
                        <li class="done">
                            <a href="<?php echo esc_url(add_query_arg('step', $step_key, remove_query_arg('activate_error'))); ?>"><?php echo esc_html($step['name']); ?></a>
                        </li>
                        <?php
                    } else {
                        ?>
                        <li><?php echo esc_html($step['name']); ?></li>
                        <?php
                    }
                }
                ?>
            </ol>
            <?php
        }

        /**
         * Output the content for the current step.
         */
        public function setup_wizard_content()
        {
            echo '<div class="wishful-blog-setup-content">';
            if (!empty($this->steps[$this->step]['view'])) {
                call_user_func($this->steps[$this->step]['view'], $this);
            }
            echo '</div>';
        }

        /**
         * Get Next Step
         * @param type $step
         * @return string
         */
        public function get_next_step_link($step = '')
        {
            if (!$step) {
                $step = $this->step;
            }

            $keys = array_keys($this->steps);
            if (end($keys) === $step) {
                return admin_url();
            }

            $step_index = array_search($step, $keys, true);
            if (false === $step_index) {
                return '';
            }

            return add_query_arg('step', $keys[$step_index + 1], remove_query_arg('activate_error'));
        }

        /**
         * Get Previous Step
         * @param type $step
         * @return string
         */
        public function get_previous_step_link($step = '')
        {

            if (!$step) {
                $step = $this->step;
            }

            $keys = array_keys($this->steps);

            $step_index = array_search($step, $keys, true);

            if (false === $step_index) {
                return '';
            }
            $url = FALSE;

            if (isset($keys[$step_index - 1])) {
                $url = add_query_arg('step', $keys[$step_index - 1], remove_query_arg('activate_error'));
            }
            return $url;
        }

        /**
         * Helper method to retrieve the current user's email address.
         *
         * @return string Email address
         */
        protected function get_current_user_email()
        {
            $current_user = wp_get_current_user();
            $user_email = $current_user->user_email;

            return $user_email;
        }

        /**
         * Step 1 Welcome
         */
        public function wishful_blog_welcome()
        {
            // Image
            $img = plugins_url('/assets/img/start.png', __FILE__);

            // Button icon
            if (is_RTL()) {
                $icon = 'left';
            } else {
                $icon = 'right';
            }
            ?>

            <div class="wishful-blog-welcome-wrap wishful-blog-wrap">
                <h2><?php esc_attr_e("Setup Wizard", 'wishful-companion'); ?></h2>
                <h1><?php esc_attr_e("Welcome!", 'wishful-companion'); ?></h1>
                <div class="wishful-blog-thumb">
                    <img src="<?php echo esc_url($img); ?>" width="425" height="290"/>
                </div>
                <p><?php esc_attr_e("Thank you for choosing Wishful Blog theme, in this quick setup wizard we'll take you through the 2 essential steps for you to get started building your dream website. Make sure to go through it to the end.", 'wishful-companion'); ?></p>
                <div class="wishful-blog-wizard-setup-actions">
                    <a class="skip-btn continue"
                       href="<?php echo $this->get_next_step_link(); ?>"><?php esc_attr_e("Get started", 'wishful-companion'); ?>
                        <i class="dashicons dashicons-arrow-<?php echo esc_attr($icon); ?>-alt"></i></a>
                </div>
                <a class="wishful-blog-setup-footer-links"
                   href="<?php echo esc_url((add_query_arg(array('wishful_blog_wizzard_hide_notice' => '2nd_notice'), admin_url()))); ?>"><?php esc_attr_e("Skip Setup Wizard", 'wishful-companion'); ?></a>
            </div>
            <?php
        }

        /**
         * Step 2 list demo
         */
        public function wishful_blog_demo_setup()
        {
            $demos = Wishful_Blog_Extra_Demos::get_demos_data();

            // Button icon
            if (is_RTL()) {
                $icon = 'left';
            } else {
                $icon = 'right';
            }
            ?>

            <div class="wishful-blog-demos-wrap wishful-blog-wrap">
                <div class="demo-import-loader preview-all"></div>
                <div class="demo-import-loader preview-icon"><i class="custom-loader"></i></div>

                <div class="wishful-blog-demo-wrap">
                    <h1><?php esc_attr_e("Selecting your demo template", 'wishful-companion'); ?></h1>
                    <p><?php
                        echo
                        sprintf(__('Clicking %1$sLive Preview%2$s will open the demo in a new window for you to decide which template to use. Then %1$sSelect%2$s the demo you want and click %1$sInstall Demo%2$s in the bottom.', 'wishful-companion'), '<strong>', '</strong>'
                        );
                        ?></p>
                    <div class="theme-browser rendered">

                        <?php $categories = Wishful_Blog_Extra_Demos::get_demo_all_categories($demos); ?>

                        <?php if (!empty($categories)) : ?>
                            <div class="wishful-blog-header-bar">
                                <nav class="wishful-blog-navigation">
                                    <ul>
                                        <li class="active"><a href="#all"
                                                              class="wishful-blog-navigation-link"><?php esc_html_e('All', 'wishful-companion'); ?></a>
                                        </li>
                                        <?php foreach ($categories as $key => $name) : ?>
                                            <li><a href="#<?php echo esc_attr($key); ?>"
                                                   class="wishful-blog-navigation-link"><?php echo esc_html($name); ?></a>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </nav>

                            </div>
                        <?php endif; ?>

                        <div class="themes wp-clearfix">

                            <?php
                            // Loop through all demos
                            foreach ($demos as $demo => $key) {

                                // Vars
                                $item_categories = Wishful_Blog_Extra_Demos::get_demo_item_categories($key);
                                ?>

                                <div class="theme-wrap" data-categories="<?php echo esc_attr($item_categories); ?>"
                                     data-name="<?php echo esc_attr(strtolower($key['demo_template'])); ?>">

                                    <div class="theme wishful-blog-open-popup"
                                         data-demo-id="<?php echo esc_attr(str_replace('-demo', '', $key['demo_template'])); ?>">

                                        <div class="theme-screenshot">
                                            <img src="<?php echo esc_url($key['screenshot']); ?>"/>

                                        </div>

                                        <div class="theme-id-container">

                                            <h2 class="theme-name"
                                                id="<?php echo esc_attr(str_replace('-demo', '', $key['demo_template'])); ?>">
                                                <span><?php echo esc_html($key['demo_name']); ?></span></h2>
                                            <div class="theme-actions">
                                                <a class="button button-primary"
                                                   href="<?php echo esc_attr( $key['demo_url'].$key['demo_slug']. '/'.$key['demo_template'] ); ?>/"
                                                   target="_blank"><?php esc_html_e('Live Preview', 'wishful-companion'); ?></a>
                                                <span class="button button-secondary"><?php esc_html_e('Select', 'wishful-companion'); ?></span>
                                            </div>
                                        </div>

                                    </div>

                                </div>

                            <?php } ?>

                        </div>
                        <div class="wishful-blog-wizard-setup-actions">
                            <button class="install-demos-button disabled" disabled
                                    data-next_step="<?php echo $this->get_next_step_link(); ?>"><?php esc_html_e("Install Demo", 'wishful-companion'); ?></button>
                            <a class="skip-btn"
                               href="<?php echo $this->get_next_step_link(); ?>"><?php esc_html_e("Skip Step", 'wishful-companion'); ?></a>
                        </div>
                    </div>

                </div>

                <div class="wishful-blog-wizard-setup-actions wizard-install-demos-buttons-wrapper final-step">
                    <a class="skip-btn continue"
                       href="<?php echo $this->get_next_step_link(); ?>"><?php esc_html_e("Next Step", 'wishful-companion'); ?>
                        <i class="dashicons dashicons-arrow-<?php echo esc_attr($icon); ?>-alt"></i></a>
                </div>
            </div>
            <?php
        }

        /**
         * Step 3 customize step
         */
        public function wishful_blog_customize_setup()
        {

            if (isset($_POST['save_step']) && !empty($_POST['save_step'])) {
                $this->save_wishful_blog_customize();
            }

            // Button icon
            if (is_RTL()) {
                $icon = 'left';
            } else {
                $icon = 'right';
            }
            ?>

            <div class="wishful-blog-customize-wrap wishful-blog-wrap">
                <form method="POST" name="wishful-blog-customize-form">
                    <?php wp_nonce_field('wishful_blog_customize_form'); ?>
                    <div class="field-group">
                        <?php
                        $custom_logo = get_theme_mod("custom_logo");
                        $display = "none";
                        $url = "";

                        if ($custom_logo) {
                            $display = "inline-block";
                            if (!($url = wp_get_attachment_image_url($custom_logo))) {
                                $custom_logo = "";
                                $display = "none";
                            }
                        }
                        ?>
                        <h1><?php esc_html_e("Logo", 'wishful-companion'); ?></h1>
                        <p><?php esc_html_e("Please add your logo below.", 'wishful-companion'); ?></p>
                        <div class="upload">
                            <img src="<?php echo $url; ?>" width="115px" height="115px" id="wishful-blog-logo-img"
                                 style="display:<?php echo $display; ?>;"/>
                            <div class="wishful-blog-logo-container">
                                <input type="hidden" name="wishful-blog-logo" id="wishful-blog-logo"
                                       value="<?php echo $custom_logo; ?>"/>
                                <button type="submit" data-name="wishful-blog-logo"
                                        class="upload_image_button button"><?php esc_html_e("Upload", 'wishful-companion'); ?></button>
                                <button style="display:<?php echo $display; ?>;" type="submit"
                                        data-name="wishful-blog-logo" class="remove_image_button button">&times;
                                </button>
                            </div>
                        </div>

                    </div>

                    <div class="field-group">
                        <h1><?php esc_html_e("Site Title", 'wishful-companion'); ?></h1>
                        <p><?php esc_html_e("Please add your Site Title below.", 'wishful-companion'); ?></p>
                        <input type="text" name="wishful-blog-site-title" id="wishful-blog-site-title"
                               class="wishful-blog-input" value="<?php echo get_option("blogname"); ?>">
                    </div>

                    <div class="field-group">
                        <h1><?php esc_html_e("Tagline", 'wishful-companion'); ?></h1>
                        <p><?php esc_html_e("Please add your Tagline below.", 'wishful-companion'); ?></p>
                        <input type="text" name="wishful-blog-tagline" id="wishful-blog-tagline"
                               class="wishful-blog-input" value="<?php echo get_option("blogdescription"); ?>">
                    </div>

                    <div class="field-group">

                        <?php
                        $favicon = get_option("site_icon");
                        $display = "none";
                        $url = "";

                        if ($favicon) {
                            $display = "inline-block";
                            $url = wp_get_attachment_image_url($favicon);
                            if (!($url = wp_get_attachment_image_url($favicon))) {
                                $favicon = "";
                                $display = "none";
                            }
                        }
                        ?>
                        <h1><?php esc_html_e("Site Icon", 'wishful-companion'); ?></h1>
                        <p><?php esc_html_e("Site Icons are what you see in browser tabs, bookmark bars, and within the WordPress mobile apps. Upload one here! Site Icons should be square and at least 512 × 512 pixels.", 'wishful-companion'); ?></p>
                        <div class="upload">
                            <img src="<?php echo $url; ?>" width="115px" height="115px" id="wishful-blog-favicon-img"
                                 style="display:<?php echo $display; ?>;"/>
                            <div>
                                <input type="hidden" name="wishful-blog-favicon" id="wishful-blog-favicon"
                                       value="<?php echo $favicon; ?>"/>
                                <button type="submit" data-name="wishful-blog-favicon"
                                        class="upload_image_button button"><?php esc_attr_e("Upload", 'wishful-companion'); ?></button>
                                <button style="display:<?php echo $display; ?>;" type="submit"
                                        data-name="wishful-blog-favicon" class="remove_image_button button">&times;
                                </button>
                            </div>
                        </div>

                    </div>

                    <div class="wishful-blog-wizard-setup-actions">
                        <input type="hidden" name="save_step" value="save_step"/>
                        <button class="continue" type="submit"><?php esc_html_e("Continue", 'wishful-companion'); ?><i
                                    class="dashicons dashicons-arrow-<?php echo esc_attr($icon); ?>-alt"></i></button>
                        <a class="skip-btn"
                           href="<?php echo $this->get_next_step_link(); ?>"><?php esc_html_e("Skip Step", 'wishful-companion'); ?></a>
                    </div>
                </form>
            </div>
            <?php
        }

        /**
         * Save Info In Step3
         */
        public function save_wishful_blog_customize()
        {

            if (current_user_can('manage_options') && isset($_REQUEST['_wpnonce']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'wishful_blog_customize_form')) {
                if (isset($_POST['wishful-blog-logo']))
                    set_theme_mod('custom_logo', absint($_POST['wishful-blog-logo']));

                if (isset($_POST['wishful-blog-site-title']))
                    update_option('blogname', sanitize_text_field($_POST['wishful-blog-site-title']));

                if (isset($_POST['wishful-blog-tagline']))
                    update_option('blogdescription', sanitize_text_field($_POST['wishful-blog-tagline']));

                if (isset($_POST['wishful-blog-favicon']))
                    update_option('site_icon', absint($_POST['wishful-blog-favicon']));

                wp_safe_redirect($this->get_next_step_link());
                exit;
            } else {
                print 'Your are not authorized to submit this form';
                exit;
            }
        }

        /**
         * Step 4 ready step
         */
        public function wishful_blog_ready_setup()
        {
            // Image
            $img = plugins_url('/assets/img/end.png', __FILE__);
            ?>

            <div class="wishful-blog-ready-wrap wishful-blog-wrap">
                <h2><?php esc_html_e("Hooray!", 'wishful-companion'); ?></h2>
                <h1 style="font-size: 30px;"><?php esc_html_e("Your website is ready", 'wishful-companion'); ?></h1>
                <div class="wishful-blog-thumb">
                    <img src="<?php echo esc_url($img); ?>" width="600" height="274"/>
                </div>

                <div class="wishful-blog-wizard-setup-actions">
                    <a class="button button-next button-large"
                       href="<?php echo esc_url((add_query_arg(array('wishful_blog_wizzard_hide_notice' => '2nd_notice', 'show' => '1',), admin_url()))); ?>"><?php esc_html_e('View Your Website', 'wishful-companion'); ?></a>
                </div>
            </div>
            <?php
        }

        /**
         * Define cronjob
         */
        public static function cronjob_activation()
        {
            $new_time_format = time() + (24 * 60 * 60);
            if (!wp_next_scheduled('add_second_notice')) {
                wp_schedule_event($new_time_format, 'daily', 'add_second_notice');
            }
        }

        /**
         * Delete cronjob
         */
        public static function cronjob_deactivation()
        {
            wp_clear_scheduled_hook('add_second_notice');
        }

    }

    new Wishful_Blog_Extra_Demo_Import_Theme_Wizard();

    register_activation_hook(WISHFUL_COMPANION_PATH, "Wishful_Blog_Extra_Demo_Import_Theme_Wizard::install");
    // when deactivate plugin
    register_deactivation_hook(WISHFUL_COMPANION_PATH, "Wishful_Blog_Extra_Demo_Import_Theme_Wizard::uninstall");
    //when activate plugin for automatic second notice
    register_activation_hook(WISHFUL_COMPANION_PATH, array("Wishful_Blog_Extra_Demo_Import_Theme_Wizard", "cronjob_activation"));
    register_deactivation_hook(WISHFUL_COMPANION_PATH, array("Wishful_Blog_Extra_Demo_Import_Theme_Wizard", "cronjob_deactivation"));
endif;
