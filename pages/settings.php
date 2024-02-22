<div class="wrap">
    <h1>T.M. Wrath Plugin Settings</h1>
    <form method="post" action="options.php">
        <?php settings_fields('tmwrath-settings-group'); ?>
        <h2>General Settings</h2>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="tmwrath_maintenance_mode">Maintenance Mode</label>
                    </th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><span>Maintenance Mode</span></legend>
                            <label>
                                <input name="tmwrath_maintenance_mode" type="checkbox" id="tmwrath_maintenance_mode" value="1" <?php checked(1, get_option('tmwrath_maintenance_mode', 0)); ?>>
                                Enable Maintenance Mode
                            </label>
                            <p class="description">If enabled, the site will be put into maintenance mode.</p>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="disable_default_post_type">Disable Default Post Type</label>
                    </th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><span>Disable Default Post Type</span></legend>
                            <label>
                                <input name="disable_default_post_type" type="checkbox" id="disable_default_post_type" value="1" <?php checked(1, get_option('disable_default_post_type', 0)); ?>>
                                Disable Default Post Type
                            </label>
                            <p class="description">If enabled, the default post type will be disabled.</p>
                        </fieldset>
                    </td>
                </tr>
            </tbody>
        </table>
        <h2>Art Posts Settings</h2>
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">
                        <label for="enable_art_post_type">Enable Art Post Type</label>
                    </th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><span>Enable Art Post Type</span></legend>
                            <label>
                                <input name="enable_art_post_type" type="checkbox" id="enable_art_post_type" value="1" <?php checked(1, get_option('enable_art_post_type', 0)); ?>>
                                Enable Art Post Type
                            </label>
                            <p class="description">If enabled, the art post type will be available.</p>
                        </fieldset>
                    </td>
                </tr>
                <tr>
                    <th scope="row">
                        <label for="enable_art_file_upload">Enable Art Post File Upload</label>
                    </th>
                    <td>
                        <fieldset>
                            <legend class="screen-reader-text"><span>Enable Art Post File Upload</span></legend>
                            <label>
                                <input name="enable_art_file_upload" type="checkbox" id="enable_art_file_upload" value="1" <?php checked(1, get_option('enable_art_file_upload', 0)); ?>>
                                Enable Art Post File Upload
                            </label>
                            <p class="description">If enabled, the file upload (not image upload) in the art post type will be available.</p>
                        </fieldset>
                    </td>
                </tr>
            </tbody>
        </table>
        <?php submit_button(); ?>
    </form>
</div>

