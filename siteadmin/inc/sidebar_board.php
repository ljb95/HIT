<?php
$submenu =  basename($_SERVER['PHP_SELF'], '.php');
?>
<div id="sidebar">
    <div class="menu_title"><h2><?php echo get_string('board_management', 'local_lmsdata'); ?></h2></div>
    <ul class="submenu">
        <li<?php echo  ' class="selected"'; ?>><a href="../board/list.php"><?php echo get_string('board_management', 'local_lmsdata'); ?></a></li>
    </ul>
    
    <?php include_once ('../inc/nav.php');?>

</div><!--Sidebar End-->
