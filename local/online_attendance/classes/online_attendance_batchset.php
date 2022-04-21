<?php

/**
 * 해당강의의 온라인 출석부의 활동별 일괄설정 class
 * 
 */
class online_attendance_batchset{
    
    const TABLE = 'local_onattend_cm_batchset';
    
    /** @var online_attendance table id*/
    public $id;
    
    /** @var course table id*/
    public $courseid;
    
    /** @var modules table name*/
    public $modname;
    
    /** @var 강의시작일로 부터 출석체크를 시작할 +/- 값*/
    public $startratio;

    /** @var 강의시작일로 부터 출석으로 인정할 종료날짜 +/- 값*/
    public $attendratio;
    
    /** @var 출석으로 인정 할 진도율, timetype값이 0이면 default 값은 100*/
    public $aprogress;
    
    /** @var 현재 강의에서 해당 mod의 출석체크 사용여부, 기본값은 사이트관리>로컬플러그인>온라인출석부의 설정으로*/
    public $visible;
    
    /** @var 생성시간*/
    public $timecreated;
    
    /** @var 변경시간*/
    public $timemodified;
    
    public function __construct($courseid, $modname) {
        global $DB;
        
        $this->courseid = $courseid;
        $this->modname = trim($modname);
        
        $cmset = $DB->get_record(self::TABLE, array('courseid'=>$this->courseid, 'modname'=>$this->modname));
        
        if(empty($cmset)) {
            $cmset = $this->local_online_attendance_add($this->courseid, $this->modname);
        } 
        
        $this->id = $cmset->id;
        $this->startration = $cmset->startration;
        $this->attendration = $cmset->attendration;
        $this->aprogress = $cmset->aprogress;
        $this->visible = $cmset->visible;
        $this->timecreated = $cmset->timecreated;
        $this->timemodified = $cmset->timemodified;
    }
    
        
    /**
     * local_onattend_cm_batchset 테이블에 add
     *
     * @return object $onattend
     */   
    protected function local_online_attendance_add($courseid, $modname) {
        global $DB,$USER;
        
        $cmset = new stdClass();
        $cmset->courseid = $this->courseid;
        $cmset->modname =  $this->modname;
        $cmset->startratio = LOCAL_ONATTENDANCE_DEFAULT_STARTRATIO;
        $cmset->attendratio = LOCAL_ONATTENDANCE_DEFAULT_ATTENDRATIO;
        $cmset->aprogress = LOCAL_ONATTENDANCE_DEFAULT_APROGRESS;
        $cmset->visible = 1;
        $cmset->timecreated = time();
        $cmset->timemodified = time();

        $cmset->id = $DB->insert_record(self::TABLE, $cmset);
        
        return $cmset;
    }
}