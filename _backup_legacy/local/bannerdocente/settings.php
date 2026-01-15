<?php
defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    
    // Crear el objeto settings si no existe (solución para error 'call on null')
    if (!isset($settings)) {
        $settings = new admin_settingpage('local_bannerdocente', get_string('pluginname', 'local_bannerdocente'));
        $ADMIN->add('localplugins', $settings);
    }

    // Cabecera
    $settings->add(new admin_setting_heading('local_bannerdocente/config',
        get_string('pluginname', 'local_bannerdocente'),
        get_string('configintro', 'local_bannerdocente')
    ));

    // DB Host
    $settings->add(new admin_setting_configtext('local_bannerdocente/dbhost',
        get_string('dbhost', 'local_bannerdocente'),
        get_string('dbhost_desc', 'local_bannerdocente'),
        'localhost', PARAM_TEXT
    ));

    // DB Name
    $settings->add(new admin_setting_configtext('local_bannerdocente/dbname',
        get_string('dbname', 'local_bannerdocente'),
        get_string('dbname_desc', 'local_bannerdocente'),
        'banner_db', PARAM_TEXT
    ));

    // DB User
    $settings->add(new admin_setting_configtext('local_bannerdocente/dbuser',
        get_string('dbuser', 'local_bannerdocente'),
        get_string('dbuser_desc', 'local_bannerdocente'),
        '', PARAM_TEXT
    ));

    // DB Pass
    $settings->add(new admin_setting_configpasswordunmask('local_bannerdocente/dbpass',
        get_string('dbpass', 'local_bannerdocente'),
        get_string('dbpass_desc', 'local_bannerdocente'),
        ''
    ));

    // DB Prefix
    $settings->add(new admin_setting_configtext('local_bannerdocente/dbprefix',
        get_string('dbprefix', 'local_bannerdocente'),
        get_string('dbprefix_desc', 'local_bannerdocente'),
        '', PARAM_TEXT
    ));
}
