<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/repository/lib.php');

class jinoboard_write_form extends moodleform {

    public static function attachment_options($board) {
        global $PAGE, $CFG;
        $maxbytes = get_user_max_upload_file_size($PAGE->context, $CFG->maxbytes);
        return array(
            'subdirs' => 0,
            'maxbytes' => $maxbytes,
            'maxfiles' => $board->maxattachments,
            'accepted_types' => '*',
            'return_types' => FILE_INTERNAL
        );
    }

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
        global $CFG, $DB, $USER;

        $id = optional_param('id', 0, PARAM_INT);
        $type = optional_param('type', 1, PARAM_INT);
        $mode = optional_param('mode', "write", PARAM_CLEAN);

        $board = $DB->get_record('jinoboard', array('type' => $type));

        $options = $this->_customdata['options'];
        $context = $this->_customdata['context'];
        $content = $this->_customdata['content'];

        $mform = $this->_form;

        $mform->addElement('header', 'nameforyourheaderelement', get_string('add', 'local_jinoboard'));
        $mform->setExpanded('nameforyourheaderelement', true);
        if($type == 7){
            $masterid = 'yongreen';
        }
       
        
        $titlearray = array();
        $titlearray[] = & $mform->createElement('text', 'name', get_string('title', 'local_jinoboard'));
        if ($board->allownotice && $mode != 'reply') {
            if (has_capability('local/jinoboard:write', $context) || ($USER->username == $masterid)) {
                $titlearray[] = & $mform->createElement('advcheckbox', 'isnotice', '', get_string('notice', 'local_jinoboard'), array('group' => 1), array(0, 1));
            }
        }
        $mform->addGroup($titlearray, 'titlear', get_string('title', 'local_jinoboard'), ' ', false);
        $mform->setType('name', PARAM_CLEAN);
        if ($board->allownotice && $mode != 'reply')
            $mform->setDefault('isnotice', $content->isnotice);
        $mform->addRule('titlear', null, 'required');
        $titlegrprules = array();
        $titlegrprules['name'][] = array(null, 'required', null, 'client');
        $mform->addGroupRule('titlear', $titlegrprules);
        
        if ($board->allowsecret) {
            $radioarray = array();
            $radioarray[] = & $mform->createElement('radio', 'issecret', '', get_string('issecretno', 'local_jinoboard'), 0);
            $radioarray[] = & $mform->createElement('radio', 'issecret', '', get_string('issecretyes', 'local_jinoboard'), 1);
            $mform->addGroup($radioarray, 'radioar', get_string('issecret', 'local_jinoboard'), array(' '), false);
            $mform->setDefault('issecret', $content->issecret);
            $mform->addRule('radioar', null, 'required');
        }

        if ($board->type == 3) {
            $categorys = $DB->get_records('jinoboard_category', array('isused' => 1));
            $categoryary = array();
            $categoryary[0] = '';
            foreach ($categorys as $category) {
                $categoryary[$category->id] = $category->name;
            }
            $mform->addElement('select', 'category', get_string('board_category','local_lmsdata'), $categoryary);
            $mform->setType('category', PARAM_CLEAN);
        }
       
        
        
        
        $editor = $mform->addElement('editor', 'contents', get_string('content', 'local_jinoboard'), null, self::editor_options($context, (empty($content->id) ? null : $content->id)));
        $mform->setType('contents', PARAM_RAW);
        $mform->addRule('contents', null, 'required', null, 'client');
        
        if ($board->allowperiod) {
            $ten_day = strtotime(date("Y-m-d", strtotime("+10 day")));
            $availablefromgroup = array();
            $availablefromgroup[] = & $mform->createElement('date_selector', 'timeend', '');
            $availablefromgroup[] = & $mform->createElement('checkbox', 'availablefromenabled', '', get_string('enable'));
            $mform->addGroup($availablefromgroup, 'availablefromgroup', get_string('timeend', 'local_jinoboard'), ' ', false);
            $mform->disabledIf('availablefromgroup', 'availablefromenabled');
            $mform->setDefault('timeend', $ten_day);
            $mform->setDefault('availablefromenabled', true);
            $mform->setType('timeend', PARAM_INT);
            $mform->setType('availablefromenabled', PARAM_INT);
        }

        if ($board->allowupload) {

            $chk = "<input type='checkbox' name='view_filemanager' /> " . get_string('attachmentcheck', 'local_jinoboard');
            $mform->addElement('static', 'static', get_string('attachment', 'local_jinoboard'), $chk);

            $staticmsg = "<br><span>" . get_string('attachmentmsg', 'local_jinoboard') . "</span>";
            $mform->addGroup(array(
                $mform->createElement('filemanager', 'attachments', get_string('attachment', 'local_jinoboard'), null, self::attachment_options($board)),
                $mform->createElement('static', 'text', '', $staticmsg)
                    ), 'filemanager', '', array(''), false);
        }
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $id);
        $mform->addElement('hidden', 'type');
        $mform->setType('type', PARAM_INT);
        $mform->setDefault('type', $type);
        $mform->addElement('hidden', 'mode');
        $mform->setType('mode', PARAM_CLEAN);
        $mform->setDefault('mode', $mode);

        $this->add_action_buttons();
    }

    function validation($data, $files) {
        return array();
    }

}
