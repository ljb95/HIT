<?php
require_once dirname(dirname(dirname (__FILE__))).'/config.php';

if(!is_siteadmin($USER)){
    redirect($CFG->wwwroot);
}

global $SESSION;

if(!empty($SESSION->split_course)) {
       unset($SESSION->split_course);
}