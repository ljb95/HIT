<?php

defined('MOODLE_INTERNAL') || die();

// List of observers.
$observers = array(
    array(
        'eventname' => '\core\event\user_created',
        'callback'  => 'local_lmsdata_observer::user_created',
    ),
    array(
        'eventname' => '\core\event\user_deleted',
        'callback'  => 'local_lmsdata_observer::user_deleted',
    ),
    array(
        'eventname' => '\core\event\user_loggedin',
        'callback'  => 'local_lmsdata_observer::userloggedin',
    ),
);

