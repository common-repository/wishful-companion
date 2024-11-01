<?php
if (!defined('ABSPATH')) {
    exit;
}

Class WizardAjax {

    public function __construct() {
        add_action('wp_ajax_wishful_blog_wizzard_ajax_get_demo_data', array($this, 'ajax_demo_data'));
    }

    public function ajax_demo_data() {


        if (!wp_verify_nonce($_GET['demo_data_nonce'], 'get-demo-data')) {
            die('This action was stopped for security purposes.');
        }

        // Database reset url
        if (is_plugin_active('wordpress-database-reset/wp-reset.php')) {
            $plugin_link = admin_url('tools.php?page=database-reset');
        } else {
            $plugin_link = admin_url('plugin-install.php?s=Wordpress+Database+Reset&tab=search');
        }

        // Get all demos
        $demos = Wishful_Blog_Extra_Demos::get_demos_data();

        // Get selected demo
        $demo = $_GET['demo_name'];

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
                            esc_html__('Importing demo data allow you to quickly edit everything instead of creating content from scratch. It is recommended uploading sample data on a fresh WordPress install to prevent conflicts with your current content. You can use this plugin to reset your site if needed: %1$sWordpress Database Reset%2$s.', 'wishful-companion'), '<a href="' . $plugin_link . '" target="_blank">', '</a>'
                    );
                    ?></p>

                <div class="wishful-blog-required-plugins-wrap">
                    <h3><?php esc_html_e('Required Plugins', 'wishful-companion'); ?></h3>
                    <p><?php esc_html_e('For your site to look exactly like this demo, the plugins below need to be activated.', 'wishful-companion'); ?></p>
                    <div class="wishful-blog-required-plugins oe-plugin-installer">
                        <?php
                        Wishful_Blog_Extra_Demos::required_plugins($free, 'free');
                        Wishful_Blog_Extra_Demos::required_plugins($premium, 'premium');
                        ?>
                    </div>
                </div>

            </div>


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

            <?php if (!defined('WISHFULBLOG_PRO_CURRENT_VERSION') && $premium['0']['slug'] == 'wishfulblog-pro') { ?>
                    <div class="wishful-blog-button wishful-blog-plugins-pro">
                        <a href="https://www.wishfulthemes.com/themes/<?php echo esc_attr( $premium['0']['slug'] ); ?>/" target="_blank" >
                            <?php esc_html_e('Install and activate Wishful Blog PRO', 'wishful-companion'); ?>
                        </a>
                    </div>
                <?php } elseif (defined('WISHFULBLOG_PRO_CURRENT_VERSION') && !defined('ACTIVATED_LICENSE_PRO') && $premium['0']['slug'] == 'wishfulblog-pro') { ?>
                    <div class="wishful-blog-button wishful-blog-plugins-pro">
                        <a href="<?php echo esc_url(network_admin_url('admin.php?page=wishfulblog-pro-license-page')) ?>" >
                            <?php esc_html_e('Activate Wishful Blog PRO license', 'wishful-companion'); ?>
                        </a>
                    </div>
                <?php } else { ?>
                    <input type="submit" name="submit" class="wishful-blog-button wishful-blog-import" value="<?php esc_html_e('Import', 'wishful-companion'); ?>"  />
                <?php } ?>

        </form>

        <div class="wishful-blog-loader">
            <h2 class="title"><?php esc_html_e('The import process could take some time, please be patient', 'wishful-companion'); ?></h2>
            <div class="wishful-blog-import-status wishful-blog-popup-text"></div>
        </div>

        <div class="wishful-blog-last">
            <svg class="checkmark" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 52 52"><circle class="checkmark-circle" cx="26" cy="26" r="25" fill="none"></circle><path class="checkmark-check" fill="none" d="M14.1 27.2l7.1 7.2 16.7-16.8"></path></svg>
            <h3><?php esc_html_e('Demo Imported!', 'wishful-companion'); ?></h3>
        </div>
        <div class="wishful-blog-error" style="display: none;">
                <p ><?php esc_html_e("The import didn't import well please contact the support.", 'wishful-companion'); ?></p>
        </div>


        <?php
        die();
    }

}

new WizardAjax();
