<?php
/*
    Author: T.M. Wrath
    Author URI: https://tmwrath.com
    Plugin Name: ThemeWrath Plugin
    Plugin URI: https://github.com/tmwrath/ThemeWrath-Plugin
    Description: ThemeWrath Plugin
    Version: 1.0

*/

if (!defined('ABSPATH')) {

    die('Invalid request.');

}


/**
 * DEFINE PATHS
 */
define( 'WRATH_PATH', plugin_dir_path( __FILE__ ) );
define( 'WRATH_FILE', __FILE__ );
define( 'WRATH_CSS_PATH', WRATH_PATH . 'assets/css/' );

/**
 * DEFINE URLS
 */
define( 'WRATH_URL', plugin_dir_url( __FILE__ ) );
define( 'WRATH_IMAGES_URL', WRATH_URL . '/assets/images/' );

$theme = wp_get_theme(); // Gets the current theme

// Check if 'ThemeWrath' is NOT the active theme or parent theme
if ('ThemeWrath' !== $theme->name && 'ThemeWrath' !== $theme->parent_theme) {
    // ThemeWrath theme is not active, display admin notice and stop further execution
    add_action('admin_notices', 'theme_wrath_plugin_admin_notice');

    function theme_wrath_plugin_admin_notice()
    {
        echo '<div class="notice notice-error"><p>';
        _e('<b>ThemeWrath-Plugin</b> requires the <b>ThemeWrath</b> theme to be active. Please activate <b>ThemeWrath</b> to use the plugin.', 'theme-wrath-plugin');
        echo '</p></div>';
    }

    return;
}

// Remove Default Wordpress Post Post-Type.
function remove_posts_menu()
{
    remove_menu_page('edit.php');
}
add_action('admin_menu', 'remove_posts_menu');

function remove_post_slug($post_link, $post)
{
    if ($post->post_type === 'post') {
        return home_url('/');
    }
    return $post_link;
}
add_filter('post_type_link', 'remove_post_slug', 10, 2);

// Add Admin Menu For T.M. Wrath Settings
function tmwrath_menu()
{
    add_menu_page(
        'T.M. Wrath', //page title
        'T.M. Wrath', //menu title
        'manage_options',
        'tmwrath-menu', //slug
        'tmwrath_menu_html', // menu html
        esc_url( WRATH_IMAGES_URL . 'icon.svg' ), // menu icon
        20
    );
    add_submenu_page(
        'tmwrath-menu',
        'Settings', //page title
        'Settings', //menu title
        'manage_options',
        'tmwrath-settings',
        'tmwrath_settings_html'
    );

}
add_action('admin_menu', 'tmwrath_menu');

function tmwrath_admin_menu_icon_css() {
    echo '
    <style>
        #adminmenu .wp-menu-image img {

            width: 60%;
            height: 60%;

        }
    </style>';
}
add_action('admin_head', 'tmwrath_admin_menu_icon_css');

function tmwrath_menu_html()
{

    if (!current_user_can('manage_options')) {
        return;
    }

}

function tmwrath_settings_html()
{

    if (!current_user_can('manage_options')) {
        return;
    }

}

function art_post_type()
{
    $args = array(
        'public' => true,
        'label' => 'Art',
        'supports' => array('title'),
        'show_ui' => true, // Ensures that the UI is shown
        'show_in_menu' => 'tmwrath-menu', // Slug of the parent menu
    );
    register_post_type('art', $args);
}
add_action('init', 'art_post_type');

function test_menu()
{
    add_menu_page(
        'Test', //page title
        'Test', //menu title
        'manage_options',
        'test-menu', //slug
        '', // menu html
        '', // menu icon
        20
    );

}
add_action('admin_menu', 'test_menu');