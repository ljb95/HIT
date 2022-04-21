<?php
$submenu =  trim(basename($_SERVER['PHP_SELF']), '.php');
?>
<div id="sidebar">
    <div class="menu_title"><h2>마이페이지</h2></div>
    <ul class="submenu">
        <li<?php echo starts_with($submenu, 'info_personal') ? ' class="selected"' : ''; ?>><a href="../mypage/info_personal.php">개인정보관리</a></li>
        <li<?php echo starts_with($submenu, 'push') ? ' class="selected"' : ''; ?>><a href="../mypage/push.php">알림설정</a></li>
    </ul>
    
    <?php include_once ('../inc/nav.php');?>
</div><!--Side Bar_mypg End-->
