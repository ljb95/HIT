<?php
    require_once dirname(dirname (dirname (__FILE__))).'/config.php';
    include_once (dirname(__FILE__).'/lib.php');
    
    //로그파일 생성
    $host= gethostname();
    $guestip = $_SERVER['REMOTE_ADDR'];
    $serverip = gethostbyname($host);
    $lastlogin = date("Y-m-d H:i:s",$USER->lastlogin);
    $accessfile = $_SERVER['PHP_SELF'];
    $username = $USER->username;
    $url = $_SERVER['REQUEST_SCHEME'].'://'.$_SERVER["HTTP_HOST"].$_SERVER['REQUEST_URI'];
    $paramtype='';
    if(!empty($_REQUEST)){
     if(!empty($_GET)){
       $paramtype=',GET';
      }else{
       $paramtype=',POST';
      }
     }

    $filename = '/var/www/moodlelogs/LS_'.date('Ymd').'.log';
    $file = fopen($filename,'a') or die("Unable to open file!");
    fwrite($file,$username.','.$guestip.','.$lastlogin.','.$serverip.','.$accessfile.','.$lastlogin.','.$url.$paramtype.PHP_EOL.PHP_EOL);
    fclose($file);
    
    $separator =  explode(DIRECTORY_SEPARATOR, getcwd());
    $topmenu = array_pop ($separator);
    
    $LMSUSER = $DB->get_record('lmsdata_user',array('userid'=>$USER->id));
    
    if(($LMSUSER->usergroup != 'de' && $LMSUSER->usergroup != 'pr') || $LMSUSER->menu_auth == 9){
        $roleadmin = true;
    } else {
        $roleadmin = false;
    }
    
    $coursecontext = context_system::instance();   // SYSTEM context.
    $PAGE->set_context($coursecontext);
    
    if(!is_siteadmin($USER)){
        redirect($CFG->wwwroot); 
    }
?>
<!doctype html>
<html dir="ltr" lang="ko" xml:lang="ko">
 <head>
     <title><?php echo $SITE->fullname; ?></title> 
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <?php //echo $OUTPUT->standard_head_html(); ?>
  <link rel="stylesheet" type="text/css" href="<?php echo $CFG->wwwroot; ?>/siteadmin/css/jquery-ui.css">
  <link rel="stylesheet" type="text/css" href="<?php echo $CFG->wwwroot; ?>/siteadmin/css/style.css">
  <link rel="stylesheet" type="text/css" href="<?php echo $CFG->wwwroot; ?>/siteadmin/css/loading.css">
  
  <script type="text/javascript" src="<?php echo $CFG->wwwroot; ?>/siteadmin/js/lib/jquery-1.11.2.min.js"></script>
  <script type="text/javascript" src="<?php echo $CFG->wwwroot; ?>/siteadmin/js/lib/jquery-ui.min.js"></script>
  <script type="text/javascript" src="<?php echo $CFG->wwwroot; ?>/siteadmin/js/lib/jquery.ui.datepicker-ko.js"></script>
  <script type="text/javascript" src="<?php echo $CFG->wwwroot; ?>/siteadmin/js/loading.js"></script>
  <script type="text/javascript" src="<?php echo $CFG->wwwroot; ?>/siteadmin/js/common.js"></script>
<?php
if(isset ($js) && is_array($js)) {
    foreach($js as $j) {
        echo '<script type="text/javascript" src="'.$j.'"></script>'; 
    }
}
?>
 </head>
 <body>
	<div class="wrap">

		<div id="header">

                    <h1 class="logo"><a href="<?php echo $CFG->wwwroot.'/siteadmin/index.php'; ?>"><?php echo $SITE->fullname; ?></a></h1>
			<ul class="gnb" style="float:left;">
                                <li><a href="<?php echo $CFG->wwwroot; ?>/siteadmin/manage/course_list.php"<?php echo ($topmenu == 'manage') ? ' class="selected"' : ''; ?>><?php echo get_string('course_management', 'local_lmsdata'); ?></a></li>
                                <?php if($roleadmin){ // de => 부서관리자 ?>
				<li><a href="<?php echo $CFG->wwwroot; ?>/siteadmin/support/popup.php"<?php echo ($topmenu == 'support') ? ' class="selected"' : ''; ?>><?php echo get_string('site_management', 'local_lmsdata'); ?></a></li>
                                <li><a href="<?php echo $CFG->wwwroot; ?>/siteadmin/users/info.php"<?php echo ($topmenu == 'users') ? ' class="selected"' : ''; ?>><?php echo get_string('user_management', 'local_lmsdata'); ?></a></li>
                                <li><a href="<?php echo $CFG->wwwroot; ?>/siteadmin/board/list.php"<?php echo ($topmenu == 'board') ? ' class="selected"' : ''; ?>><?php echo get_string('board_management', 'local_lmsdata'); ?></a></li>
                                <li><a href="<?php echo $CFG->wwwroot; ?>/siteadmin/stats/course_all.php"<?php echo ($topmenu == 'stats') ? ' class="selected"' : ''; ?>><?php echo get_string('stats_management', 'local_lmsdata'); ?></a></li>
                                <li><a target="_blank" href="<?php echo $CFG->wwwroot; ?>/local/repository/index.php"<?php echo ($topmenu == 'contents') ? ' class="selected"' : ''; ?>><?php echo get_string('lcms_management', 'local_lmsdata'); ?></a></li>
                                <?php } ?>
                                <li style="float:right;padding-right:10px;"><input type="button" value="<?php echo get_string('logout', 'local_lmsdata'); ?>" onclick="location.href='<?php echo $CFG->wwwroot . '/login/logout.php?sesskey=' . $USER->sesskey; ?>'" class="gray_btn_small"/></li>
                                <li style="float:right;padding-right:10px;"><input type="button" value="<?php echo get_string('golms', 'local_lmsdata'); ?>" onclick="location.href='<?php echo $CFG->wwwroot; ?>'" class="orange_btn_small"/></li>
			</ul> <!--GNB End-->
		</div> <!--Header End-->
