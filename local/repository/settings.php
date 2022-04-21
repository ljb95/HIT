<?php

defined('MOODLE_INTERNAL') || die;

if (is_siteadmin()) {

    $settings = new admin_settingpage('local_repository', get_string('pluginname', 'local_repository'));

    $ADMIN->add('localplugins', $settings);
    
    $name = 'local_repository/ftp_server';
    $title = get_string('ftp_server', 'local_repository');
    $description = '';
    $options = array(1  => get_string('yes'),
                     0 => get_string('no'));
    $settings->add(new admin_setting_configtext($name, $title, $description));
    
    $name = 'local_repository/ftp_user';
    $title = get_string('ftp_user', 'local_repository');
    $description = '';
    $options = array(1  => get_string('yes'),
                     0 => get_string('no'));
    $settings->add(new admin_setting_configtext($name, $title, $description));
    
     $name = 'local_repository/ftp_pw';
    $title = get_string('ftp_pw', 'local_repository');
    $description = '';
    $options = array(1  => get_string('yes'),
                     0 => get_string('no'));
    $settings->add(new admin_setting_configtext($name, $title, $description));
    
}

