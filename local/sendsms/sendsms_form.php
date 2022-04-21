<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/repository/lib.php');

class sendsms_form extends moodleform {

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
            'subdirs' => file_area_contains_subdirs($context, 'local_sendsms', 'contents', $historyid)
        );
    }

    function definition() {
        global $CFG, $DB, $USER;

        $id = optional_param('id', 0, PARAM_INT);
        $context = get_context_instance(CONTEXT_COURSE, $id);

        $mform = $this->_form;
        
        $mform->addElement('static','selected_users',get_string('target','local_sendsms').'<span style="color:red;">*</span>','<div id="selected_users"><font id="user_selected">대상을 선택해주세요.</font></div>');
        $mform->addElement('hidden', 'user');
        
        $mform->addElement('text', 'subject', get_string('subject', 'local_sendsms'), array('size'=>'50'));
        $mform->setType('subject', PARAM_TEXT);
        $mform->addRule('subject', null, 'required', null, 'client');
        
        $mform->addElement('text', 'sphone', get_string('sender_number', 'local_sendsms'), array('placeholder'=>'0426709000~0426709999만 가능','size'=>'50'));
        $mform->setType('sphone', PARAM_TEXT);
        $mform->addRule('sphone', null, 'required', null, 'client');
        
        $mform->addElement('hidden', 'fontlimitinput','');
        
        $mform->addElement('textarea', 'contents', get_string('matilcontents', 'local_sendsms'), array('size'=>'98%','maxlength'=>'2001','cols'=>'70','rows'=>10,'onKeyUp'=>"fnChkByte(this,'2000')"));
        $mform->setType('contents', PARAM_RAW);
        $mform->addRule('contents', null, 'required', null, 'client');
        
        $mform->addElement('static', 'fontlimit','','<span id="byteInfo">0</span>/2000Byte');

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $id); 
        
        $buttonarray=array();
        $buttonarray[]= & $mform->createElement('submit','submit_btn','변경사항 저장');
        $buttonarray[]= & $mform->createElement('button','cancel_btn','취소',array('id'=>'cancel_btn'));
        $mform->addGroup($buttonarray,'buttonar','',array(' '),false);

    }

    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if(!$data['user']){
            $errors['selected_users'] = '선택된 대상이 없습니다.';
        }
         return $errors;
    }

}
