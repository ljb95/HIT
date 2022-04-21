<?php

require(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once $CFG->dirroot.'/local/board/lib.php';
require_once $CFG->libdir.'/formslib.php';
require_once $CFG->dirroot . '/lib/form/filemanager.php';


$courselist = optional_param_array('courselist', array(), PARAM_INT);

$type = optional_param('type', BOARD_NOTICE, PARAM_INT);     
$current_course = optional_param('courseid', 1, PARAM_INT);
$reply = optional_param('reply', 0, PARAM_INT);
$b = optional_param('b', 0, PARAM_INT);
$edit = optional_param('edit', 0, PARAM_INT);
$delete = optional_param('delete', 0, PARAM_INT);
$name = optional_param('name', '', PARAM_CLEAN);
$category = optional_param('category', 0, PARAM_INT);
$isnotice = optional_param('isnotice', false, PARAM_BOOL);
$isprivate = optional_param('isprivate', false, PARAM_BOOL);
$ref = optional_param('ref', 0, PARAM_INT);
$step = optional_param('step', 0, PARAM_INT);
$lev = optional_param('lev', 0, PARAM_INT);
$contents = optional_param('contents', "", PARAM_CLEAN);
$title = optional_param('title', '', PARAM_CLEAN);
$confirmed = optional_param('confirmed', 0, PARAM_INT);
$mode = optional_param('mode', "", PARAM_CLEAN);
$contentId = optional_param('contentId', 0, PARAM_INT);
$parentId = optional_param('parentId', 0, PARAM_INT);
$num_notice = optional_param('num_notice', 0, PARAM_INT);
$itemid = optional_param('itemid', 0, PARAM_INT);
$p_year = optional_param('year', 0, PARAM_INT);
$p_month = optional_param('month', 0, PARAM_INT);
$p_day = optional_param('day', 0, PARAM_INT);
$endtimeok = optional_param('endtimeok', 0, PARAM_INT);

require_login();
if($mode =="" && $type == BOARD_NOTICE && $confirmed == 1 ) {
    $current_course = $courselist[0];
}    
$course = $DB->get_record('course', array('id' => $current_course), '*', MUST_EXIST);

if($current_course != 1) {
    $board = $DB->get_record_sql("select * from {jinotechboard} where type='".$type."' and course = ".$current_course);
	$b = $board->id;
}

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

}else if (!$cm = get_coursemodule_from_instance("jinotechboard", $board->id, $current_course)) {
        $b = $board->id;
}

require_course_login($course, true, $cm);

$PAGE->set_pagelayout('standard');

if($type == BOARD_NOTICE) {
    $strplural = get_string("notice:write", "local_board");
}else {
    $strplural = get_string("qna:write", "local_board", $COURSE->fullname);
}
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string("pluginnameplural", "local_board"));
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading($strplural);

$classname = context_helper::get_class_for_level(CONTEXT_MODULE);

if(!empty($cm->id)){
$context = context_module::instance($cm->id);
} else {
    $context = context_system::instance();
}

$PAGE->set_url('/local/board/write.php', array(
    'reply' => $reply,
    'b' => $b,
    'edit' => $edit,
    'delete' => $delete,
    'confirmed' => $confirmed,
));

echo $OUTPUT->header();

