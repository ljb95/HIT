<?php

require_once("../../config.php");

$id = required_param('id', PARAM_INT);
$track_id = optional_param('track_id',0, PARAM_INT);
$lcmsid = optional_param('lcms', 0, PARAM_INT);
$userid = optional_param('userid', 0, PARAM_INT);

 
if($track_id){ 
$track = $DB->get_record('lcms_track',array('id'=>$track_id));

$DB->delete_records('lcms_track',array('id'=>$track_id));
}

redirect('index.php?id='.$id);