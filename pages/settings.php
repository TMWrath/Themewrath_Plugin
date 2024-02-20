<!-- Begin settings form -->
<form method="post" action="options.php">
    <?php settings_fields('tmwrath-settings-group'); ?>
    <?php do_settings_sections('tmwrath-settings'); ?>
    <?php submit_button(); ?>
</form>
<!-- End settings form -->
<?php

function tmwrath_settings_section_callback()
{
    echo '<p></p>';
}

function tmwrath_maintenance_mode_callback()
{
    $value = get_option('tmwrath_maintenance_mode');
    echo '<input type="checkbox" id="tmwrath_maintenance_mode" name="tmwrath_maintenance_mode" value="1" ' . checked(1, $value, false) . '/>';
    echo '<label for="tmwrath_maintenance_mode">Enable Maintenance Mode</label>';
}