if ($confirmed == 0) {
    if ($mode == "edit" && $contentId > 0) {
        $data = $DB->get_record('jinotechboard_contents', array('id' => $contentId));

        $data = trusttext_pre_edit($data, 'contents', $context);

//        if (!has_capability('mod/jinotechboard:edit', $context) || ($data->userid != $USER->id && (jino_get_usercase($USER->id) != 'manager'))) {
        if (!has_capability('mod/jinotechboard:edit', $context) && ($data->userid != $USER->id)) {
            notice('cannoteditpost', 'jinotechboard');
        }

        $title = $data->title;
        $contents = $data->contents;
        $isnotice = $data->isnotice;
        $isprivate = $data->isprivate;
        $category_field = $data->category;
        $timeend = date('Y-m-d',$data->timeend);
        if($data->timeend != 0){
            $endtimeok = 1;
        }
        
        $timeend_array = explode('-', $timeend);
        $p_year = $timeend_array[0];
        $p_month = $timeend_array[1];
        $p_day = $timeend_array[2];

    } else if ($mode == "reply") {
        if (!has_capability('mod/jinotechboard:reply', $context) || $board->allowreply == 0) {
            notice('cannotreplypost', 'jinotechboard');
        }

        $parentData = $DB->get_record('jinotechboard_contents', array('id' => $contentId));
        $title = "Re:" . $parentData->title;
    } else {
        if (!has_capability('mod/jinotechboard:write', $context)) {
            notice('cannotaddpost', 'jinotechboard');
        }
    }
} else if ($confirmed == 1) {
    if ($mode == "edit" && $contentId > 0) {
        $data = $DB->get_record('jinotechboard_contents', array('id' => $contentId));

//        if (!has_capability('mod/jinotechboard:edit', $context) || ($data->userid != $USER->id && (jino_get_usercase($USER->id) != 'manager'))) {
        if (!has_capability('mod/jinotechboard:edit', $context) && ($data->userid != $USER->id)) {
            notice('cannoteditpost', 'jinotechboard');
        }

        $newdata = new object();
        $newdata->course = $course->id;
        $newdata->board = $b;
        $newdata->userid = $USER->id;

        $newdata->title = $title;
        $newdata->contents = $contents;

        $newdata->id = $contentId;
        $newdata->category = $category;
        
        if (!$isnotice) {
            $isnotice = 0;
        } else {
            $isnotice = 1;
        }
        
        if (!$isprivate) {
            $isprivate = 0;
        } else {
            $isprivate = 1;
        }
        
        $newdata->isnotice = $isnotice;
        $newdata->isprivate = $isprivate;
        $newdata->timemodified = time();

        $newdata->itemid = $itemid;
        
        $newdata->timeend = mktime(0,0,0,$p_month,$p_day,$p_year);
        
        $newdata->contents = file_save_draft_area_files($newdata->itemid, $context->id, 'mod_jinotechboard', 'contents', $newdata->id, jinotechboard_editor_options($context, null), $newdata->contents);
        $DB->set_field('jinotechboard_contents', 'contents', $newdata->contents, array('id' => $newdata->id));

        $DB->update_record('jinotechboard_contents', $newdata);
        $draftitemid = file_get_submitted_draft_itemid('attachments');
        file_save_draft_area_files($draftitemid, $context->id, 'mod_jinotechboard', 'attachment', $newdata->id);

        echo '<script language="javascript">';
        echo 'document.location.href="' . $CFG->wwwroot . '/local/board/index.php?type='.$type.'&b='. $b .'";';
        echo '</script>';
        die();
    } else if ($mode == "reply") {
        if (!has_capability('mod/jinotechboard:reply', $context) || $board->allowreply == 0) {
            notice('cannotreplypost', 'jinotechboard');
        }

        $updatedata = new object();

        $query = "update {jinotechboard_contents} set step = step + 1 where board = :board and ref = :ref and step > :step";
        $DB->execute($query, array('board'=>$board->id, 'ref'=>$ref, 'step'=>$step));

        $newdata = new object();

        $newdata->course = $course->id;
        $newdata->board = $b;
        $newdata->userid = $USER->id;

        $newdata->title = $title;
        $newdata->category = $category;
        $newdata->contents = $contents;
        
        if (!$isnotice) {
            $isnotice = 0;
        } else {
            $isnotice = 1;
        }
        
        if (!$isprivate) {
            $isprivate = 0;
        } else {
            $isprivate = 1;
        }
        $newdata->isnotice = $isnotice;
        $newdata->isprivate = $isprivate;
        $newdata->ref = $ref;
        $newdata->step = $step + 1;
        $newdata->lev = $lev + 1;

        $newdata->viewcnt = 0;
        $newdata->timecreated = time();
        $newdata->timemodified = time();

        $newdata->itemid = $itemid;

        $newid = $DB->insert_record('jinotechboard_contents', $newdata);
        $newdata->contents = file_save_draft_area_files($newdata->itemid, $context->id, 'mod_jinotechboard', 'contents', $newdata->id, jinotechboard_editor_options($context, null), $newdata->contents);
        $DB->set_field('jinotechboard_contents', 'contents', $newdata->contents, array('id' => $newdata->id));

        $draftitemid = file_get_submitted_draft_itemid('attachments');
        file_save_draft_area_files($draftitemid, $context->id, 'mod_jinotechboard', 'attachment', $newid);

        echo '<script language="javascript">';
        echo 'document.location.href="' . $CFG->wwwroot . '/local/board/index.php?type='.$type.'&b=' . $b . '";';
        echo '</script>';
        die();
    } else {
        if (!has_capability('mod/jinotechboard:write', $context)) {
            notice('cannotaddpost', 'jinotechboard');
        }

        $newdata = new object();

        if (!empty($title) && confirm_sesskey(sesskey())) {
            $newdata->board = $b;
            $newdata->userid = $USER->id;
            $newdata->title = $title;
            $newdata->category = $category;
            $newdata->contents = $contents;
            $newdata->ref = 0;
            $newdata->step = 0;
            $newdata->lev = 0;
            
            if (!$isnotice) {
                $isnotice = 0;
            } else {
                $isnotice = 1;
            }
            if (!$isprivate) {
                $isprivate = 0;
            } else {
                $isprivate = 1;
            }
            $newdata->isnotice = $isnotice;
            $newdata->isprivate = $isprivate;
            $newdata->viewcnt = 0;

            $newdata->timecreated = time();
            $newdata->timemodified = time();

            $newdata->itemid = $itemid;
            
            $newdata->timeend = mktime(0,0,0,$p_month,$p_day,$p_year);
            if($type == BOARD_NOTICE) {
                if(is_array($courselist)) {
                    
                    list($coursessql, $params) = $DB->get_in_or_equal(array_keys(array_flip($courselist)), SQL_PARAMS_NAMED, 'c0');
                    $params['type'] = $type;
                    $sql = 'select id, course from {jinotechboard} where type = :type and course '.$coursessql;
                    $courses = $DB->get_records_sql($sql, $params);
                    
                    foreach($courses as $bids){
                        $newdata->course = $bids->course;
                        $newdata->board = $bids->id;
                        if ($newid = $DB->insert_record('jinotechboard_contents', $newdata)) {
                            $newdata->id = $newid;
                            $DB->set_field_select('jinotechboard_contents', "ref", $newid, "id=$newid");
                            
                            $cm = get_coursemodule_from_instance("jinotechboard", $newdata->board, $newdata->course);
                            $classname = context_helper::get_class_for_level(CONTEXT_MODULE);

                            $contexts[$cm->id] = $classname::instance($cm->id);

                            $context = $contexts[$cm->id];
                            
                            $newdata->contents = file_save_draft_area_files($newdata->itemid, $context->id, 'mod_jinotechboard', 'contents', $newdata->id, jinotechboard_editor_options($context, null), $newdata->contents);
                            $DB->set_field('jinotechboard_contents', 'contents', $newdata->contents, array('id' => $newdata->id));
                            
                            $draftitemid = file_get_submitted_draft_itemid('attachments');
                            file_save_draft_area_files($draftitemid, $context->id, 'mod_jinotechboard', 'attachment', $newid);
                        }
                    }
                }
            }else{
                $newdata->course = $course->id;
                if ($newid = $DB->insert_record('jinotechboard_contents', $newdata)) {
                    $newdata->id = $newid;
                    $DB->set_field_select('jinotechboard_contents', "ref", $newid, "id=$newid");

                    $newdata->contents = file_save_draft_area_files($newdata->itemid, $context->id, 'mod_jinotechboard', 'contents', $newdata->id, jinotechboard_editor_options($context, null), $newdata->contents);
                    $DB->set_field('jinotechboard_contents', 'contents', $newdata->contents, array('id' => $newdata->id));
                    
                    $draftitemid = file_get_submitted_draft_itemid('attachments');
                    file_save_draft_area_files($draftitemid, $context->id, 'mod_jinotechboard', 'attachment', $newid);
                }
            }
        }


        echo '<script type="text/javascript">';
        echo 'document.location.href="' . $CFG->wwwroot . '/local/board/index.php?type='.$type.'";';
        echo '</script>';
        die();
    }
}

