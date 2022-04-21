<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');
                    
class lcms_repository_write_form extends moodleform {

    public static function editor_options($context) {
        global $PAGE, $CFG;
        // TODO: add max files and max size support
        $maxbytes = get_user_max_upload_file_size($PAGE->context, $CFG->maxbytes);
        return array(
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'maxbytes' => $maxbytes,
            'trusttext' => true,
            'return_types' => FILE_INTERNAL | FILE_EXTERNAL,
            'subdirs' => file_area_contains_subdirs($context, 'local_repository', 'files', 0)
        );
    }

    function definition() {
        global $CFG, $DB, $USER;

        $id = optional_param('id', 0, PARAM_INT);
        $mode = optional_param('mode', "write", PARAM_CLEAN);
        $ref = optional_param('ref', 0, PARAM_INT);

        $mform = $this->_form;
        $context = $this->_customdata['context'];
        
        if($mode=='write'){
            $modtxt = 'add:contents';
        }else if($mode=='reply'){
            $modtxt = 'add:reference';
        }else if($mode=='edit'){
            $modtxt = ($ref)? 'edit:reference':'edit:contents';
        }

        $mform->addElement('header', 'nameforyourheaderelement', get_string($modtxt, 'local_repository'));
        $mform->setExpanded('nameforyourheaderelement', true);

        // 강의 제목
        $mform->addElement('text', 'con_name', get_string('title', 'local_repository'),array('onkeydown'=>'enter_key();'));
        $mform->setType('con_name', PARAM_CLEAN);
        $mform->addRule('con_name', null, 'required', null);
        
        // 강의자
        $mform->addGroup(array(
            $mform->createElement('text', 'prof_name', get_string('title', 'local_repository'),array('disabled'=>'disabled', 'value' => fullname($USER) )),
            $mform->createElement('hidden', 'prof_userid'),
            $mform->createElement('static', 'search_btn', '', '<input type="button" value="' . get_string('search', 'local_repository') . '" onclick="search_prof_popup()">')
        ), 'searchgroup',get_string('contents_lecturer', 'local_lmsdata'),'',false);
        
         //등록자
        $mform->addElement('text', 'teacher', get_string('update_user', 'local_lmsdata'),array('readonly'=>'readonly', 'value' => fullname($USER)));
        
        // 내용 소개
        $mform->addElement('editor', 'con_des', get_string('description', 'local_repository'), null, self::editor_options($context));
        $mform->setType('con_des', PARAM_RAW);
        
        // 파일형식
        if ($mode == 'write') {
            $mform->addGroup(array(
                $mform->createElement('radio', 'con_type', '', get_string('document', 'local_repository') . "&nbsp;&nbsp;", "word"),
                $mform->createElement('radio', 'con_type', '', get_string('video', 'local_repository') . "&nbsp;&nbsp;", "video"),
                $mform->createElement('radio', 'con_type', '', get_string('html', 'local_repository') . "&nbsp;&nbsp;", "html"),
                $mform->createElement('radio', 'con_type', '', get_string('embed', 'local_repository') . "&nbsp;&nbsp;", "embed"),
                    ), 'publicgroup', get_string('file_type', 'local_repository'), '', false);
            $mform->setDefault('con_type', 'word');
        } else if($mode == 'edit') {
            $mform->addElement('hidden', 'con_type');
            $mform->setType('con_type', PARAM_CLEAN);
            $staygroup = array();
            $staygroup[] = $mform->createElement('checkbox', 'stay_file', '', '', array(0, 1));
            $staygroup[] = $mform->createElement('static','fileinfo','');
            $mform->addGroup($staygroup, 'staygroup', get_string('filechange', 'local_repository'), '', false);
        } else if($mode == 'reply') {
            $mform->addElement('hidden', 'con_type');
            $mform->setDefault('con_type','ref');
            $mform->addElement('hidden', 'con_type_ref');
            $mform->setDefault('con_type_ref','ref');
        }
        $mform->addGroup(array(
                $mform->createElement('radio', 'share_yn', '', get_string('y','local_repository').'&nbsp;&nbsp;', 'Y'),
                $mform->createElement('radio', 'share_yn', '', get_string('n','local_repository').'&nbsp;&nbsp;', 'N')
            ),'share_group', get_string('list:isopen','local_repository'),'', false);
             $mform->setDefault('share_yn', 'N');
                    $mform->addElement('hidden', 'mode');
            $mform->setType('mode', PARAM_CLEAN);
            $mform->setDefault('mode', $mode);
        // 파일
        $attgroup = array();
        $attform = '<div id="lcms_upload_component">'
                . '<span class="btn fileinput-button"><span>'.get_string('fileadd','local_repository').'</span>'
                . '<input id="fileupload" type="file" name="files[]" title="file" multiple></span>'
                . '<div id="progress" class="progress"><div class="progress-bar progress-bar-success"></div></div>'
                . '<ul id="files" class="files">'.lcms_temp_dir_filemode('li').'</ul></div>';
        $attgroup[] = $mform->createElement('static', 'attachments', '', $attform);
        $mform->addGroup($attgroup, 'attachmentsgroup', get_string('attachment', 'forum').'<span class="red">*</span>', '', false);
        
        
        $vodgroup = array();
        $path = 'lms/'.$USER->id.'/'.date('Ymd').'/'.date('Ahis').'_r'.  mt_rand(1, 99);
        $transcording_src = MEDIA. '/index.php?id='.$id.'&path='.$path.'' 
                . '&userid='.$USER->id.''
                . '&returnpath='.$CFG->wwwroot.'/local/repository/return_file_data.php';
        $video_frame = '<iframe src="'.$transcording_src.'" width="100%" id="video_frame" height="306" title="iframe"></iframe>';
        $vodgroup[] = $mform->createElement('static', 'videobtn', '',$video_frame);
        $vodgroup[] = $mform->createElement('hidden', 'data_dir',$path);
        $vodgroup[] = $mform->createElement('hidden', 'video_file_id','',array('id'=>'video_file_id'));
        $vodgroup[] = $mform->createElement('text','filename','',array('id'=>'video_file_name','style'=>'display:none;','readonly'=>true));
        $mform->addGroup($vodgroup, 'videogroup', get_string('video', 'local_repository').'<span class="red">*</span>', '', false);
        $mform->setType('filename', PARAM_RAW);
        $mform->setType('data_dir', PARAM_RAW);
        
        
        //embed
        $embed_seletc_options = array('youtube' => 'Youtube','vimeo'=> 'Vimeo','kocw'=>'kocw');
        $mform->addGroup(array(
            $mform->createElement('select', 'emb_type', get_string('copy_none', 'local_repository'), $embed_seletc_options),
            $mform->createElement('text', 'emb_code', get_string('author','local_repository'),array('onkeydown'=>'enter_search(); ')),
            $mform->createElement('static', 'search_btn', '', '<input type="button" value="' . get_string('search', 'local_repository') . '" class="red_form" onclick="search_embed_contents()">'),
                ), 'embedgroup', get_string('embed', 'local_repository').'<span class="red">*</span>', ' ', false);
         $mform->setType('emb_code', PARAM_RAW);

        
        if ($id != 0) {
            $mform->addElement('hidden', 'id');
            $mform->setType('id', PARAM_INT);
            $mform->setDefault('id', $id);
        }
        
        $mform->addElement('hidden', 'con_id');
        $mform->setType('con_id', PARAM_INT);
               
        $mform->addElement('hidden', 'ref');
        $mform->setType('ref', PARAM_INT);
        $mform->setDefault('ref', 0);

        $group_options = array(0 => get_string('groupselect', 'local_repository'));

        // 그룹 선택
        if(!is_siteadmin()){
            $groups = $DB->get_records("lcms_repository_groups", array('userid' => $USER->id));
        } else {
            $groups = $DB->get_records("lcms_repository_groups", array());
        }
        foreach ($groups as $group) {
            $group_options[$group->id] = $group->name;
        }

        $mform->addGroup(array(
            $mform->createElement('select', 'groupid', get_string('group', 'local_repository'), $group_options),
            $mform->createElement('static', 'addnewGrooup', 'addnewGrooup', "<br><br>")
                ), 'groupgroup', get_string('groupselect', 'local_repository'), ' ', false);
        $mform->setDefault('groupid', 0);
        
        //스크립트파일 등록
        $mform->addElement('filemanager', 'script', get_string('form:caption', 'local_repository'), null, self::attachment_options());
        $this->add_action_buttons();
    }

