<?php
$submenu =  trim(basename($_SERVER['PHP_SELF']), '.php');
?>
<div id="sidebar">
    <div class="menu_title"><h2><?php echo get_string('user_management', 'local_lmsdata'); ?></h2></div>
    <ul class="submenu">
        <li<?php echo (starts_with($submenu, 'info') || starts_with($submenu, 'student_detail')) ? ' class="selected"' : ''; ?>><a href="<?php echo $CFG->wwwroot; ?>/siteadmin/users/info.php"><?php echo get_string('stu_management', 'local_lmsdata'); ?></a></li>
        <li<?php echo (starts_with($submenu, 'infpro') || starts_with($submenu, 'rofessor_detail'))? ' class="selected"' : ''; ?>><a href="<?php echo $CFG->wwwroot; ?>/siteadmin/users/infpro.php"><?php echo get_string('prof_management', 'local_lmsdata'); ?></a></li>
        <li<?php echo starts_with($submenu, 'infadmin') ? ' class="selected"' : ''; ?>><a href="<?php echo $CFG->wwwroot; ?>/siteadmin/users/infadmin.php"><?php echo get_string('admin_management', 'local_lmsdata'); ?></a></li>
        <li<?php echo starts_with($submenu, 'inftem') ? ' class="selected"' : ''; ?>><a href="<?php echo $CFG->wwwroot; ?>/siteadmin/users/inftemp.php"><?php echo get_string('user_manageaccounts', 'local_lmsdata'); ?></a></li>
        <li<?php echo starts_with($submenu, 'ipblock') ? ' class="selected"' : ''; ?>><a href="<?php echo $CFG->wwwroot; ?>/siteadmin/users/ipblock.php">IP 제한</a></li>
        <li<?php echo starts_with($submenu, 'sync') ? ' class="selected"' : ''; ?>><a href="<?php echo $CFG->wwwroot; ?>/siteadmin/users/sync.php">엑셀 사용자 등록
            </a></li>
    </ul>
    
    <?php include_once ('../inc/nav.php');?>
</div><!--Side Bar End-->