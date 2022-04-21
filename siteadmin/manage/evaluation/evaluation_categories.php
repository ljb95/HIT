<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php';
require_once dirname(dirname(dirname(__FILE__))) . '/lib/paging.php';
require_once dirname(dirname(dirname(__FILE__))) . '/lib.php';
require_once './lib.php';

// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string) new moodle_url('/siteadmin/manage/evaluation/evaluation_form.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$formid = required_param('formid', PARAM_INT);
$currpage = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 20, PARAM_INT);
$search = optional_param('search', 1, PARAM_INT);
$searchtext = optional_param('searchtext', '', PARAM_TEXT);


$page_params = array();
$params = array(
    'contextlevel' => CONTEXT_COURSE
);
$form = $DB->get_record('lmsdata_evaluation_forms', array('id' => $formid));
?>

<?php include_once (dirname(dirname(dirname(__FILE__))) . '/inc/header.php'); ?>
<link rel="stylesheet" type="text/css" href="styles.css" >
<div id="contents">
    <?php include_once (dirname(dirname(dirname(__FILE__))) . '/inc/sidebar_manage.php'); ?>

    <div id="content">
        <h3 class="page_title"><?php echo ($form->type == 1) ? "<?php echo get_string('evaluation','local_lmsdata'); ?>" : get_string('survey', 'local_lmsdata'); ?> <?php echo get_string('prev_sample','local_lmsdata'); ?></h3>
        <div class="page_navbar"><a href="./category_list.php"><?php echo get_string('course_management', 'local_lmsdata'); ?></a> > <a href="evaluation_form.php"><?php echo get_string('evalandsur','local_lmsdata'); ?></a> > <a href="evaluation_form.php"><?php echo get_string('eval_form','local_lmsdata'); ?></a> > <strong><?php echo get_string('prev_sample','local_lmsdata'); ?></strong></div>
        <div style="clear: both;">
            <fieldset style="border:2px solid #eee;">
                <legend><h2><?php echo $form->title; ?></h2></legend>
                <div id="evaluation_header"><?php echo nl2br($form->contents); ?></div>
                <?php
                        $qustions_cnt = $DB->count_records('lmsdata_evaluation_questions', array('formid' => $formid, 'category' => 0));
                        $questions = $DB->get_records('lmsdata_evaluation_questions', array('formid' => $formid, 'category' => 0), 'sortorder asc', '*');
                        foreach ($questions as $question) {
                        ?>
                        <div class="questions">
                        <div class="question_header">
                            <h3><?php echo $question->title; if($question->required == 1)echo '(<span class="red">*</span>)'; ?></h3>
                            <div style="clear: both; float:right;">
                                <input type="button" class="gray_btn_small" style="margin-right: 10px;" value="<?php echo get_string('edit','local_lmsdata'); ?>" onclick="location.href = './evaluation_question_modify.php?formid=<?php echo $formid; ?>&questionid=<?php echo $question->id; ?>'"/>
                                <input type="button" class="gray_btn_small" style="margin-right: 10px;" value="<?php echo get_string('delete', 'local_lmsdata'); ?>" onclick="location.href = './evaluation_deletes.php?target=question&instanceid=<?php echo $question->id; ?>'"/></div>
                            <div class="question_header_text"><h4><?php echo nl2br($question->contents); ?></h4></div>
                        </div>
                            <?php
                            switch ($question->qtype) {
                                case '1':
                                    $answers = preg_split('/\n|\r\n?/', trim($question->answers));
                                    print_checkbox_form($answers,$question);
                                    break;
                                case '2': 
                                    $answers = preg_split('/\n|\r\n?/', trim($question->answers));
                                    print_radio_form($answers,$question);  
                                    break;
                                case '3':
                                    print_text_form($question);  
                                    break;
                                case '4':
                                    print_textarea_form($question);  
                                    break;
                                case '5':
                                    $answers = preg_split('/\n|\r\n?/', trim($question->answers));
                                    print_fiveselect_form($answers,$question);  
                                    break;
                                case '6':
                                    print_textint_form($question);  
                                    break;
                            }
                        ?>
                        </div> <!-- question end -->
                            <?php
                        }
                        ?>
                <?php
                $categories_cnt = $DB->count_records('lmsdata_evaluation_category', array('formid' => $formid));
                $categories = $DB->get_records('lmsdata_evaluation_category', array('formid' => $formid), 'sortorder asc', '*');
                foreach ($categories as $category) {
                    ?>
                <fieldset class="categories">
                        <legend><h3><?php echo $category->name; ?></h3></legend>
                        <div style="clear: both; float:right;">
                            <input type="button" class="blue_btn" style="margin-right: 10px;" value="<?php echo get_string('add_question','local_lmsdata'); ?>" onclick="location.href = './evaluation_question_add.php?formid=<?php echo $formid; ?>&categoryid=<?php echo $category->id; ?>'"/>
                            <input type="button" class="blue_btn" style="margin-right: 10px;" value="<?php echo get_string('edit_category','local_lmsdata'); ?>" onclick="location.href = './evaluation_category_modify.php?formid=<?php echo $formid; ?>&categoryid=<?php echo $category->id; ?>'"/>
                            <input type="button" class="blue_btn" style="margin-right: 10px;" value="<?php echo get_string('delete_category','local_lmsdata'); ?>" onclick="if(confirm('<?php echo get_string('confirm1','local_lmsdata'); ?>')){ location.href = './evaluation_deletes.php?target=category&instanceid=<?php echo $category->id; ?>' }"/>
                        </div>
                        <?php
                        $questions = $DB->get_records('lmsdata_evaluation_questions', array('formid' => $formid, 'category' => $category->id), 'sortorder asc', '*');
                        foreach ($questions as $question) {
                        ?>
                        <div class="questions">
                        <div class="question_header">
                            <h3><?php echo $question->title; if($question->required == 1)echo '(<span class="red">*</span>)'; ?></h3>
                            <div style="clear: both; float:right;">
                                <input type="button" class="gray_btn_small" style="margin-right: 10px;" value="<?php echo get_string('edit','local_lmsdata'); ?>" onclick="location.href = './evaluation_question_modify.php?formid=<?php echo $formid; ?>&categoryid=<?php echo $category->id; ?>&questionid=<?php echo $question->id; ?>'"/>
                                <input type="button" class="gray_btn_small" style="margin-right: 10px;" value="<?php echo get_string('delete', 'local_lmsdata'); ?>" onclick="location.href = './evaluation_deletes.php?target=question&instanceid=<?php echo $question->id; ?>'"/></div>
                            <div class="question_header_text"><h4><?php echo nl2br($question->contents); ?></h4></div>
                        </div>
                            <?php
                            switch ($question->qtype) {
                                case '1':
                                    $answers = preg_split('/\n|\r\n?/', trim($question->answers));
                                    print_radio_form($answers,$question);     
                                    break;
                                case '2':
                                    $answers = preg_split('/\n|\r\n?/', trim($question->answers));
                                    print_checkbox_form($answers,$question);
                                    break;
                                case '3':
                                    print_text_form($question);  
                                    break;
                                case '4':
                                    print_textarea_form($question);  
                                    break;
                                case '5':
                                    $answers = preg_split('/\n|\r\n?/', trim($question->answers));
                                    print_fiveselect_form($answers,$question);  
                                    break;
                                case '6':
                                    print_textint_form($question);  
                                    break;
                            }
                        ?>
                        </div> <!-- question end -->
                            <?php
                        }
                        ?>
                    </fieldset>
                    <?php
                }
                if ($categories_cnt <= 0 && $form->allow_category == 1) {
                    echo '<h4 style="color:red;">'.get_string('empty_category','local_lmsdata').'</h4>';
                } else if ($qustions_cnt <= 0 && $form->allow_category == 2){
                    echo '<h4 style="color:red;">'.get_string('empty_question','local_lmsdata').'</h4>';
                }
                ?>
            </fieldset>

        </div>
        <div id="btn_area">

            <div style="float:right;">
                <?php if ($form->allow_category == 1) { ?>
                    <input type="button" class="blue_btn" style="margin-right: 10px;" value="<?php echo get_string('add_category','local_lmsdata'); ?>" onclick="location.href = './evaluation_category_add.php?formid=<?php echo $form->id; ?>'"/> 
                <?php } else { ?>
                    <input type="button" class="blue_btn" style="margin-right: 10px;" value="<?php echo get_string('add_question','local_lmsdata'); ?>" onclick="location.href = './evaluation_question_add.php?formid=<?php echo $form->id; ?>'"/> 
                <?php } ?>
                    <input type="button" class="normal_btn" style="margin-right: 10px;" value="<?php echo get_string('list2','local_lmsdata'); ?>" onclick="location.href = './<?php echo ($form->type == 1)?'evaluation':'survey'; ?>_form.php'"/> 
            </div>

        </div>
        <?php
        //  print_paging_navbar_script($count_forms, $currpage, $perpage, 'javascript:cata_page(:page);');       
        ?>

    </div><!--Content End-->

</div> <!--Contents End-->

<?php include_once ('../../inc/footer.php'); ?>
