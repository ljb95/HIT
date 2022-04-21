<?php 
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once dirname (__FILE__).'/synclib.php';

// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/manage/course_list.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);


$tabs = siteadmin_get_sync_tabs();

$tab = optional_param('tab', 0, PARAM_INT);

$year = get_config('moodle', 'haxa_year');
$term = get_config('moodle', 'haxa_term');

$js = array( $CFG->wwwroot.'/siteadmin/manage/sync.js');

include_once ('../inc/header.php');
?>

<div id="contents">
    <?php include_once ('../inc/sidebar_manage.php');?>

    <div id="content">
        <h3 class="page_title"><?php echo get_string('synchronization', 'local_lmsdata'); ?></h3>
        <div class="page_navbar"><a href="<?php echo $CFG->wwwroot . '/siteadmin/manage/course_list.php'; ?>"><?php echo get_string('course_management', 'local_lmsdata'); ?></a> > <?php echo get_string('synchronization', 'local_lmsdata'); ?></div>
        <p class="page_sub_title"> <?php echo get_string('msg4', 'local_lmsdata'); ?></p>
        
        <div class="content_navigation">
            <?php 
            foreach($tabs AS $i=>$t) {
                $css_class = $t['class'];
                if($tab == $i) {
                    $css_class .= ' '.$css_class.'_selected';
                }
                echo '<a href="sync.php?tab='.$i.'"><p class="'.$css_class.'">'.$t['text'].'</p></a>';
            }
            ?>
        </div><!--Content Navigation End-->
        
        <?php
        if((empty($year) || empty($term)) && $tabs[$tab]['page'] != 'config') { 
            end($tabs);         // move the internal pointer to the end of the array
            $key = key($tabs);
            ?>
        <div class="extra_information"><?php echo get_string('msg5','local_lmsdata'); ?></div>
        <div id="btn_area">
            <input type="submit" class="blue_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('okay','local_lmsdata'); ?>" onclick="sync_goto_config('<?php echo $key; ?>')"/> 
        </div>
        <?php } else {
            include_once('sync.'.$tabs[$tab]['page'].'.php');
        } ?>
    </div><!--Content End-->
    
</div> <!--Contents End-->

 <?php include_once ('../inc/footer.php');?>