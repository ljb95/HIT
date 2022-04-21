<?php

defined('MOODLE_INTERNAL') || die;

global $CFG;

require_once $CFG->dirroot . '/local/online_attendance/lib.php';

if (is_siteadmin()) {
     
    $settings = new admin_settingpage('local_online_attendance', get_string('pluginname', 'local_online_attendance'));

    $ADMIN->add('localplugins', $settings);
    
    $name = 'local_online_attendance/absentfail';
    $title = get_string('grade:absentfail', 'local_online_attendance');
    $description = get_string('grade:absentfailtext', 'local_online_attendance');
    $options = array(1  => get_string('yes'),
                     0 => get_string('no'));
    $settings->add(new admin_setting_configselect($name, $title, $description, 0, $options));
    
    $name = 'local_online_attendance/maxscore';
    $title = get_string('grade:maxscore', 'local_online_attendance');
    $description = get_string('grade:maxscoretext', 'local_online_attendance');
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
    
    $name = 'local_online_attendance/minscore';
    $title = get_string('grade:minscore', 'local_online_attendance');
    $description = get_string('grade:minscoretext', 'local_online_attendance');
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
    
    $name = 'local_online_attendance/late';
    $title = get_string('grade:late', 'local_online_attendance');
    $description = get_string('grade:latetext', 'local_online_attendance');
    $default = 4;
    $choices = array(
        1 => '1',
        2 => '2',
        3 => '3',
        4 => '4',
        5 => '5',
        6 => '6',
        7 => '7',
        8 => '8',
        9 => '9',
        10 => '10',
    );
    $settings->add(new admin_setting_configselect($name, $title, $description, $default, $choices));
    
    $name = 'local_online_attendance/absent';
    $title = get_string('grade:absent', 'local_online_attendance');
    $description = get_string('grade:absenttext', 'local_online_attendance');
    $default = -1;
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
    
    $activityes = local_onattendance_realize_modules();
    
    foreach($activityes as $activity) {
        $name = 'local_online_attendance/mod_'.$activity->modname;
        $modname = get_string('pluginname', 'mod_'.$activity->modname);
        $title = get_string('grade:plugin', 'local_online_attendance', $modname);
        $description = get_string('grade:plugintext', 'local_online_attendance', $modname);
        $default = 1;
        $settings->add(new admin_setting_configcheckbox($name, $title, $description, $default));
        
        set_config('timetype_'.$activity->modname, $activity->timetype , 'local_online_attendance');
    }
    
}

