<?php

if (!defined('ABSPATH')) {

    die('Invalid request.');

}

function tmwrath_settings_html()
{
    if (!current_user_can('manage_options')) {
        return;
    }

    $settings_html_file = WRATH_PAGES_PATH . 'settings.php';

    // Check if the file exists
    if (file_exists($settings_html_file)) {
        // Include the HTML file directly
        include($settings_html_file);
    } else {
        // Fallback content if the HTML file is not found
        echo '<p>Settings file not found.</p>';
    }
}

// Settings Area

function register_tmwrath_settings()
{
    // Register Maintenance Mode setting
    register_setting('tmwrath-settings-group', 'tmwrath_maintenance_mode');

    // Register Disable Default Post setting
    register_setting('tmwrath-settings-group', 'disable_default_post_type');

    // Register Enable Art Post setting
    register_setting('tmwrath-settings-group', 'enable_art_post_type');

    // Add settings section
    add_settings_section(
        'tmwrath_settings_section', // Section ID
        'T.M. Wrath Settings', // Title
        'tmwrath_settings_section_callback', // Callback function
        'tmwrath-settings' // Page slug
    );

    // Add settings field for Maintenance Mode
    add_settings_field(
        'tmwrath-maintenance-mode', // ID
        'Maintenance Mode', // Title
        'tmwrath_maintenance_mode_callback', // Callback function to render the checkbox
        'tmwrath-settings', // Page to display the setting
        'tmwrath_settings_section' // Section ID
    );

    // Add settings field for Disable Default Post
    add_settings_field(
        'disable_default_post_type', // ID
        'Disable Default Post', // Title
        'disable_default_post_type_callback', // Callback function to render the checkbox
        'tmwrath-settings', // Page to display the setting
        'tmwrath_settings_section' // Section ID
    );

    // Add settings field for Enable Art Post
    add_settings_field(
        'enable_art_post_type', // ID
        'Enable Art Post', // Title
        'enable_art_post_type_callback', // Callback function to render the checkbox
        'tmwrath-settings', // Page to display the setting
        'tmwrath_settings_section' // Section ID
    );

}
add_action('admin_init', 'register_tmwrath_settings');

function tmwrath_maintenance_mode()
{
    if (get_option('tmwrath_maintenance_mode') && (!current_user_can('edit_themes') || !is_user_logged_in())) {

        $maintenance_file = WRATH_PAGES_PATH . 'maintenance.html';

        // Check if the file exists
        if (file_exists($maintenance_file)) {
            // Include the HTML file
            include($maintenance_file);
            exit; // Stop further loading of WordPress
        }
    }
}
add_action('template_redirect', 'tmwrath_maintenance_mode');

// Remove Default Wordpress Post Post-Type.

function remove_default_posts()
{
    if (get_option('disable_default_post_type')) {
        remove_menu_page('edit.php');
    }
}
add_action('admin_menu', 'remove_default_posts');

function remove_post_slug($post_link, $post)
{
    if (get_option('disable_default_post_type') && $post->post_type === 'post') {
        return home_url('/');
    }
    return $post_link;
}
add_filter('post_type_link', 'remove_post_slug', 10, 2);



function tmwrath_settings_section_callback()
{
    echo '<p>Settings For T.M. Wrath Plugin</p>';
}

function tmwrath_maintenance_mode_callback()
{
    $value = get_option('tmwrath_maintenance_mode');
    echo '<input type="checkbox" id="tmwrath_maintenance_mode" name="tmwrath_maintenance_mode" value="1" ' . checked(1, $value, false) . '/>';
    echo '<label for="tmwrath_maintenance_mode">Enable Maintenance Mode</label>';
}

function disable_default_post_type_callback()
{
    $value = get_option('disable_default_post_type');
    echo '<input type="checkbox" id="disable_default_post_type" name="disable_default_post_type" value="1" ' . checked(1, $value, false) . '/>';
    echo '<label for="disable_default_post_type">Disable Default Post Type</label>';
}

function enable_art_post_type_callback()
{
    $value = get_option('enable_art_post_type');
    echo '<input type="checkbox" id="enable_art_post_type" name="enable_art_post_type" value="1" ' . checked(1, $value, false) . '/>';
    echo '<label for="enable_art_post_type">Enable Art Post Type</label>';
}