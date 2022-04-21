<?php

/**
 * Class for loading/storing competencies from the DB.
 *
 * @package    local_competence
 * @copyright  2016 JINOTECH CHS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_competence;
defined('MOODLE_INTERNAL') || die();

class local_competence_user_grades {
    
    /** @var array $userid 현재 user id */
    public $userid;
    
    /** @var array $enrol_courses 현재 user가 enrol 된 coruselist */
    public $enrol_courses;

    /** @var array $course_grades course grade object. */
    public $course_grades;
    
    /** @var array $activity_grades activity grade object. */
    public $activity_grades;
    
    /**
     * Create an instance of this class.
     *
     * @param int $userid 성적을 검색할 userid
     */
    public function __construct($userid = 0, $courseid = 0) {
        
        //setting userid
        $this->set_userid($userid);
        
        //setting enrol course list
        $this->set_enrol_courses();
        
        if(!empty($this->enrol_courses)) {
            //setting course grades
            $this->set_course_grades();
            //setting course grades
            $this->set_activity_grades($courseid);
        }
    }
    
    /**
     * 사용자 id
     * 
     * @return int 
     */
    public function get_userid() {
        $userid = $this->userid;
        return $userid;
    } 
    
    /**
     * 학생으로 등록되어있는 course id 목록을 배열로 반환
     * 
     * @return array
     */
    public function get_user_enrol_courses() {
        
        $enrol_courses = $this->enrol_courses;
        if (!$enrol_courses) {
            return null;
        }
        return $enrol_courses;
    }
    
    /**
     * 등록되어있는 course의 정보와 grade 값을 반환
     * 
     * @return array
     */
    public function get_course_grades() {
        
        $course_grades = $this->course_grades;
        if (!$course_grades) {
            return null;
        }
        return $course_grades;
    }
    
    /**
     * 등록되어있는 course내의 activity정보와 grade 값을 반환
     * array key 값은 courseid
     * 
     * @return array
     */
    public function get_activity_grades() {
        
        $activity_grades = $this->activity_grades;
        if (!$activity_grades) {
            return null;
        }
        return $activity_grades;
    }
    
    /**
     * setting course_grades
     *
     * @param int $userid user 테이블의 id
     * @return void
     */
    protected function set_userid($userid = 0) {
        global $USER;
        
        //userid setting
        if(empty($userid)) {
            $this->userid = $USER->id;
        } else {
            $this->userid = $userid;
        }
    }
    
    /**
     * setting enrol_courses 등록되어있는 course lsit 
     *
     * @param int $userid user 테이블의 id
     * @return void
     */
    protected function set_enrol_courses() {
        global $DB;
        
        $userid = $this->userid;
        
        $sql = 'SELECT co.id, co.id as courseid  
                FROM {course} co 
                JOIN {lmsdata_class} lc ON lc.course = co.id
                JOIN (
                    SELECT  co.instanceid, co.instanceid as courseid 
                    FROM {role_assignments} ra
                    JOIN {role} ro ON ro.id = ra.roleid
                    JOIN {context} co ON co.id = ra.contextid
                    WHERE ra.userid = :userid and ro.archetype = :type and co.contextlevel = :level 
                  ) eco ON eco.courseid = co.id ';
       
        $params = array(
                    'userid' => $userid,
                    'type' => 'student',
                    'level' => CONTEXT_COURSE
                );
        $enrol_courses = $DB->get_records_sql_menu($sql, $params);
        
        if(empty($enrol_courses)) {
            $this->enrol_courses = null;
        } else {
            $this->enrol_courses = $enrol_courses;
        }
    }
    
    /**
     * setting course_grades
     *
     * @return void
     */
    protected function set_course_grades() {
        global $DB;
        
        $userid = $this->userid;
        $enrol_courses = $this->enrol_courses;
        
        list($sql_in, $params) = $DB->get_in_or_equal($enrol_courses, SQL_PARAMS_NAMED, 'courseid');
        $sql_conditions[] = ' gi.courseid '.$sql_in;
        
        $sql_select = 'SELECT gi.courseid, gi.grademax as totalmaxgrade, 
                              gg.rawgrademax, gg.finalgrade, 
                              CASE WHEN cc.timecompleted IS NULL THEN 0 ELSE cc.timecompleted END as completed
                        FROM {grade_items} gi
                        LEFT JOIN {grade_grades} gg ON gg.itemid = gi.id 
                        LEFT JOIN (SELECT * FROM {course_completions} WHERE userid =:userid ) cc ON cc.course = gi.courseid ';
       
        $params['itemtype'] = 'course';
        $sql_conditions[] = 'gi.itemtype = :itemtype';
        $params['userid'] = $userid; 
        $sql_where = ' WHERE '.implode(' AND ', $sql_conditions);
        
        $course_grades = $DB->get_records_sql($sql_select.$sql_where, $params);
        
        if(empty($course_grades)) {
            $this->course_grades = null;
        } else {
            $this->course_grades = $course_grades;
        }

    }
    
    /**
     * setting activity_grades
     *
     * @return void
     */
    protected function set_activity_grades($courseid = 0) {
        global $DB;
        
        $userid = $this->userid;
        if(empty($courseid)) {
            $enrol_courses = $this->enrol_courses;
        } else {
            $enrol_courses = array($courseid => $courseid);
        }
        
        list($sql_in, $params) = $DB->get_in_or_equal($enrol_courses, SQL_PARAMS_NAMED, 'courseid');
        $sql_conditions[] = ' gi.courseid '.$sql_in;
        
        $sql_select = 'SELECT gi.id, gi.itemname, gi.courseid, gi.grademax as totalmaxgrade, 
                              gg.rawgrademax, gg.finalgrade
                FROM {grade_items} gi
                LEFT JOIN {grade_grades} gg ON gg.itemid = gi.id ';
       
        $params['itemtype'] = 'mod';
        $sql_conditions[] = 'gi.itemtype = :itemtype';
        $params['userid'] = $userid; 
        $sql_where = ' WHERE '.implode(' AND ', $sql_conditions);
        
        $activity_grades = $DB->get_records_sql($sql_select.$sql_where, $params);
       
        if(empty($activity_grades)) {
            $this->activity_grades = null;
        } else {
            $this->activity_grades = $activity_grades;
        }
    }
}