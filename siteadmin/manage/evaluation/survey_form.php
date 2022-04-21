<?php 
require_once dirname(dirname(dirname (dirname (__FILE__)))).'/config.php';
require_once dirname(dirname(dirname (__FILE__))).'/lib/paging.php';
require_once dirname(dirname (dirname (__FILE__))).'/lib.php';

// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/manage/evaluation/evaluation_form.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$currpage     = optional_param('page', 1, PARAM_INT);
$perpage      = optional_param('perpage', 20, PARAM_INT);
$search       = optional_param('search', 1, PARAM_INT);
$searchtext   = optional_param('searchtext', '', PARAM_TEXT);

$page_params = array();
$params = array(
    'contextlevel'=>CONTEXT_COURSE
);

include_once (dirname(dirname(dirname(__FILE__))).'/inc/header.php'); ?>
<div id="contents">
    <?php include_once  (dirname(dirname (dirname (__FILE__))).'/inc/sidebar_manage.php');?>
    <div id="content">
        <h3 class="page_title"><?php echo get_string('survey_form', 'local_lmsdata'); ?></h3>
        <div class="siteadmin_tabs">
            <a href="<?php echo $CFG->wwwroot; ?>/siteadmin/manage/evaluation/survey_form.php"><p class="black_btn black_btn_selected"><?php echo get_string('survey_form', 'local_lmsdata'); ?></p></a>
            <a href="<?php echo $CFG->wwwroot; ?>/siteadmin/manage/evaluation/evaluation_form.php"><p class="black_btn"><?php echo get_string('lectureevaluation_form', 'local_lmsdata'); ?></p></a>
        </div>
        <div class="page_navbar"><a href="./category_list.php"><?php echo get_string('course_management', 'local_lmsdata'); ?></a> > <a href="evaluation_form.php"><?php echo get_string('survey','local_lmsdata'); ?></a> > <strong><?php echo get_string('survey_form', 'local_lmsdata'); ?></strong></div>
        <table>
            <caption class="hidden-caption">설문조사 양식</caption>
            <col width="5%" />
            <col width="50%" />
            <col width="10%" />
            <col width="15%" />
            <col width="10%" />
            <thead>
                <tr align="center">
                    <th scope="row"><?php echo get_string('number', 'local_lmsdata'); ?></th>
                    <th scope="row"><?php echo get_string('title', 'local_lmsdata'); ?></th>
                    <th scope="row"><?php echo get_string('category_use', 'local_lmsdata'); ?></th>
                    <th scope="row"><?php echo get_string('datecreated', 'local_lmsdata'); ?></th>
                    <th scope="row"><?php echo get_string('update_del', 'local_lmsdata'); ?></th>
                </tr>
            </thead>
            <?php
                $count_forms = $DB->count_records('lmsdata_evaluation_forms',array('type'=>2));
                
                $offset = ($currpage -1) * $perpage;
                
                $startnum = $count_forms - $offset;
                $forms = $DB->get_records('lmsdata_evaluation_forms',array('type'=>2),'id desc');
                if($count_forms <= 0){
                    echo '<tr><td colspan="7">'. get_string('empty_sample','local_lmsdata').'</td></tr>';
                }
                foreach($forms as $form){
            ?>
            <tr>
                <td><?php echo $startnum--; ?></td>
                <td class="text-left"><a title="<?php echo $form->title; ?>" href="<?php echo './evaluation_categories.php?formid='.$form->id; ?>"><?php echo $form->title; ?></a></td>
                <td><?php echo ($form->allow_category == 1)?"Y":"N"; ?></td>
                <td><?php echo date('Y-m-d H:i:s',$form->timecreated) ?></td>
                <td>
                    <input type="button" class="" value="<?php echo get_string('edit','local_lmsdata'); ?>" onclick="location.href='evaluation_modify.php?formid=<?php echo $form->id; ?>' " />
                    <input type="button" class="" value="<?php echo get_string('delete', 'local_lmsdata'); ?>" onclick="if(confirm('<?php echo get_string('confirm2','local_lmsdata'); ?>')){ location.href='evaluation_deletes.php?target=form&instanceid=<?php echo $form->id; ?>' }" />
                </td>
            </tr>
                <?php } ?>
        </table><!--Table End-->
        
        <div id="btn_area">
            <div style="float:right;">
                <input type="button" class="blue_btn" style="margin-right: 10px;" value="<?php echo get_string('add_sample','local_lmsdata'); ?>" onclick="location.href='./evaluation_add.php?type=2'"/> 
                <input type="button" class="blue_btn" style="margin-right: 10px;" value="<?php echo get_string('add_sample_excell','local_lmsdata'); ?>" onclick="location.href='./evaluation_add_excell.php?type=2'"/> 
            </div>
        </div>
        <?php
            print_paging_navbar_script($count_forms, $currpage, $perpage, 'javascript:cata_page(:page);');       
        ?>
            
    </div><!--Content End-->
    
</div> <!--Contents End-->

 <?php include_once ('../../inc/footer.php');?>
