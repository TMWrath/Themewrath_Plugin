<?php

if (!defined('ABSPATH')) {

    die('Invalid request.');

}

function Themewrath_art_load_css()
{
    wp_register_style('themewrath_art_css', get_template_directory_uri() . '/assets/css/art.css', array(), false, 'all');
    wp_enqueue_style('themewrath_art_css');

}
add_action('wp_enqueue_scripts', 'Themewrath_art_load_css');

// Load JS

function Themewrath_art_load_js()
{

    wp_register_script('themewrath_art_js', get_template_directory_uri() . '/assets/js/art.js', 'jquery', false, true);
    wp_enqueue_script('themewrath_art_js');

}
add_action('wp_enqueue_scripts', 'Themewrath_art_load_js');

function art_post_type()
{
    $args = array(
        'public' => true,
        'label' => 'Art',
        'supports' => array('title'),
        'show_ui' => true, // Ensures that the UI is shown
        'show_in_menu' => false, // Slug of the parent menu
    );
    register_post_type('art', $args);
}
add_action('init', 'art_post_type');

// art fields

add_action('add_meta_boxes', function() {
    // Meta box for ZIP file upload
    add_meta_box('custom_file_upload', 'ZIP File Upload', 'custom_file_upload_callback', 'art', 'side', 'default');
    // Meta box for image upload
    add_meta_box('custom_image_upload', 'Image Upload', 'custom_image_upload_callback', 'art', 'side', 'default');
    
    add_meta_box('custom_color_picker', 'Select Colors', 'custom_color_picker_callback', 'art', 'side', 'default');
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
    if (isset($_POST['custom_colors_nonce'], $_POST['custom_colors']) && wp_verify_nonce($_POST['custom_colors_nonce'], basename(__FILE__))) {
        $colors = array_map('sanitize_hex_color', $_POST['custom_colors']);
        update_post_meta($post_id, '_custom_colors', $colors);
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

    // Ensure the global $post object is not null and is an object
    if (null !== $post && is_object($post)) {
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
    }
});


// Color picker for colors

function custom_color_picker_callback($post) {
    // Use nonce for verification
    wp_nonce_field(basename(__FILE__), 'custom_colors_nonce');

    // Get the color values if already set
    $colors = get_post_meta($post->ID, '_custom_colors', true);
    if (!is_array($colors)) {
        $colors = array('', '', '', ''); // Default to four empty values
    }

    // Output the color pickers
    for ($i = 0; $i < 4; $i++) {
        echo '<label>Color ' . ($i + 1) . ':</label> ';
        echo '<input type="text" name="custom_colors[]" class="custom-color-field" value="' . esc_attr($colors[$i]) . '" data-default-color="#ffffff" /><br>';
    }
}

add_action('admin_enqueue_scripts', function($hook_suffix) {
    global $post;

    if ($hook_suffix == 'post.php' || $hook_suffix == 'post-new.php') {
        if ('art' === $post->post_type) {
            wp_enqueue_style('wp-color-picker');
            wp_enqueue_script('wp-color-picker');
            add_action('admin_footer', 'color_picker_init');
        }
    }
});

function color_picker_init() {
    ?>
    <script>
    jQuery(document).ready(function($) {
        $('.custom-color-field').wpColorPicker();
    });
    </script>
    <?php
}