$postid = empty($data->id) ? null : $data->id;
         $context = context_system::instance();
$editor_option = board_editor_options($context, $postid);

//$draftitemid1 = file_get_submitted_draft_itemid('content');
$draftitemid1 = file_get_submitted_draft_itemid('itemid');
$contents = file_prepare_draft_area($draftitemid1, $context->id, 'mod_jinotechboard', 'contents', $postid, $editor_option, $contents);

editors_head_setup();

$args = new stdClass();
// need these three to filter repositories list
$args->accepted_types = array('web_image');
$args->return_types = (FILE_INTERNAL | FILE_EXTERNAL);
$args->context = $context;
$args->env = 'filepicker';

// advimage plugin
$image_options = initialise_filepicker($args);
$image_options->context = $context;
$image_options->client_id = uniqid();
$image_options->maxbytes = $editor_option['maxbytes'];
$image_options->env = 'editor';
$image_options->itemid = $draftitemid1;

$fpoptions = array();
$fpoptions['image'] = $image_options;

$editor = editors_get_preferred_editor(FORMAT_HTML);
$editor->id = "editor_contents";
$editor->use_editor($editor->id, $editor_option, $fpoptions);


?>

    <form id="frm_group" action="<?php echo($CFG->wwwroot . "/local/board/write.php"); ?>" method='post' ENCTYPE='multipart/form-data'>
        <?php
            if($type == BOARD_NOTICE && $mode=="") {
            
                $enrol_params['type'] = BOARD_NOTICE;
                $enrol_params['contextlevel'] = CONTEXT_COURSE;
                $enrol_params['userid'] = $USER->id;
                $enrol_params['shortname1'] = 'editingteacher';
                $enrol_params['shortname2'] = 'teacher';
                $enrol_params['year'] = get_config('moodle', 'haxa_year');
                $enrol_params['term'] = get_config('moodle', 'haxa_term');
                $enrol_params['self_course'] = '9999';
                $noitce_sql = 'SELECT co.id, co.fullname, yc.subject_id 
                               FROM {course} co 
                               JOIN {context} ct ON ct.instanceid = co.id 
                               JOIN {role_assignments} ra ON ra.contextid = ct.id
                               JOIN {role} ro ON ro.id = ra.roleid
                               JOIN {lmsdata_class} yc ON yc.course = co.id 
                               JOIN {jinotechboard} jb ON jb.course = co.id 
                               WHERE ct.contextlevel = :contextlevel and jb.type = :type and ra.userid = :userid and (ro.shortname = :shortname1 or ro.shortname = :shortname2) and ((yc.year = :year AND yc.term = :term) OR yc.year = :self_course)';
                $notice_courses = $DB->get_records_sql($noitce_sql, $enrol_params); 
                echo ' <div class="table-search-option"> ';
                foreach($notice_courses as $course) {
                    echo '<div class="course_check" style="padding-bottom:5px">
                            <input type="checkbox" title="courselist" name="courselist[]" value="'.$course->id.'">
                            <sapn class="table-search-option-label">'.$course->fullname.'  [ '.$course->subject_id.' ]</sapn>
                          </div>';
                }
                if(!$notice_courses){
                    echo '<div class="course_check" style="padding-bottom:5px">
                            <sapn class="table-search-option-label">'.get_string('no_course','local_board').'</sapn>
                          </div>';
                }
                echo '</div>';
            }else if($type == BOARD_QNA) {
        ?>

        <?php
        }
        ?>
        <input type="hidden" name="confirmed" value="1" />
        <input type="hidden" name="sesskey" value="<?php echo(sesskey()); ?>" />
        <input type="hidden" name="b" value="<?php echo($b); ?>" />
        <input type="hidden" name="itemid" value ="<?php echo $draftitemid1; ?>" />
        <input type="hidden" name="type" value ="<?php echo $type; ?>" />
        <input type="hidden" name="courseid" value ="<?php echo $current_course; ?>" />
