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

$context = context_system::instance();
require_capability('moodle/site:config', $context);

include_once (dirname(dirname(dirname(__FILE__))) . '/inc/header.php');
?>

<div id="contents">
    <?php include_once (dirname(dirname(dirname(__FILE__))) . '/inc/sidebar_manage.php'); ?>

    <div id="content">
        <h3 class="page_title"><?php echo get_string('add_courseeval','local_lmsdata'); ?></h3>
        <div class="page_navbar"><a href="../category_list.php"><?php echo get_string('course_management', 'local_lmsdata'); ?></a> > <a href="../evaluation/evaluation_form.php"><?php echo get_string('evaluation_questionnaire', 'local_lmsdata'); ?></a> > <a href="evaluation_list.php"><?php echo get_string('lectureevaluation', 'local_lmsdata'); ?></a> > <strong><?php echo get_string('add_evaluation','local_lmsdata'); ?></strong></div>

        <form id="evaluation_add_form" action="submit.php" method="post" enctype="multipart/form-data"> 
            <div>
                <table cellpadding="0" cellspacing="0" class="detail">
                    <input type="hidden" name="userid" value="<?php echo $USER->id; ?>" />
                    <input type="hidden" name="mode" value="add" />
                    <tbody>
                        <tr> 
                            <td class="field_title"><label for="form_course"><?php echo get_string('course_select','local_lmsdata'); ?></label></td>
                            <td class="field_value">
                                <input name="courseid" type="hidden" message="<?php echo get_string('course_select_message','local_lmsdata'); ?>" required>
                                <input id="form_course" name="coursename" message="<?php echo get_string('course_select_message','local_lmsdata'); ?>" type="text" placeholder="<?php echo get_string('search_placeholder','local_lmsdata'); ?>" disabled>
                                <input type="button" class="gray_btn" id="opener_course" value="<?php echo get_string('search','local_lmsdata'); ?>">
                            </td>
                        </tr>
                        <tr>
                            <td class="field_title"><label for="form_form"><?php echo get_string('eval_form','local_lmsdata'); ?></label></td>
                            <td class="field_value">
                                <input name="formid" type="hidden" message="<?php echo get_string('sample_select_message','local_lmsdata'); ?>" required>
                                <input name="formname" id="form_form" type="text" message="<?php echo get_string('sample_select_message','local_lmsdata'); ?>" placeholder="<?php echo get_string('search_placeholder','local_lmsdata'); ?>" disabled>
                                <input type="button" value="검색" id="opener_form" class="gray_btn">
                            </td>
                        </tr>
                        <tr>
                            <td class="field_title"><label for="form_content"><?php echo get_string('eval_period','local_lmsdata'); ?></label></td>
                            <td class="field_value">
                                <input type="text" title="평가기간" name="starttime" id="timestart" class="w_120" value="<?php echo date("Y-m-d", time()); ?>" placeholder="yyyy-mm-dd"/>
                                ~ 
                                <input type="text" title="평가기간" name="endtime" id="timeend" class="w_120" value="<?php echo date("Y-m-d", time() + 86400 * 7); ?>" placeholder="yyyy-mm-dd"/>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="btn_area">
                <input type="submit" class="blue_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('save','local_lmsdata'); ?>" />
                <input type="button" class="normal_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('list2','local_lmsdata'); ?>" onclick="location.href = 'evaluation_list.php';"/>
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
        // 다이얼로그 <?php echo get_string('search','local_lmsdata'); ?>버튼 클릭
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
