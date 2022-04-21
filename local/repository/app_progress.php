<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot.'/mod/lcms/lib.php';
require_once $CFG->dirroot.'/mod/lcmsprogress/locallib.php';
require_once('lib.php');

$service_id = optional_param('service_id', '', PARAM_RAW); 
$user_id = required_param('user_id',PARAM_RAW);
$content_id = required_param('content_id', PARAM_INT);  // LCMS ID 
$duration = optional_param('duration', 0, PARAM_INT);
$event = optional_param('event', '', PARAM_RAW); // pause,seek,finish,progress
$from = optional_param('from', 0, PARAM_INT);
$to = optional_param('to', 0, PARAM_INT);
$continue_time = optional_param('continue_time', 0, PARAM_INT);
$program_ver = optional_param('program_ver','',PARAM_RAW);
$device_model = optional_param('device_model','',PARAM_TEXT);
$user_agent = optional_param('user_agent','',PARAM_TEXT);
$insflag = required_param('insflag', PARAM_RAW);  // (최초값:S , 진행중값:M , 종료값:E )
$vod_total_time = required_param('vod_total_time', PARAM_RAW);  // (최초값:S , 진행중값:M , 종료값:E )


$user_id =  jinoapp_decrypt($user_id);


$lcms = $DB->get_record('lcms', array('id' => $content_id));
$track = $DB->get_record('lcms_track',array('lcms'=>$lcms->id,'userid'=>$user_id));

$returnvalue = new stdClass();

 if($user_id && $from < $to){
        $lcms_pt = new stdClass();
        $lcms_pt->userid = $user_id;
        $lcms_pt->lcmsid = $lcms->id;
        $lcms_pt->rtype = '';
        $lcms_pt->positionpage = 1;
        
        $lcms_pt->positionfrom = $from;
        $lcms_pt->positionto = $to;
        
        $lcms_pt->timereg = time();
        $lcms_pt->timecreated = time();
        
        if($lcms_pt->positionto > 0 && $vod_total_time>1){
            
            if($lcms->duration!=$duration){
                //$cour->duration = $duration;
                //$DB->update_record('lcms',$cour);
            }
            
            $query = 'select count(*) from {lcms_playtime} 
                where userid=:userid and lcmsid=:lcmsid and positionpage=:positionpage 
                and positionfrom<=:positionfrom and positionto>=:positionto';
            $params = array('userid'=>$user_id,'lcmsid'=>$lcms->id,'positionpage'=>$lcms_pt->positionpage,
                'positionto'=>$lcms_pt->positionto,'positionfrom'=>$lcms_pt->positionfrom);
            $playcount = $DB->count_records_sql($query,$params);
            
            //플레이시간을 저장한다. 
            if($playcount==0) $DB->insert_record('lcms_playtime',$lcms_pt);
            
            if($lcms->duration!=$duration){
                $lcms->duration = $duration;
                $DB->update_record('lcms',$lcms);
            }
            
            if(!$track->id){
                $track = new stdClass();
                $track->lcms = $lcms->id;
                $track->userid = $user_id;
                $track->attempts = 1;
            }
            $track->lasttime = $lcms_pt->positionto;
            $track->lastpage = $lcms_pt->positionpage;
            $track->playtime = lcms_get_progress($lcms->id,$user_id);
            $track->playpage = lcms_get_progress_page($lcms->id,$user_id);
            if($lcms->rtype==''){
                if($duration>1) $track->progress = round($track->playtime/$vod_total_time*100);
            }else{
                if($duration>1) $track->progress = round($track->playpage/$vod_total_time*100);
            }
            $track->timeview = time();
            if($track->id){
                $DB->update_record('lcms_track',$track);
            }else{
                $DB->insert_record('lcms_track',$track);
            }
            
            lcmsprogress_update_progress_score($lcms->course, $user_id);
            
            $returnvalue->last = date('Y-m-d H:i:s',$track->timeview); 
            $ptm = time_from_seconds($track->playtime);
            $returnvalue->totaltime = $ptm->h.':'.$ptm->m.':'.$ptm->s;
            $returnvalue->totalpage = $track->playpage;
            $returnvalue->progress = $track->progress.' %';
            
        } else { 
            $returnvalue->text = 'duration Zero';
        }
    }
 
$returnvalue->track = $track;


$returnvalue->response = new stdClass();
$returnvalue->response->result = 'true';

@header('Content-type: application/json; charset=utf-8');
echo json_encode($returnvalue);

