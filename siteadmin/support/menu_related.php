<?php 
require_once dirname(dirname (dirname (__FILE__))).'/config.php';
require_once $CFG->dirroot.'/siteadmin/lib/paging.php';
require_once $CFG->dirroot.'/siteadmin/lib.php';
// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/support/menu_main.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

include_once  ($CFG->dirroot.'/siteadmin/inc/header.php'); ?>
<div id="contents">
    <?php include_once  ($CFG->dirroot.'/siteadmin/inc/sidebar_support.php');?>
    <div id="content">
        <h3 class="page_title"><?php echo get_string('menu_manage','local_lmsdata'); ?></h3>
        <div class="siteadmin_tabs">
            <a href="<?php echo $CFG->wwwroot; ?>/siteadmin/support/menu_main.php"><p class="black_btn black_btn_selected"><?php echo get_string('main_menu','local_lmsdata'); ?></p></a>
            <a href="<?php echo $CFG->wwwroot; ?>/siteadmin/support/menu_footer.php"><p class="black_btn"><?php echo get_string('menu_footer','local_lmsdata'); ?></p></a>
            <a href="<?php echo $CFG->wwwroot; ?>/siteadmin/support/menu_related.php"><p class="black_btn"><?php echo get_string('famliy','local_lmsdata'); ?></p></a>
        </div>
        <div class="page_navbar"><a href="<?php echo $CFG->dirroot.'/support/notices.php' ;?>" ><?php echo get_string('site_management', 'local_lmsdata'); ?></a> > <strong><?php echo get_string('menu_manage','local_lmsdata'); ?></strong></div>
    </div><!--Content End-->
    
</div> <!--Contents End-->

 <?php include_once  ($CFG->dirroot.'/siteadmin/inc/footer.php');?>
