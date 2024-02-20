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

function Themewrath_Plugin_load_css()
{
    wp_register_style('themewrath_plugin_main_css', get_template_directory_uri() . '/assets/css/main.css', array(), false, 'all');
    wp_enqueue_style('themewrath_plugin_main_css');

}
add_action('wp_enqueue_scripts', 'Themewrath_Plugin_load_css');

function Themewrath_Plugin_load_js()
{

    wp_register_script('themewrath_plugin_main_js', get_template_directory_uri() . '/assets/js/main.js', 'jquery', false, true);
    wp_enqueue_script('themewrath_plugin_main_js');

}
add_action('wp_enqueue_scripts', 'Themewrath_Plugin_load_js');

include_once 'functions/art_post.php';

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
    // Your menu HTML content here
}

function tmwrath_settings_html()
{
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

function tmwrath_register_settings()
{
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

function tmwrath_settings_section_callback()
{
    echo '<p>Enable or Disable Maintenance Mode.</p>';
}

function tmwrath_maintenance_mode_callback()
{
    $value = get_option('tmwrath_maintenance_mode');
    echo '<input type="checkbox" id="tmwrath_maintenance_mode" name="tmwrath_maintenance_mode" value="1" ' . checked(1, $value, false) . '/>';
    echo '<label for="tmwrath_maintenance_mode">Enable Maintenance Mode</label>';
}

function tmwrath_maintenance_mode()
{
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
function your_plugin_create_page($page_title, $page_content)
{
    $page_obj = get_page_by_title($page_title, 'OBJECT', 'page');

    if ($page_obj) {
        // Page already exists
        return false;
    }

    $page_args = array(
        'post_type' => 'page',
        'post_status' => 'publish',
        'post_title' => ucwords($page_title),
        'post_name' => strtolower(str_replace(' ', '-', trim($page_title))),
        'post_content' => $page_content,
    );

    $page_id = wp_insert_post($page_args);

    return $page_id;
}

// Function to be called upon plugin activation
function themewrath_pages_on_activation()
{
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
function themewrath_admin_notice_page_creation()
{
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

// Updater

add_filter('pre_set_site_transient_update_plugins', 'themewrath_plugin_check_for_update');

function themewrath_plugin_check_for_update($transient)
{
    if (empty($transient->checked)) {
        return $transient;
    }

    $current_version = $transient->checked['Themewrath_Plugin/themewrath-plugin.php']; // Adjust the plugin's main file path
    $latest_release = themewrath_plugin_get_latest_release_from_github();

    if ($latest_release && version_compare($current_version, $latest_release->tag_name, '<')) {
        $obj = new stdClass();
        $obj->slug = 'themewrath-plugin';
        $obj->plugin = 'Themewrath_Plugin/themewrath-plugin.php'; // Adjust the plugin's main file path
        $obj->new_version = $latest_release->tag_name;
        $obj->url = $latest_release->html_url; // URL to the plugin homepage or GitHub release page
        $obj->package = $latest_release->zipball_url; // Direct URL to the zip file of the release
        $transient->response['Themewrath_Plugin/themewrath-plugin.php'] = $obj; // Adjust the plugin's main file path
    }

    return $transient;
}

function themewrath_plugin_get_latest_release_from_github()
{
    $url = "https://api.github.com/repos/TMWrath/Themewrath_Plugin/releases/latest"; // Adjust to your GitHub repo
    $response = wp_remote_get($url, array(
        'headers' => array(
            'Accept' => 'application/vnd.github.v3+json',
            'User-Agent' => 'WordPress-Themewrath-Plugin' // GitHub requires a user agent
        )
    )
    );

    if (is_wp_error($response)) {
        return false;
    }

    $release_data = json_decode(wp_remote_retrieve_body($response));
    return $release_data;
}

// art fields

add_action('add_meta_boxes', function() {
    // Meta box for ZIP file upload
    add_meta_box('custom_file_upload', 'ZIP File Upload', 'custom_file_upload_callback', 'art', 'side', 'default');
    // Meta box for image upload
    add_meta_box('custom_image_upload', 'Image Upload', 'custom_image_upload_callback', 'art', 'side', 'default');
});

// Display callback for ZIP file upload
function custom_file_upload_callback($post) {
    wp_nonce_field('custom_file_upload_action', 'custom_file_upload_nonce');
    $existing_value = get_post_meta($post->ID, '_custom_art_file', true);

    echo '<input type="file" id="custom_art_file" name="custom_art_file" accept=".zip">';
    if (!empty($existing_value)) {
        $file_url = wp_get_attachment_url($existing_value);
        echo "<p>Current File: <a href='{$file_url}' target='_blank'>" . basename($file_url) . "</a></p>";
        echo "<p><a href='#' class='delete-custom-file' data-target='custom_art_file_delete'>Delete File</a></p>";
        echo '<input type="hidden" id="custom_art_file_delete" name="custom_art_file_delete" value="0">';
    }
}

// Display callback for image upload
function custom_image_upload_callback($post) {
    wp_nonce_field('custom_image_upload_action', 'custom_image_upload_nonce');
    $existing_value = get_post_meta($post->ID, '_custom_image', true);

    echo '<input type="file" id="custom_image" name="custom_image" accept="image/*">';
    if (!empty($existing_value)) {
        $image_url = wp_get_attachment_url($existing_value);
        echo "<p><img src='{$image_url}' alt='Uploaded Image' style='max-width:100%; height:auto;'/></p>";
        echo "<p><a href='#' class='delete-custom-file' data-target='custom_image_delete'>Delete Image</a></p>";
        echo '<input type="hidden" id="custom_image_delete" name="custom_image_delete" value="0">';
    }
}

// Save post action to handle both ZIP and image file uploads/deletions
add_action('save_post_art', function($post_id) {
    if (!isset($_POST['custom_file_upload_nonce']) || !wp_verify_nonce($_POST['custom_file_upload_nonce'], 'custom_file_upload_action') || !isset($_POST['custom_image_upload_nonce']) || !wp_verify_nonce($_POST['custom_image_upload_nonce'], 'custom_image_upload_action')) {
        return;
    }
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    // Handle ZIP file upload/delete
    custom_file_handle_upload($post_id, 'custom_art_file', '_custom_art_file', 'custom_art_file_delete');
    // Handle image upload/delete
    custom_file_handle_upload($post_id, 'custom_image', '_custom_image', 'custom_image_delete');
});

// Generic function to handle file upload and deletion
function custom_file_handle_upload($post_id, $file_input_name, $meta_key, $delete_field_name) {
    if (isset($_POST[$delete_field_name]) && $_POST[$delete_field_name] == '1') {
        $existing_value = get_post_meta($post_id, $meta_key, true);
        if (!empty($existing_value)) {
            wp_delete_attachment($existing_value, true);
            delete_post_meta($post_id, $meta_key);
        }
    } elseif (!empty($_FILES[$file_input_name]['name'])) {
        require_once(ABSPATH . 'wp-admin/includes/file.php');
        require_once(ABSPATH . 'wp-admin/includes/media.php');
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        $attachment_id = media_handle_upload($file_input_name, $post_id);
        if (!is_wp_error($attachment_id)) {
            update_post_meta($post_id, $meta_key, $attachment_id);
        }
    }
}

// Ensure the form includes enctype for file uploads
add_action('post_edit_form_tag', function() {
    echo ' enctype="multipart/form-data"';
});

// JavaScript for handling delete actions
add_action('admin_footer', function() {
    global $post;
    if ($post->post_type == 'art') {
        ?>
        <script>
        document.addEventListener('DOMContentLoaded', function() {
            document.querySelectorAll('.delete-custom-file').forEach(function(link) {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    if (confirm('Are you sure you want to delete this item?')) {
                        var targetId = this.getAttribute('data-target');
                        var hiddenInput = document.getElementById(targetId);
                        if (hiddenInput) {
                            hiddenInput.value = '1';
                            this.closest('form').submit();
                        }
                    }
                });
            });
        });
        </script>
        <?php
    }
});

