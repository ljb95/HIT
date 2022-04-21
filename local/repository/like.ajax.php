<?php
require_once("../../config.php");
require_once("./lib.php");
require_once("./config.php");
require_once($CFG->dirroot . "/lib/coursecatlib.php");
require_once($CFG->dirroot . "/lib/filelib.php");

$context = context_system::instance();
$PAGE->set_context($context);

$instance = optional_param('instance', 0, PARAM_INT);//코스모듈 아이디

//현재 유저가 좋아요 누른적이 있는지 확인
$usql = "select * from {lcms_like} where instance = :instance and userid = :userid ";
$userlog = $DB->get_record_sql($usql,array('instance'=>$instance,'userid'=>$USER->id));
$data = new stdClass();
if($userlog){
    //update
    if($userlog->likey==0){
        $data->id = $userlog->id;
        $data->likey = 1;
        $DB->update_record('lcms_like', $data);
    }else{
        $data->id =$userlog->id;
        $data->likey = 0;
        $DB->update_record('lcms_like', $data);
    }
    
}else{
    //insert
    $data = new stdClass();
    $data->userid = $USER->id;
    $data->course = 0;
    $data->instance = $instance;
    $data->likey = 1;
    
    $DB->insert_record('lcms_like', $data);
}


//전체 좋아요 수 data
$sql = "select sum(likey) as sum from {lcms_like} where instance = :instance group by instance";

$result = $DB->get_field_sql($sql,array('instance'=>$instance));

echo $result;
?>

