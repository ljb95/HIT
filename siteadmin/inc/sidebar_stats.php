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
?>
<div id="sidebar">
    <div class="menu_title"><h2><?php echo get_string('stats_management', 'local_lmsdata'); ?></h2></div>
    <ul class="submenu">
        <li<?php echo starts_with($submenu, 'contact_stats') ? ' class="selected"' : ''; ?>><a href="<?php echo $CFG->wwwroot; ?>/siteadmin/stats/contact_stats_day.php"><?php echo get_string('stats_loginstats', 'local_lmsdata'); ?></a>
        <li <?php echo starts_with($submenu, 'course_count') ? ' class="selected"' : ''; ?>><a href="<?php echo $CFG->wwwroot; ?>/siteadmin/stats/course_count_year.php">강의 개설현황</a></li>
        <li <?php echo starts_with($submenu, 'activity') ? ' class="selected"' : ''; ?>><a href="<?php echo $CFG->wwwroot; ?>/siteadmin/stats/activity_year.php">활동 사용현황</a></li>
        <li <?php echo starts_with($submenu, 'course_note') ? ' class="selected"' : ''; ?>><a href="<?php echo $CFG->wwwroot; ?>/siteadmin/stats/course_note_list.php">강의 노트현황</a></li>
        <li<?php echo (starts_with($submenu, 'course_all') || starts_with($submenu, 'course_history') || starts_with($submenu, 'course_progress')) ? ' class="selected"' : ''; ?>><a href="<?php echo $CFG->wwwroot; ?>/siteadmin/stats/course_all.php"><?php echo get_string('stats_allcourselist', 'local_lmsdata'); ?></a>
        <?php
            if (starts_with($submenu, 'course_all') || starts_with($submenu, 'course_history') || starts_with($submenu, 'course_progress')) {  
                $coursetype = optional_param('coursetype', 0, PARAM_INT); //0:교과, 1:비교과, 2:이러닝 
        ?>
                <ul>
                    <li<?php echo ((starts_with($submenu, 'course_all') || starts_with($submenu, 'course_history') || starts_with($submenu, 'course_progress')) && $coursetype == 0 )  ? ' class="selected"' : ''; ?>><a href="<?php echo $CFG->wwwroot; ?>/siteadmin/stats/course_all.php"><?php echo get_string('regular_course', 'local_lmsdata'); ?></a></li> 
                    <li<?php echo ((starts_with($submenu, 'course_all') || starts_with($submenu, 'course_history') || starts_with($submenu, 'course_progress')) && $coursetype == 1) ? ' class="selected"' : ''; ?>><a href="<?php echo $CFG->wwwroot; ?>/siteadmin/stats/course_all.php?coursetype=1"><?php echo get_string('irregular_course', 'local_lmsdata'); ?></a></li>
                    <li<?php echo ((starts_with($submenu, 'course_all') || starts_with($submenu, 'course_history') || starts_with($submenu, 'course_progress')) && $coursetype == 2) ? ' class="selected"' : ''; ?>><a href="<?php echo $CFG->wwwroot; ?>/siteadmin/stats/course_all.php?coursetype=2"><?php echo get_string('elearning_course', 'local_lmsdata'); ?></a></li>
                </ul>
        <?php } ?>
        </li>
        <li<?php echo starts_with($submenu, 'course_teacher_all') ? ' class="selected"' : ''; ?>><a href="<?php echo $CFG->wwwroot; ?>/siteadmin/stats/course_teacher_all.php"><?php echo get_string('stats_profcourselist', 'local_lmsdata'); ?></a>
        <?php
            if (starts_with($submenu, 'course_teacher_all')) {  
                $coursetype = optional_param('coursetype', 0, PARAM_INT); //0:교과, 1:비교과, 2:이러닝 
        ?>
                <ul>
                    <li<?php echo (starts_with($submenu, 'course_teacher_all') && $coursetype == 0 )  ? ' class="selected"' : ''; ?>><a href="<?php echo $CFG->wwwroot; ?>/siteadmin/stats/course_teacher_all.php"><?php echo get_string('regular_course', 'local_lmsdata'); ?></a></li> 
                    <li<?php echo (starts_with($submenu, 'course_teacher_all') && $coursetype == 1) ? ' class="selected"' : ''; ?>><a href="<?php echo $CFG->wwwroot; ?>/siteadmin/stats/course_teacher_all.php?coursetype=1"><?php echo get_string('irregular_course', 'local_lmsdata'); ?></a></li>
                    <li<?php echo (starts_with($submenu, 'course_teacher_all') && $coursetype == 2) ? ' class="selected"' : ''; ?>><a href="<?php echo $CFG->wwwroot; ?>/siteadmin/stats/course_teacher_all.php?coursetype=2"><?php echo get_string('elearning_course', 'local_lmsdata'); ?></a></li>
                </ul>
        <?php } ?>
        
        </li>
<!--        <li><a href="https://www.whatap.io/"  target="_blank"><?php echo get_string('stats_survermonitoring', 'local_lmsdata'); ?></a></li>-->
    </ul>

<?php include_once dirname(dirname(__FILE__)) . '/inc/nav.php'; ?>

</div><!--Sidebar Manage End-->