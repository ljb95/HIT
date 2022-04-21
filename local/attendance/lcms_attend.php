<?php

require_once("../../config.php");

$id = required_param('id', PARAM_INT);
$track_id = optional_param('track_id',0, PARAM_INT);
$lcmsid = optional_param('lcms', 0, PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT);

 
if($track_id){ 
$track = $DB->get_record('lcms_track',array('id'=>$track_id));
$track->progress = 100;

$DB->update_record('lcms_track',$track);
} else {
    $track = new stdClass();
    $track->lcms = $lcmsid;
    $track->userid = $userid;
    $track->timeview = time();
    $track->playtime = 0;
    $track->progress = 100;
    $DB->insert_record('lcms_track',$track);
}

redirect('index.php?id='.$id);