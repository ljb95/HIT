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

global $CFG;
require_once $CFG->dirroot.'/local/lmsdata/lib.php';

/**
 * Event observer for local_courselist.
 */

class local_lmsdata_observer {
    public static function user_created(\core\event\user_created $event) {
        global $CFG, $DB;

        $event_data = $event->get_data();
        $userid = $DB->get_field('lmsdata_user', 'userid', array('userid'=>$event_data['objectid']));
        if($userid === FALSE) {
            $user = $DB->get_record('user', array('id'=>$userid));
            
            $lmsdata_user = new stdClass();
            $lmsdata_user = new stdClass();
            $lmsdata_user->userid = $user->id;
            $lmsdata_user->eng_name = $user->lastname.' '.$user->firstname;
            $lmsdata_user->usergroup = 'rs';
            $lmsdata_user->b_temp = 1;
            $lmsdata_user->b_mobile = 0;
            $lmsdata_user->b_email = 0;
            $lmsdata_user->univ = null;
            $lmsdata_user->major = null;
            $lmsdata_user->year = 0;
            $lmsdata_user->b_tel = 0;
            $lmsdata_user->b_univ = 0;
            $lmsdata_user->b_major = 0;
            $lmsdata_user->ehks = null;
            $lmsdata_user->edhs = null;
            $lmsdata_user->domain = null;
            $lmsdata_user->hyhg = null;
            $lmsdata_user->persg = null;
            $lmsdata_user->psosok = null;
            $lmsdata_user->sex = null;
            
            $DB->insert_record('lmsdata_user', $lmsdata_user);
        }
    }
    
    public static function user_deleted(\core\event\user_deleted $event) {
        global $CFG, $DB;
        
        $event_data = $event->get_data();
        
        $DB->delete_records('lmsdata_user', array('userid'=>$event_data['objectid']));
    }
    
    public static function userloggedin(\core\event\user_loggedin $event) {
        global $DB, $CFG;
        
        $event_data = $event->get_data();
        $ipaddress = local_lmsdata_get_client_ip();
        
        $user = $DB->get_record('user',array('id'=>$event_data['userid']));
        $data = local_lmsdata_browser_check();
        $user_log = new stdClass();
        $user_log->username=$user->username;
        $user_log->ip=$ipaddress;
        $user_log->mobile=$data->device;
        $user_log->action='logged';
        $user_log->log_date=time();
       
        $DB->insert_record('siteadmin_loginfo',$user_log);
        
        
    }
    
    
}
