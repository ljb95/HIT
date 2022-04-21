<?php

require('../../config.php');

//$lcid_check = $DB->record_exists('lmsdata_user', array('userid'=>114));
//print_object($lcid_check);
//die;
// haxa_user_info 테이블에 있는 login_id 가 null이 아닌 사용자
$husers = $DB->get_records_sql('SELECT * FROM {haxa_user_info} WHERE login_id is not null');
$count = 0;
foreach($husers as $huser) {
    $lcid_check = $DB->record_exists('lmsdata_user', array('userid'=>$huser->userid));
        if(empty($lcid_check)) {
            $hcount = $DB->count_records('haxa_user_info', array('userid' => $huser->userid));

            if($hcount > 1) {

                $sql = ' SELECT hui1.* from {haxa_user_info} hui1 
                         JOIN (
                            SELECT MAX(id) as id FROM {haxa_user_info} WHERE userid = :userid GROUP BY userid 
                        ) hui2 ON hui2.id = hui1.id';
                $huser = $DB->get_record_sql($sql, array('userid' => $huser->userid));
            } 

            $luobj = new stdClass();

            $luobj->userid = $huser->userid;
            $luobj->eng_name = $huser->kor_rel_psn_nm;

            if($huser->stts_clsf_dcd == '100') {
                $luobj->usergroup = 'rs';
            } else if ($huser->stts_clsf_dcd == '300') {
                $luobj->usergroup = 'pr';
            }

            $luobj->b_temp  = 0;
            $luobj->b_mobile  = 1;
            $luobj->b_email  = 1;
            $luobj->univ  = $huser->posi_dept_nm;
            $luobj->major  = $huser->major_nm;
            $luobj->hyear  = 0;
            $luobj->b_tel  = 0;
            $luobj->b_univ  = 1;
            $luobj->b_major  = 1;

            if($luobj->usergroup == 'pr') {
                $luobj->psosok = $huser->posi_dept_nm;
            }
            $luid = $DB->insert_record('lmsdata_user', $luobj);

            if(empty($luid)) {
                print_object($lcobj);
            } else {
               $count++;
            }
        }
}

// user 테이블에 있으면서 haxa_user_info 테이블에 있는 login_id 가 null인 사용자

$sql = ' SELECT * FROM {user} ur
         JOIN (
            SELECT * FROM {haxa_user_info} WHERE kor_rel_psn_nm is null
        ) hui ON hui.userid = ur.id ';

$uids = $DB->get_records_sql($sql);

foreach($uids as $uid) {
    $luid_check = $DB->record_exists('lmsdata_user', array('userid'=>$uid->id));
    
    if(empty($luid_check)) {
        $luobj = new stdClass();
        
        $luobj->userid = $uid->id;
        $luobj->eng_name = $uid->firstname.$uid->lastname;
        $luobj->usergroup = 'rs';
        $luobj->b_temp  = 0;
        $luobj->b_mobile  = 1;
        $luobj->b_email  = 1;
        $luobj->hyear  = 0;
        $luobj->b_tel  = 0;
        $luobj->b_univ  = 1;
        $luobj->b_major  = 1;
        
        $luid = $DB->insert_record('lmsdata_user', $luobj);
        
        if(empty($luid)) {
            print_object($luobj);
        } else {
           $count++;
        }

    }
}

// course 테이블에는 있는데 haxa_course_info 테이블에 없는 강의 lmsdata_class 테이블에 저장 1번강의는 제외

$sql = ' SELECT * FROM (
            SELECT ur.*, hui.userid as userid  FROM {user} ur
            LEFT join {haxa_user_info} hui ON hui.userid = ur.id
        )where userid IS NULL and id <> 1 and id <> 2 ';

$uids = $DB->get_records_sql($sql);

foreach($uids as $uid) {
    $luid_check = $DB->record_exists('lmsdata_user', array('userid'=>$uid->id));
    
    if(empty($luid_check)) {
        $luobj = new stdClass();
        
        $luobj->userid = $uid->id;
        $luobj->eng_name = $uid->firstname.$uid->lastname;
        $luobj->usergroup = 'rs';
        $luobj->b_temp  = 0;
        $luobj->b_mobile  = 1;
        $luobj->b_email  = 1;
        $luobj->hyear  = 0;
        $luobj->b_tel  = 0;
        $luobj->b_univ  = 1;
        $luobj->b_major  = 1;
        
        $luid = $DB->insert_record('lmsdata_user', $luobj);
        
        if(empty($luid)) {
            print_object($luobj);
        } else {
           $count++;
        }

    }
}



print_object($count);