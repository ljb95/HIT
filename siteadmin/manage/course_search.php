<?php 
require (dirname(dirname(dirname(__FILE__))) . '/config.php');

$id = optional_param('id', 0, PARAM_INT);
$searchstring = optional_param('value', '', PARAM_RAW);

$context = context_system::instance();
$PAGE->set_context($context);


$sql_select  = "SELECT mc.id, mc.fullname, mc.shortname, 
                lc.timestart, lc.timeend, lc.timeregstart, lc.timeregend, 
                lc.subject_id, lc.year, lc.term, lc.isreged, lc.prof_userid,
                ur.firstname, ur.lastname";

$sql_from    = " FROM {course} mc
                 JOIN {lmsdata_class} lc ON lc.course = mc.id
                 JOIN {course_categories} ca ON ca.id = mc.category 
                 LEFT JOIN {user} ur ON ur.id = lc.prof_userid ";

$sql_where   =  array();
$params = array();

$sql_where[] = "lc.isnonformal = :coursetype";
$params['coursetype'] = 1;

$sql_where[] = "mc.id != :id";
$params['id'] = $id;

if(!empty($searchstring)) {
    $sql_where[]= '( '.$DB->sql_like('lc.subject_id', ':subject_id').' or '.$DB->sql_like('lc.kor_lec_name', ':kor_lec_name') . ')';
    $params['subject_id'] = '%'.$searchstring.'%';
    $params['kor_lec_name'] = '%'.$searchstring.'%';
}

$sql_orderby = " ORDER BY lc.subject_id ASC, mc.timecreated DESC ";

if(!empty($sql_where)) {
    $sql_where = ' WHERE '.implode(' and ', $sql_where);
}else {
    $sql_where = '';
}

$courses = $DB->get_records_sql($sql_select.$sql_from.$sql_where.$sql_orderby, $params);
$count_courses = $DB->count_records_sql("SELECT COUNT(*) ".$sql_from.$sql_where, $params);
?>

<div class="popup_content" id="irregular_course_search_popup">
    <form id="frm_course_search" class="search_area" onsubmit="irregular_course_search(); return false;" method="POST">
        <input type="text" name="value" value="<?php echo $searchstring; ?>" class="w_300" placeholder="<?php echo get_string('placeholder7','local_lmsdata'); ?>"/>   
        <input type="submit" class="blue_btn" id="search" value="<?php echo get_string('search','local_lmsdata'); ?>"/>
    </form>
   
    <form id="frm_course_certificate" name="frm_course_certificate" onsubmit="return false;">
        <table cellpadding="0" cellspacing="0">
            <tbody>
            <tr>
                <th><?php echo get_string('number', 'local_lmsdata'); ?></th>
                <th><?php echo get_string('stats_coursename','local_lmsdata'); ?></th>
                <th><?php echo get_string('prof_name','local_lmsdata'); ?></th>
                <th><?php echo get_string('select','local_lmsdata'); ?></th>
            </tr>
            <?php
            if($count_courses > 0) {
                $count = 0;
                foreach($courses as $course) {
                    echo '<tr>';
                    echo '<td>'.($count_courses - $count).'</td>';
                    echo '<td>'.$course->fullname.'</td>';
                    echo '<td>'.$course->firstname.$course->lastname.'</td>';
                    echo '<td><input type="button" value="'.get_string('select','local_lmsdata').'" class="orange_btn" onclick="irregular_course_select(\''.$course->id.'\', \''.$course->fullname.'\');"></td>';
                    echo '</tr>';
                   
                    $count++;
                }
            } else {
                echo '<tr><td colspan="4">'.get_string('empty_course','local_lmsdata').'</td></tr>';
            }
            ?>
            </tbody>
        </table>
    </form>
</div>

<script type="text/javascript">
    function irregular_course_search() {
        var searchstring = $( "#frm_course_search input[name=value]" ).val();
        $.ajax({
            url: '<?php echo $CFG->wwwroot.'/siteadmin/manage/course_search.php'; ?>',
            method: 'POST',
            data: {
                'value': searchstring
            },
            success: function(data) {
                $("#irregular_course_search_popup").parent().html(data);
            },
            error: function(jqXHR, textStatus, errorThrown ) {
            }
        });
    }
    function irregular_course_select(id, name) {
        $( "input[name=criterion_course_id]" ).attr("value",id);
        $( "input[name=criterion_course]" ).attr("value",name);
        $( "input[name=criterion_course_id_submit]" ).attr("value",id);
        $("#course_search_popup").dialog( "close" );
    }
</script> 
