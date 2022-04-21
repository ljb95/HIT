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

$sql_select  = "SELECT mc.id, mc.fullname, mc.shortname
     , lc.subject_id
     , lc.isnonformal
     , lc.year
     , lc.term
     , ca.name as category_name
     , (SELECT count(*) from {user_enrolments} ue
        JOIN {enrol} en ON en.id = ue.enrolid
        where en.courseid = lc.course and ue.status = :app1 and ue.userid != :userid1 and userid != 2 ) AS app_num
     , (SELECT count(*) from {user_enrolments} ue
        JOIN {enrol} en ON en.id = ue.enrolid
        where en.courseid = lc.course and ue.status = :app2 and ue.userid != :userid2 and userid != 2 ) AS total_num 
     , mc.visible 
     , hc.deleted ";
$sql_from    = " FROM {course} mc
JOIN {lmsdata_class} lc ON lc.course = mc.id
JOIN {course_categories} ca ON ca.id = mc.category
JOIN {context} ctx ON mc.id = ctx.instanceid 
JOIN {role_assignments} ra ON ra.contextid = ctx.id and ra.roleid = 3 
LEFT JOIN {haksa_class} hc ON hc.hakno||'-'||hc.bb = lc.subject_id ";

$condition = array('ctx.contextlevel =:contextlevel','ra.userid =:userid3','lc.isnonformal = :coursetype');
if($coursetype == 1){
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
    'userid1'=>$USER->id,
    'userid2'=>$USER->id,
    'userid3'=>$USER->id,
    'coursetype'=>$coursetype
);

$courses = $DB->get_records_sql($sql_select.$sql_from.$sql_where, $params, ($currpage-1)*$perpage, $perpage);
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

$strplural = get_string('course:list', 'local_courselist');
$PAGE->navbar->add(get_string("course:manage", "local_courselist"), new moodle_url($CFG->wwwroot.'/local/courselist/course_manage.php'));
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading(get_string("course:manage", "local_courselist"));
echo $OUTPUT->header();

//tab
if ($coursetype == 0) {
    $currenttab = '0';
} else if($coursetype == 1){
     $currenttab = '1';
} else if($coursetype == 2){
     $currenttab = '2';
}

$rows = array (
    new tabobject('0', "$CFG->wwwroot/local/courselist/course_manage.php?coursetype=0", get_string('regular', 'local_courselist')),
    new tabobject('1', "$CFG->wwwroot/local/courselist/course_manage.php?coursetype=1", get_string('irregular', 'local_courselist')),
    new tabobject('2', "$CFG->wwwroot/local/courselist/course_manage.php?coursetype=2", get_string('elearning', 'local_courselist'))
    );
 
