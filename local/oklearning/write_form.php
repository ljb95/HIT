<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');

class local_oklearning_write_form extends moodleform {

    function definition() {
        global $CFG, $DB, $USER;

        $id = optional_param('id', 0, PARAM_INT);
        $mode = optional_param('mode', "add", PARAM_CLEAN);

        $mform = $this->_form;
        $course        = $this->_customdata['course']; // this contains the data of this form
        $category      = $this->_customdata['category'];
        $editoroptions = $this->_customdata['editoroptions'];
        
        if (!empty($course->id)) {
            $coursecontext = context_course::instance($course->id);
            $context = $coursecontext;
            $modtxt = 'course:edit';
        } else {
            $coursecontext = null;
            $context = $categorycontext;
            $modtxt = 'course:add';
        }

        $courseconfig = get_config('moodlecourse');

        $this->course  = $course;
        $this->context = $context;
        
        $mform->addElement('header', 'nameforyourheaderelement', get_string($modtxt, 'local_oklearning'));
        $mform->setExpanded('nameforyourheaderelement', true);
        
        $mform->addElement('hidden', 'id');
        $mform->setType('hidden', PARAM_INT);
        $mform->setDefault('id', $id);
        
        // 강의명(한글)
        $mform->addElement('text', 'kor_lec_name', get_string('coursename:ko', 'local_oklearning'), array('style'=>'width:70%;'));
        $mform->setType('kor_lec_name', PARAM_CLEAN);
        $mform->addRule('kor_lec_name', null, 'required', null);
        
        // 강의명(영문)
        $mform->addElement('text', 'eng_lec_name', get_string('coursename:en', 'local_oklearning'), array('style'=>'width:70%;'));
        $mform->setType('eng_lec_name', PARAM_CLEAN);
        $mform->addRule('eng_lec_name', null, 'required', null);
        
        //이용목적
        $options = array(1=>get_string('purpose:course', 'local_oklearning'),2=>get_string('purpose:community', 'local_oklearning'));
        $mform->addElement('select','purpose',get_string('purpose', 'local_oklearning'),$options);
        $mform->addRule('purpose', null, 'required', null);
        
        //이용기간
        $starttime = usertime(time());
        $mform->addGroup(array(
            $mform->createElement('date_time_selector', 'timestart', '', array('timezone'=>'Asia/Seoul')),
            $mform->createElement('date_time_selector', 'timeend', '', array('timezone'=>'Asia/Seoul'))
                ), 'dategroup', get_string('course:date', 'local_oklearning'), '', false);
        $mform->setDefault('timeend', strtotime('+1 month'));
        $mform->addRule('dategroup', null, 'required', null);
        
        //공개여부 (공개, 비공개)
        $options = array(1=>get_string('sel:yes', 'local_oklearning'),0=>get_string('sel:no', 'local_oklearning'));
        $mform->addGroup(array(
            $mform->createElement('select','isopened','',$options),
            $mform->createElement('static', 'ispoenedesc', '', '<span class="is_red">'.get_string('isopeneddesc','local_oklearning').'</span>')
         ), 'isopenedgroup', get_string('isopened', 'local_oklearning'), '', false);
        $mform->addRule('isopenedgroup', null, 'required', null);
        
        //승인방법 (자동 승인, 비밀번호, 개설자 승인)
        $mform->addGroup(array(
            $mform->createElement('radio', 'approved', '', get_string('approved:auto', 'local_oklearning'), 0),
            $mform->createElement('static', 'approvedautodesc', '', get_string('approved:autodesc','local_oklearning').'<br>'),
            $mform->createElement('radio', 'approved', '', get_string('approved:pass', 'local_oklearning'), 1),
            $mform->createElement('password', 'approvedpass', '', array('title' => 'password')),
            $mform->createElement('static', 'approvedpassdesc', '', '<span class="is_red">'.get_string('approved:passdesc','local_oklearning').'</span>'.'<br>'),
            $mform->createElement('radio', 'approved', '', get_string('approved:review', 'local_oklearning'), 2),
            $mform->createElement('static', 'approvedreviewdesc', '', get_string('approved:reviewdesc','local_oklearning'))
                ), 'approvedgroup', get_string('approved', 'local_oklearning'), ' ', false);
        $mform->setDefault('approved',0);
        $mform->addRule('approvedgroup', null, 'required', null);
        
        //강의소개
        $mform->addElement('editor', 'summary_editor', get_string('description', 'local_oklearning'), null, $editoroptions);
        $mform->setType('summary_editor', PARAM_RAW);
        
        //이미지
        if ($overviewfilesoptions = course_overviewfiles_options($course)) {
            $mform->addElement('filemanager', 'overviewfiles_filemanager', get_string('course:image', 'local_oklearning'), null, $overviewfilesoptions);
        }

        //개인정보 보호 동의
        $mform->addElement('radio','isagree',get_string('agree:privacy','local_oklearning'),get_string('agree:privacydesc','local_oklearning'),1);
        $mform->addRule('isagree', null, 'required', null); 
        
        $this->add_action_buttons();
  
    }

    function validation($data, $files) {
        global $LCFG;
        $errors = parent::validation($data, $files);
        if($data[approved] == 1 && !$data[approvedpass]){
            $errors['approvedgroup'] = get_string('password_plz','local_oklearning');
        }

        return $errors;
    }

}
