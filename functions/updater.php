<?php

add_filter('pre_set_site_transient_update_plugins', 'check_plugin_update_from_github');

function check_plugin_update_from_github($transient) {
    if (empty($transient->checked)) {
        return $transient;
    }

    $apiUrl = 'https://api.github.com/repos/TMWrath/themewrath_plugin/releases/latest';
    $response = wp_remote_get($apiUrl, array(
        'headers' => array('Accept' => 'application/vnd.github.v3+json')
    ));

    if (is_wp_error($response) || 200 !== wp_remote_retrieve_response_code($response)) {
        return $transient;
    }

    $release = json_decode(wp_remote_retrieve_body($response), true);
    // Assuming your main plugin file is named 'themewrath_plugin.php' and resides in a folder named 'theme-wrath-plugin'
    $pluginSlug = WRATH_PATH . '/themewrath_plugin.php'; 
    // Make sure to include the WordPress Administration API to use get_plugin_data()
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');
    $currentVersion = get_plugin_data(WRATH_FILE)['Version'];

    if (isset($release['tag_name']) && version_compare($currentVersion, $release['tag_name'], '<')) {
        $transient->response[$pluginSlug] = (object) array(
            'new_version' => $release['tag_name'],
            'package' => $release['zipball_url'],
            'url' => $release['html_url']
        );
    }

    return $transient;
}

add_filter('upgrader_source_selection', 'rename_github_plugin_folder', 10, 4);

function rename_github_plugin_folder($source, $remote_source, $upgrader, $hook_extra) {
    global $wp_filesystem;

    if (isset($hook_extra['plugin']) && $hook_extra['plugin'] === plugin_basename(__FILE__)) {
        $correctedSource = trailingslashit($remote_source) . dirname(plugin_basename(__FILE__));
        if (@$wp_filesystem->move($source, $correctedSource, true)) {
            return $correctedSource;
        } else {
            return new WP_Error('rename_failed', __('The plugin folder rename failed.', 'theme-wrath-plugin'));
        }
    }

    return $source;
}
