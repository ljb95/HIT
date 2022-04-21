<?php
$submenu =  basename($_SERVER['PHP_SELF'], '.php');
?>
<div id="sidebar">
    <div class="menu_title"><h2><?php echo get_string('lcms_management', 'local_lmsdata'); ?></h2></div>
    <ul class="submenu">
        <li<?php echo starts_with($submenu, 'index') ? ' class="selected"' : ''; ?>><a href="../contents/index.php"><?php echo get_string('contents_contentslist', 'local_lmsdata'); ?></a></li>
        <li<?php echo starts_with($submenu, 'add') ? ' class="selected"' : ''; ?>><a href="../contents/add.php"><?php echo get_string('contents_registration', 'local_lmsdata'); ?></a></li>
        <li<?php echo starts_with($submenu, 'excell') ? ' class="selected"' : ''; ?>><a href="../contents/excell_add.php"><?php echo get_string('contents_packageregistration', 'local_lmsdata'); ?></a></li>
        <li<?php echo starts_with($submenu, 'history') ? ' class="selected"' : ''; ?>><a href="../contents/history.php"><?php echo get_string('contents_contenthistory', 'local_lmsdata'); ?></a></li>
    </ul>
    
    <?php include_once ('../inc/nav.php');?>

</div><!--Sidebar End-->
