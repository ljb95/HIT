<?php
define('TRANS','');
define('TRANS_THUMB','');
define('MEDIA',$CFG->vodserver); 
define('STORAGE','/appdata/lcmsdata'); 
define('STORAGE2','/appdata');
$LCFG = new stdClass();
$LCFG->allowexthtml = array('zip', 'html');
$LCFG->allowextword = array('hwp', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'pdf');
$LCFG->allowextvideo = array('mp4');
$LCFG->allowextref = array('hwp', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'pdf', 'mp4', 'mp3', 'wmv');
$LCFG->notallowfile = array('sh','exe','js','php','sql','jsp','asp','cgi','php3','php4','php5','unknown');

?>
