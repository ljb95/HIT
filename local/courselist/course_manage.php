<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot.'/siteadmin/lib/paging.php';
require_once $CFG->dirroot . '/siteadmin/lib.php';
require_once $CFG->dirroot . '/local/courselist/lib.php';

require_login();
$currpage     = optional_param('page', 1, PARAM_INT);
$perpage      = optional_param('perpage', 99999, PARAM_INT);
$coursetype   = optional_param('coursetype', 0, PARAM_INT);

// 현재 년도, 학기
$year = get_config('moodle', 'haxa_year');
$term = get_config('moodle', 'haxa_term');

$context = context_system::instance();
$user = $DB->get_record('lmsdata_user', array('userid'=>$USER->id));
if(!is_siteadmin($USER) && ($user->usergroup != 'pr') && ($user->usergroup != 'sa')){
    redirect($CFG->wwwroot); 
}

//역할코드 가져오기
$roleid_student = $DB->get_field('role','id',array('shortname'=>'student'));
$roleid_auditor = $DB->get_field('role','id',array('shortname'=>'auditor'));

$sql_select  = "SELECT mc.id, mc.fullname, mc.shortname
     , lc.subject_id
     , lc.isnonformal
     , lc.year
     , lc.term
     ,lc.timestart, lc.timeend
     , ca.name as category_name
     , (SELECT count(*) from {user_enrolments} ue
        JOIN {enrol} en ON en.id = ue.enrolid 
        LEFT JOIN {role_assignments} ra ON ra.userid = ue.userid 
        where en.courseid = lc.course and en.status = 0 and ue.status = :app1 and (ra.roleid = :roleid1 or ra.roleid = :roleid2) and ra.contextid = ctx.id) AS app_num
     , (SELECT count(*) from {user_enrolments} ue
        JOIN {enrol} en ON en.id = ue.enrolid
        LEFT JOIN {role_assignments} ra ON ra.userid = ue.userid
        where en.courseid = lc.course and en.status = 0 and ue.status = :app2 and (ra.roleid = :roleid3 or ra.roleid = :roleid4) and ra.contextid = ctx.id) AS total_num 
     , mc.visible 
    ";
$sql_from    = " FROM {course} mc
JOIN {lmsdata_class} lc ON lc.course = mc.id
JOIN {course_categories} ca ON ca.id = mc.category
JOIN {context} ctx ON mc.id = ctx.instanceid 
JOIN {role_assignments} ra2 ON ra2.contextid = ctx.id and ra2.roleid = 3 
";

$condition = array('ctx.contextlevel =:contextlevel','ra2.userid =:userid','lc.isnonformal = :coursetype');
if($coursetype == 0){
    $condition[] = 'lc.year = :year and lc.term = :term';
}

$sql_where = ' where ' . implode(' and ',$condition);

$page_params = array();
$params = array(
    'year'=>$year,
    'term'=>$term,
    'contextlevel'=>CONTEXT_COURSE,
    'app1'=>0,
    'app2'=>1,
    'userid'=>$USER->id,
    'coursetype'=>$coursetype,
    'roleid1' => $roleid_student,
    'roleid2' => $roleid_auditor,
    'roleid3' => $roleid_student,
    'roleid4' => $roleid_auditor
);

$sql_orderby = ' order by lc.timestart asc, lc.id desc';

$courses = $DB->get_records_sql($sql_select.$sql_from.$sql_where.$sql_orderby, $params, ($currpage-1)*$perpage, $perpage);
$count_courses = $DB->count_records_sql("SELECT COUNT(*) ".$sql_from.$sql_where, $params);

$PAGE->set_context($context);

$PAGE->set_url('/local/courselist/course_manage.php');
$PAGE->set_pagelayout('standard');

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->jquery_plugin('migrate');
$PAGE->requires->css('/local/courselist/style.css');
$PAGE->requires->css('/siteadmin/css/loading.css');
$PAGE->requires->js('/siteadmin/js/loading.js');

$strplural = get_string('course:manage', 'local_courselist');
$PAGE->navbar->add(get_string("mypage", "local_courselist"), new moodle_url($CFG->wwwroot.'/local/courselist/course_manage.php'));
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading(get_string("mypage", "local_courselist"));
echo $OUTPUT->header();

//tab
if ($coursetype == 0) {
    $currenttab = '0';
} else if($coursetype == 1){
     $currenttab = '1';
}

$rows = array (
    new tabobject('0', "$CFG->wwwroot/local/courselist/course_manage.php?coursetype=0", get_string('regular', 'local_courselist')),
    new tabobject('1', "$CFG->wwwroot/local/courselist/course_manage.php?coursetype=1", get_string('menu:irregular', 'local_lmsdata'))
    );
 
print_tabs(array($rows), $currenttab);

include_once('course_manage_'.$coursetype.'.php');

echo $OUTPUT->footer();
?>

<script type="text/javascript">
    
function split_course_dialog(){
        var count = 0;
        $('#merge_button').append('<form method="post" id="merge_course" action="course_list_merge_form.php"></form>');
        $(".courseid").each(function(index, element){
          if($(this).is(":checked")){
            $('#merge_course').append('<input type="hidden" name="course[]" value="'+$(this).val()+'" />');
            count++;
          }
        });

        if(count < 2) {
            alert("<?php echo get_string('course:sel_more', 'local_courselist'); ?>");
            return false;
        }
        $('#merge_course').submit();
}

function course_delete(courseid){
    if(confirm("<?php echo get_string('deletecoursecheck');?>") == true) {
        $.ajax({
          url: '<?php echo $CFG->wwwroot."/local/courselist/course_delete.execute.php"?>',
          method: 'POST',
          data : {
            id : courseid,  
          },
          success: function(data) {
            document.location.href = "<?php echo $CFG->wwwroot."/local/courselist/course_manage.php"?>";
          }
        });
    }
}

function check_course_id(check, checkClass){
    if($(check).is(":checked")){
        $("."+checkClass).each(function(){
            this.checked = true;   
        });
    }else{
        $("."+checkClass).each(function(){
            this.checked = false;   
        });
    }
}

function radio_check(courseid){
    $("input:checkbox[name=course_invisible]").each(function(index, element){
        if($(this).val() != courseid){
            this.checked = true;
            this.disabled = false;
        } else {
            this.checked = false;
            this.disabled = true;
        }
    });
}

    
</script>    
