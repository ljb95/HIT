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
        <h3 class="page_title"><?php echo get_string('manage_certi','local_lmsdata'); ?></h3>
        <div class="siteadmin_tabs">
            <a href="<?php echo $CFG->wwwroot; ?>/siteadmin/manage/certi.php"><p class="black_btn black_btn_selected"><?php echo get_string('sample_manage','local_lmsdata'); ?></p></a>
            <a href="<?php echo $CFG->wwwroot; ?>/siteadmin/manage/certi_history.php"><p class="black_btn"><?php echo get_string('certi_history_view','local_lmsdata'); ?></p></a>
        </div>
        <div class="page_navbar"><a href="./category_list.php"><?php echo get_string('course_management', 'local_lmsdata'); ?></a> > <a href="certi.php"><?php echo get_string('diplopia_management', 'local_lmsdata'); ?></a> > <strong><?php echo get_string('sample_manage','local_lmsdata'); ?></strong></div>
        <table>
            <caption class="hidden-caption">이수증관리</caption>
            <col width="5%" />
            <col width="15%" />
            <col width="10%" />
            <col />
            <col width="10%" />
            <col width="25%" />
            <col width="10%" />
            <tr>
                <th scope="row"><?php echo get_string('number', 'local_lmsdata'); ?></th>
                <th scope="row"><?php echo get_string('gubun','local_lmsdata'); ?></th>
                <th scope="row"><?php echo get_string('lang','local_lmsdata'); ?></th>
                <th scope="row"><?php echo get_string('certi_name','local_lmsdata'); ?></th>
                <th scope="row"><?php echo get_string('certi_author','local_lmsdata'); ?></th>
                <th scope="row"><?php echo get_string('certi_period','local_lmsdata'); ?></th>
                <th scope="row"><?php echo get_string('certi_preview','local_lmsdata'); ?></th>
            </tr>
            <?php
                $count_forms = $DB->count_records('lmsdata_certificate');
                
                $offset = ($currpage -1) * $perpage;
                
                $startnum = $count_forms - $offset;
                $forms = $DB->get_records('lmsdata_certificate');
                if($count_forms <= 0){
                    echo '<tr><td colspan="8">'. get_string('empty_sample','local_lmsdata').'</td></tr>';
                }
                foreach($forms as $form){
                    $codename = $DB->get_field('lmsdata_certificate_code','name',array('id'=>$form->codeid));
            ?>
            <tr>
                <td><?php echo $startnum--; ?></td>
                <td><?php echo $codename;?></td>
                <td><?php echo $form->lang =='ko' ? get_string('ko','local_lmsdata'):get_string('en','local_lmsdata');?></td>
                <td class="text-left"><a title="<?php echo $form->name; ?>" href="<?php echo './certi_edit.php?id='.$form->id; ?>"><strong><?php echo $form->name; ?></strong></a></td>
                <td><?php echo $form->author;?></td>
                <td><?php echo date('Y-m-d',$form->periodstart).' ~ '. date('Y-m-d',$form->periodend);?></td>
                <td>
                    <a href="<?php echo $CFG->wwwroot; ?>/local/certi/certi_preview.php?mod=adminpreview&id=<?php echo $form->id; ?>" target="_blank"><input type="button" class="" value="<?php echo get_string('certi_preview','local_lmsdata'); ?>" /> </a>
                </td>
            </tr>
                <?php } ?>
        </table><!--Table End-->
        
        <div id="btn_area">
            <div style="float:right;">
                <input type="button" class="blue_btn" style="margin-right: 10px;" value="<?php echo get_string('add_sample','local_lmsdata'); ?>" onclick="location.href='./certi_add.php'"/> 
            </div>
        </div>
        <?php
            print_paging_navbar_script($count_forms, $currpage, $perpage, 'javascript:cata_page(:page);');       
        ?>
            
    </div><!--Content End-->
    
</div> <!--Contents End-->

 <?php include_once ('../inc/footer.php');?>
