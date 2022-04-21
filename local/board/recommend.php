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

        $classname = context_helper::get_class_for_level(CONTEXT_MODULE);

        $contexts[$cm->id] = $classname::instance($cm->id);

        $context = $contexts[$cm->id];

        $PAGE->set_context($context);


        if (!has_capability('mod/jinotechboard:viewcontent', $context)) {
            notice('cannotdeletepost', 'jinotechboard');
        }

        $db_recommendcnt = $DB->get_field('jinotechboard_contents', 'recommendcnt', array('id' => $contentId, 'board' => $b));
        
        $DB->set_field_select('jinotechboard_contents', 'recommendcnt', intval($db_recommendcnt) + 1, " id='$contentId'");
        
        $recommend = new stdClass();
        $recommend->userid = $USER->id;
        $recommend->jinotechboardid = $b;
        $recommend->contentsid = $contentId;
        $recommend->timerecommend = time();
        
        $DB->insert_record('jinotechboard_recommend', $recommend);

        redirect("index.php?b=$b&contentId=$contentId&type=$type");

        ?>

    </body>
</html>