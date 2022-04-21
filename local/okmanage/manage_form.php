<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/repository/lib.php');
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->libdir . '/completionlib.php');
require_once($CFG->libdir . '/coursecatlib.php');

class okmanage_form extends moodleform {

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
        global $CFG, $DB, $USER, $PAGE;

        $mform = $this->_form;
        $PAGE->requires->yui_module('moodle-course-formatchooser', 'M.course.init_formatchooser', array(array('formid' => $mform->getAttribute('id'))));
        
        $id = optional_param('id', 0, PARAM_INT);
        $context = get_context_instance(CONTEXT_COURSE, $id);


        $course = get_course($id);
        $courseconfig = get_config('moodlecourse');

        $mform = $this->_form;

        $mform->addElement('header', 'courseedit', get_string('course_edit', 'local_okmanage'));
        $mform->setExpanded('courseedit', true);
        
        
        $mform->addElement('date_selector', 'startdate', get_string('startdate', 'local_okmanage'));
        $mform->addHelpButton('startdate', 'startdate');
        $mform->setDefault('startdate', time() + 3600 * 24);

        $mform->addElement('date_selector', 'enddate', get_string('enddate', 'local_okmanage'), array('optional' => true));
        $mform->addHelpButton('enddate', 'enddate');
        
        $choices = array();
        $choices['0'] = get_string('hide');
        $choices['1'] = get_string('show');
        $mform->addElement('select', 'visible', get_string('visible'), $choices);
        $mform->addHelpButton('visible', 'visible');
        $mform->setDefault('visible', $courseconfig->visible);

        $languages = array();
        $languages[''] = get_string('forceno');
        $languages += get_string_manager()->get_list_of_translations();
        $mform->addElement('select', 'lang', get_string('forcelanguage'), $languages);
        $mform->setDefault('lang', $courseconfig->lang);
        
        $mform->addElement('selectyesno', 'notice', get_string('notice','local_okmanage'));
        $mform->setDefault('notice', '1');

        $mform->addElement('header', 'courseformathdr', get_string('format', 'local_okmanage'));
        $mform->setExpanded('courseformathdr', true);

        $courseformats = get_sorted_course_formats(true);
        $formcourseformats = array();
        foreach ($courseformats as $courseformat) {
            $formcourseformats[$courseformat] = get_string('pluginname', "format_$courseformat");
        }
        if (isset($course->format)) {
            $course->format = course_get_format($course)->get_format(); // replace with default if not found
            if (!in_array($course->format, $courseformats)) {
                // this format is disabled. Still display it in the dropdown
                $formcourseformats[$course->format] = get_string('withdisablednote', 'moodle', get_string('pluginname', 'format_' . $course->format));
            }
        }
        
        $mform->registerNoSubmitButton('updatecourseformat');
        $mform->addElement('submit', 'updatecourseformat', get_string('courseformatudpate'));

        $mform->addElement('select', 'format', get_string('format'), $formcourseformats);
        $mform->addHelpButton('format', 'format');
        $mform->setDefault('format', $courseconfig->format);
        


        $mform->addElement('header', 'background', get_string('background', 'local_okmanage'));
        $mform->setExpanded('background', true);

        
        

        $mform->addElement('filemanager', 'attachments', get_string('thumbnail','local_okmanage'), null, self::attachment_options());
        
        $mform->addElement('header', 'progress', get_string('progress', 'local_okmanage'));
        $mform->setExpanded('progress', true);
        
        $mform->addElement('selectyesno', 'useprogress', get_string('useprogress','local_okmanage'));
        $mform->setDefault('useprogress', '1');
        
        $mform->addElement('selectyesno', 'onlineatt', get_string('onlineatt','local_okmanage'));
        $mform->setDefault('onlineatt', '1');
        
        $choices = array();
        $choices['0'] = get_string('standard1','local_okmanage');
        $choices['1'] = get_string('standard2','local_okmanage');
        $mform->addElement('select', 'attstandard', get_string('attstandard','local_okmanage'),$choices);
        $mform->setDefault('attstandard', '0');
        
        $mform->addElement('selectyesno', 'offatt', get_string('offatt','local_okmanage'));
        $mform->setDefault('offatt', '1');
        
        
        $mform->addElement('hidden', 'addcourseformatoptionshere');
        $mform->setType('addcourseformatoptionshere', PARAM_BOOL);

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', $id);

        $this->add_action_buttons();
    }

    function definition_after_data() {
        global $DB;

        $mform = $this->_form;

        // add course format options
        $formatvalue = $mform->getElementValue('format');
        if (is_array($formatvalue) && !empty($formatvalue)) {

            $params = array('format' => $formatvalue[0]);
            // Load the course as well if it is available, course formats may need it to work out
            // they preferred course end date.
            if ($courseid) {
                $params['id'] = $courseid;
            }
            $courseformat = course_get_format((object)$params);

            $elements = $courseformat->create_edit_form_elements($mform);
            for ($i = 0; $i < count($elements); $i++) {
                $mform->insertElementBefore($mform->removeElement($elements[$i]->getName(), false),
                        'background');
            }

            // Remove newsitems element if format does not support news.
        }
    }

    function validation($data, $files) {
        return array();
    }

}
