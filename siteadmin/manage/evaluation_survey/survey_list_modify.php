<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php';
require_once dirname(dirname(dirname(__FILE__))) . '/lib/paging.php';
require_once dirname(dirname(dirname(__FILE__))) . '/lib.php';

// Check for valid admin user - no guest autologin

require_login(0, false);

$id = required_param('id', PARAM_INT);

$evaluation = $DB->get_record('lmsdata_evaluation',array('id'=>$id));

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
        <h3 class="page_title"><?php echo get_string('add_survey','local_lmsdata'); ?></h3>
        <div class="page_navbar"><a href="../category_list.php"><?php echo get_string('course_management', 'local_lmsdata'); ?></a> > <a href="../evaluation/evaluation_form.php"><?php echo get_string('evalandsur','local_lmsdata'); ?></a> > <a href="survey_list.php"><?php echo get_string('survey', 'local_lmsdata'); ?></a> > <strong><?php echo get_string('add_survey','local_lmsdata'); ?></strong></div>

        <form id="evaluation_add_form" action="submit.php" method="post" enctype="multipart/form-data"> 
            <div>
                <table cellpadding="0" cellspacing="0" class="detail">
                    <input type="hidden" name="userid" value="<?php echo $USER->id; ?>" />
                    <input type="hidden" name="mode" value="modify" />
                    <input type="hidden" name="id" value="<?php echo $id; ?>" />
                    <tbody>
                        <tr>
                            <td class="field_title"><label for="form_form"><?php echo get_string('force','local_evaluation'); ?></label></td>
                            <td class="field_value">
                                <input type="checkbox" value="1" name="compulsion" <?php if($evaluation->compulsion == 1) echo 'checked';?> />
                            </td>
                        </tr>
                        <tr>
                            <td class="field_title"><label for="form_content"><?php echo get_string('eval_form','local_lmsdata'); ?></label></td>
                            <td class="field_value">
                            <?php
                                    if(!empty($evaluation->formid)){
                                        $formname = $DB->get_field('lmsdata_evaluation_forms','title',array('id'=>$evaluation->formid));
                                    }
                                    echo $formname;
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_title"><label for="form_content"><?php echo get_string('eval_period','local_lmsdata'); ?></label></td>
                            <td class="field_value">
                                <input type="text" title="평가기간" name="starttime" id="timestart" class="w_120" value="<?php echo empty($evaluation->timestart) ?  date('Y-m-d', time()) : date('Y-m-d', $evaluation->timestart); ?>" placeholder="yyyy-mm-dd"/>
                                ~ 
                                <input type="text" title="평가기간" name="endtime" id="timeend" class="w_120" value="<?php echo empty($evaluation->timeend) ?  date('Y-m-d', time()) : date('Y-m-d', $evaluation->timeend); ?>" placeholder="yyyy-mm-dd"/>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div id="btn_area">
                <input type="submit" class="blue_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('save','local_lmsdata'); ?>" />
                <?php if(empty($submits)){ ?>
                <input type="button" class="red_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('delete', 'local_lmsdata'); ?>" onclick="location.href = 'survey_delete.php?id=<?php echo $id; ?>';"/>
                <?php } ?>
                <input type="button" class="normal_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('list2','local_lmsdata'); ?>" onclick="location.href = 'survey_list.php';"/>
            </div>
        </form><!--Search Area2 End-->
    </div><!--Content End-->

</div> <!--Contents End--> 

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
</script>
<?php
include_once ('../../inc/footer.php');