    function validation($data, $files) {
        global $LCFG;
        $errors = parent::validation($data, $files);
        $mode = $data['mode'];
        if ($mode == 'write' || $mode == 'reply' || ($mode == 'edit' && $data['stay_file'] == 1) ) {
            if ($data['con_type'] == 'html' || $data['con_type'] == 'word' || $data['con_type'] == 'ref') {

                switch ($data['con_type']) {
                    case 'html':
                        $extarr = $LCFG->allowexthtml;
                        $n = 1;
                        break;
                    case 'word':
                        $extarr = $LCFG->allowextword;
                        $n = 0;
                        break;
                    case 'video':
                        $extarr = $LCFG->allowextvideo;
                        $n = 0;
                        break;
                    case 'ref': 
                        $extarr = $LCFG->allowextref;
                        $n = 0;
                        break;
                }
                
                $msg = lcms_temp_dir_allow_filecount($extarr, $n , $data['con_type']);

                if ($msg != 1) {
                    $errors['attachmentsgroup'] = $msg;
                }
            }
            if ($data['con_type'] == 'video' && !$data['filename']) {
                $errors['videogroup'] = get_string('error:notaddfile', 'local_repository');
            }
            if ($data['con_type'] == 'embed' && !$data['emb_code']) {
                $errors['embedgroup'] = get_string('error:notembed', 'local_repository');
            }
        }

        return $errors;
    }
    
    public static function attachment_options() {
        return array(
            'subdirs' => 0,
            'maxfiles' => 2,
            'accepted_types' => array('.smi'),
            'return_types' => FILE_INTERNAL
        );
    }

}
