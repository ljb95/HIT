<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php';
require_once dirname(dirname(dirname(__FILE__))) . '/lib/paging.php';
require_once dirname(dirname(dirname(__FILE__))) . '/lib.php';

// Check for valid admin user - no guest autologin

require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string) new moodle_url('/siteadmin/manage/evaluation/evaluation_add.php');
    redirect(get_login_url());
}

$id = required_param('id', PARAM_INT);
$profid = optional_param('profid',0, PARAM_INT);

$context = context_system::instance();
require_capability('moodle/site:config', $context);

include_once (dirname(dirname(dirname(__FILE__))) . '/inc/header.php');

$evaluation = $DB->get_record('lmsdata_evaluation',array('id'=>$id));
?>
<style>
    .ui-button {
        background-color:red !important;
    }
    .ui-dialog-titlebar {
        float: right;
        border: 0;
        display: none;
        padding: 0;
    }
    .ui-dialog-titlebar-close {
        top: 0;
        right: 0;
        margin: 0;
        display: none;
        z-index: 999;
    }
</style>
<div id="contents">
    <?php include_once (dirname(dirname(dirname(__FILE__))) . '/inc/sidebar_manage.php'); ?>

    <div id="content">
        <h3 class="page_title"><?php echo get_string('add_evaluation','local_lmsdata'); ?></h3>
        <div class="page_navbar"><a href="../category_list.php"><?php echo get_string('course_management', 'local_lmsdata'); ?></a> > <a href="../evaluation/evaluation_form.php"><?php echo get_string('evalandsur','local_lmsdata'); ?></a> > <a href="evaluation_list.php"><?php echo get_string('lectureevaluation', 'local_lmsdata'); ?></a> > <strong><?php echo get_string('add_evaluation','local_lmsdata'); ?></strong></div>

        <form id="evaluation_add_form" action="submit.php" method="post" enctype="multipart/form-data"> 
            <div>
                <table cellpadding="0" cellspacing="0" class="detail">
                    <input type="hidden" name="userid" value="<?php echo $USER->id; ?>" />
                    <input type="hidden" name="mode" value="modify" />
                    <input type="hidden" name="id" value="<?php echo $id; ?>" />
                    <tbody>
                        <?php
                        $submits = $DB->get_records('lmsdata_evaluation_submits',array('evaluation'=>$id));
                        if(empty($submits)){ 
                        ?>
                        <tr>
                            <td class="field_title"><label for="form_target"><?php echo get_string('eval_target','local_lmsdata'); ?></label></td>
                            <td class="field_value">
                                <select id="form_target" name="targets" class="w_90">
                                    <option <?php if($evaluation->targets == 1)echo 'selected'; ?> value="1"><?php echo get_string('course','local_lmsdata'); ?></option>
                                    <option <?php if($evaluation->targets == 2)echo 'selected'; ?> value="2"><?php echo get_string('teacher', 'local_lmsdata'); ?></option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_title"><label for="form_course"><?php echo get_string('course_select','local_lmsdata'); ?></label></td>
                            <td class="field_value">
                                <?php
                                    if(!empty($evaluation->course)){
                                        $coursename = $DB->get_field('course','fullname',array('id'=>$evaluation->course));
                                    }
                                ?>
                                <input name="courseid" value="<?php echo $evaluation->course ?>" type="hidden" message="<?php echo get_string('course_select_message','local_lmsdata'); ?>" required>
                                <input id="form_course" name="coursename" value="<?php echo $coursename; ?>" message="<?php echo get_string('course_select_message','local_lmsdata'); ?>" type="text" placeholder="<?php echo get_string('search_placeholder','local_lmsdata'); ?>" disabled>
                                <input type="button" id="opener_course" value="<?php echo get_string('search','local_lmsdata'); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td class="field_title"><label for="form_form"><?php echo get_string('eval_form','local_lmsdata'); ?></label></td>
                            <td class="field_value">
                               <?php
                                    if(!empty($evaluation->formid)){
                                        $formname = $DB->get_field('lmsdata_evaluation_forms','title',array('id'=>$evaluation->formid));
                                    }
                                ?>
                                <input name="formid" type="hidden" value="<?php echo $evaluation->formid; ?>" message="<?php echo get_string('sample_select_message','local_lmsdata'); ?>" required>
                                <input name="formname" id="form_form" type="text" value="<?php echo $formname; ?>" message="<?php echo get_string('sample_select_message','local_lmsdata'); ?>" placeholder="<?php echo get_string('search_placeholder','local_lmsdata'); ?>" disabled>
                                <input type="button" value="<?php echo get_string('search','local_lmsdata'); ?>" id="opener_form">
                            </td>
                        </tr>
                        <?php } else { ?>
                        <tr>
                            <td class="field_title"><label for="form_target"><?php echo get_string('eval_target','local_lmsdata'); ?></label></td>
                            <td class="field_value">
                                <?php 
                                    if($evaluation->targets == 1)echo get_string('course','local_lmsdata'); 
                                    if($evaluation->targets == 2)echo get_string('teacher', 'local_lmsdata');
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_title"><label for="form_course"><?php echo get_string('course_select','local_lmsdata'); ?></label></td>
                            <td class="field_value">
                                <?php
                                    if(!empty($evaluation->course)){
                                        $coursename = $DB->get_field('course','fullname',array('id'=>$evaluation->course));
                                    }
                                    echo $coursename; 
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_title"><label for="form_form"><?php echo get_string('eval_form','local_lmsdata'); ?></label></td>
                            <td class="field_value">
                                <?php 
                                if(!empty($evaluation->formid)){
                                        $formname = $DB->get_field('lmsdata_evaluation_forms','title',array('id'=>$evaluation->formid));
                                    }
                                 echo $formname; 
                                ?>
                            </td>
                        </tr>
                        <?php } ?>
                        <tr>
                            <td class="field_title"><label for="form_content"><?php echo get_string('eval_period','local_lmsdata'); ?></label></td>
                            <td class="field_value">
                                <input type="text" name="starttime" id="timestart" class="w_120" value="<?php echo empty($evaluation->timestart) ?  date('Y-m-d', time()) : date('Y-m-d', $evaluation->timestart); ?>" placeholder="yyyy-mm-dd"/>
                                ~ 
                                <input type="text" name="endtime" id="timeend" class="w_120" value="<?php echo empty($evaluation->timeend) ?  date('Y-m-d', time()) : date('Y-m-d', $evaluation->timeend); ?>" placeholder="yyyy-mm-dd"/>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="btn_area">
                <input type="submit" class="red_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('save','local_lmsdata'); ?>" />
                <?php if(empty($submits)){ ?>
                <input type="button" class="blue_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('delete', 'local_lmsdata'); ?>" onclick="location.href = 'evaluation_delete.php?id=<?php echo $id; ?>';"/>
                <?php } ?>
                <input type="button" class="blue_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('list2','local_lmsdata'); ?>" onclick="location.href = 'evaluation_list.php';"/>
            </div>
        </form><!--Search Area2 End-->
    </div><!--Content End-->

