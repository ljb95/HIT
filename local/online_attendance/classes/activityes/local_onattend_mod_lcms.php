<?php

/* 
 * 온라인 출석부, mod_lcms 출석체크 Class
 * 
 * 온라인출석부 -> 각 mod 진도율 형식으로 가져 오려만들었으나, 현재는 사이트에서 사용할 mod 종류에만 사용됨
 * 
 * ********************************************************************************************************
 * online_attendance/lib.php 에 있는 local_onattendance_set_status 함수를 해당 mod의 진도체크 부분에 적용하면
 * 
 * 각 mod -> 온라인 출석부 형식으로 진도율이 저장됨                                                        
 * ********************************************************************************************************
 * 
 * mod_lcms 에 적용한 부분
 * 
 * /mod/lcms/download.php
 * 
 * require_once $CFG->dirroot . '/local/online_attendance/lib.php';
 * require_once($CFG->dirroot.'/local/online_attendance/classes/autoloader.php');
 * 
 * 100 라인 :  local_onattendance_set_status($cm->id, $track->userid, $track->progress, time());
 * 
 * /mod/lcms/package.php
 * 
 * require_once $CFG->dirroot . '/local/online_attendance/lib.php';
 * require_once($CFG->dirroot.'/local/online_attendance/classes/autoloader.php');
 * 
 * 51 라인 :  local_onattendance_set_status($cm->id, $track->userid, $track->progress, time());
 * 
 * /mod/lcms/package2.php
 * 
 * require_once $CFG->dirroot . '/local/online_attendance/lib.php';
 * require_once($CFG->dirroot.'/local/online_attendance/classes/autoloader.php');
 * 
 * 50 라인 :  local_onattendance_set_status($cm->id, $track->userid, $track->progress, time());
 * 
 * /mod/lcms/package_ajax.php
 * 
 * require_once $CFG->dirroot . '/local/online_attendance/lib.php';
 * require_once($CFG->dirroot.'/local/online_attendance/classes/autoloader.php');
 * 
 * 101 라인 :  llocal_onattendance_set_status($cm->id, $track->userid, $track->progress, time());
 * 
 * /mod/lcms/playtime_ajax.php
 * 
 * require_once $CFG->dirroot . '/local/online_attendance/lib.php';
 * require_once($CFG->dirroot.'/local/online_attendance/classes/autoloader.php');
 * 
 * 106 라인 :  llocal_onattendance_set_status($cm->id, $track->userid, $track->progress, time());
 * 
 * /mod/lcms/playtime_ajax1.php
 * 
 * require_once $CFG->dirroot . '/local/online_attendance/lib.php';
 * require_once($CFG->dirroot.'/local/online_attendance/classes/autoloader.php');
 * 
 * 108 라인 :  llocal_onattendance_set_status($cm->id, $track->userid, $track->progress, time());
 */


global $CFG;

require_once $CFG->dirroot . '/local/online_attendance/classes/onattend_activity_interface.php';

class local_onattend_mod_lcms{ 
    
    const TIMETYPE = 1;
    
    public function get_onattendance_status($cmset, $userid){
        
        return true;
    }
}