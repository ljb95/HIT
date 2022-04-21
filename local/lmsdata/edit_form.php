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
 * Form to edit a users profile
 *
 * @copyright 1999 Martin Dougiamas  http://dougiamas.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package core_user
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    //  It must be included from a Moodle page.
}

require_once($CFG->dirroot.'/lib/formslib.php');

/**
 * Class user_edit_form.
 *
 * @copyright 1999 Martin Dougiamas  http://dougiamas.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class user_info_edit_form extends moodleform {

    /**
     * Define the form.
     */
    public function definition () {
        global $CFG, $COURSE, $USER, $OUTPUT, $DB;

        $mform = $this->_form;
        $editoroptions = null;
        $filemanageroptions = null;
        if (is_array($this->_customdata)) {
            if (array_key_exists('editoroptions', $this->_customdata)) {
                $editoroptions = $this->_customdata['editoroptions'];
            }
            if (array_key_exists('filemanageroptions', $this->_customdata)) {
                $filemanageroptions = $this->_customdata['filemanageroptions'];
            }
            if (array_key_exists('userid', $this->_customdata)) {
                $userid = $this->_customdata['userid'];
            }
            if (array_key_exists('description', $this->_customdata)) {
                $description = $this->_customdata['description'];
            }
        }
        // Accessibility: "Required" is bad legend text.
        $strgeneral  = get_string('general');
        $strrequired = get_string('required');

        // Add some extra hidden fields.
        $mform->addElement('hidden', 'id', $userid);
        $mform->setType('id', PARAM_RAW);
        $mform->addElement('hidden', 'course', $COURSE->id);
        $mform->setType('course', PARAM_INT);
        $mform->addElement('hidden', 'edit', true);
        $mform->setType('edit', PARAM_BOOL);
        $mform->addElement('static', 'username',  get_string('user_id','local_lmsdata'));
        $mform->setType('username', PARAM_RAW);
        
        $userdata = $DB->get_record('user', array('id'=>$userid));
        
        $mform->toHtml($OUTPUT->user_picture($userdata, array('size'=>70, 'alt'=>'User Profile')));
        
        $mform->addElement('checkbox', 'deletepicture', get_string('delete'));
        $mform->setDefault('deletepicture', 0);
        
        $mform->addElement('filemanager', 'imagefile',  get_string('image_upload','local_lmsdata'));
        
        $elementemail = array();
        $elementemail[] = &$mform->createElement('text', 'email');
        $elementemail[] = &$mform->createElement('radio', 'maildisplay', '', get_string('open', 'local_lmsdata'), 1);
        $elementemail[] = &$mform->createElement('radio', 'maildisplay', '', get_string('close', 'local_lmsdata'), 0);
        $mform->addGroup($elementemail, 'emailgroup', get_string('email','local_lmsdata'), array(' '), false);
        
        $elementphone1 = array();
        $elementphone1[] = &$mform->createElement('text', 'phone1');
        $elementphone1[] = &$mform->createElement('radio', 'b_tel', '', get_string('open', 'local_lmsdata'), 1);
        $elementphone1[] = &$mform->createElement('radio', 'b_tel', '', get_string('close', 'local_lmsdata'), 0);
        $mform->addGroup($elementphone1, 'phone1group', get_string('telephone','local_lmsdata'), array(' '), false);

        $elementphone2 = array();
        $elementphone2[] = &$mform->createElement('text', 'phone2');
        $elementphone2[] = &$mform->createElement('radio', 'b_mobile', '', get_string('open', 'local_lmsdata'), 1);
        $elementphone2[] = &$mform->createElement('radio', 'b_mobile', '', get_string('close', 'local_lmsdata'), 0);
        $mform->addGroup($elementphone2, 'phone2group', get_string('phone','local_lmsdata'), array(' '), false);
        
        $mform->addElement('static', '', get_string('attach','local_lmsdata'));
        
        $elementuniv = array();
        $elementuniv[] = &$mform->createElement('radio', 'b_univ', '', get_string('open', 'local_lmsdata'), 1);
        $elementuniv[] = &$mform->createElement('radio', 'b_univ', '', get_string('close', 'local_lmsdata'), 0);
        $mform->addGroup($elementuniv, 'univgroup', get_string('univ','local_lmsdata'), array(' '), false);
        
        $elementmajor = array();
        $elementmajor[] = &$mform->createElement('radio', 'b_major', '', get_string('open', 'local_lmsdata'), 1);
        $elementmajor[] = &$mform->createElement('radio', 'b_major', '', get_string('close', 'local_lmsdata'), 0);
        $mform->addGroup($elementmajor, 'majorgroup', get_string('major','local_lmsdata'), array(' '), false);

        $editor = $mform->addElement('editor', 'description',  get_string('description','local_lmsdata'), array('rows' => 8, 'cols' => 41));
        $editor->setValue(array('text'=>$description));
        $mform->setType('description', PARAM_TEXT);
        
        // Extra settigs.
        if (!empty($CFG->disableuserimages)) {
            $mform->removeElement('deletepicture');
            $mform->removeElement('imagefile');
            $mform->removeElement('imagealt');
        }

        // Next the customisable profile fields.
        $this->add_action_buttons(false, get_string('updatemyprofile'));
    }

    /**
     * Extend the form definition after the data has been parsed.
     */
}


