<?php
$submenu =  basename($_SERVER['PHP_SELF'], '.php');
?>
<div id="sidebar">
    <div class="menu_title"><h2><?php echo get_string('site_management', 'local_lmsdata'); ?></h2></div>
    <ul class="submenu">
        <li<?php echo starts_with($submenu, 'popup') ? ' class="selected"' : ''; ?>><a href="../support/popup.php"><?php echo get_string('popup_manage','local_lmsdata'); ?></a></li>
        <li<?php echo starts_with($submenu, 'main_menu') ? ' class="selected"' : ''; ?>><a href="../support/main_menu.php">메뉴관리</a></li>
        <!--li<?php echo starts_with($submenu, 'admin_menu') ? ' class="selected"' : ''; ?>><a href="../support/admin_menu.php">관리자 메뉴</a></li-->
        <li<?php echo starts_with($submenu, 'sms') ? ' class="selected"' : ''; ?>><a href="../support/sms.php">문자발송</a></li>
        <li<?php echo starts_with($submenu, 'exams') ? ' class="selected"' : ''; ?>><a href="../support/exams.php">시험일정 조회</a></li>
        <li<?php echo starts_with($submenu, 'quiz_upload') ? ' class="selected"' : ''; ?>><a href="../support/quiz_upload.php">퀴즈 엑셀 업로드</a></li>
        <li<?php echo starts_with($submenu, 'excell') ? ' class="selected"' : ''; ?>><a href="../support/excell_add.php"><?php echo get_string('contents_packageregistration', 'local_lmsdata'); ?></a></li>
    </ul>
    <?php include_once ('../inc/nav.php');?>
</div><!--Sidebar End-->