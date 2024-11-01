<?php
// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * @review_dismiss()
 * @review_pending()
 * @wishful_companion_review_notice_message()
 * Make all the above functions working.
 */
function wishful_companion_review_notice() {

    wishful_companion_review_dismiss();
    wishful_companion_review_pending();

    $activation_time = get_site_option('wishful_companion_active_time');
    $review_dismissal = get_site_option('wishful_companion_review_dismiss');
    $maybe_later = get_site_option('wishful_companion_maybe_later');

    if ('yes' == $review_dismissal) {
        return;
    }

    if (!$activation_time) {
        add_site_option('wishful_companion_active_time', time());
    }

    $daysinseconds = 1209600; // 1209600 14 Days in seconds.
    if ('yes' == $maybe_later) {
        $daysinseconds = 2419200; // 28 Days in seconds.
    }

    if (time() - $activation_time > $daysinseconds) {
        add_action('admin_notices', 'wishful_companion_review_notice_message');
    }
}

//add_action('admin_init', 'wishful_companion_review_notice');

/**
 * For the notice preview.
 */
function wishful_companion_review_notice_message() {
    $scheme = (parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY)) ? '&' : '?';
    $url = $_SERVER['REQUEST_URI'] . $scheme . 'wishful_companion_review_dismiss=yes';
    $dismiss_url = wp_nonce_url($url, 'wishful-blog-review-nonce');

    $_later_link = $_SERVER['REQUEST_URI'] . $scheme . 'wishful_companion_review_later=yes';
    $later_url = wp_nonce_url($_later_link, 'wishful-blog-review-nonce');
    $theme = wp_get_theme();
    $themetemplate = $theme->template;
    $themename = $theme->name;
    ?>

    <div class="wishful-blog-review-notice">
        <div class="wishful-blog-review-thumbnail">
            <img src="<?php echo esc_url(WISHFUL_COMPANION_PLUGIN_URL) . 'img/et-logo.png'; ?>" alt="">
        </div>
        <div class="wishful-blog-review-text">
            <h3><?php esc_html_e('Leave A Review?', 'wishful-companion') ?></h3>
            <p><?php echo sprintf(esc_html__('We hope you\'ve enjoyed using %1$s theme! Would you consider leaving us a review on WordPress.org?', 'wishful-companion'), esc_html($themename)) ?></p>
            <ul class="wishful-blog-review-ul">
                <li>
                    <a href="https://wordpress.org/support/theme/<?php echo esc_html($themetemplate); ?>/reviews/?rate=5#new-post" target="_blank">
                        <span class="dashicons dashicons-external"></span>
                        <?php esc_html_e('Sure! I\'d love to!', 'wishful-companion') ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo $dismiss_url ?>">
                        <span class="dashicons dashicons-smiley"></span>
                        <?php esc_html_e('I\'ve already left a review', 'wishful-companion') ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo $later_url ?>">
                        <span class="dashicons dashicons-calendar-alt"></span>
                        <?php esc_html_e('Maybe Later', 'wishful-companion') ?>
                    </a>
                </li>
                <li>
                    <a href="https://wishfulthemes.com/support/" target="_blank">
                        <span class="dashicons dashicons-sos"></span>
                        <?php esc_html_e('Found a bug!', 'wishful-companion') ?>
                    </a>
                </li>
                <li>
                    <a href="<?php echo $dismiss_url ?>">
                        <span class="dashicons dashicons-dismiss"></span>
                        <?php esc_html_e('Never show again', 'wishful-companion') ?>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <?php
}

/**
 * For Dismiss!
 */
function wishful_companion_review_dismiss() {

    if (!is_admin() ||
            !current_user_can('manage_options') ||
            !isset($_GET['_wpnonce']) ||
            !wp_verify_nonce(sanitize_key(wp_unslash($_GET['_wpnonce'])), 'wishful-blog-review-nonce') ||
            !isset($_GET['wishful_companion_review_dismiss'])) {

        return;
    }

    add_site_option('wishful_companion_review_dismiss', 'yes');
}

/**
 * For Maybe Later Update.
 */
function wishful_companion_review_pending() {

    if (!is_admin() ||
            !current_user_can('manage_options') ||
            !isset($_GET['_wpnonce']) ||
            !wp_verify_nonce(sanitize_key(wp_unslash($_GET['_wpnonce'])), 'wishful-blog-review-nonce') ||
            !isset($_GET['wishful_companion_review_later'])) {

        return;
    }
    // Reset Time to current time.
    update_site_option('wishful_companion_active_time', time());
    update_site_option('wishful_companion_maybe_later', 'yes');
}

function wishful_companion_pro_notice() {

    wishful_companion_pro_dismiss();

    $activation_time = get_site_option('wishful_companion_active_pro_time');

    if (!$activation_time) {
        add_site_option('wishful_companion_active_pro_time', time());
    }

    $daysinseconds = 432000; // 5 Days in seconds (432000).

    if (time() - $activation_time > $daysinseconds) {
        if (!defined('WISHFUL_COMPANION_CURRENT_VERSION')) {
            add_action('admin_notices', 'wishful_companion_pro_notice_message');
        }
    }
}

add_action('admin_init', 'wishful_companion_pro_notice');

/**
 * For PRO notice
 */
function wishful_companion_pro_notice_message() {
    $scheme = (parse_url($_SERVER['REQUEST_URI'], PHP_URL_QUERY)) ? '&' : '?';
    $url = $_SERVER['REQUEST_URI'] . $scheme . 'wishful_companion_pro_dismiss=yes';
    $dismiss_url = wp_nonce_url($url, 'wishfulblog-pro-nonce');
    ?>

    <div class="wishful-blog-review-notice">
        <div class="wishful-blog-review-thumbnail">
            <img src="<?php echo esc_url(WISHFUL_COMPANION_PLUGIN_URL) . 'img/wishful-blog-logo.png'; ?>" alt="">
        </div>
        <div class="wishful-blog-review-text">
            <h3><?php esc_html_e('Go PRO for More Features', 'wishful-companion') ?></h3>
            <p>
                <?php echo sprintf(esc_html__('Get the %1$s for more stunning elements, demos and customization options.', 'wishful-companion'), '<a href="https://wishfulthemes.com/" target="_blank">PRO version</a>') ?>
            </p>
            <ul class="wishful-blog-review-ul">
                <li class="show-mor-message">
                    <a href="https://wishfulthemes.com/" target="_blank">
                        <span class="dashicons dashicons-external"></span>
                        <?php esc_html_e('Show me more', 'wishful-companion') ?>
                    </a>
                </li>
                <li class="hide-message">
                    <a href="<?php echo $dismiss_url ?>">
                        <span class="dashicons dashicons-smiley"></span>
                        <?php esc_html_e('Hide this message', 'wishful-companion') ?>
                    </a>
                </li>
            </ul>
        </div>
    </div>

    <?php
}

/**
 * For PRO Dismiss!
 */
function wishful_companion_pro_dismiss() {

    if (!is_admin() ||
            !current_user_can('manage_options') ||
            !isset($_GET['_wpnonce']) ||
            !wp_verify_nonce(sanitize_key(wp_unslash($_GET['_wpnonce'])), 'wishfulblog-pro-nonce') ||
            !isset($_GET['wishful_companion_pro_dismiss'])) {

        return;
    }
    $daysinseconds = 1209600; // 14 Days in seconds (1209600).
    $newtime = time() + $daysinseconds;
    update_site_option('wishful_companion_active_pro_time', $newtime);
}
