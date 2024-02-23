<?php


// Load CSS
function load_sidebar_css() {
    wp_register_style('sidebar_css', WRATH_URL . 'assets/css/sidebar.css');
    wp_enqueue_style('sidebar_css');
}
add_action('wp_enqueue_scripts', 'load_sidebar_css');

// Load JS
function load_sidebar_js() {
    wp_register_script('sidebar_js', WRATH_URL . 'assets/js/sidebar.js', array('jquery'), '1.0', true);
    wp_enqueue_script('sidebar_js');
}
add_action('wp_enqueue_scripts', 'load_sidebar_js');


function sidebars()
{
	register_sidebar(
		array(

			'name' => 'Menu Sidebar',
			'id' => 'menu_sidebar',
			'before_title' => '<h4 class="widget-title">',
			'after_title' => '</h4>',

		)
	);
}
add_action('widgets_init', 'sidebars');

function menu_sidebar() {
    include_once WRATH_PAGES_PATH . 'sidebar.php';
}
add_action('wp_head', 'menu_sidebar');
