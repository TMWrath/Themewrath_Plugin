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


// DEFINE PATHS

define( 'WRATH_FILE', __FILE__ );
define( 'WRATH_PATH', plugin_dir_path( WRATH_FILE ) );
define( 'WRATH_CSS_PATH', WRATH_PATH . 'assets/css/' );

// DEFINE URLS

define( 'WRATH_URL', plugin_dir_url( WRATH_FILE ) );
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

function Themewrath_Plugin_load_css()
{
    wp_register_style('main', get_template_directory_uri() . '/assets/css/main.css', array(), false, 'all');
    wp_enqueue_style('main');

}
add_action('wp_enqueue_scripts', 'Themewrath_Plugin_load_css');

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

/// Add Admin Menu For T.M. Wrath Settings
function tmwrath_menu() {
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

function tmwrath_menu_html() {
    if (!current_user_can('manage_options')) {
        return;
    }
    // Your menu HTML content here
}

function tmwrath_settings_html() {
    if (!current_user_can('manage_options')) {
        return;
    }

    // Correctly output the settings form using echo
    echo '<form method="post" action="options.php">';
    settings_fields('tmwrath-settings-group');
    do_settings_sections('tmwrath-settings');
    submit_button();
    echo '</form>';
}

function tmwrath_register_settings() {
    register_setting('tmwrath-settings-group', 'tmwrath_maintenance_mode');
    add_settings_section(
        'tmwrath_settings_section', // Section ID
        'Maintenance Mode Settings', // Title
        'tmwrath_settings_section_callback', // Callback function
        'tmwrath-settings' // Page slug
    );
    add_settings_field(
        'tmwrath-maintenance-mode', // ID
        'Maintenance Mode', // Title
        'tmwrath_maintenance_mode_callback', // Callback function to render the checkbox
        'tmwrath-settings', // Page to display the setting
        'tmwrath_settings_section' // Section ID
    );
}
add_action('admin_init', 'tmwrath_register_settings');

function tmwrath_settings_section_callback() {
    echo '<p>Enable or Disable Maintenance Mode.</p>';
}

function tmwrath_maintenance_mode_callback() {
    $value = get_option('tmwrath_maintenance_mode');
    echo '<input type="checkbox" id="tmwrath_maintenance_mode" name="tmwrath_maintenance_mode" value="1" ' . checked(1, $value, false) . '/>';
    echo '<label for="tmwrath_maintenance_mode">Enable Maintenance Mode</label>';
}

function tmwrath_maintenance_mode() {
    if (get_option('tmwrath_maintenance_mode') && (!current_user_can('edit_themes') || !is_user_logged_in())) {
        // Specify the path to your custom HTML file within your plugin directory
        $maintenance_file = plugin_dir_path(WRATH_FILE) . 'maintenance.html';
        
        // Check if the file exists
        if (file_exists($maintenance_file)) {
            // Include the HTML file
            include($maintenance_file);
            exit; // Stop further loading of WordPress
        }
    }
}
add_action('template_redirect', 'tmwrath_maintenance_mode');

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


// Create A Page On Activation
function your_plugin_create_page($page_title, $page_content) {
    $page_obj = get_page_by_title($page_title, 'OBJECT', 'page');

    if ($page_obj) {
        // Page already exists
        return false;
    }

    $page_args = array(
        'post_type'      => 'page',
        'post_status'    => 'publish',
        'post_title'     => ucwords($page_title),
        'post_name'      => strtolower(str_replace(' ', '-', trim($page_title))),
        'post_content'   => $page_content,
    );

    $page_id = wp_insert_post($page_args);

    return $page_id;
}

// Function to be called upon plugin activation
function themewrath_pages_on_activation() {
    $page_title = 'The Collection'; // Define your page title here
    $page_content = '<h3>Welcome to The Collection</h3>'; // Define your page content here

    $current_page = your_plugin_create_page($page_title, $page_content);

    if (false !== $current_page) {
        // If the page was successfully created, set a transient to show a success notice
        set_transient('themewrath_page_creation_notice', 'created', 60);
    } else {
        // If the page already exists, set a transient to show a notice that it exists
        set_transient('themewrath_page_creation_notice', 'exists', 60);
    }
}

// Register the activation hook
register_activation_hook(WRATH_FILE, 'themewrath_pages_on_activation');

// Admin notice for page creation
function themewrath_admin_notice_page_creation() {
    if ($notice = get_transient('themewrath_page_creation_notice')) {
        if ('created' === $notice) {
            echo '<div class="notice notice-success is-dismissible"><p>"The Collection" page has been created successfully.</p></div>';
        } elseif ('exists' === $notice) {
            echo '<div class="notice notice-warning is-dismissible"><p>"The Collection" page already exists.</p></div>';
        }
        delete_transient('themewrath_page_creation_notice');
    }
}
add_action('admin_notices', 'themewrath_admin_notice_page_creation');
