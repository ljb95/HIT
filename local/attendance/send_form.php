<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');

class local_attendance_form extends moodleform {

    public static function editor_options($context, $contentid) {
        global $PAGE, $CFG;
        // TODO: add max files and max size support
        $maxbytes = get_user_max_upload_file_size($PAGE->context, $CFG->maxbytes);
        return array(
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'maxbytes' => $maxbytes,
            'trusttext' => true,
            'return_types' => FILE_INTERNAL | FILE_EXTERNAL,
            'subdirs' => file_area_contains_subdirs($context, 'local_jinoboard', 'contents', $contentid)
        );
    }

    function definition() {
        global $DB;

        $mform = $this->_form;

        $context = $this->_customdata['context'];
        $id = optional_param('id', 0, PARAM_INT);
        $users = optional_param_array('userid', array(), PARAM_INT);
        $mform->addElement('header', 'nameforyourheaderelement', get_string('encouraging', 'local_attendance'));

        $userspan = '';
        foreach ($users as $user => $on) {
            $user = $DB->get_record('user', array('id' => $user));
            $userspan .= '<div class="selected_user user' . $user->id . '">' . fullname($user) . '<input type="hidden" name="users[]" value="' . $user->id . '"><span class="deleteX" onclick="delete_user(' . $user->id . ')">X</span></div>';
        }

        $mform->addElement('static', 'selected_users', get_string('target', 'local_sendmessage'), '<div id="selected_users">' . $userspan . '</div>');

        $chk = '<input name="send_type[sms]" value="true" type="checkbox">SMS<input name="send_type[mail]" value="true" type="checkbox">메일<input name="send_type[message]" value="true" type="checkbox">메시지';

        $mform->addElement('static', 'static', get_string('send_type', 'jinotechboard'), $chk);

        $mform->addElement('hidden', 'id', get_string('title', 'local_jinoboard'));
        $mform->setDefault('id', $id);

        $mform->addElement('text', 'name', get_string('title', 'local_jinoboard'));

        $editor = $mform->addElement('editor', 'contents', get_string('content', 'local_jinoboard'), null, self::editor_options($context, null));
        $mform->setType('contents', PARAM_RAW);
        $mform->addRule('contents', null, 'required', null, 'client');

        $smssender = array();
        $smssender[] = & $mform->createElement('text', 'smssender', get_string('smssender', 'local_board'));
        $smssender[] = & $mform->createElement('static', 'des_sender', '', '형식) 010-1111-1111 : 대쉬(-)를 포함하여 작성');
        $mform->addGroup($smssender, 'sms', get_string('sms_sender', 'jinotechboard'), ' ', false);
        $mform->addElement('textarea', 'sms_content', get_string('sms_content', 'jinotechboard'),array('maxlength'=>80,'size'=>30,'rows'=>2,'onKeyUp'=>"fnChkByte_att(this,'80')"));
        
        $mform->addElement('static', 'fontlimit','','<span id="byteInfo">0</span>/80Byte');

        $this->add_action_buttons();
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);


        return $errors;
    }

}
