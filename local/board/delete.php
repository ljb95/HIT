<html>
    <head>
        <title></title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">

    </head>
    <body>
        <?php
        require_once('../../config.php');

        $b = optional_param('b', 0, PARAM_INT);
        $contentId = optional_param('contentId', 0, PARAM_INT);

        if ($b) {

            if (!$board = $DB->get_record("jinotechboard", array("id" => $b))) {
                print_error('invalidboardid', 'jinotechboard');
            }
            if (!$course = $DB->get_record("course", array("id" => $board->course))) {
                print_error('coursemisconf');
            }

            if (!$cm = get_coursemodule_from_instance("jinotechboard", $board->id, $course->id)) {
                print_error('missingparameter');
            }
        } else {
            print_error('missingparameter');
        }

        require_course_login($course, true, $cm);

        $context = get_context_instance(CONTEXT_MODULE, $cm->id);

        $PAGE->set_context($context);


        if (!has_capability('mod/jinotechboard:view', $context)) {
            notice('cannotdeletepost', 'jinotechboard');
        }



        $db_userid = $DB->get_field('jinotechboard_contents', 'userid', array('id' => $contentId, 'board' => $b));

//        if ($db_userid == $USER->id || (jino_get_usercase($USER->id) == 'manager')) {
        if ($db_userid == $USER->id) {

            $DB->delete_records_select("jinotechboard_contents", "id=? and board=?", array($contentId, $b));



            $msg = get_string('Deletionshavebeencompleted', 'jinotechboard');


            redirect("index.php?type=$board->type");
        } else {
            $msg = get_string('no_delete_permission', 'jinotechboard');


            redirect("index.php?type=$board->type");
        }
        ?>

    </body>
</html>