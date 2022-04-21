<?php

/**
 * 온라인 출석부 activity 출석 templates.
 *
 */
interface online_attendace {

    /**
     * @param local_onattend_cm_set $output     local_onattend_cm_set 테이블 object
     * @param int $userid                       출석체크 할 userid, null이면 해당강의 모든 학생  
     * @return stdClass|array
     */
    public function get_onattendance_status($cmset,$userid);
 
}