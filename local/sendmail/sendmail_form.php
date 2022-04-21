<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/repository/lib.php');

class sendmail_form extends moodleform {

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
            'subdirs' => file_area_contains_subdirs($context, 'local_sendmail', 'contents', $historyid)
        );
    }

    function definition() {
        global $CFG, $DB, $USER;

        $id = optional_param('id', 0, PARAM_INT);
        $context = get_context_instance(CONTEXT_COURSE, $id);

        $mform = $this->_form;
        
        $mform->addElement('static','selected_users',get_string('target','local_sendmail').'<span style="color:red;">*</span>','<div id="selected_users"><font id="user_selected">대상을 선택해주세요.</font></div>');               
        $mform->addElement('hidden', 'user');
        
        $mform->addElement('text', 'subject', get_string('mailtitle','local_sendmail'));
        $mform->setType('subject', PARAM_RAW);
        $mform->addRule('subject', null, 'required', null, 'client');
        
        $mform->addElement('editor', 'contents', get_string('matilcontents', 'local_sendmail'), null, self::editor_options($context, (empty($content->id) ? null : $content->id)));
        $mform->setType('contents', PARAM_RAW);
        $mform->addRule('contents', null, 'required', null, 'client');


        $mform->addElement('filemanager', 'attachments', get_string('attachment', 'local_jinoboard'), null, self::attachment_options());
        
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
