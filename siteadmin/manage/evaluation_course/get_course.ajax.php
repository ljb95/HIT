<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php';
require_once dirname(dirname(dirname(__FILE__))) . '/lib/paging.php';
require_once dirname(dirname(dirname(__FILE__))) . '/lib.php';

header("Content-Type: text/html; charset=UTF-8");

$year = optional_param('year', 0, PARAM_INT);
$term = optional_param('term', 0, PARAM_INT);
$hakyear = optional_param('hakyear', '', PARAM_RAW); //학년
$search = optional_param('search', '', PARAM_RAW);
$searchtext = optional_param('searchtext', '', PARAM_TEXT);
?>
<form id="course_searchform" method="post" onsubmit="return false;">
    <select name="year" class="w_160">
        <option value="0"  <?php echo $year == 0 ? 'selected' : '' ?>><?php echo get_string('all','local_lmsdata'); ?></option>
        <?php
        $year_arr = lmsdata_get_years();
        foreach ($year_arr as $tg_year) {
            $selected = "";
            if ($tg_year == $year) {
                $selected = "selected";
            }
            echo '<option value="' . $tg_year . '"  ' . $selected . '>' . get_string('year','local_lmsdata',$tg_year) . '</option>';
        }
        ?>
    </select>
    <select name="term" class="w_160">
        <option value="0" <?php echo $term == 0 ? 'selected' : '' ?>><?php echo get_string('all','local_lmsdata'); ?></option>
        <?php
        $term_arr = lmsdata_get_terms();
        foreach ($term_arr as $term_key => $tg_term) {
            $selected = "";
            if ($term_key === $term) {
                $selected = "selected";
            }
            echo '<option value="' . $term_key . '"  ' . $selected . '>' . $tg_term . '</option>';
        }
        ?>
    </select>
    <br/>
    <select name="search" class="w_160">
        <option value=""><?php echo get_string('all','local_lmsdata'); ?></option>
        <option <?php if($search == 'subjectid')echo 'selected'; ?> value="subjectid"><?php echo get_string('course_code', 'local_lmsdata'); ?></option>
        <option <?php if($search == 'coursename')echo 'coursename'; ?> value="coursename"><?php echo get_string('course_name', 'local_lmsdata'); ?></option>
    </select> 
    <input type="text" name="searchtext" value="<?php echo $searchtext; ?>" placeholder="<?php echo get_string('search_placeholder','local_lmsdata'); ?>"  class="search-text"/>
    <input type="submit" id="course_searchbtn" class="search_btn" value="<?php echo get_string('search','local_lmsdata'); ?>"/>          
</form>
<table>
    <col width="10%">
    <col width="50%">
    <col width="30%">
    <col width="10%">
    <tr>
        <th><?php echo get_string('number', 'local_lmsdata'); ?></th>
        <th><?php echo get_string('course_code', 'local_lmsdata'); ?></th>
        <th><?php echo get_string('course_name', 'local_lmsdata'); ?></th>
        <th>책임교수</th>
        <th><?php echo get_string('select','local_lmsdata'); ?></th>
    </tr>
    <?php
    $lcon = '';
    $params = array();
    if ($year) {
        $lcon .= ' and lc.year = :year ';
        $params['year'] = $year;
    }
    if ($term) {
        $lcon .= ' and lc.term = :term ';
        $params['term'] = $term;
    }
    if ($hakyear) {
        $lcon .= ' and lc.hakyear = :hakyear ';
        $params['hakyear'] = $hakyear;
    }
    if (!empty($searchtext)) {
        $params['searchtxt'] = '%' . $searchtext . '%';
        $params['searchtxt2'] = '%' . $searchtext . '%';
        $params['searchtxt3'] = '%' . $searchtext . '%';
        switch ($search) {
            case 'coursename':
                $lcon .= ' and (lc.kor_lec_name like :searchtxt or lc.eng_lec_name like :searchtxt2) ';
                break;
            case 'subjectid':
                $lcon .= ' and lc.subject_id like :searchtxt ';
                break;
            default:
                $lcon .= ' and (lc.kor_lec_name like :searchtxt or lc.eng_lec_name like :searchtxt2 or lc.subject_id like :searchtxt3) ';
                break;
        }
    }


    $courses = $DB->get_records_sql('select c.*,lc.subject_id from {course} c join {lmsdata_class} lc on lc.course = c.id'. $lcon .' order by lc.subject_id asc',$params);
    $sql = "select u.*
                from {course} c 
                join {context} ct on ct.contextlevel = 50 and ct.instanceid = c.id 
                join {lmsdata_class} lc on lc.course = c.id  
                join {role_assignments} ra on ra.contextid = ct.id 
                join {user} u on u.id = ra.userid  
                join {role} r on r.id = ra.roleid and r.shortname = 'editingteacher01' 
                where c.id = :courseid";
    $cnt = 1;
    foreach ($courses as $course) {
        $professors = $DB->get_records_sql($sql, array('courseid' => $course->id));
        ?>
        <tr>
            <td><?php echo $cnt++; ?></td>
            <td><?php echo $course->subject_id; ?></td>
            <td><?php echo $course->fullname; ?></td>
            <td>
                <?php
                $pro = "";
                foreach ($professors as $professor) {
                    $pro .= fullname($professor) . "<br>";
                }
                echo rtrim($pro, "<br>");
                ?>
            </td>
            <td><input type="button" class="normal_btn" value="<?php echo get_string('select','local_lmsdata'); ?>" onclick="course_selete(<?php echo $course->id; ?>, '<?php echo $course->fullname; ?>')"></td>
        </tr>
    <?php } if($cnt == 1){ ?>
        <tr>
            <td colspan="5"><?php echo get_string('empty_course2','local_lmsdata'); ?></td>
        </tr>
    <?php } ?>
</table><!--Table End-->

<script>
    $("#course_searchform").submit(function () {
        var postData = {
            year: $("select[name=year] option:selected").val(),
            term: $("select[name=term] option:selected").val(),
            hakyear: $("select[name=hakyear] option:selected").val(),
            search: $("select[name=search] option:selected").val(),
            searchtext: $("input[name=searchtext]").val()
        };
        $.ajax({
            type:"POST",
            url: "get_course.ajax.php",
            data:postData,
            success: function (result) {
                parent.$("#course_search_dialog").html(result);
            }
        });
    });
</script>
