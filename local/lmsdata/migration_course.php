<?php

require('../../config.php');

$hcourses = $DB->get_records('haxa_course_info');

// haxa_course_info 테이블에 있는 강의 lmsdata_class 테이블에 저장
$count = 0;
foreach($hcourses as $hcourse) {
    $cid_check = $DB->record_exists('course', array('id'=>$hcourse->course));
    $lcid_check = $DB->record_exists('lmsdata_class', array('course'=>$hcourse->course));
    if($cid_check && !$lcid_check) {
        $lcobj = new Stdclass();
        
        $lcobj->course = $hcourse->course;
        $lcobj->subject_id = $hcourse->shyy.'_'.$hcourse->shtm_dcd.'_'.$hcourse->gwamok_no;
        $lcobj->category = $DB->get_field('course', 'category', array('id'=>$hcourse->course));
        $lcobj->kor_lec_name = $hcourse->sbjt_nm;
        $lcobj->eng_lec_name = $hcourse->sbjt_nm;
        
        if(!empty($hcourse->lt_prof_no)) {
            $lcobj->prof_userid = $DB->get_field('user', 'id', array('username' => $hcourse->lt_prof_no));
        }
        $lcobj->year = $hcourse->shyy;
        
        if($hcourse->shtm_dcd  == '10') {
            $lcobj->term = 1;
        } else if($hcourse->shtm_dcd  == '20') {
            $lcobj->term = 2;
        }
        
        $startdate = strtotime(str_replace('.', '/', $hcourse->from_dt));
        $lcobj->timestart = $startdate;
        
        if(!empty($hcourse->to_dt)) {
            $enddate = strtotime(str_replace('.', '/', $hcourse->to_dt));
            $lcobj->timeend = $enddate;
        }
        
        $lcobj->isnonformal = 0;
        
        if($hcourse->lesson_val == '영어강의') {
            $lcobj->gubun = 2;
        } else {
            $lcobj->gubun = 1;
        }
        
        $lcobj->timemodified = time();
        $lcobj->ohakkwa = $hcourse->orgn_clsf_dcd;
        $lcobj->domain = $hcourse->asgn_sust_cd;
        $lcobj->certificate = 0;
        $lcobj->isreged = 0;
        $lcobj->isopened = 0;
        $lcobj->purpose = 1;
        
        $lcid = $DB->insert_record('lmsdata_class', $lcobj);
        
        if(empty($lcid)) {
            print_object($lcobj);
        } else {
           $count++;
        }
    }
    
}


// course 테이블에는 있는데 haxa_course_info 테이블에 없는 강의 lmsdata_class 테이블에 저장 1번강의는 제외

$sql = ' SELECT * FROM (
            SELECT co.*, hci.course 
            FROM {course} co
            LEFT join {haxa_course_info} hci ON hci.course = co.id
        ) WHERE course IS NULL AND id <> 1 ';

$cids = $DB->get_records_sql($sql);

foreach($cids as $cid) {
    $lcid_check = $DB->record_exists('lmsdata_class', array('course'=>$cid->id));
    
    if(empty($lcid_check)) {
        $lcobj = new Stdclass();

        $lcobj->course = $cid->id;
        $lcobj->subject_id = $cid->id.'_'.$cid->sortorder;
        $lcobj->category = $cid->category;
        $lcobj->kor_lec_name = $cid->fullname;
        $lcobj->eng_lec_name = $cid->fullname;
        $lcobj->prof_userid = 0;
        $lcobj->year = 0;
        $lcobj->term = 0;
        $lcobj->timestart = $cid->startdate;
        $lcobj->timemodified = time();
        $lcobj->isnonformal = 1;
        $lcobj->gubun = 1;
        $lcobj->certificate = 0;
        $lcobj->isreged = 0;
        $lcobj->isopened = 0;
        $lcobj->purpose = 1;

        $lcid = $DB->insert_record('lmsdata_class', $lcobj);
        
        if(empty($lcid)) {
            print_object($lcobj);
        } else {
           $count++;
        }
    }
}

print_object($count);
