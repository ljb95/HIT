<?php
require_once("../../config.php");
require_once("./lib.php");
require_once($CFG->dirroot . "/lib/coursecatlib.php");

$PAGE->set_url('/local/repository/detail.php');
$PAGE->set_pagelayout('standard');

$context = context_system::instance();
$PAGE->set_context($context);

$gid = optional_param('gid', 0, PARAM_INT);

require_login();

unset($_REQUEST['gid']);

foreach($_REQUEST as $content => $id){
    $DB->update_record('lcms_repository',array('id'=>$id ,'groupid'=>$gid));
}

redirect('index.php');
