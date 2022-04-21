<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/repository/lib.php');

class sendpush_form extends moodleform {

    public static function attachment_options() {
        global $PAGE, $CFG;
        $maxbytes = get_user_max_upload_file_size($PAGE->context, $CFG->maxbytes);
        return array(
            'subdirs' => 0,
            'maxbytes' => $maxbytes,
            'maxfiles' => 1,
            'accepted_types' => '*',
            'return_types' => FILE_INTERNAL
        );
    }

    public static function editor_options($context, $historyid) {
        global $PAGE, $CFG;
        // TODO: add max files and max size support
        $maxbytes = get_user_max_upload_file_size($PAGE->context, $CFG->maxbytes);
        return array(
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'maxbytes' => $maxbytes,
            'trusttext' => true,
            'return_types' => FILE_INTERNAL | FILE_EXTERNAL,
            'subdirs' => file_area_contains_subdirs($context, 'local_sendpush', 'contents', $historyid)
        );
    }

    function definition() {
        global $CFG, $DB, $USER;

        $id = optional_param('id', 0, PARAM_INT);
        $context = get_context_instance(CONTEXT_COURSE, $id);

        $mform = $this->_form;
        
        $mform->addElement('static','selected_users',get_string('target','local_sendpush').'<span style="color:red;">*</span>','<div id="selected_users"><input id="user_selected" type="text" readonly value="대상을 선택해주세요."></div>');
        $mform->addElement('hidden', 'user');
        
        $mform->addElement('text', 'subject', get_string('subject', 'local_sendpush'), array('size'=>'50'));
        $mform->setType('subject', PARAM_TEXT);
        $mform->addRule('subject', null, 'required', null, 'client');
        
        $mform->addElement('text', 'sphone', get_string('sender_number', 'local_sendpush'), array('size'=>'50'));
        $mform->setType('sphone', PARAM_TEXT);
        $mform->addRule('sphone', null, 'required', null, 'client');
        
        
        $mform->addElement('textarea', 'contents', get_string('matilcontents', 'local_sendpush'), array('size'=>'80','maxlength'=>'80','cols'=>'70','rows'=>2,'onKeyUp'=>"fnChkByte(this,'80')"));
        $mform->setType('contents', PARAM_RAW);
        $mform->addRule('contents', null, 'required', null, 'client');
        
        $mform->addElement('static', 'fontlimit','','<span id="byteInfo">0</span>/80Byte');

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $id); 
        
        $this->add_action_buttons();
    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if(!$data['user']){
            $errors['selected_users'] = '선택된 대상이 없습니다.';
        }
         return $errors;
    }

}
