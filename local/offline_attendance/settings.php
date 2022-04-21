<?php

defined('MOODLE_INTERNAL') || die;

if (is_siteadmin()) {
     
    $settings = new admin_settingpage('local_offline_attendance', get_string('pluginname', 'local_offline_attendance'));

    $ADMIN->add('localplugins', $settings);
    
    $name = 'local_offline_attendance/auto';
    $title = get_string('grade:auto', 'local_offline_attendance');
    $description = get_string('grade:autotext', 'local_offline_attendance');
    $options = array(1  => get_string('yes'),
                     0 => get_string('no'));
    $settings->add(new admin_setting_configselect($name, $title, $description, 1, $options));
    
    $name = 'local_offline_attendance/absentfail';
    $title = get_string('grade:absentfail', 'local_offline_attendance');
    $description = get_string('grade:absentfailtext', 'local_offline_attendance');
    $options = array(1  => get_string('yes'),
                     0 => get_string('no'));
    $settings->add(new admin_setting_configselect($name, $title, $description, 1, $options));
    
    $name = 'local_offline_attendance/maxscore';
    $title = get_string('grade:maxscore', 'local_offline_attendance');
    $description = get_string('grade:maxscoretext', 'local_offline_attendance');
    $default = 20;
    $choices = array(
        0 => '0',
        10 => '10',
        20 => '20',
        30 => '30',
        40 => '40',
        50 => '50',
        60 => '60',
        70 => '70',
        80 => '80',
        90 => '90',
        100 => '100'
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));
    
    $name = 'local_offline_attendance/minscore';
    $title = get_string('grade:minscore', 'local_offline_attendance');
    $description = get_string('grade:minscoretext', 'local_offline_attendance');
    $default = 0;
    $choices = array(
        0 => '0',
        10 => '10',
        20 => '20',
        30 => '30',
        40 => '40',
        50 => '50',
        60 => '60',
        70 => '70',
        80 => '80',
        90 => '90',
        100 => '100'
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));
    
    $name = 'local_offline_attendance/late';
    $title = get_string('grade:late', 'local_offline_attendance');
    $description = get_string('grade:latetext', 'local_offline_attendance');
    $default = -1;
    $choices = array(
        -1 => '-1',
        -2 => '-2',
        -3 => '-3',
        -4 => '-4',
        -5 => '-5',
        -6 => '-6',
        -7 => '-7',
        -8 => '-8',
        -9 => '-9',
        -10 => '-10',
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));
    
    $name = 'local_offline_attendance/early';
    $title = get_string('grade:early', 'local_offline_attendance');
    $description = get_string('grade:earlytext', 'local_offline_attendance');
    $default = -1;
    $choices = array(
        -1 => '-1',
        -2 => '-2',
        -3 => '-3',
        -4 => '-4',
        -5 => '-5',
        -6 => '-6',
        -7 => '-7',
        -8 => '-8',
        -9 => '-9',
        -10 => '-10',
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));
    
    $name = 'local_offline_attendance/absent';
    $title = get_string('grade:absent', 'local_offline_attendance');
    $description = get_string('grade:absenttext', 'local_offline_attendance');
    $default = 0;
    $choices = array(
         0 => '0',
        -1 => '-1',
        -2 => '-2',
        -3 => '-3',
        -4 => '-4',
        -5 => '-5',
        -6 => '-6',
        -7 => '-7',
        -8 => '-8',
        -9 => '-9',
        -10 => '-10',
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));
}

