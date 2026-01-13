<?php
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    // Section header
    $settings->add(new admin_setting_heading('local_bannerdocente/config',
        get_string('pluginname', 'local_bannerdocente'),
        get_string('configintro', 'local_bannerdocente')));

    // Database Host
    $settings->add(new admin_setting_configtext('local_bannerdocente/dbhost',
        get_string('dbhost', 'local_bannerdocente'),
        get_string('dbhost_desc', 'local_bannerdocente'), 'localhost', PARAM_TEXT));

    // Database Name
    $settings->add(new admin_setting_configtext('local_bannerdocente/dbname',
        get_string('dbname', 'local_bannerdocente'),
        get_string('dbname_desc', 'local_bannerdocente'), 'banner_db', PARAM_TEXT));

    // Database User
    $settings->add(new admin_setting_configtext('local_bannerdocente/dbuser',
        get_string('dbuser', 'local_bannerdocente'),
        get_string('dbuser_desc', 'local_bannerdocente'), '', PARAM_TEXT));

    // Database Password
    $settings->add(new admin_setting_configpasswordunmask('local_bannerdocente/dbpass',
        get_string('dbpass', 'local_bannerdocente'),
        get_string('dbpass_desc', 'local_bannerdocente'), ''));

    // Table Prefix
    $settings->add(new admin_setting_configtext('local_bannerdocente/dbprefix',
        get_string('dbprefix', 'local_bannerdocente'),
        get_string('dbprefix_desc', 'local_bannerdocente'), '', PARAM_TEXT));
}
