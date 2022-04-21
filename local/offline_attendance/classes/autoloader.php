<?php
// autoloader

function local_offline_attendance_autoloader($class) {
    global $CFG;
    static $classmap;
    if (!isset($classmap)) {
        $classmap = array(
        'offline_attendance' => 'offline_attendance.php'
        );
    }

    if (isset($classmap[$class])) {
        require_once($CFG->dirroot . '/local/offline_attendance/classes/' . $classmap[$class]);
    }
}

spl_autoload_register('local_offline_attendance_autoloader');