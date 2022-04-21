<?php

require(dirname(dirname(dirname(__FILE__))) . '/config.php');


$year = required_param('year', PARAM_INT); 
$term = required_param('term', PARAM_INT); 
$sync_times = optional_param_array('sync_time', array(), PARAM_INT);

$DB->delete_records('haksa_auto_sync',array('year'=>$year,'term'=>$term));
foreach($sync_times as $sync_time){
    $st = new stdClass();
    $st->year = $year;
    $st->term = $term;
    $st->hour = $sync_time;
    $st->userid = $USER->id;
    $st->timecreated = time();
    $DB->insert_record('haksa_auto_sync',$st);
}

set_config('haxa_year', $year);
set_config('haxa_term', $term);

echo '현재학기 설정을 완료했습니다.';