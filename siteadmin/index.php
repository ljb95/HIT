<?php
require(dirname(dirname(__FILE__)) . '/config.php');

$LMSUSER = $DB->get_record('lmsdata_user',array('userid'=>$USER->id));

 if($LMSUSER->usergroup != 'de' && $LMSUSER->usergroup != 'pr'){ // de => 부서관리자 
header("Location: ".$CFG->wwwroot."/siteadmin/manage/course_list.php"); /* Redirect browser */
 } else { 
     header("Location: ".$CFG->wwwroot."/siteadmin/manage/course_list.php?coursetype=1"); /* Redirect browser */
 }


exit();
?>