<?php 
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot.'/siteadmin/lib/paging.php';
require_once $CFG->dirroot . '/siteadmin/lib.php';

// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/manage/course_list.php');
    redirect(get_login_url());
}
$context = context_system::instance();
$user = $DB->get_record('lmsdata_user', array('userid'=>$USER->id));
if(!is_siteadmin($USER) && ($user->usergroup != 'pr') && ($user->usergroup != 'sa')){
    redirect($CFG->wwwroot); 
}


$currpage     = optional_param('page', 1, PARAM_INT);
$perpage      = optional_param('perpage', 99999, PARAM_INT);

$year = get_config('moodle', 'haxa_year');
$term = get_config('moodle', 'haxa_term');

$sql_select  = "SELECT lcd.id, lcd.invisible, lcd.timecreated, lcd.restore_flag, lcd.timerestore
     ,mc.fullname AS subjectname
     , sco.fullname AS standardname
     , lc.subject_id, lc.isnonformal
     , dus.firstname||dus.lastname AS executename
     , mu.firstname||mu.lastname AS prof_name ";
$sql_from    = " FROM {lmsdata_class_drive_log} lcd
JOIN {course} mc ON mc.id = lcd.subject_id
JOIN {course} sco ON sco.id = lcd.standard_id
JOIN {lmsdata_class} lc ON lc.course = lcd.subject_id
JOIN {user} dus ON dus.id = lcd.user_id
JOIN {context} ctx ON ctx.instanceid = sco.id 
JOIN {role_assignments} ra ON ra.contextid = ctx.id and ra.roleid = 3 
LEFT JOIN {user} mu ON mu.id = lc.prof_userid ";
$sql_where   = " WHERE ((lc.year = :year 
  AND lc.term = :term ) or (lc.year= 9999)) AND lcd.type = 0 and lcd.restore_flag = 1 and ctx.contextlevel =:contextlevel and ra.userid =:userid  ";
$sql_orderby = " ORDER BY mc.fullname";

$page_params = array();
$params = array(
    'year'=>$year,
    'term'=>$term,
    'contextlevel'=>CONTEXT_COURSE,
    'userid'=>$USER->id
);


$courses = $DB->get_records_sql($sql_select.$sql_from.$sql_where.$sql_orderby, $params, ($currpage-1)*$perpage, $perpage);
$count_courses = $DB->count_records_sql("SELECT COUNT(*) ".$sql_from.$sql_where, $params);

$PAGE->set_context($context);

$PAGE->set_url('/local/courselist/course_list_restore.php');
$PAGE->set_pagelayout('standard');

$PAGE->requires->css('/local/courselist/style.css');
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->jquery_plugin('migrate');
$PAGE->requires->css('/siteadmin/css/loading.css');
$PAGE->requires->js('/siteadmin/js/loading.js');

$strplural = get_string("course:classes_restore_log", "local_courselist");
$PAGE->navbar->add(get_string("course:manage", "local_courselist"), new moodle_url($CFG->wwwroot.'/local/courselist/course_manage.php'));
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading(get_string("course:manage", "local_courselist"));
echo $OUTPUT->header();

//tab
$tabmenu =  trim(basename($_SERVER['PHP_SELF']), '.php');
if ($tabmenu === 'course_manage') {
    $currenttab = 'manage';
} else if($tabmenu === 'complete_show'){
    $currenttab = 'completion';
} else if($tabmenu === 'course_list_drive'){
    $currenttab = 'list_drive';
} else if($tabmenu === 'course_list_restore'){
    $currenttab = 'list_restore';
}

$rows = array (
    new tabobject('manage', "$CFG->wwwroot/local/courselist/course_manage.php", get_string('course:list', 'local_courselist')),
    new tabobject('completion', "$CFG->wwwroot/local/courselist/complete_show.php", get_string('course:completion', 'local_courselist')),
    new tabobject('list_drive', "$CFG->wwwroot/local/courselist/course_list_drive.php", get_string('course:classes_drive_log', 'local_courselist')),
    new tabobject('list_restore', "$CFG->wwwroot/local/courselist/course_list_restore.php", get_string('course:classes_restore_log', 'local_courselist'))
    );
print_tabs(array($rows), $currenttab);
?>
        <table class="generaltable" id="course_restore">
            <thead>
            <tr>
                <th class="col-1"><?php echo get_string('no', 'local_courselist'); ?></th>
                <th class="col-2"><?php echo get_string('course:standard_name', 'local_courselist'); ?></th>
                <th class="col-3"><?php echo get_string('course:subject_name', 'local_courselist'); ?></th>
                <th class="col-4"><?php echo get_string('user:execute', 'local_courselist'); ?></th>
                <th class="col-7"><?php echo get_string('regular:short', 'local_courselist').'/'.get_string('irregular:short', 'local_courselist'); ?></th>
                <th class="col-8"><?php echo get_string('date:execute', 'local_courselist'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            if($count_courses === 0) { ?>
            <tr>
                <td colspan="11"><?php echo get_string('course:empty', 'local_courselist'); ?></td>
            </tr>
            <?php } else {
            $startnum = $count_courses - (($currpage - 1) * $perpage);
            foreach($courses as $course) { 
            ?>
            <tr>
                <td class="col-1"><?php echo $startnum--; ?></td>
                <td class="col-2"><?php echo $course->standardname; ?></td>
                <td class="col-3"><?php echo $course->subjectname; ?></td>
                <td class="col-4"><?php echo $course->executename; ?></td>
                <td class="col-7"><?php echo $course->isnonformal == 1 ? get_string('irregular:short', 'local_courselist') : get_string('regular:short', 'local_courselist'); ?></td>
                <td class="col-8"><?php echo date("Y-m-d", $course->timerestore); ?></td>
            </tr>
            <?php }} ?>
            </tbody>
        </table><!--Table End-->
        <div class="table-footer-area">
    </div>
<?php 
    echo $OUTPUT->footer();
?>

<script type="text/javascript">
    function cata_page(page) {
        $('[name=page]').val(page);
        $('#course_search').submit();
    }    
</script>
