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
 * mod_commmons.php에 적용한 부분
 * 
 * /mod/commons/setprogress.php
 * 
 * require_once $CFG->dirroot . '/local/online_attendance/lib.php';
 * require_once($CFG->dirroot.'/local/online_attendance/classes/autoloader.php');
 * 
 * 하단에 local_onattendance_set_status($cm->id, $userid, $progress, time()); 함수
 * 
 */

global $CFG;

require_once $CFG->dirroot . '/local/online_attendance/classes/onattend_activity_interface.php';

class local_onattend_mod_commons implements online_attendace {
    
    const TIMETYPE = 1;
        
    public function get_onattendance_status($cmset, $userid){
        
        return true;
    }
    
}