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
 * @package    local_courselist
 * @copyright  2013 Rajesh Taneja <rajesh@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Event observer for local_courselist.
 */

class local_courselist_observer {
    public static function course_created(\core\event\course_created $event) {
        global $CFG, $DB;

        $courseid = $event->get_data()['courseid'];
       
        $lmsdata_class = $DB->get_record('lmsdata_class', array('course'=>$courseid));
        if($lmsdata_class === FALSE) {
            $course = $DB->get_record('course', array('id'=>$courseid));
            
            $lmsdata_class = new stdClass();
            $lmsdata_class->course = $course->id;
            $lmsdata_class->subject_id =  ' ';
            $lmsdata_class->category =  $course->category;
            $lmsdata_class->kor_lec_name =  $course->fullname;
            $lmsdata_class->eng_lec_name =  $course->fullname;
            $lmsdata_class->prof_userid =  0;
            $lmsdata_class->year =  get_config('moodle', 'haxa_year');
            $lmsdata_class->term =  get_config('moodle', 'haxa_term');
            $lmsdata_class->timestart =  $course->startdate;
            $lmsdata_class->timeend =  0;
            $lmsdata_class->timeregstart =  0;
            $lmsdata_class->timeregend =  0;
            $lmsdata_class->isnonformal =  1;
            $lmsdata_class->gubun =  1;
            $lmsdata_class->timemodified =  $course->timemodified;
            $lmsdata_class->ohakkwa =  ' ';
            
            $DB->insert_record('lmsdata_class', $lmsdata_class);
        }
    }
    
    public static function course_deleted(\core\event\course_deleted $event) {
        global $CFG, $DB;
        
        $courseid = $event->get_data()['courseid'];
        
        $DB->delete_records('lmsdata_class', array('course'=>$courseid));
    }
}
