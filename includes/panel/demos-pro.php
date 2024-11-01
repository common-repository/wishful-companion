<?php
/**
 * Demos
 *
 * @package Wishful-companion
 * @category Core
 * @author WishfulThemes
 */
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

function wishful_blog_pro_get_demos_data_extra($data) {
    $theme = wp_get_theme();
    $theme_slug = get_stylesheet();

    $is_child_theme = (bool) $theme->parent();

    if( $is_child_theme ) {

        $parent = $theme->parent();

        $theme_slug = $parent->get( 'TextDomain' );

    }

    // Demos url
    $demo_url = 'https://demo.wishfulthemes.com/';
    $import_url = $demo_url . 'demo-import/' . $theme_slug . '/pro-demo/';

    $extra = array(
        'pro-three' => array(
            'demo_name' => 'Pro : General',
            'demo_slug' => $theme_slug,
            'demo_url' => $demo_url,
            'categories' => array('Pro', 'Fashion', 'Travel', 'Food', 'Photography', 'WooCommerce'),
            'xml_file' => $import_url . 'three/content.xml',
            'theme_settings' => $import_url . 'three/customizer.dat',
            'widgets_file' => $import_url . 'three/widgets.wie',
            'screenshot' => $import_url . 'three/screenshot.jpg',
            'demo_template' => 'pro-demo-three',
            'home_title' => '',
            'blog_title' => '',
            'posts_to_show' => '10',
            'main_nav_name' => 'Main Menu',
            'main_nav_id' => 'menu-1',
            'top_nav_name' => '',
            'top_nav_id' => null,
            'footer_nav_name' => 'Footer Menu',
            'footer_nav_id' => 'menu-2',
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
                'premium' => array(
                    array(
                        'slug' => 'wishfulblog-pro',
                        'init' => 'wishfulblog-pro/wishfulblog-pro.php',
                        'name' => 'Wishful Blog PRO',
                    ),
                ),
            ),
        ),
        'pro-four' => array(
            'demo_name' => 'Pro : Food',
            'demo_slug' => $theme_slug,
            'demo_url' => $demo_url,
            'categories' => array('Pro', 'Food'),
            'xml_file' => $import_url . 'four/content.xml',
            'theme_settings' => $import_url . 'four/customizer.dat',
            'widgets_file' => $import_url . 'four/widgets.wie',
            'screenshot' => $import_url . 'four/screenshot.jpg',
            'demo_template' => 'pro-demo-four',
            'home_title' => '',
            'blog_title' => '',
            'posts_to_show' => '9',
            'main_nav_name' => 'Main Menu',
            'main_nav_id' => 'menu-1',
            'top_nav_name' => '',
            'top_nav_id' => null,
            'footer_nav_name' => 'Footer Menu',
            'footer_nav_id' => 'menu-2',
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
                'premium' => array(
                    array(
                        'slug' => 'wishfulblog-pro',
                        'init' => 'wishfulblog-pro/wishfulblog-pro.php',
                        'name' => 'Wishful Blog PRO',
                    ),
                ),
            ),
        ),
        'pro-two' => array(
            'demo_name' => 'Pro : Fashion',
            'demo_slug' => $theme_slug,
            'demo_url' => $demo_url,
            'categories' => array('Pro', 'Fashion', 'WooCommerce'),
            'xml_file' => $import_url . 'two/content.xml',
            'theme_settings' => $import_url . 'two/customizer.dat',
            'widgets_file' => $import_url . 'two/widgets.wie',
            'screenshot' => $import_url . 'two/screenshot.jpg',
            'demo_template' => 'pro-demo-two',
            'home_title' => '',
            'blog_title' => '',
            'posts_to_show' => '13',
            'main_nav_name' => 'Main Menu',
            'main_nav_id' => 'menu-1',
            'top_nav_name' => '',
            'top_nav_id' => null,
            'footer_nav_name' => 'Footer Menu',
            'footer_nav_id' => 'menu-2',
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
                'premium' => array(
                    array(
                        'slug' => 'wishfulblog-pro',
                        'init' => 'wishfulblog-pro/wishfulblog-pro.php',
                        'name' => 'Wishful Blog PRO',
                    ),
                ),
            ),
        ),
        'pro-one' => array(
            'demo_name' => 'Pro : Travel',
            'demo_slug' => $theme_slug,
            'demo_url' => $demo_url,
            'categories' => array('Pro', 'Travel'),
            'xml_file' => $import_url . 'one/content.xml',
            'theme_settings' => $import_url . 'one/customizer.dat',
            'widgets_file' => $import_url . 'one/widgets.wie',
            'screenshot' => $import_url . 'one/screenshot.jpg',
            'demo_template' => 'pro-demo-one',
            'home_title' => '',
            'blog_title' => '',
            'posts_to_show' => '10',
            'main_nav_name' => 'Main Menu',
            'main_nav_id' => 'menu-1',
            'top_nav_name' => '',
            'top_nav_id' => null,
            'footer_nav_name' => 'Footer Menu',
            'footer_nav_id' => 'menu-2',
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
                'premium' => array(
                    array(
                        'slug' => 'wishfulblog-pro',
                        'init' => 'wishfulblog-pro/wishfulblog-pro.php',
                        'name' => 'Wishful Blog PRO',
                    ),
                ),
            ),
        ),
    );
    // combine the two arrays
    $data = array_merge($data, $extra);

    return $data;
}

add_filter('wishful_blog_demos_data', 'wishful_blog_pro_get_demos_data_extra');
