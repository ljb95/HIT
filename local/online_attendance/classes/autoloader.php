<?php
// autoloader

require_once $CFG->dirroot . '/local/online_attendance/lib.php';

function local_offline_attendance_autoloader($class) {
    global $CFG;
    static $classmap;
    if (!isset($classmap)) {
        $classmap = array(
            'online_attendance' => 'online_attendance.php',
            'online_attendance_batchset' => 'online_attendance_batchset.php'
        );
    }

    if (isset($classmap[$class])) {
        require_once($CFG->dirroot . '/local/online_attendance/classes/' . $classmap[$class]);
    }
    
    // 출석부 추가 class
    $activityes = local_onattendance_realize_modules();

    foreach($activityes as $activity) {
        if ($activity->classname == $class) {
            require_once $activity->path;
        }
    }
}

spl_autoload_register('local_offline_attendance_autoloader');