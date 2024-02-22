<?php
/**
 * Plugin Name: ThemeWrath-Plugin
 * Plugin URI: https://github.com/TMWrath/themewrath_plugin
 * Description: ThemeWrath Plugin
 * Version: 0.0.5
 * Author: T.M. Wrath
 * Author URI: https://tmwrath.com
 */

if (!defined('ABSPATH')) {
    die('Invalid request.');
}

// DEFINE PATHS
define('WRATH_FILE', __FILE__);
define('WRATH_PATH', plugin_dir_path(WRATH_FILE));
define('WRATH_PAGES_PATH', WRATH_PATH . 'pages/');

// DEFINE URLS
define('WRATH_URL', plugin_dir_url(WRATH_FILE));
define('WRATH_IMAGES_URL', WRATH_URL . 'assets/images/'); // Removed extra slash

// Load CSS
function themewrath_plugin_load_css() {
    wp_register_style('themewrath_plugin_main_css', WRATH_URL . 'assets/css/main.css');
    wp_enqueue_style('themewrath_plugin_main_css');
}
add_action('wp_enqueue_scripts', 'themewrath_plugin_load_css');

// Load JS
function themewrath_plugin_load_js() {
    wp_register_script('themewrath_plugin_main_js', WRATH_URL . 'assets/js/main.js', array('jquery'), '1.0', true);
    wp_enqueue_script('themewrath_plugin_main_js');
}
add_action('wp_enqueue_scripts', 'themewrath_plugin_load_js');

// Include Art Post Type
if (get_option('enable_art_post_type')) {
    include_once 'functions/art_post_functions.php';
}

// Include Settings
include_once 'functions/settings_functions.php';
include_once 'assets/functions/sidebar_functions.php';

// Add Admin Menu For T.M. Wrath plugin
function int_tmwrath_menu() {
    add_menu_page(
        'T.M. Wrath', //page title
        'T.M. Wrath', //menu title
        'manage_options',
        'tmwrath-menu', //slug
        'tmwrath_menu_html', // menu html
        esc_url(WRATH_IMAGES_URL . 'icon.svg'), // menu icon
        20
    );
    if (get_option('enable_art_post_type')) {
        add_submenu_page(
            'tmwrath-menu',
            'Art', //page title
            'Art', //menu title
            'manage_options', //capability
            'edit.php?post_type=art', //menu slug
            '' //function
        );
    }
    add_submenu_page(
        'tmwrath-menu',
        'Settings', //page title
        'Settings', //menu title
        'manage_options',
        'tmwrath-settings',
        'tmwrath_settings_html'
    );
}
add_action('admin_menu', 'int_tmwrath_menu');

function tmwrath_menu_html() {
    if (!current_user_can('manage_options')) {
        return;
    }

    $wrath_menu_html_file = WRATH_PAGES_PATH . 'wrath_menu.html';

    if (file_exists($wrath_menu_html_file)) {
        include($wrath_menu_html_file);
    } else {
        echo '<p>Menu file not found.</p>';
    }
}