<?php if ($mode == "edit") { ?>
            <input type="hidden" name="contentId" value="<?php echo($contentId); ?>" />
            <input type="hidden" name="mode" value="edit" />
<?php } else if ($mode == "reply") { ?>
            <input type="hidden" name="contentId" value="<?php echo($contentId); ?>" />
            <input type="hidden" name="ref" value="<?php echo($parentData->ref); ?>" />
            <input type="hidden" name="step" value="<?php echo($parentData->step); ?>" />
            <input type="hidden" name="lev" value="<?php echo($parentData->lev); ?>" />
            <input type="hidden" name="mode" value="reply" />
        <?php } ?>
    <table class="detail">
        <tbody>
                <tr>
                    <td class="option"><?php echo get_string('content:title', 'local_board'); ?></td>
                    <td class="value">
                        <input type="text" name="title" title="title" id="title" value="<?php echo($title); ?>" />
                        <?php
                            $checked = "";
                            if($type == BOARD_NOTICE) {
                                if($isnotice) {
                                    $checked = "checked";
                                }
                                echo '<input type="checkbox" name="isnotice" title="isnotice" '.$checked.'>'.get_string('notice:isnotice', 'local_board'); 
                            }else if($board->allowsecret == 1){
                                if($isprivate) {
                                    $checked = "checked";
                                }
                                echo '<input type = "checkbox" name = "isprivate" title="isnotice" '.$checked.'>'.get_string('content:private', 'local_board');  
                            }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td class="option"><?php echo get_string('content:contents', 'local_board'); ?></td>
                    <td class="value"><textarea name="contents" title="내용" id="editor_contents" style="width:100%;height:300px; resize:none;"><?php echo $contents; ?></textarea></td>
                </tr>
                <?php
 					$tempDt=mktime(0,0,0,date('m'),date('d')+10,date('Y'));
                    $currentday = date('d',$tempDt);
                    $currentmonth = date('m',$tempDt);
                    $currentyear = date('Y',$tempDt);
					$lastday = date('t',mktime(0,0,1,$currentmonth,1,$currentyear));
                    if($mode == 'edit' && $endtimeok == 1){
                        $day = $p_day;
                        $month = $p_month;
                        $year = $p_year;
                    } else {
                        $day = $currentday;
                        $month = $currentmonth;
                        $year = $currentyear;
                    }
                    if($type == 1){
                ?>
                <tr>
                    <td class="option"><?php echo get_string('content:enddate', 'local_board'); ?></td>
                    <td class="value">
                        <?php
                            echo "<select id='year_selecter' title='year_selecter' name='year'>";
                                for($y=$year; $y<=$currentyear+5; $y++){
                                    if($year == $y){
                                        echo "<option selected>$y</option>";
                                    } else {
                                        echo "<option>$y</option>";
                                    }
                                }
                            echo "</select>";
                            echo "<select id='month_selecter' title='month_selecter' name = 'month'>";
                                for($m=1; $m<=12; $m++){
                                    if($month == $m){
                                        echo "<option selected>$m".  get_string('month','local_board')."</option>";
                                    } else {
                                        echo "<option>$m".  get_string('month','local_board')."</option>";
                                    }
                                }
                            echo "</select>";
                            echo "<select id='day_selecter' name='day'>";
                                for($i=1; $i<=$lastday; $i++){
                                    if($day == $i){
                                        echo "<option selected>$i</option>";
                                    } else {
                                        echo "<option>$i</option>";
                                    }
                                }
                            echo "</select>";
                        ?>
                        <input type="checkbox" checked <?php if($endtimeok == 1){echo "checked";}?> id="endtimeok" name="endtimeok"> <?php echo get_string('content:managementmode', 'local_board'); ?>
                    </td>
                </tr>
                    <?php }?>
                <tr>
                    <td class="option"><?php echo get_string('content:attachments', 'local_board'); ?></td>
                    <td class="value">
                        <div id="filearea">
                        <?php
                            echo $OUTPUT->container_start('');
                            echo ($mode == 'edit') ?"<input type='checkbox' title='view_filemanager' name='view_filemanager' checked /> " . get_string('attachmentcheck', 'local_jinoboard'):"<input type='checkbox' title='view_filemanager' name='view_filemanager' /> " . get_string('attachmentcheck', 'local_jinoboard');
                            echo '<div id="fgroup_id_filemanager">';
                            if ($mode == 'edit') {
                                $draftitemid = file_get_submitted_draft_itemid('attachment');
                                file_prepare_draft_area($draftitemid, $context->id, 'mod_jinotechboard', 'attachment', $contentId, array('subdirs' => false));
                                $filemanager = new MoodleQuickForm_filemanager('attachments', get_string('attachment', 'jinotechboard'), 
                                        array('id' => 'attachments', 'value' => $draftitemid), array('subdirs' => false, 'maxfiles' => $board->maxattachments, 'maxbytes' => $board->maxbytes));
                            } else {
                                $filemanager = new MoodleQuickForm_filemanager('attachments', get_string('attachment', 'jinotechboard'), 
                                        array('id' => 'attachments'), array('subdirs' => false, 'maxfiles' => $board->maxattachments, 'maxbytes' => $board->maxbytes));
                            }
                            echo $filemanager->toHtml();
                            echo "</div>";
                            echo $OUTPUT->container_end();
                        ?>
                        </div>
                        <?php
                            if (isset($filedata)) {
                                foreach ($filedata as $onefile) {
                                    echo($onefile->filename . '  <input type="checkbox" title="delete" name="delete[]" value="' . $onefile->id . '" />' . get_string('delete', 'jinotechboard') . '<br>');
                                }
                            }
                        ?> 
                    </td>
                </tr>
        </tbody>
    </table>
    </form> 
