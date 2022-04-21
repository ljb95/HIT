<?php 
require_once dirname(dirname(dirname (__FILE__))).'/config.php';
require_once dirname(dirname (__FILE__)).'/lib/paging.php';
require_once dirname(dirname (__FILE__)).'/lib.php';

// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/manage/course_list.php');
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

include_once (dirname(dirname(__FILE__)).'/inc/header.php'); ?>
<div id="contents">
    <?php include_once  (dirname(dirname ( __FILE__)).'/inc/sidebar_manage.php');?>
    <div id="content">
        <h3 class="page_title"><?php echo get_string('certi_history_view','local_lmsdata'); ?></h3>
        <div class="siteadmin_tabs">
            <a href="<?php echo $CFG->wwwroot; ?>/siteadmin/manage/certi.php"><p class="black_btn"><?php echo get_string('sample_manage','local_lmsdata'); ?></p></a>
            <a href="<?php echo $CFG->wwwroot; ?>/siteadmin/manage/certi_history.php"><p class="black_btn black_btn_selected"><?php echo get_string('certi_history_view','local_lmsdata'); ?></p></a>
        </div>
        <div class="page_navbar"><a href="./category_list.php"><?php echo get_string('course_management', 'local_lmsdata'); ?></a> > <a href="certi.php"><?php echo get_string('diplopia_management', 'local_lmsdata'); ?></a> > <strong><?php echo get_string('certi_history_view','local_lmsdata'); ?></strong></div>
        <table>
            <caption class="hidden-caption">이수내역 조회</caption>
            <col width="5%" />
            <col width="50%" />
            <col width="10%" />
            <col width="15%" />
            <col width="10%" />
            <thead>
            <tr>
                <th scope="row"><?php echo get_string('number', 'local_lmsdata'); ?></th>
                <th scope="row"><?php echo get_string('certi_name','local_lmsdata'); ?></th>
                <th scope="row">이수자</th>
                <th scope="row"><?php echo get_string('datecreated', 'local_lmsdata'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
                $count_forms = $DB->count_records('lmsdata_certificate_history');
                
                $offset = ($currpage -1) * $perpage;
                
                $startnum = $count_forms - $offset;
                $forms = $DB->get_records('lmsdata_certificate_history',array(),'id desc','*',$offset,$perpage);
                if($count_forms <= 0){
                    echo '<tr><td colspan="7">'.get_string('empty_certi','local_lmsdata').'</td></tr>';
                }
                foreach($forms as $form){
            ?>
            <tr>
                <td><?php echo $startnum--; ?></td>
                <td class="text-left"><a title="<?php echo $form->title; ?>"><strong><?php echo (!empty($form->title))?$form->title:'삭제된 이수증입니다.'; ?></strong></a></td>
                <td><?php echo ($form->allow_category == 1)?"Y":"N"; ?></td>
                <td><?php echo date('Y-m-d H:i:s',$form->timecreated) ?></td>
            </tr> 
                <?php 
                } 
                ?>
            </tbody>
        </table><!--Table End-->
        
        <?php
            print_paging_navbar_script($count_forms, $currpage, $perpage, $CFG->wwwroot.'/siteadmin/manage/certi_history.php?page=:page');       
        ?>
            
    </div><!--Content End-->
    
</div> <!--Contents End-->

 <?php include_once ('../inc/footer.php');?>
