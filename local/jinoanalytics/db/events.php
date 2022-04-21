<?php
defined('MOODLE_INTERNAL') || die();

$observers = array(
    array(
        'eventname' => '\local_jinoanalytics\event\jinoanalytics_index',
        'includefile'     => '/local/jinoanalytics/locallib.php',
        'callback' => 'local_jinoanalytics_index_handler',
        'internal' => false
    ),
    array(
        'eventname' => '\local_jinoanalytics\event\jinoanalytics_view',
        'includefile'     => '/local/jinoanalytics/locallib.php',
        'callback' => 'local_jinoanalytics_view_handler',
        'internal' => false
    ),
);
