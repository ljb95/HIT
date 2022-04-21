<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Class for user_competency persistence.
 *
 * @package    local_competency
 * @copyright  2016.9.13 CHS
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_competence;
defined('MOODLE_INTERNAL') || die();



class local_competence_user_competencies {
    /** @var array $userid 현재 user id */
    public $userid;
    
    /** @var array $course_competencies course competencies list */
    public $course_competencies;
    
    /** @var array $user_competencies user completed list */
    public $user_competencies;
    
    /**
     * Create an instance of this class.
     *
     * @param int $userid 역량을 검색할 userid
     */
    public function __construct($userid = 0) {
        
        $this->set_userid($userid);
        $class_grades = new \local_competence\local_competence_user_grades($this->userid);
        
        $courses = $class_grades->get_user_enrol_courses();
        
        //setting course competencies list
        $this->set_course_competencies($courses);
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
     * 학생으로 등록되어있는 course의 역량과 획득역량 목록
     * 
     * @return array
     */
    public function get_course_competencies() {
        
        $course_competencies = $this->course_competencies;
        if (!$course_competencies) {
            return null;
        }
        return $course_competencies;
    }
    
    /**
     * competency_usercomp 데이터 반환
     * 
     * @return array
     */
    public function get_user_competencies() {
        
        $user_competencies = $this->user_competencies;
        if (!$user_competencies) {
            return null;
        }
        return $user_competencies;
    }
    
    /**
     * setting set_userid
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
     * setting 등록되어있는 course 의 역량 list 
     *
     * @param array $courses user가 학생으로 등록되어있는 courselist
     * @return void
     */
    protected function set_course_competencies($courses) {
        global $DB;
         
        if(empty($courses)) {
            $this->course_competencies = null;
        } else {
            
            list($sql_in, $params) = $DB->get_in_or_equal($courses, SQL_PARAMS_NAMED, 'courseid');
            $sql_conditions[] = ' ccs.courseid '.$sql_in;

            $sql_select = 'SELECT 
                                ccs.id, ccs.courseid, ccs.competencyid, ccs.sortorder, 
                                com.shortname, com.idnumber, com.path, com.parentid, com.competencyframeworkid, 
                                cfw.shortname as fname,
                                cur.proficiency, cur.grade  
                            FROM {competency_coursecomp} ccs
                            JOIN {competency} com ON com.id = ccs.competencyid
                            JOIN {competency_framework} cfw ON cfw.id = com.competencyframeworkid
                            LEFT JOIN {competency_usercompcourse} cur ON cur.courseid = ccs.courseid AND cur.competencyid = ccs.competencyid ';
            
            $sql_where = ' WHERE '.implode(' AND ', $sql_conditions);
            $course_competencies = $DB->get_records_sql($sql_select.$sql_where, $params);
            if(empty($course_competencies)) {
                $this->course_competencies = null;
            } else {
                $this->course_competencies = $course_competencies;
            }
        }
    }
    
    /**
     * setting 등록되어있는 course 의 역량 list 
     *
     * @param array $courses user가 학생으로 등록되어있는 courselist
     * @return void
     */
    protected function set_user_competencies($courses) {
        global $DB;
        
        $userid = $this->userid;

        $sql_select = 'SELECT * FROM {competency_usercomp}  ';
        $params = array('userid' => $userid);

        $user_competencies = $DB->get_records('competency_usercomp', $params);
        if(empty($user_competencies)) {
            $this->user_competencies = null;
        } else {
            $this->user_competencies = $user_competencies;
        }
    }
    
}