</div> <!--Contents End--> 


<!-- 강의선택 다이얼로그 시작 -->
<div id="course_search_dialog">
</div>
<!-- 강의선택 다이얼로그 끝 -->

<!-- 양식선택 다이얼로그 시작 -->
<div id="form_search_dialog">
</div>
<!-- 양식선택 다이얼로그 끝 -->

<script>
    $(document).ready(function() {
        $( "#timestart" ).datepicker({
            dateFormat: "yy-mm-dd",
            onClose: function( selectedDate ) {
                $( "#timeend" ).datepicker( "option", "minDate", selectedDate );
            }
        });
        $( "#timeend" ).datepicker({
            dateFormat: "yy-mm-dd",
            onClose: function( selectedDate ) {
                $( "#timestart" ).datepicker( "option", "maxDate", selectedDate );
            }
        });
    });
    function course_selete(id, name) {
        $('#course_search_dialog').dialog('close');
        $('input[name=courseid]').val(id);
        $('input[name=coursename]').val(name);
    }
    function form_selete(id, name) {
        $('#form_search_dialog').dialog('close');
        $('input[name=formid]').val(id);
        $('input[name=formname]').val(name);
    }
    $('#evaluation_add_form').submit(function () {
        var no_val;
        $('input[required]').each(function (i, requiredField) {
            if ($(requiredField).val() == '') {
                alert($(requiredField).attr('message'));
                no_val = true;
            }
        });
        if(no_val == true)return false;
    });
    $(function () {
        $("#course_search_dialog").css('zIndex', 100);
        $("#course_search_dialog").dialog({
            maxWidth: 800,
            maxHeight: 500,
            width: 800,
            height: 500,
            autoOpen: false,
            hide: {
                effect: "explode",
                duration: 1000,
            },
            buttons: {
                "<?php echo get_string('close','local_lmsdata'); ?>": function () {
                    $(this).dialog("close");
                }
            }
        });

        // 다이얼로그 오픈
        $("#opener_course").click(function () {
            $("#course_search_dialog").dialog("open");
            $.ajax({url: "get_course.ajax.php",
                success: function (result) {
                    $("#course_search_dialog").html(result);
                }
            });
        });
        // 다이얼로그 검색버튼 클릭
        $("#searchbtn_course").click(function () {

        });
    });
    $(function () {
        $("#form_search_dialog").css('zIndex', 100);
        $("#form_search_dialog").dialog({
            maxWidth: 800,
            maxHeight: 500,
            width: 800,
            height: 500,
            autoOpen: false,
            hide: {
                effect: "explode",
                duration: 1000,
            },
            buttons: {
                "<?php echo get_string('close','local_lmsdata'); ?>": function () {
                    $(this).dialog("close");
                }
            }
        });

        // 다이얼로그 오픈
        $("#opener_form").click(function () {
            $("#form_search_dialog").dialog("open");
            $.ajax({url: "get_form.ajax.php",
                success: function (result) {
                    $("#form_search_dialog").html(result);
                }
            });
        });
        // 다이얼로그 검색버튼 클릭
        $("#searchbtn_form").click(function () {

        });
    });
    function mutiselecte_change(leave, arrive) {
        var arrive_node = $('#'+arrive+' optgroup');
        $('#'+leave+' option:selected').each(function(i, selected){
            arrive_node.append(selected);
        });
    }
</script>
<?php
include_once ('../../inc/footer.php');
