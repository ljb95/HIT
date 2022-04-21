<?php

require_once dirname(dirname(dirname (__FILE__))).'/config.php';
require_once $CFG->dirroot.'/siteadmin/lib.php';

// Check for valid admin user - no guest autologin

require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/local/courselist/course_manage.php');
    redirect(get_login_url());
}

$course_list       = optional_param_array('course', array(), PARAM_INT);

$context = context_system::instance();
require_capability('moodle/site:config', $context);

// 현재 년도, 학기
$year = get_config('moodle', 'haxa_year');
$term = get_config('moodle', 'haxa_term');

//관리자 course 설정 옵션
$course_option = get_config('moodle', 'siteadmin_course_option_set');
$course_option = unserialize($course_option);

$js = array(
    $CFG->wwwroot.'/siteadmin/manage/course_list.js'
);

$PAGE->set_context($context);

$PAGE->set_url('/local/courselist/course_list_merge_form.php');
$PAGE->set_pagelayout('standard');
$strplural = get_string('merge:sel', 'local_courselist');
$PAGE->navbar->add(get_string("course:list", "local_courselist"), new moodle_url($CFG->wwwroot.'/local/courselist/course_manage.php'));
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading(get_string("merge:sel", "local_courselist"));
$PAGE->requires->css('/local/courselist/style.css');
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->jquery_plugin('migrate');
$PAGE->requires->js('/siteadmin/manage/course_list.js');
$PAGE->requires->js('/siteadmin/js/lib/jquery.ui.datepicker-ko.js');

echo $OUTPUT->header();

//tab
$tabmenu =  trim(basename($_SERVER['PHP_SELF']), '.php');
if ($tabmenu === 'course_list_merge_form') {
    $currenttab = 'manage';
} else if($tabmenu === 'complete_show'){
    $currenttab = 'completion';
} else if($tabmenu === 'course_list_drive'){
    $currenttab = 'list_drive';
} else if($tabmenu === 'course_list_restore'){
    $currenttab = 'list_restore';
}

$rows = array (
    new tabobject('manage', "$CFG->wwwroot/local/courselist/course_manage.php", get_string('course:list', 'local_courselist')),
    new tabobject('completion', "$CFG->wwwroot/local/courselist/complete_show.php", get_string('course:completion', 'local_courselist')),
    new tabobject('list_drive', "$CFG->wwwroot/local/courselist/course_list_drive.php", get_string('course:classes_drive_log', 'local_courselist')),
    new tabobject('list_restore', "$CFG->wwwroot/local/courselist/course_list_restore.php", get_string('course:classes_restore_log', 'local_courselist'))
    );
print_tabs(array($rows), $currenttab);
?>

<h3 class="page_title"><?php echo get_string('course:create_class', 'local_courselist');?></h3>
<p class="page_sub_title"><?php echo get_string('course:apply_drive', 'local_courselist');?></p>

