<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php';
require_once dirname(dirname(dirname(__FILE__))) . '/lib/paging.php';
require_once dirname(dirname(dirname(__FILE__))) . '/lib.php';

// Check for valid admin user - no guest autologin

require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string) new moodle_url('/siteadmin/manage/course_list_add.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$formid = required_param('formid', PARAM_INT);
$categoryid = optional_param('categoryid', 0, PARAM_INT);
?>
<link rel="stylesheet" type="text/css" href="styles.css" >
<?php include_once (dirname(dirname(dirname(__FILE__))) . '/inc/header.php'); ?>
<div id="contents">
    <?php include_once (dirname(dirname(dirname(__FILE__))) . '/inc/sidebar_manage.php'); 
    $orders = $DB->get_records('lmsdata_evaluation_questions', array('formid' => $formid, 'category' => $categoryid), 'sortorder asc', 'sortorder');
    $used = "&nbsp;".get_string('used_order','local_lmsdata')." : ";
    $max = 0 ;
    foreach ($orders as $order) {
        if ($max < $order->sortorder) {
            $max = $order->sortorder;
        }
        $used .= $order->sortorder . ",";
    }
    $max++;
    ?>
    <div id="content">
        <h3 class="page_title"><?php echo get_string('add_question','local_lmsdata'); ?></h3>
        <div class="page_navbar"><a href="./category_list.php"><?php echo get_string('course_management', 'local_lmsdata'); ?></a> > <a href="evaluation_form.php"><?php echo get_string('evalandsur','local_lmsdata'); ?></a> > <a href="evaluation_form.php"><?php echo get_string('eval_form','local_lmsdata'); ?></a> > <a href="evaluation_categories.php?formid=<?php echo $formid; ?>"><?php echo get_string('prev_sample','local_lmsdata'); ?></a> > <strong><?php echo get_string('add_question','local_lmsdata'); ?></strong></div>
        <form id="question_add_form" onsubmit="return validateRequiredFields();" action="evaluation_question_submit.php" method="post" enctype="multipart/form-data"> 
            <input type="hidden" name="formid" value="<?php echo $formid; ?>">
            <input type="hidden" name="categoryid" value="<?php echo $categoryid; ?>">
            <input type="hidden" name="mode" value="add">
            <table cellpadding="0" cellspacing="0" class="detail">
                <tbody>
                    <tr>
                        <td class="field_title"><?php echo get_string('question_name','local_lmsdata'); ?></td>
                        <td class="field_value">
                            <input type="text" name="title" value="<?php echo $max; ?>. " size="80" class="form_text" placeholder="<?php echo get_string('question_name','local_lmsdata'); ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_title"><?php echo get_string('header','local_lmsdata'); ?></td>
                        <td class="field_value">
                            <textarea name="contents" class="form_textarea" placeholder="<?php echo get_string('header','local_lmsdata'); ?>"></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_title" id="qtype" rowspan="2"><?php echo get_string('qtype','local_lmsdata'); ?></td>
                        <td class="field_value">
                            <input name="qtype" type="radio" value="1" checked=""> <?php echo get_string('qtype2','local_lmsdata'); ?> 
                            <input name="qtype" type="radio" value="2"> <?php echo get_string('qtype1','local_lmsdata'); ?>
                            <input name="qtype" type="radio" value="3"> <?php echo get_string('qtype3','local_lmsdata'); ?> 
                            <input name="qtype" type="radio" value="4"> <?php echo get_string('qtype4','local_lmsdata'); ?> 
                            <input name="qtype" type="radio" value="5"> <?php echo get_string('qtype5','local_lmsdata'); ?> 
                            <input name="qtype" type="radio" value="6"> <?php echo get_string('qtype6','local_lmsdata'); ?> 
                        </td>
                    </tr>
                    <tr id="answers">
                        <td class="text-left" style="padding:0 0 15px 15px;">
                            &nbsp;
                            <div id="answer_texts">
                                <input type="text" id="answer1"  size="50" maxlength="120"  name="answers[]" class="answer_text form_text" placeholder="<?php echo get_string('qnumber','local_lmsdata',1); ?>">
                                <input type="text" id="answer2"  size="50" maxlength="120"  name="answers[]" class="answer_text form_text" placeholder="<?php echo get_string('qnumber','local_lmsdata',2); ?>">
                                <input type="text" id="answer3"  size="50" maxlength="120"  name="answers[]" class="answer_text form_text" placeholder="<?php echo get_string('qnumber','local_lmsdata',3); ?>">
                                <input type="text" id="answer4"  size="50" maxlength="120"  name="answers[]" class="answer_text form_text" placeholder="<?php echo get_string('qnumber','local_lmsdata',4); ?>">
                                <input type="text" id="answer5"  size="50" maxlength="120"  name="answers[]" class="answer_text form_text" placeholder="<?php echo get_string('qnumber','local_lmsdata',5); ?>">
                            </div>
                            <div id="answer_texts_etc">
                                <input type="text" id="answeretc" class="form_text" size="50" maxlength="120"  name="answers_etc" placeholder="<?php echo get_string('etc_string','local_lmsdata'); ?>">
                            </div>
                            <div class="btns">
                                <input type="button" class="gray_btn_small" style="float:left; margin-right: 10px;" value="<?php echo get_string('delete', 'local_lmsdata'); ?>" onclick="delete_select()"/>
                                <input type="button" class="gray_btn_small" style="float:left; margin-right: 10px;" value="<?php echo get_string('etc_string','local_lmsdata'); ?>" onclick="add_select()"/>
                            </div>
                        </td>
                    </tr>
                    <tr id="answers_5">
                        <td class="text-left" style="padding:0 0 15px 15px;">
                            &nbsp;
                            <div id="answer5_text">
                                <input type="text" id="answer5_1"  size="50" maxlength="120" required name="answers[]" class="answer_text_5 form_text" value="<?php echo get_string('answer1','local_lmsdata'); ?>" disabled>
                                <input type="text" id="answer5_2"  size="50" maxlength="120" required name="answers[]" class="answer_text_5 form_text" value="<?php echo get_string('answer2','local_lmsdata'); ?>" disabled>
                                <input type="text" id="answer5_3"  size="50" maxlength="120" required name="answers[]" class="answer_text_5 form_text" value="<?php echo get_string('answer3','local_lmsdata'); ?>" disabled>
                                <input type="text" id="answer5_4"  size="50" maxlength="120" required name="answers[]" class="answer_text_5 form_text" value="<?php echo get_string('answer4','local_lmsdata'); ?>" disabled>
                                <input type="text" id="answer5_5"  size="50" maxlength="120" required name="answers[]" class="answer_text_5 form_text" value="<?php echo get_string('answer5','local_lmsdata'); ?>" disabled>
                            </div>
                        </td>
                    </tr>
                    <tr id="expression">
                        <td class="field_title"><?php echo get_string('selectxy','local_lmsdata'); ?></td>
                        <td class="field_value">
                            <?php echo get_string('x','local_lmsdata'); ?> <input name="expression" type="radio" value="1" checked="">
                            <?php echo get_string('y','local_lmsdata'); ?> <input name="expression" type="radio" value="2">
                        </td>
                    </tr> 
                    <tr>
                        <td class="field_title"><?php echo get_string('selectrequire','local_lmsdata'); ?></td>
                        <td class="field_value">
                            <?php echo get_string('required','local_lmsdata'); ?> <input name="required" type="radio" value="1" checked="">
                            <?php echo get_string('select','local_lmsdata'); ?> <input name="required" type="radio" value="2" >
                        </td>
                    </tr>
                    <tr>
                        <td class="field_title"><label for="form_sortorder"><?php echo get_string('order','local_lmsdata'); ?></label></td>
                        <td class="field_value">
                            <input type="text" name="sortorder" id="form_sortorder" value="<?php echo $max; ?>" placeholder="ex) 1" size="2" required />
                            <?php
                            echo rtrim($used, ',');
                            ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            <div id="btn_area">
                <input type="submit" class="red_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('save','local_lmsdata'); ?>" />
                <input type="button" class="blue_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('list2','local_lmsdata'); ?>" onclick="location.href = 'evaluation_categories.php?formid=<?php echo $formid; ?>';"/>
            </div>
        </form>
        <script>
            function delete_select() {
                var number = $('.answer_text').length;
                $('#answers input[id=answer' + number + ']').remove(); //수정
            }
            function add_select() {
                var number = $('.answer_text').length;
                number++;
                $('#answer_texts').append('<input type="text" id="answer' + number + '" name="answer[]" size="50" required maxlength="120" class="answer_text form_text" placeholder="<?php echo get_string('qtype2','local_lmsdata'); ?> ' + number + '번 문구">');
            }
            $('input[name=qtype]').click(function () {
                var value = $('input[name=qtype]:checked').val();

                switch (value) {
                    case '1':
                    case '2':
                        $('#expression').show();
                        $('#answers').show();
                        $('#answers_5').hide();
                        $('#qtype').prop('rowspan', '2');
                        $('input[name=expression]').prop('disabled', false);
                        $('.answer_text').prop('disabled', false);
                        $('.answer_text_5').prop('disabled', true);
                        break;
                    case '5':
                        $('#expression').show();
                        $('#answers_5').show();
                        $('#answers').hide();
                        $('#qtype').prop('rowspan', '2');
                        $('input[name=expression]').prop('disabled', false);
                        $('.answer_text_5').prop('disabled', false);
                        $('.answer_text').prop('disabled', true);
                    break;
                    default :
                        $('#expression').hide();
                        $('#answers').hide();
                        $('#answers_5').hide();
                        $('input[name=expression]').prop('disabled', true);
                        $('.answer_text').prop('disabled', true);
                        $('.answer_text_5').prop('disabled', true);
                        $('#qtype').prop('rowspan', '1');
                        break;
                }
            });
               function validateRequiredFields() {
            $('input,textarea,select').attr('required', true).filter(':visible:first').each(function (i, requiredField) {
                if ($(requiredField).val() == '') {
                    alert($(requiredField).attr('name'));
                    return false;
                }
            });
        }
        </script>
    </div><!--Content End-->
</div>
    <?php include_once ('../../inc/footer.php'); ?>