<div class="table-footer-area">
    <div class="btn-area btn-area-left">
        <input type="button" class="blue-form"  onClick="javascript:location.href='<?php echo $CFG->wwwroot."/local/board/index.php?type=$type"; ?>'" value="<?php echo get_string('content:list', 'local_board'); ?>" />
    </div>
    <div class="btn-area btn-area-right"> 
        <input type="button" class="gray-form" value="<?php echo get_string('content:cancel', 'local_board'); ?>"  onclick="document.location.href = '<?php echo($CFG->wwwroot); ?>/local/board/index.php?b=<?php echo($b); ?>';"/>
        <input type="button" class="red-form"  onClick="formSubmit()" value="<?php echo get_string('content:save', 'local_board'); ?>" />
    </div>
</div>

</body>

<?php
    echo $OUTPUT->footer();
	
?>
<script type="text/javascript">
     $(window).load(function () {
        if ($("input[name=view_filemanager]").prop("checked")) {
            $("#fgroup_id_filemanager").show();
        } else {
            $("#fgroup_id_filemanager").hide();
        }
    });
    $("input[name=view_filemanager]").click(function () {
        if ($("input[name=view_filemanager]").prop("checked")) {
            $("#fgroup_id_filemanager").show();
        } else {
            $("#fgroup_id_filemanager").hide();
        }
    });
    $('#endtimeok').click(function(){
        if($('#endtimeok').prop('checked')){
            $('#day_selecter').attr('disabled',null);
            $('#month_selecter').attr('disabled',null);
            $('#year_selecter').attr('disabled',null);
        } else {
            $('#day_selecter').attr('disabled','disabled');
            $('#month_selecter').attr('disabled','disabled');
            $('#year_selecter').attr('disabled','disabled');
        }
    })

    function formSubmit(){
        var frm = document.getElementById('frm_group');
        var isCourseChk = false;
        var arr_course = document.getElementsByName("courselist[]");
      
        for(var i=0;i<arr_course.length;i++){
            if(arr_course[i].checked == true) {
                isCourseChk = true;
                break;
            }
        }
        
        <?php 
            if(!($type == BOARD_NOTICE && $mode =="")){
                echo "isCourseChk = true; ";
            }
        ?>
        
        var title = document.getElementById('title').value;
       
        if(title=='' || title =='undefined'){
            alert("<?php echo get_string('title:enter', 'local_board'); ?>");
            return false;
        }
        
        if(!isCourseChk){
            alert("<?php echo get_string('course:enter', 'local_board'); ?>");
            return false;
        }else{
            frm.submit();
            return true;
        }
                
    }    
</script>







