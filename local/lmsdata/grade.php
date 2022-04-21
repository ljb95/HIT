<?php
    require('../../config.php');
     
    $current = optional_param('current', 0, PARAM_INT);
    
    $context = context_system::instance();

require_login();

$PAGE->set_context($context);

$PAGE->set_url('/local/board/index.php');
$PAGE->set_pagelayout('standard');

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

?>
<!-- Resources -->
<script src="https://www.amcharts.com/lib/3/amcharts.js"></script>
<script src="https://www.amcharts.com/lib/3/serial.js"></script>
<script src="https://www.amcharts.com/lib/3/plugins/export/export.min.js"></script>
<link rel="stylesheet" href="https://www.amcharts.com/lib/3/plugins/export/export.css" type="text/css" media="all" />
<script src="https://www.amcharts.com/lib/3/themes/light.js"></script>
<?php

$strplural = get_string("pluginnameplural", "local_board");
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading($strplural);
echo $OUTPUT->header();


$year = get_config('moodle', 'haxa_year'); 
$term = get_config('moodle', 'haxa_term');  

$stuselect = "select c.id, c.fullname ,mt.grade as mgrade , ft.grade as fgrade  ,mhigher.mmaxgrade,fhigher.fmaxgrade ";
$proselect = "select c.id, c.fullname ,cnt.ucnt ";
$query = "
    from {course} c
    join {context} ctx on ctx.contextlevel = 50 and ctx.instanceid = c.id 
    join {role_assignments} ra on ra.contextid = ctx.id 
    join {role} r on r.id = ra.roleid and r.shortname = :rolename  
    join {user} u on u.id = ra.userid ";
$student = " left join v_grade_cates mt on mt.courseid = c.id and mt.userid = u.id and mt.fullname = '중간' 
    left join v_grade_cates ft on mt.courseid = c.id and mt.userid = u.id and mt.fullname = '기말' 
    left join (select max(grade) as mmaxgrade ,courseid,fullname from v_grade_cates where fullname = '중간') mhigher on mhigher.courseid = c.id 
    left join (select max(grade) as fmaxgrade ,courseid,fullname from v_grade_cates where fullname = '기말') fhigher on fhigher.courseid = c.id 
    where u.id = :userid";
$prof = "              
            left join 
            (select c.id as courseid , count(u.id) as ucnt 
            from {course} c
            join {context} ctx on ctx.contextlevel = 50 and ctx.instanceid = c.id 
            join {role_assignments} ra on ra.contextid = ctx.id 
            join {role} r on r.id = ra.roleid and r.shortname = 'student' 
            join {user} u on u.id = ra.userid group by c.id) as cnt on cnt.courseid = c.id 
    where u.id = :userid ";
$params = array('userid'=>$USER->id,'rolename'=>'student');



$courses = $DB->get_records_sql($stuselect.$query.$student,$params);

echo html_writer::tag('h3', get_string('coursesiamtaking', 'grades'));
?>
<table class="generaltable">
    <thead>
    <tr>
        <th>강좌명</th>
        <th>중간고사</th>
        <th>기말고사</th>
        <th>성적</th>
    </tr>
    </thead>
    <tbody>
    <?php 
    foreach($courses as $course){
        if($course->mgrade && $course->fgrade){
            $total = ($course->mgrade+$course->fgrade) / 2;
        } else if($course->mgrade){
            $total = $course->mgrade;
        } else if($course->fgrade){
            $total = $course->fgrade;
        } else {
            $total = false;
        }
        $mhighrank = ($course->mgrade * $course->mmaxgrade) / 100;
        $fhighrank = ($course->fgrade * $course->fmaxgrade) / 100;
        ?>
        <tr>
            <td><?php echo $course->fullname; ?></td>
            <td><?php echo ($course->mgrade)?round($course->mgrade).'(상위'.round($mhighrank).'%)':'-'; ?></td>
            <td><?php echo ($course->fgrade)?round($course->fgrade).'(상위'.round($fhighrank).'%)':'-'; ?></td>
            <td><?php echo ($total)?round($total):'-'; ?></td>
        </tr>
        <?php
    }
    ?>
    </tbody>
</table>
<?php
$params = array('userid'=>$USER->id,'rolename'=>'student');
$courses = $DB->get_records_sql($proselect.$query.$prof,$params);

echo html_writer::tag('h3', get_string('coursesiamteaching', 'grades'));

?>
<table class="generaltable">
    <thead>
    <tr>
        <th>강좌명</th>
        <th>수강생 수</th>
        <th>평균점수</th>
        <th>성적분포</th>
    </tr>
    </thead>
    <tbody>
    <?php 
    foreach($courses as $course){ 
        $conn = odbc_connect($CFG->local_haksa_sid, $CFG->local_haksa_username, $CFG->local_haksa_password);
        if ($conn) {
    $dates = "SELECT SUM(GPA) / count(GPA) as gpa FROM V_HAK_SCOR_INFO WHERE LEC_CD = ".$lmsdata_class->subject_id;
    $param = array();
    $res = odbc_exec($conn,$dates);
    
    $row = odbc_fetch_array($res);

        } else {
            $row = array('GPA'=>'-');
        }
    ?>
        <tr>
            <td><?php echo $course->fullname; ?></td>
            <td><?php echo ($course->ucnt)?$course->ucnt:0; ?></td>
            <td> <?php echo number_format($row['gpa'],2); ?></td>
            <td><input type="button" class="btn_st01" onclick="view_graph('<?php echo $course->id; ?>')" value="성적분포도"></td>
        </tr>
    <?php } ?>
    </tbody>
</table>
<script>
    function view_graph(courseid){
        var tag = $("<div id='view_graph'></div>");
        $.ajax({
          url: '<?php echo $CFG->wwwroot.'/local/lmsdata/grade_graph.php'; ?>',
          method: 'POST',
          data: {
            id : courseid
          },
          success: function(data) {
            tag.html(data).dialog({
                title: '성적분포도',
                modal: true,
                width: 800,
                resizable: false,
                height: 500,
                buttons: [ 
                        {id:'close',
                            text:'닫기',
                            disable: true,
                            click: function() {
                                $( this ).dialog( "close" );
                            }}
                       
                    ],
                close: function () {
                    $( this ).dialog('destroy').remove();
                }
            }).dialog('open');
          }
        });
    }
</script>
<?php 

echo $OUTPUT->footer();