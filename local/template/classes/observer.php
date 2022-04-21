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
 * Event observers used in forum.
 *
 * @package    local_template
 * @copyright 2015 Jinotech  {@link http://www.jinotech.com}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Event observer for local_template.
 */
require_once $CFG->dirroot.'/local/template/lib.php';
require_once $CFG->libdir.'/filelib.php';

class local_template_observer {
    public static function course_restored(\core\event\course_restored $event) {
        global $CFG, $DB;

        $data = $event->get_data();
        
        $courseid = $data['courseid'];
        $course = $DB->get_record('course',array('id'=>$courseid));
        /*
         * original_course_id 를 가져오기 위해
         * /backup/util/plan/restore_plan.class.php 파일 182 번째 줄에 아래 내용 추가
         * 
         * 'original_course_id' => $this->get_info()->original_course_id
         */
        $original_courseid = $data['other']['original_course_id'];
        
        $original_course = $DB->get_record_sql('SELECT ca.idnumber AS category_idnumber,
            co.format AS course_format 
            FROM {course} co
            JOIN {course_categories} ca ON ca.id = co.category
            WHERE co.id = :courseid', array('courseid'=>$original_courseid));
        
        
        // template로 부터 복구하는 경우에만 format을 바꿈.
        if($original_course->category_idnumber == TEMPLATE_IDNUMBER) {
            $DB->set_field('course', 'format', $original_course->course_format, array('id'=>$courseid));
        }
       
        if($original_course->course_format != $course->course_format){
        $contextid = get_context_instance_by_id($course->id);
        blocks_delete_all_for_context($contextid);
        blocks_add_default_course_blocks($course);
        }
        
    }
}
