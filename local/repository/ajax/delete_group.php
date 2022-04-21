<?php
    require_once("../../../config.php");
    
    $id = optional_param('id', 0, PARAM_INT);
    
    //레파지토리에 그룹 아이디 초기화
    if($reps = $DB->get_records('lcms_repository',array('groupid'=>$id,'userid'=>$USER->id))){
        foreach($reps as $rep){
            $rep->groupid = '';
            $DB->update_record('lcms_repository',$rep);
        }
    }
    
    $DB->delete_records('lcms_repository_groups',array('id'=>$id,'userid'=>$USER->id));
    
    