<form name="" id="course_search" class="search_area" action="course_list_merge.execute.php" method="post" enctype="multipart/form-data">
    <table cellpadding="0" cellspacing="0" class="detail">
        <table cellpadding="0" cellspacing="0" class="detail">
        <tbody>
            <tr>
                <td class="field_title"><?php print_string('year:sel', 'local_courselist');?></td>
                <td class="field_value">
                    <select name="year" class="w_160">
                    <?php 
                        $years = lmsdata_get_years();
                        if(!empty($course->year)) {
                            $year = $course->year;
                        }
                        if (empty($year))$year = date('Y');
                        foreach($years as $v=>$y) {
                            $selected = '';
                            if($v == $year) {
                                $selected = ' selected';
                            }
                            echo '<option value="'.$v.'"'.$selected.'> '.$y.'</option>';
                        }
                    ?>      
                    </select>
                    <select name="term" class="w_160">
                    <?php 
                        $terms = lmsdata_get_terms();
                        if(!empty($course->year)) {
                            $term = $course->term;
                        }
                        foreach($terms as $v=>$t) {
                            $selected = '';
                            if($v == $term) {
                                $selected = ' selected';
                            }
                            echo '<option value="'.$v.'"'.$selected.'> '.$t.'</option>';
                        }
                    ?>     
                    </select>
                </td>
            </tr>
            <tr>
                <td class="field_title"><?php print_string('course:category', 'local_courselist');?></td>
                <td class="field_value">
                    <select name="cata1" id="course_search_cata1" onchange="cata1_changed(this);"  class="w_160" style="margin:5px 20px 5px 0;">
                        <option value="0"> - <?php print_string('category:all', 'local_courselist');?> -</option>
                        <?php
                        $catagories = $DB->get_records('course_categories', array('visible'=>1, 'parent'=>0), 'sortorder', 'id, idnumber, name');
                        if(!empty($path_arr[1])) {
                            $cata1 = $path_arr[1];
                        }
                        foreach($catagories as $catagory) {
                            $selected = '';
                            if($catagory->id == $cata1) {
                                $selected = ' selected';
                            }
                            echo '<option value="'.$catagory->id.'"'.$selected.'> '.$catagory->name.'</option>';
                        }
                        ?>
                    </select>
                    <select name="cata2" id="course_search_cata2" onchange="cata2_changed(this)" class="w_160" style="margin: 5px 20px 5px 0;">
                        <option value="0"> - <?php print_string('category:all', 'local_courselist');?> -</option>
                        <?php
                        if($cata1) {
                            $catagories = $DB->get_records('course_categories', array('visible'=>1, 'parent'=>$cata1), 'sortorder', 'id, idnumber, name');
                            if(!empty($path_arr[2])) {
                                $cata2 = $path_arr[2];
                            }
                            foreach($catagories as $catagory) {
                                $selected = '';
                                if($catagory->id == $cata2) {
                                    $selected = ' selected';
                                }
                                echo '<option value="'.$catagory->id.'"'.$selected.'> '.$catagory->name.'</option>';
                            }
                        }
                        ?>
                    </select>
                    <select name="cata3" id="course_search_cata3" class="w_160" style="margin:5px 20px 5px 0;">
                        <option value="0"> - <?php print_string('category:all', 'local_courselist');?> -</option>
                        <?php
                        if($cata1 && $cata2) {
                            $catagories = $DB->get_records('course_categories', array('visible'=>1, 'parent'=>$cata2), 'sortorder', 'id, idnumber, name');
                            if(!empty($path_arr[3])) {
                                $cata3 = $path_arr[3];
                            }
                            foreach($catagories as $catagory) {
                                $selected = '';
                                if($catagory->id == $cata3) {
                                    $selected = ' selected';
                                }
                                echo '<option value="'.$catagory->id.'"'.$selected.'> '.$catagory->name.'</option>';
                            }
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <?php 
                if($course_option->irregular){ 
            ?>
            <tr> 
                <td class="field_title"><?php echo get_string('regular:short', 'local_courselist').'/'.get_string('irregular:short', 'local_courselist');?></td>
                <td class="field_value">
                    <p>
                        <input type="radio" name="isnonformal" value="0" /> <?php print_string('regular:short', 'local_courselist') ?>
                        <input type="radio" name="isnonformal" value="1" checked/> <?php print_string('irregular:short', 'local_courselist') ?>
                    </p>
                </td>
            </tr>
            <?php 
                } else { 
                    echo '<input type="hidden" name="isnonformal" value="'.$course->isnonformal.'"/>';
                } 
            ?>
             <tr> 
                <td class="field_title"><?php print_string('course:name', 'local_courselist');?></td>
                <td class="field_value">
                    <p>
                    <input type="text" name="kor_lec_name" placeholder="<?php print_string('subject:ko_input', 'local_courselist');?>" size="60" value="<?php echo !empty($course->kor_lec_name) ? $course->kor_lec_name : ''; ?>"/>
                    <input type="text" name="eng_lec_name" placeholder="<?php print_string('subject:en_input', 'local_courselist');?>" size="60" value="<?php echo !empty($course->eng_lec_name) ? $course->eng_lec_name : ''; ?>"/>
                    </p>
                </td>
            </tr>
            <tr> 
                <td class="field_title"><?php print_string('course:section', 'local_courselist');?></td>
                <td class="field_value">
                   <select name="section" class="w_160" style="margin:5px 20px 5px 0;">
                        <?php
                        for($i=1 ; $i<20 ; $i++ ) {
                            $selected = '';
                            if((empty($course->numsections) && $i == 15) || ($course->numsections== $i)) {
                                $selected = ' selected';
                            } 
                            echo '<option value="'.$i.'"'.$selected.'> '.$i.'</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="field_title"><?php print_string('course:professor', 'local_courselist');?></td>
                <td class="field_value"> 
                    <input type="text" name="prof_name" placeholder="<?php echo get_string('professorsearch','local_courselist');?>" size="30" disabled  value="<?php echo !empty($course->prof_name) ? $course->prof_name : ''; ?>"/>
                    <input type="hidden" name="prof_userid" value="<?php echo !empty($course->prof_userid) ? $course->prof_userid : 0; ?>"/>
                    <input type="button" value="<?php echo get_string('search', 'local_courselist');?>" class="gray_btn" onclick="search_prof_popup()"/>
                </td>
            </tr>
            <?php 
                if($course_option->isreged){ 
            ?>
            <tr>
                <td class="field_title"><?php echo get_string('enrolment', 'local_courselist');?></td>
                <td class="field_value"> 
                    <?php
                        $isreged = "checked";
                        $disable = "disabled";
                        if(isset($course->isreged) && $course->isreged == 0) {
                            $isreged = "";
                            $disable = "";
                        }
                    ?>
                    <input type="text" name="timeregstart" id="timeregstart" class="w_120" <?php echo $disable ;?> value="<?php echo empty($course->timeregstart) ?   date('Y-m-d', time()) : date('Y-m-d', $course->timeregstart); ?>" placeholder="yyyy-mm-dd"/> ~ 
                    <input type="text" name="timeregend" id="timeregend" class="w_120" <?php echo $disable ;?> value="<?php echo empty($course->timeregend) ?   date('Y-m-d', time()) : date('Y-m-d', $course->timeregend); ?>" placeholder="yyyy-mm-dd"/> 
                    <input type="checkbox" name="isreged" <?php echo $isreged;?> onclick="text_disable(this, 'timeregstart', false);text_disable(this, 'timeregend', false)"/> <?php echo get_string('notenrolment', 'local_courselist');?>
                </td>
            </tr>
            <?php 
                } else {
                    $timeregstart = empty($course->timeregstart) ?   date('Y-m-d', time()) : date('Y-m-d', $course->timeregstart);
                    $timeregend = empty($course->timeregend) ?   date('Y-m-d', time()) : date('Y-m-d', $course->timeregend);
                    echo '<input type="hidden" name="timeregstart" id="timeregstart" value="'.$timeregstart.'" />';
                    echo '<input type="hidden" name="timeregend" id="timeregend" value="'.$timeregend.'" />'; 
                    echo '<input type="hidden" name="isreged" value="1"/>';
                }
            ?>
            <tr>
                <td class="field_title"><?php echo get_string('course:open', 'local_courselist');?></td>
                <td class="field_value"> 
                    <input type="text" name="timestart" id="timestart" class="w_120" value="<?php echo empty($course->timestart) ?  date('Y-m-d', time()) : date('Y-m-d', $course->timestart); ?>" placeholder="yyyy-mm-dd"/> ~ 
                    <input type="text" name="timeend" id="timeend" class="w_120" value="<?php echo empty($course->timeend) ?  date('Y-m-d', time()) : date('Y-m-d', $course->timeend); ?>" placeholder="yyyy-mm-dd"/> 
                </td>
            </tr>
            <?php 
                if($course_option->certificate){ 
            ?>
            <tr>
                <td class="field_title"><?php echo get_string('certificate:sel', 'local_courselist');?></td>
                <td class="field_value">
                    <input type="radio" name="certificate"  value="1" /><?php echo get_string('yes', 'local_courselist');?>
                    <input type="radio" name="certificate"  value="0" checked /><?php echo get_string('nono', 'local_courselist');?>
                </td>
            </tr>
            <?php 
                } else{ 
                    echo '<input type="hidden" name="isnonformal" value="'.$course->isnonformal.'"/>';
                }
            ?>
            <tr>
                <td class="field_title"><?php echo get_string('course:thumnail', 'local_courselist');?></td>
                <td class="field_value"> 
                    <input type="file" name="overviewfiles" onchange="filename_del()" size="50"/>
                    <?php
                    if(!empty($course)) {
                        $courseimage = new course_in_list($course);
                        foreach ($courseimage->get_course_overviewfiles() as $file) {
                            $filename = $file->get_filename();
                        }
                        if(!empty($filename)){
                            echo ' <span name="filename">'.$filename.'</span>';
                        }
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td class="field_title"><?php echo get_string('course:goal', 'local_courselist');?></td>
                <td class="field_value"> 
                    <textarea name="objective" class="w_100" rows="5" ><?php echo !empty($course->summary) ? $course->summary : ''; ?></textarea>
                </td>
            </tr>
            <tr>
                <td class="field_title"><?php echo get_string('course:classeslist', 'local_courselist');?></td>
                <td class="field_value"> 
                <?php
                    list($sql_in, $params) = $DB->get_in_or_equal($course_list, SQL_PARAMS_NAMED, 'course');
                    $sql_where = " WHERE course ".$sql_in;
                    $sql_select = "SELECT id, course, kor_lec_name, eng_lec_name, subject_id from {lmsdata_class} ";

                    $courses = $DB->get_records_sql($sql_select.$sql_where, $params);
                    $count = 1;
                    foreach ($courses as $course) {
                        echo '<label class="w100">'.get_string('course:classes', 'local_courselist').$count.'</label>'.$course->kor_lec_name.'</br>';
                        echo '<input type="hidden" name="course[]" value="'.$course->course.'" />';
                        $count++;
                    }
                ?>
                </td>
            </tr>
        </tbody>
    </table>
</form><!--Search Area2 End-->

<div id="btn_area">
    <input type="submit" class="blue_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('course:create_class', 'local_courselist');?>" onclick="create_merge_course_submit()"/>
    <input type="submit" class="blue_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('cansel', 'local_courselist');?>" onclick="javascript:location.href='course_manage.php';"/> 
</div>
    
<script type="text/javascript">
    
    function filename_del(){
        $('span[name=filename]').text("");
    }
    
    function create_merge_course_submit() {
        if($("select[name=year]").val() == '0') {
            alert("<?php echo get_string('course:alert01', 'local_courselist');?>");
            return false;
        }
        if($("select[name=term]").val() == '0') {
            alert("<?php echo get_string('course:alert09', 'local_courselist');?>");
            return false;
        }
        if($("select[name=cata1]").val() == '0' && $("select[name=cata2]").val() == '0' && $("select[name=cata3]").val() == '0') {
            alert("<?php echo get_string('course:alert02', 'local_courselist');?>");
            return false;
        }
        if($("input:radio[name=isnonformal]:checked").val() == undefined) {
            alert("<?php echo get_string('course:alert10', 'local_courselist');?>");
            return false;
        }
        if( ($.trim($("input[name='timeregstart']").val()) == '') || ($.trim($("input[name='timeregend']").val()) == '')) {
            alert("<?php echo get_string('course:alert11', 'local_courselist');?>");
            return false;
        }
        if( ($.trim($("input[name='timestart']").val()) == '') || ($.trim($("input[name='timeend']").val()) == '')) {
            alert("<?php echo get_string('course:alert12', 'local_courselist');?>");
            return false;
        }
        if( $.trim($("input[name='kor_lec_name']").val()) == '' ) {
            alert("<?php echo get_string('course:alert03', 'local_courselist');?>");
            return false;
        }
        if( $.trim($("input[name='eng_lec_name']").val()) == '' ) {
            alert("<?php echo get_string('course:alert04', 'local_courselist');?>");
            return false;
        }
        if($("input[name=prof_userid]").val() == '0') {
            alert("<?php echo get_string('course:alert13', 'local_courselist');?>");
            return false;
        }
   
        if($.trim($("input[name='overviewfiles']").val()) != '') {
             var filename = $.trim($("input[name='overviewfiles']").val());
             var extension = filename.replace(/^.*\./, '');
             if(extension == filename) {
                 extension = "";
             } else {
                 extension = extension.toLowerCase();
             }
             if($.inArray( extension, [ "jpg", "png" ] ) == -1) {
                 alert("<?php echo get_string('course:alert08', 'local_courselist');?>");
                 return false;
             };
        }
        $('#course_search').submit();
    }
    
    function search_prof_popup(){
        var tag = $('<div id="course_prof_popup" style="z-index:10000"></div>');
        $.ajax({
          url: '<?php echo $CFG->wwwroot.'/siteadmin/manage/course_prof.php'; ?>',
          method: 'POST',
          success: function(data) {
            tag.html(data).dialog({
                title: '교수 검색',
                modal: true,
                width: 800,
                resizable: false,
                height: 400,
                buttons: [ {id:'close',
                            text:'취소',
                            disable: true,
                            click: function() {
                                $( this ).dialog( "close" );
                            }}],
                close: function () {
                    $('#course_prof_popup').remove();
                    $( this ).dialog('destroy').remove()
                }
            }).dialog('open');
          }
        });
    }
    
    $(document).ready(function() {
        $( "#timeregstart" ).datepicker({
            dateFormat: "yy-mm-dd",
            onClose: function( selectedDate ) {
                $( "#courseend" ).datepicker( "option", "minDate", selectedDate );
            }
        });
        $( "#timeregend" ).datepicker({
            dateFormat: "yy-mm-dd",
            onClose: function( selectedDate ) {
                $( "#coursestart" ).datepicker( "option", "maxDate", selectedDate );
            }
        });
        $( "#timestart" ).datepicker({
            dateFormat: "yy-mm-dd",
            onClose: function( selectedDate ) {
                $( "#applyend" ).datepicker( "option", "minDate", selectedDate );
            }
        });
        $( "#timeend" ).datepicker({
            dateFormat: "yy-mm-dd",
            onClose: function( selectedDate ) {
                $( "#applystart" ).datepicker( "option", "maxDate", selectedDate );
            }
        });
    });
</script>
 <?php 
   echo $OUTPUT->footer();