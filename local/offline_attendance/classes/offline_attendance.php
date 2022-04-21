<?php

defined('MOODLE_INTERNAL') || die();

require_once $CFG->dirroot.'/grade/lib.php';

class offline_attendance {
    
    const TABLE = 'local_off_attendance';
     
    /** @var offline_attendance table id*/
    public $id;
    
    /** @var course table id*/
    public $courseid;
    
    /** @var user table id*/
    public $userid;
    
    /** @var grade_items table id*/
    public $itemid;
    
    /** @var grade_items class*/
    public $itemobj;
    
    /** @var grade_items table  grademax field*/
    public $maxscore;
    
    /** @var grade_items table  grademin field*/
    public $minscore;
    
    /** @var grade_items table  latesubtract field*/
    public $late;
    
    /** @var grade_items table  earlysubtract field*/
    public $early;
    
    /** @var grade_items table  absentsubtract field*/
    public $absent;
    
    /**
     * Create an instance of this class.
     *
     * @param int $courseid 오프라인 출석부 설정을 검색할 course id
     */
    public function __construct($courseid) {
        global $DB, $USER;
        
        $this->courseid = $courseid;
        $off_attendance = $DB->get_record(self::TABLE, array('courseid' => $this->courseid));
        
        if(empty($off_attendance)) {
            $off_attendance = new stdClass();
            $off_attendance->courseid = $courseid;
            $off_attendance->userid = $USER->id;
            $off_attendance->itemid = 0;
            $off_attendance->latesubtract = get_config('local_offline_attendance', 'late');
            $off_attendance->earlysubtract = get_config('local_offline_attendance', 'early');
            $off_attendance->absentsubtract = get_config('local_offline_attendance', 'absent');
            $off_attendance->timecreated = time();
            $off_attendance->timemodified = time();

            $off_attendance->id = $DB->insert_record('local_off_attendance', $off_attendance);
        }
        
        $this->id = $off_attendance->id;
        $this->userid = $off_attendance->userid;
        $this->itemid = $off_attendance->itemid;
        $this->late = $off_attendance->latesubtract;
        $this->early = $off_attendance->earlysubtract;
        $this->absent = $off_attendance->absentsubtract;
        
        // get_config('local_offline_attendance', 'auto') 값이 0이면 course create event 시 성적 항목(itemid)값이 0으로 셋팅됨
        if($this->itemid) {
            $itemobj = $this->get_attendance_grade_item();
        } else {
           // 템플릿 기능 사용으로 성적항목은 있으나 local_off_attendance에 itemid 값이 0인경우
           if($this->itemid = $DB->get_field('grade_items', 'id', array('courseid' => $courseid, 'idnumber' => LOCAL_ATTENDANCE_GRADE_ITEM_IDNUMBER))) {
               $itemobj = $this->get_attendance_grade_item();
           } else {
               $itemobj = $this->set_attendance_grade_item();
           }
           
           $DB->set_field(self::TABLE, 'itemid', $itemobj->id, array('courseid' => $this->courseid));
        }
        
       $this->set_itemobj($itemobj);
    }
    
/**
 * itemobj를 이용해서 offline_attendance class 값 셋팅
 *
 * @param object $itemobj grade_item class
 */
    
    protected function set_itemobj($itemobj) {
        $this->itemobj = $itemobj;
        $this->itemid = $itemobj->id;
        $this->name = $itemobj->itemname;
        $this->maxscore = $itemobj->grademax;
        $this->minscore = $itemobj->grademin;
    }
    
/**
 * 오프라인 출석부 성적 항목을 생성
 *
 * @return object $grade_item grade_item class
 */     
    protected function set_attendance_grade_item() {
        $data = new stdClass();
        $data->id =  0;
        $data->courseid =  $this->courseid;
        $data->itemtype = 'manual';
        $data->itemname = get_string('attendance:book', 'local_offline_attendance');
        $data->iteminfo = '';
        $data->idnumber = LOCAL_ATTENDANCE_GRADE_ITEM_IDNUMBER;
        $data->gradetype = 1;
        $data->grademax = get_config('local_offline_attendance', 'maxscore');
        $data->grademin =  get_config('local_offline_attendance', 'minscore');
        $data->gradepass = 0;
        $data->display = 1;
        $data->decimals = 0;
        $data->weightoverride = 0;
        $data->aggregationcoef = 0;
        $data->aggregationcoef2 = 0;

        $grade_item = new grade_item(array('id'=>0, 'courseid'=>$this->courseid));

        grade_item::set_properties($grade_item, $data);

        $grade_item->outcomeid = null;
        $itemid = $grade_item->insert();
        $grade_item->id = $itemid;
        
        return $grade_item;
    }
    
/**
 * 해당 코스의 성적항목 class - grade_item class를 가져옴 
 *
 * @return object $grade_item grade_item class
 */   
    protected function get_attendance_grade_item() {
        if($this->itemobj) {
            $grade_item = $this->itemobj;
        } else {
            if($this->itemid) {
                $grade_item = new grade_item(array('id'=>$this->itemid, 'courseid'=>$this->courseid));
            } else {
                $grade_item = null;
            }
        }
        
        return $grade_item;
    }
    
/**
 * $this->itemobj 에 있는 성적 항목의 이름을 가져 옴
 *
 * @return String $itemobj->itemname    성적 항목 이름, default 값은 get_string('attendance:book', 'local_offline_attendance')
 */   
    public function get_attendance_book_name() {
        $itemobj = $this->itemobj;
        
        return $itemobj->itemname;
    }
}
