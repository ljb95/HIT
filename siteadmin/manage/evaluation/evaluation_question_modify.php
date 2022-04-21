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

$questionid = required_param('questionid', PARAM_INT);
$formid = required_param('formid', PARAM_INT);
$categoryid = optional_param('categoryid', 0, PARAM_INT);
?>
<link rel="stylesheet" type="text/css" href="styles.css" >
<?php include_once (dirname(dirname(dirname(__FILE__))) . '/inc/header.php'); ?>
<div id="contents">
    <?php
    include_once (dirname(dirname(dirname(__FILE__))) . '/inc/sidebar_manage.php');
    $question = $DB->get_record('lmsdata_evaluation_questions', array('id' => $questionid));

    $orders = $DB->get_records('lmsdata_evaluation_questions', array('formid' => $formid, 'category' => $categoryid), 'sortorder asc', 'sortorder');
    $used = "&nbsp;".get_string('used_order','local_lmsdata')." : ";
    foreach ($orders as $order) {
        $used .= $order->sortorder . ",";
    }
    ?>
    <div id="content">
        <h3 class="page_title"><?php echo get_string('question_edit','local_lmsdata'); ?></h3>
        <div class="page_navbar"><a href="./category_list.php"><?php echo get_string('course_management', 'local_lmsdata'); ?></a> > <a href="evaluation_form.php"><?php echo get_string('evalandsur','local_lmsdata'); ?></a> > <a href="evaluation_form.php"><?php echo get_string('eval_form','local_lmsdata'); ?></a> > <a href="evaluation_categories.php?formid=<?php echo $formid; ?>"><?php echo get_string('prev_sample','local_lmsdata'); ?></a> > <strong><?php echo get_string('question_edit','local_lmsdata'); ?></strong></div>
        <form id="question_add_form" onsubmit="return validateRequiredFields();" action="evaluation_question_submit.php" method="post" enctype="multipart/form-data"> 
            <input type="hidden" name="formid" value="<?php echo $formid; ?>">
            <input type="hidden" name="categoryid" value="<?php echo $categoryid; ?>">
            <input type="hidden" name="questionid" value="<?php echo $question->id; ?>">
            <input type="hidden" name="mode" value="modify">
            <table cellpadding="0" cellspacing="0" class="detail">
                <tbody>
                    <tr>
                        <td class="field_title"><?php echo get_string('question_name','local_lmsdata'); ?></td>
                        <td class="field_value">
                            <input type="text" name="title" value="<?php echo $question->title; ?>" size="80" class="form_text" placeholder="<?php echo get_string('question_name','local_lmsdata'); ?>" required>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_title"><?php echo get_string('header','local_lmsdata'); ?></td>
                        <td class="field_value">
                            <textarea name="contents" class="form_textarea" placeholder="<?php echo get_string('header','local_lmsdata'); ?>"><?php echo $question->contents; ?></textarea>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_title" id="qtype" rowspan="2"><?php echo get_string('qtype','local_lmsdata'); ?></td>
                        <td class="field_value">
                            <input name="qtype" type="radio" value="1" <?php if ($question->qtype == 1) echo "checked"; ?>> <?php echo get_string('qtype1','local_lmsdata'); ?> 
                            <input name="qtype" type="radio" value="2" <?php if ($question->qtype == 2) echo "checked"; ?>> <?php echo get_string('qtype2','local_lmsdata'); ?> 
                            <input name="qtype" type="radio" value="3" <?php if ($question->qtype == 3) echo "checked"; ?>> <?php echo get_string('qtype3','local_lmsdata'); ?> 
                            <input name="qtype" type="radio" value="4" <?php if ($question->qtype == 4) echo "checked"; ?>> <?php echo get_string('qtype4','local_lmsdata'); ?> 
                            <input name="qtype" type="radio" value="5" <?php if ($question->qtype == 5) echo "checked"; ?>> <?php echo get_string('qtype5','local_lmsdata'); ?> 
                            <input name="qtype" type="radio" value="6" <?php if ($question->qtype == 6) echo "checked"; ?>> <?php echo get_string('qtype6','local_lmsdata'); ?> 
                        </td>
                    </tr>
                    <tr id="answers">
                        <td class="text-left" style="padding:0 0 15px 15px;">
                            &nbsp;
                            <div id="answer_texts">
                                <?php 
                                
                                    $answers = preg_split('/\n|\r\n?/',trim($question->answers));
                                    $cnt = 1;
                                    foreach($answers as $answer => $val){
                                ?>
                                <input type="text" id="answer<?php echo $cnt; ?>" value="<?php echo $val; ?>"  size="50" maxlength="120" name="answers[]" class="answer_text form_text" placeholder="<?php echo get_string('qstring','local_lmsdata',$cnt++); ?>">
                                    <?php } ?>
                            </div>
                            <div id="answer_texts_etc">
                                <input type="text" id="answeretc" class="form_text" size="50" maxlength="120"  value="<?php echo $question->etcname; ?>"  name="answers_etc" placeholder="<?php echo get_string('etc_string','local_lmsdata'); ?>">
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
                            <div id="answer_texts">
                                <?php
                                    $answers = preg_split('/\n|\r\n?/',trim($question->answers));
                                    $cnt = 1;
                                    $point = 5;
                                    foreach($answers as $answer => $val){
                                ?>
                                <div><?php echo get_string('point','local_lmsdata',$point--); ?><input type="text" id="answer<?php echo $cnt++; ?>" value="<?php echo $val; ?>"  size="50" maxlength="120" name="answers[]" class="answer_text_5 form_text"></div>
                                    <?php } ?>
                            </div>
                        </td>
                    </tr>
                    <tr id="expression">
                        <td class="field_title"><?php echo get_string('selectxy','local_lmsdata'); ?></td>
                        <td class="field_value">
                            <?php echo get_string('x','local_lmsdata'); ?> <input name="expression" type="radio" value="1" <?php if ($question->expression == 1) echo "checked"; ?>>
                            <?php echo get_string('y','local_lmsdata'); ?> <input name="expression" type="radio" value="2" <?php if ($question->expression == 2) echo "checked"; ?>>
                        </td>
                    </tr> 
                    <tr>
                        <td class="field_title"><?php echo get_string('selectrequire','local_lmsdata'); ?></td>
                        <td class="field_value">
                            <?php echo get_string('required','local_lmsdata'); ?> <input name="required" type="radio" value="1" <?php if ($question->required == 1) echo "checked"; ?>>
                            <?php echo get_string('select','local_lmsdata'); ?> <input name="required" type="radio" value="2" <?php if ($question->required == 2) echo "checked"; ?>>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_title"><label for="form_sortorder"><?php echo get_string('order','local_lmsdata'); ?></label></td>
                        <td class="field_value">
                            <input type="text" name="sortorder" id="form_sortorder" value="<?php echo $question->sortorder; ?>" placeholder="ex) 1" size="2" required />
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
                $('input[id=answer' + number + ']').remove();
            }
            function add_select() {
                var number = $('.answer_text').length;
                number++;
                $('#answer_texts').append('<input type="text" id="answer' + number + '" name="answers[]" size="50" maxlength="120" class="answer_text form_text" placeholder="<?php echo get_string('qtype2','local_lmsdata'); ?> ' + number + '<?php echo get_string('qstring2','local_lmsdata'); ?>">');
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
            $(document).ready(function () {
                var value = $('input[name=qtype]:checked').val();

                switch (value) {
                    case '1':
                    case '2':
                        $('#expression').show();
                        $('#answers').show();
                        $('#qtype').prop('rowspan', '2');
                        $('input[name=expression]').prop('disabled', false);
                         $('.answer_text_5').prop('disabled', true);
                        $('.answer_text').prop('disabled', false);
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
                        $('input[name=expression]').prop('disabled', true);
                         $('.answer_text_5').prop('disabled', true);
                        $('.answer_text').prop('disabled', true);
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