print_tabs(array($rows), $currenttab);
?>
        <!-- 정규교과 과정 -->
        <h1 class="course_h1" ><?php print_string('regular', 'local_courselist'); ?></h1>
        <table class="generaltable" id="course_manage">
            <thead>
            <tr>
                <th class="col-1"><?php echo get_string('course:category', 'local_courselist'); ?></th>
                <th class="col-2"><?php echo get_string('course:subjectid', 'local_courselist'); ?></th>
                <th class="col-3"><?php echo get_string('course:name', 'local_courselist'); ?></th>
                <th class="col-5"><?php echo get_string('course:app', 'local_courselist').'/'.get_string('course:wait', 'local_courselist'); ?></th>
                <th class="col-6"><?php echo get_string('manage', 'local_courselist'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php 
            $count_courses = 0;
            foreach($courses as $course) {
                if($course->isnonformal == 0) {
                    $count_courses++;
            ?>
                <tr>
                    <td class="col-1"><?php echo $course->category_name; ?></td>
                    <td class="col-2"><?php echo $course->subject_id; ?></td>
                    <td class="left col-3"><a href="<?php echo $CFG->wwwroot.'/course/view.php?id='.$course->id; ?>"><?php echo $course->fullname; ?></a></td>
                    <td class="col-5"><?php echo $course->app_num.'/'.$course->total_num; ?></td>
                    <td class="col-6">
                    <?php 
                       echo '<input type="button" class="red_btn student_list" value="'.get_string('student', 'local_courselist').'" onclick="javascript:location.href = \''.$CFG->wwwroot.'/local/courselist/course_students_list.php?id='.$course->id.'\'"/>';
                       if($course->visible == 0 && $course->deleted == 1) {
                           echo '<input type="button" class="red_btn coruse_delete" value="'.get_string('course:delete', 'local_courselist').'" onclick="course_delete('.$course->id.')"/>';
                       }
                    ?>
                    </td>
                </tr>
            <?php 
               }
            }
            if($count_courses === 0) { ?>
            <tr>
                <td colspan="6"><?php echo get_string('course:empty', 'local_courselist'); ?></td>
            </tr>
            <?php } ?>
            </tbody>
        </table><!--Table End-->
        <div class="table-footer-area">
            <div class="btn-area btn-area-left"></div>
        </div>
        
        <!-- 비교과 과정 -->
        <div>
            <h1 class="course_h1"><?php print_string('irregular', 'local_courselist'); ?></h1>
        </div>
        <table class="generaltable" id="course_manage"> 
            <thead>
            <tr>
                <th class="col-1"><input type="checkbox" onclick="check_course_id(this, 'courseid')"/></th>
                <th class="col-2"><?php echo get_string('course:category', 'local_courselist'); ?></th>
                <th class="col-3"><?php echo get_string('course:subjectid', 'local_courselist'); ?></th>
                <th class="col-4"><?php echo get_string('course:name', 'local_courselist'); ?></th>
                <th class="col-6"><?php echo get_string('course:app', 'local_courselist').'/'.get_string('course:wait', 'local_courselist'); ?></th>
                <th class="col-7"><?php echo get_string('manage', 'local_courselist'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $count_courses = 0;
            foreach($courses as $course) {
                if($course->isnonformal == 1) {
                    $count_courses++;
            ?>
            <tr>
                <td class="col-1"><input type="checkbox" class="courseid" name="courseid" value="<?php echo $course->id; ?>"/></td>
                <td class="col-2"><?php echo $course->category_name; ?></td>
                <td class="col-3"><?php echo $course->subject_id; ?></td>
                <td class="left col-4"><a href="<?php echo $CFG->wwwroot.'/course/view.php?id='.$course->id; ?>"><?php echo $course->fullname; ?></a></td>
                <td class="col-6"><?php echo $course->app_num.'/'.$course->total_num; ?></td>
                <td class="col-7">
                    <?php 
                       echo '<input type="button" class="btn_small student_list" value="'.get_string('student', 'local_courselist').'" onclick="javascript:location.href = \''.$CFG->wwwroot.'/local/courselist/course_students_list.php?id='.$course->id.'\'"/>';
                       echo '<input type="button" class="btn_small course_edit" value="'.get_string('edit', 'local_courselist').'" onclick="javascript:location.href = \''.$CFG->wwwroot.'/local/courselist/course_add.php?id='.$course->id.'\'"/>';
                       echo '<input type="button" class="btn_small coruse_delete" value="'.get_string('course:delete', 'local_courselist').'" onclick="course_delete('.$course->id.')"/>';
                    ?>
                </td>
            </tr>
            <?php 
                }
            }
            if($count_courses === 0) { ?>
            <tr>
                <td colspan="7"><?php echo get_string('course:empty', 'local_courselist'); ?></td>
            </tr>
            <?php } ?>
            </tbody>
        </table><!--Table End-->
        <div class="table-footer-area">
        <div class="btn-area btn-area-left" id="merge_button"> 
            <input type="submit" style="float:left; margin-right: 10px;" value="<?php echo get_string('merge:sel', 'local_courselist'); ?>" onclick="split_course_dialog();"/>
            <input type="button" style="float:left; margin-right: 10px;" value="<?php echo get_string('course:add', 'local_courselist'); ?>" onclick="javascript:location.href = '<?php echo $CFG->wwwroot."/local/courselist/course_add.php";?>'"/>
        </div>
        </div>     
<?php            
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