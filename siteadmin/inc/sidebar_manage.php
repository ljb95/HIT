<?php
/**
 * Sidebar for siteadmin
 *
 * @package newoklass
 * @copyright Copyright (c) 2016 (oklass)
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
$submenu = trim(basename($_SERVER['PHP_SELF']), '.php');
$eval_on = false;

if (($LMSUSER->usergroup != 'de' && $LMSUSER->usergroup != 'pr') || $LMSUSER->menu_auth == 9) {
    $roleadmin = true;
} else {
    $roleadmin = false;
}
?>
<div id="sidebar">
    <div class="menu_title"><h2><?php echo get_string('course_management', 'local_lmsdata'); ?></h2></div>
    <ul class="submenu">
        <li<?php echo starts_with($submenu, 'course_list') ? ' class="selected"' : ''; ?>><a href="<?php echo $roleadmin ? $CFG->wwwroot . '/siteadmin/manage/course_list.php' : $CFG->wwwroot . '/siteadmin/manage/course_list.php?coursetype=1'; ?>"><?php echo get_string('opencourse', 'local_lmsdata'); ?></a>
            <?php
            if (starts_with($submenu, 'course_list')) {
                $coursetype = optional_param('coursetype', 0, PARAM_INT); //0:교과, 1:비교과, 2:이러닝  
                ?>
                <ul>
                    <li<?php if ($roleadmin) {
                echo (starts_with($submenu, 'course_list') && $coursetype == 0 ) ? ' class="selected"' : ''; ?>><a href="<?php echo $CFG->wwwroot; ?>/siteadmin/manage/course_list.php"><?php echo get_string('regular_course', 'local_lmsdata');
            } ?></a></li> 
                    <li<?php echo (starts_with($submenu, 'course_list') && $coursetype == 1) ? ' class="selected"' : ''; ?>>
                        <a href="<?php echo $CFG->wwwroot; ?>/siteadmin/manage/course_list.php?coursetype=1">
                            <?php
                            if ($roleadmin) {
                                echo get_string('irregular_course', 'local_lmsdata');
                            } else {
                                echo $LMSUSER->domain . ' 관리';
                            }
                            ?>
                        </a>
                    </li>
                    <li<?php if ($roleadmin) {
                                echo (starts_with($submenu, 'course_list') && $coursetype == 2) ? ' class="selected"' : ''; ?>><a href="<?php echo $CFG->wwwroot; ?>/siteadmin/manage/course_list.php?coursetype=2"><?php echo get_string('elearning_course', 'local_lmsdata');
        } ?></a></li>
                </ul>
<?php } ?>
        </li>
        <?php if ($roleadmin) { // de => 부서관리자 ?>

            <li<?php echo starts_with($submenu, 'sync') ? ' class="selected"' : ''; ?>><a href="<?php echo $CFG->wwwroot; ?>/siteadmin/manage/sync.php"><?php echo get_string('synchronization', 'local_lmsdata'); ?></a></li>
    <?php } ?>
    </ul>

<?php include_once dirname(dirname(__FILE__)) . '/inc/nav.php'; ?>

</div><!--Sidebar Manage End-->
