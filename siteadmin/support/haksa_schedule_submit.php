<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
<?php
require_once dirname(dirname(dirname (__FILE__))).'/config.php';
    
// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/support/hsksa_schedule_submit.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

require_once($CFG->libdir . '/datalib.php');

$write = optional_param('write', 0, PARAM_INT);
$modifide = optional_param('modifide', 0, PARAM_INT);
$year = optional_param('year', date('Y'), PARAM_INT);
$univ = optional_param('c_univ', 2, PARAM_TEXT);
$hyear = optional_param('c_hyear', 1, PARAM_TEXT);
$schedule = optional_param_array('schedule', '', PARAM_TEXT);
$timestart = optional_param_array('startdate', null, PARAM_ALPHANUMEXT);
$timeend = optional_param_array('enddate', null, PARAM_ALPHANUMEXT);
$modi_list = optional_param('modi_list', null, PARAM_TEXT);

global $DB, $USER;
if($write != 0){
    $arr_num = 0;
//    print_object($timeend[0]);
    for($i=1; $i<=$write; $i++){
        $content = new stdClass();

        $startstemp = strtotime($timestart[$arr_num]);
        $ydate = date('Y',$startstemp);

        $content->year = $ydate;
        $content->hyear = $hyear;
        $content->userid = $USER->id;
        $content->univ = $univ;
        $content->startdate = strtotime($timestart[$arr_num]);
        $content->enddate = strtotime($timeend[$arr_num]);
        $content->schedule = $schedule[$arr_num];
        $content->timecreated = time();
        $content->timemodified = time();
        
        $DB->insert_record('lmsdata_haksa_schedule', $content);
        $arr_num++;
    }    
}

if($modifide == 1){
    $list = explode(',',$modi_list);
//    print_object($_POST);
    $count = count($list);
    $arrnum = 0;
//    print_object($list);
//    print_object($count);
    
    $content = new stdClass();
    
    for($i=1; $i<$count; $i++){
        $startstemp = strtotime($_POST['startdate'.$list[$arrnum]]);
        $ydate = date('Y',$startstemp);

        $content->id = $list[$arrnum];
        $content->year = $ydate;
        $content->userid = $USER->id;
        $content->startdate = strtotime($_POST['startdate'.$list[$arrnum]]);
        $content->enddate = strtotime($_POST['enddate'.$list[$arrnum]]);
        $content->schedule = $_POST['schedule'.$list[$arrnum]];
        $content->timecreated = time();
        
        $DB->update_record('lmsdata_haksa_schedule', $content);
        $arrnum++;
    }
}
?>
<script>
    window.onload = function(){
        location.href = '<?php echo $CFG->wwwroot;?>/siteadmin/support/haksa_schedule.php?year=<?php echo $year;?>&hyear=<?php echo $hyear;?>&univ=<?php echo $univ;?>'
    }
</script>