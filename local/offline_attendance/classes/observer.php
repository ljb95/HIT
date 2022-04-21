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

require_once $CFG->dirroot.'/grade/lib.php';
require_once $CFG->dirroot.'/local/offline_attendance/lib.php';

/**
 * Event observer for local_offline_attendance.
 */

class local_offline_attendance_observer {
    public static function course_created(\core\event\course_created $event) {
        global $CFG, $DB;
        
        $event_data = $event->get_data();
        $courseid = $event_data['objectid'];
        $itemid = 0;
        if(get_config('local_offline_attendance', 'auto')) {
            if(!$itemid = $DB->get_field('grade_items', 'id', array('courseid' => $courseid, 'idnumber' => LOCAL_ATTENDANCE_GRADE_ITEM_IDNUMBER))) {
                $data = new stdClass();
                $data->id =  0;
                $data->courseid =  $courseid;
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

                $grade_item = new grade_item(array('id'=>0, 'courseid'=>$courseid));
                
                grade_item::set_properties($grade_item, $data);
                
                $grade_item->outcomeid = null;
                $itemid = $grade_item->insert();
            }
        }
        
        $attdendance_option = new stdClass();
        $attdendance_option->courseid = $courseid;
        $attdendance_option->userid = 0;
        $attdendance_option->itemid = $itemid;
        $attdendance_option->latesubtract = get_config('local_offline_attendance', 'late');
        $attdendance_option->earlysubtract = get_config('local_offline_attendance', 'early');
        $attdendance_option->absentsubtract = get_config('local_offline_attendance', 'absent');
        $attdendance_option->timecreated = time();
        $attdendance_option->timemodified = time();

        $DB->insert_record('local_off_attendance', $attdendance_option);
    }
}
