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

require_once($CFG->dirroot.'/local/online_attendance/classes/autoloader.php');

defined('MOODLE_INTERNAL') || die();

/**
 * Event observer for local_online_attendance.
 */

class local_online_attendance_observer {
    public static function course_created(\core\event\course_created $event) {
        $event_data = $event->get_data();
        $courseid = $event_data['objectid'];
        
        //local_onattend
        $onattend = new online_attendance($courseid);
        
        //local_onattend_batchset
        local_onattendance_default_batchset($courseid);
    }
    
    public static function course_module_created(core\event\course_module_created $event) {
        global $DB;
        
        $event_data = $event->get_data();
        $courseid = $event_data['courseid'];
        $mod_data = $event_data['other'];
        $modname = $mod_data['modulename'];
        $cmid =  $event_data['contextinstanceid'];
        $visible_mods =  $DB->get_records_menu('local_onattend_cm_batchset', array('courseid'=>$courseid),'', 'modname, visible');
        
        // 해당 mod 유형을 온라인출석부에서 사용할 때만 추가
        if(isset($visible_mods[$modname])){
            
            $sql = 'SELECT  lob.*, co.startdate 
                    FROM {local_onattend_cm_batchset} lob
                    JOIN {course} co ON co.id = lob.courseid
                    WHERE courseid = :courseid and lob.modname = :modname ';
            $batchset = $DB->get_record_sql($sql, array('courseid'=>$courseid, 'modname'=>$modname));
            
            $section = $DB->get_field_sql('SELECT cs.section FROM {course_modules} cm JOIN {course_sections} cs ON cs.id = cm.section WHERE cm.id = :cmid', array('cmid'=>$cmid));
            
            $sectiondate = 0;
            if($section != 0) {
                $sectiondate = ($section-1) * 60 *60 *24 * 7;
            }
            
            $cmset = new Stdclass();
            $cmset->courseid = $courseid;
            $cmset->cmid = $cmid;
            $cmset->modname = $modname;
            $cmset->section = $section;
            $cmset->approval = $visible_mods[$modname] ? $visible_mods[$modname] : 0;
            $cmset->starttime = $sectiondate + $batchset->startdate + $batchset->startratio;
            $cmset->attendtime = $sectiondate + $batchset->startdate + $batchset->attendratio;
            $cmset->aprogress = $batchset->aprogress;
            $cmset->timecreated = time();
            $cmset->timemodified = time();
     
            $DB->insert_record('local_onattend_cm_set', $cmset);
            
            local_onattendance_week_recalculate($courseid, $section);
        }
    }
    
    public static function course_module_deleted(core\event\course_module_deleted $event) {
       global $DB;
       
       $event_data = $event->get_data();
       $cmid = $event_data['objectid'];
       
       $DB->delete_records('local_onattend_cm_set', array('cmid'=>$cmid));
       $DB->delete_records('local_onattend_status', array('cmid'=>$cmid));
       
       $section = $DB->get_field_sql('SELECT cs.section FROM {course_modules} cm JOIN {course_sections} cs ON cs.id = cm.section WHERE cm.id = :cmid', array('cmid'=>$cmid));
       local_onattendance_week_recalculate($courseid, $section);
       
    }
}
