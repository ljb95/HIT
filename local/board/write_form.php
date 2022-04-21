<?php

require_once($CFG->libdir . '/formslib.php');

class jinotechboard_write_form extends moodleform {

    function definition() {
        global $DB;

        $mform = $this->_form;
        $board = $this->_customdata['board'];
        $boardform = $this->_customdata['boardform'];
        $context = $this->_customdata['context'];
        $mode = $this->_customdata['mode'];
        $lev = $this->_customdata['lev'];
        $type = $this->_customdata['type'];

        $isnotice = $this->_customdata['isnotice'];
        $isprivate = $this->_customdata['isprivate'];

        //강의선택(공지사항일경우)
        if ($type == BOARD_NOTICE && $mode == "") {
            $mform->addElement('header', 'nameforyourheaderelement', get_string('course:selecttitle', 'local_board'));
            $checkarray = array();
            $courses_all = enrol_get_my_courses();
            $contents = board_my_courses_in_board($courses_all, $type);
            foreach ($contents as $course) {
                $checkarray[] = & $mform->createElement('advcheckbox', 'courselist[' . $course->course . ']', '', $course->coursename, array('group' => 1), $course->course);
            }
            $mform->addGroup($checkarray, 'checkar', get_string('course:name', 'local_board'), array(' '), false);
        }

        //게시글 등록
        $mform->addElement('header', 'nameforyourheaderelement', get_string('notice:write', 'local_board'));

        //제목영역
        $titlearray = array();
        $titlearray[] = & $mform->createElement('text', 'title', get_string('content:title', 'local_board'));
        if ($board->allownotice && $mode != 'reply' && $lev == 0) {
            $titlearray[] = & $mform->createElement('advcheckbox', 'isnotice', '', get_string('notice', 'local_jinoboard'), array('group' => 2), array(0, 1));
        }
        $mform->addGroup($titlearray, 'titlear', get_string('title', 'jinotechboard'), ' ', false);
        $mform->setType('title', PARAM_CLEAN);
        if ($board->allownotice && $mode != 'reply' && $lev == 0)
            $mform->setDefault('isnotice', $isnotice);
        $mform->addRule('titlear', null, 'required');
        $titlegrprules = array();
        $titlegrprules['title'][] = array(null, 'required', null, 'client');
        $mform->addGroupRule('titlear', $titlegrprules);

        if ($board->allowsecret && $mode != 'reply') {
            $radioarray = array();
            $radioarray[] = & $mform->createElement('radio', 'isprivate', '', get_string('issecretno', 'local_jinoboard'), 0);
            $radioarray[] = & $mform->createElement('radio', 'isprivate', '', get_string('issecretyes', 'local_jinoboard'), 1);
            $mform->addGroup($radioarray, 'radioar', get_string('issecret', 'local_jinoboard'), array(' '), false);
            $mform->setDefault('isprivate', $isprivate);
            $mform->addRule('radioar', null, 'required');
        }

        $mform->addElement('editor', 'contents', get_string('content', 'jinotechboard'), null, self::editor_options());
        $mform->setType('contents', PARAM_RAW);
        $mform->addRule('contents', null, 'required', null, 'client');

        $chk = "<input type='checkbox' title='비공개글' name='view_filemanager' /> " . get_string('attachmentcheck', 'local_jinoboard');
        $mform->addElement('static', 'static', get_string('attachment', 'local_jinoboard'), $chk);

        $staticmsg = "<br><span>" . get_string('attachmentmsg', 'local_jinoboard') . "</span>";
        $mform->addGroup(array(
            $mform->createElement('filemanager', 'attachments', get_string('attachment', 'local_jinoboard'), null, self::attachment_options($board)),
            $mform->createElement('static', 'text', '', $staticmsg)
                ), 'filemanager', '', array(''), false);

        $this->add_action_buttons();

        $mform->addElement('hidden', 'b');
        $mform->setType('b', PARAM_INT);
        $mform->setDefault('b', $board->id);

        $mform->addElement('hidden', 'confirmed');
        $mform->setType('confirmed', PARAM_INT);
        $mform->setDefault('confirmed', $this->_customdata['confirmed']);

        $mform->addElement('hidden', 'boardform');
        $mform->setType('boardform', PARAM_INT);
        $mform->setDefault('boardform', $boardform);

        $mform->addElement('hidden', 'itemid');
        $mform->setType('itemid', PARAM_INT);
        $mform->setDefault('itemid', $this->_customdata['itemid']);

        $mform->addElement('hidden', 'type');
        $mform->setType('type', PARAM_INT);
        $mform->setDefault('type', $this->_customdata['type']);

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);
        $mform->setDefault('courseid', $this->_customdata['courseid']);

        $mform->addElement('hidden', 'boardform');
        $mform->setType('boardform', PARAM_INT);
        $mform->setDefault('mode', '1');

        if ($this->_customdata['mode'] == "edit") {
            $mform->addElement('hidden', 'mode');
            $mform->setType('mode', PARAM_TEXT);
            $mform->setDefault('mode', 'edit');

            $mform->addElement('hidden', 'contentId');
            $mform->setType('contentId', PARAM_INT);
        } else if ($this->_customdata['mode'] == "reply") {
            $mform->addElement('hidden', 'mode', "reply");
            $mform->setType('mode', PARAM_TEXT);

            $mform->addElement('hidden', 'contentId');
            $mform->setType('contentId', PARAM_INT);

            $mform->addElement('hidden', 'ref');
            $mform->setType('ref', PARAM_INT);

            $mform->addElement('hidden', 'step');
            $mform->setType('step', PARAM_INT);

            $mform->addElement('hidden', 'lev');
            $mform->setType('lev', PARAM_INT);
        }
    }

    public static function editor_options() {
        global $COURSE, $PAGE, $CFG;
        require_once($CFG->dirroot . "/repository/lib.php");

        $maxbytes = get_user_max_upload_file_size($PAGE->context, $CFG->maxbytes, $COURSE->maxbytes);
        return array(
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'maxbytes' => $maxbytes,
            'trusttext' => true,
            'return_types' => FILE_INTERNAL | FILE_EXTERNAL
        );
    }

    public static function attachment_options($board) {

        return array(
            'subdirs' => 0,
            'maxbytes' => $board->maxbytes,
            'maxfiles' => $board->maxattachments,
            'accepted_types' => '*',
            'return_types' => FILE_INTERNAL
        );
    }

    // Perform some extra moodle validation
    function validation($data, $files) {
        $errors = array();
        if ($data['type'] == BOARD_NOTICE && empty($data['mode'])) {
            $cnt = 0;
            foreach ($data['courselist'] as $val) {
                if (!empty($val)) {
                    $cnt++;
                }
            }
            if ($cnt == 0) {
                $errors['checkar'] = get_string('required');
            }
        }
        return $errors;
    }

}

?>
