<?php
/*
    Author: T.M. Wrath
    Author URI: https://tmwrath.com
    Plugin Name: ThemeWrath Plugin
    Plugin URI: https://github.com/tmwrath/ThemeWrath_Plugin
    Description: ThemeWrath Plugin
    Version: 0.0.2
*/

if (!defined('ABSPATH')) {

    die('Invalid request.');

}

// DEFINE PATHS

define('WRATH_FILE', __FILE__);
define('WRATH_PATH', plugin_dir_path(WRATH_FILE));
define('WRATH_CSS_PATH', WRATH_PATH . 'assets/css/');
define('WRATH_PAGES_PATH', WRATH_PATH . 'pages/');

// DEFINE URLS

define('WRATH_URL', plugin_dir_url(WRATH_FILE));
define('WRATH_IMAGES_URL', WRATH_URL . '/assets/images/');

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

// Load CSS

function Themewrath_Plugin_load_css()
{
    wp_register_style('themewrath_plugin_main_css', get_template_directory_uri() . '/assets/css/main.css', array(), false, 'all');
    wp_enqueue_style('themewrath_plugin_main_css');

}
add_action('wp_enqueue_scripts', 'Themewrath_Plugin_load_css');

// Load JS

function Themewrath_Plugin_load_js()
{

    wp_register_script('themewrath_plugin_main_js', get_template_directory_uri() . '/assets/js/main.js', 'jquery', false, true);
    wp_enqueue_script('themewrath_plugin_main_js');

}
add_action('wp_enqueue_scripts', 'Themewrath_Plugin_load_js');

// Include Art Post Type

if (get_option('enable_art_post_type')) {

    include_once 'functions/art_post_functions.php';

}

// Include Settings

include_once 'functions/settings_functions.php';

// Add Admin Menu For T.M. Wrath plugin

function tmwrath_menu()
{
    add_menu_page(
        'T.M. Wrath', //page title
        'T.M. Wrath', //menu title
        'manage_options',
        'tmwrath-menu', //slug
        'tmwrath_menu_html', // menu html
        esc_url(WRATH_IMAGES_URL . 'icon.svg'), // menu icon
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

function tmwrath_menu_html()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $settings_html_file = WRATH_PAGES_PATH . 'settings.php';

    // Check if the file exists
    if (file_exists($wrath_menu_html_file)) {
        // Include the HTML file directly
        include($wrath_menu_html_file);
    } else {
        // Fallback content if the HTML file is not found
        echo '<p>Menu file not found.</p>';
    }
}