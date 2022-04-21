<?php
require (dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once dirname(dirname (__FILE__)).'/lib/paging.php';
require_once dirname(dirname (__FILE__)).'/lib.php';
require_once $CFG->dirroot . '/course/lib.php';
require_once $CFG->dirroot . '/course/report/statistics/lib.php';
require_once $CFG->libdir . '/formslib.php';
require_once("$CFG->libdir/excellib.class.php");


$id = required_param('id', PARAM_INT); // course id
$search = optional_param('search', '', PARAM_CLEAN);
$page = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 5, PARAM_INT);
$type = optional_param('type', 'all', PARAM_CLEAN);
$excell = optional_param('excell', 0, PARAM_INT);

$offset = ($page - 1) * $perpage;

require_login();


$context = context_course::instance($id);
$PAGE->set_context($context);


$course = get_course($id);

$sql = "select u.* from {role_assignments} ra "
        . "join {user} u on u.id = ra.userid "
        . "join {role} r on ra.roleid = r.id and r.shortname = :rolename "
        . "where ra.contextid = :contextid ";
$where = '';
$param = array('contextid' => $context->id, 'rolename' => 'student');
if ($search) {
    $where = ' and  ((u.firstname like :searchtxt1 or u.lastname like :searchtxt2 or concat(u.firstname, u.lastname) like :searchtxt3) or u.username like :searchtxt4 )';
    $param['searchtxt1'] = $param['searchtxt2'] = $param['searchtxt3'] = $param['searchtxt4'] = '%' . $search . '%';
}
if (!$excell) {
    $users = $DB->get_records_sql($sql . $where . ' order by u.username asc', $param, $offset, $perpage);
} else {
    $users = $DB->get_records_sql($sql . $where . ' order by u.username asc', $param);
}
$sql_select = "SELECT mc.id, mc.fullname, mc.shortname , mc.format as courseformat 
     , lcs.subject_id, lcs.isnonformal, mu.firstname, mu.lastname, mu.USERNAME 
     , lcs.ohakkwa as ohakkwa, ca.name as category_name 
        FROM {course} mc JOIN {lmsdata_class} lcs ON lcs.course = mc.id JOIN {course_categories} ca ON ca.id = mc.category LEFT JOIN {user} mu ON mu.id = lcs.prof_userid 
        LEFT JOIN {lmsdata_user} lc ON lc.userid = mu.id WHERE mc.id = :courseid";
$param2 = array('courseid' => $id, 'contextlevel1' => CONTEXT_COURSE, 'contextlevel2' => CONTEXT_COURSE);
$course_data = $DB->get_record_sql($sql_select, $param2);
$countsql = "select count(u.id) from {role_assignments} ra "
        . "join {user} u on u.id = ra.userid "
        . "join {role} r on ra.roleid = r.id and r.shortname = :rolename "
        . "where ra.contextid = :contextid ";
$usercount = $DB->count_records_sql($countsql . $where . ' order by u.username asc', $param);
$headsql = "select cm.id, cm.instance, mo.name, ctx.id as contextid from {course_modules} cm "
        . "join {modules} mo on mo.id = cm.module and mo.name = 'lcms' and mo.visible = 1 "
        . "join {context} ctx on ctx.instanceid = cm.id and ctx.contextlevel = :contextlevel "
        . "where cm.course = :course and cm.section = :section ";
$section_cnt = $DB->get_field('course_format_options', 'value', array('courseid' => $course_data->id, 'format' => $course_data->courseformat, 'name' => 'numsections'));
$sections_sql = "select DISTINCT cs.id,cs.name,cs.section , 
	(select count(*) FROM {course_modules} cm 
	JOIN m_modules mo ON mo.id = cm.module AND mo.name = 'lcms' 
	where cm.section = cs.id ) AS colspan 
	FROM {course_sections} cs
	where cs.visible = 1 and cs.course = :id
	order by cs.section asc";
$course_sections = $DB->get_records_sql($sections_sql, array('id' => $id));

$course_info_select = "SELECT mc.id, mc.fullname, mc.shortname
     , yc.subject_id, yc.isnonformal , yc.year, yc.term, yc.timestart, yc.timeend
     , ca.name as category_name
     ,(SELECT COUNT(*) 
        FROM {role_assignments} ra
        JOIN {role} ro ON ra.roleid = ro.id
        JOIN {context} ctx ON ra.contextid = ctx.id
        JOIN {course} co ON ctx.instanceid = co.id AND contextlevel = :contextlevel2
        WHERE co.id = mc.id
        AND ro.id = 5) as student ";

$course_info_from = " FROM {course} mc 
JOIN {lmsdata_class} yc ON yc.course = mc.id 
JOIN {course_categories} ca ON ca.id = mc.category ";

$course_info_params = array(
    'coursetype' => $coursetype,
    'contextlevel1' => CONTEXT_COURSE,
    'contextlevel2' => CONTEXT_COURSE,
    'id' => $id
);
$course_info_where = ' where mc.id = :id ';
$courses_info = $DB->get_records_sql($course_info_select . $course_info_from . $course_info_where, $course_info_params);

if (!$excell) {
    $js = array(
    $CFG->wwwroot.'/siteadmin/manage/course_list.js'
);
    include_once ('../inc/header.php');
    ?>
    <?php
    $url = "course_progress.php?id=$id&excell=1&search=$search";
    if (has_capability('coursereport/statistics:isteacher', $context)) {
        ?>        
<script type="text/javascript" src= "<?php echo $CFG->wwwroot ?>/theme/javascript.php?theme=oklasscampus&amp;rev=-1&amp;type=head"></script>
<div id="contents">
        <?php include_once ('../inc/sidebar_stats.php'); ?>
    <div id="content">
                <?php foreach ($courses_info as $course) { ?>
            <table cellpadding="0" cellspacing="0" class="detail">
                <tbody>
                    <tr>
                        <td class="field_title"><?php echo get_string('stats_years', 'local_lmsdata'); ?></td>
                        <td class="field_value" style="width: 30%;"><?php echo $course->year ?></td>
                        <td class="field_title"><?php echo get_string('stats_terms', 'local_lmsdata'); ?></td>
                                                <?php
                            if ($course->term == 1 || $course->term == 2) {
                             $term = $i . get_string('term', 'local_lmsdata');
                            } else if ($course->term == 3 || $course->term == 4) {
                                $term = str_replace(array(3, 4), array(get_string('summer', 'local_okregular')), $course->term);
                            } else {
                                $term = '-';
                            }
                            
                        ?>
                         <td class="field_value" style="width: 30%;"><?php echo ($course->term == 0)? get_string('term:all','local_okregular'):$term;  ?></td>
                    </tr>
                    <tr>
                        <td class="field_title"><?php echo get_string('stats_classification', 'local_lmsdata'); ?></td>
                        <td class="field_value" style="width: 30%;"><?php echo $course->category_name ?></td>
                        <td class="field_title"><?php echo get_string('stats_coursename', 'local_lmsdata'); ?></td>
                        <td class="field_value" style="width: 30%;"><?php echo $course->fullname ?></td>
                    </tr>
                    <tr>
                        <td class="field_title"><?php echo get_string('stats_learningperiod', 'local_lmsdata'); ?></td>
                        <td class="field_value" style="width: 30%;"><?php echo date("Y-m-d", $course->timestart) . ' ~ ' . date("Y-m-d", $course->timeend) ?></td>
                        <td class="field_title"><?php echo get_string('stats_student', 'local_lmsdata'); ?></td>
                        <td class="field_value" style="width: 30%;"><?php echo $course->student . '명' ?></td>
                    </tr>
                </tbody>
            </table>
<?php } ?>
        <form id="course_search" class="table-search-option stat_form">
            <input type="hidden" name="page" value="1" />
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <div class="stat_search_area">
                <input type="text" title="search" name="search" value="<?php echo $search; ?>" class="search-text" placeholder="<?php echo get_string('searchplaceholder', 'coursereport_statistics'); ?>">
                <input type="submit" value="<?php echo get_string('search', 'local_jinoboard'); ?>" class="board-search"/>
            </div>
        </form>
        <?php
    } else {
        echo '<h3>' . get_string('my_edu', 'coursereport_statistics') . '</h3>';
    }
    echo '<div class="no-overflow">';
    echo '<table class="generaltable stat_table"><caption class="hidden-caption">학습진도<thead>'
    . '<tr>';
    if (has_capability('coursereport/statistics:isteacher', $context)) {
        echo '<th scope="row" rowspan="2">' . get_string('no', 'coursereport_statistics') . '</th>';
    }
    echo '<th scope="row" rowspan="2">' . get_string('user:name', 'coursereport_statistics') . '</th>'
    . '<th scope="row" rowspan="2">' . get_string('user:hakbun', 'coursereport_statistics') . '</th>';
    $rows1 = array();
    $rows3 = array();
    foreach ($course_sections as $course_section) {
        echo '<th scope="row" colspan="' . $course_section->colspan . '">';
        $title = get_section_name($id, $course_section);
        echo $title;
        echo '</th>';
        $heads = $DB->get_records_sql($headsql, array('course' => $id, 'contextlevel' => CONTEXT_MODULE, 'section' => $course_section->id));
        foreach ($heads as $cm) {
            $mod = $DB->get_record($cm->name, array('id' => $cm->instance));
            $modulename = get_string('modulename', $cm->name);
            $activityicon = $OUTPUT->pix_icon('icon', $modulename, $cm->name, array('class' => 'icon', 'title' => $mod->name, 'alt' => $mod->name));
            $rows1[] = '<th scope="row"><a href="' . $CFG->wwwroot . '/mod/' . $cm->name . '/view.php?id=' . $cm->id . '">' . $activityicon . '</a></th>';
        }
        if (!$heads) {
            $rows1[] = '<th scope="row">&nbsp;</th>';
        }
    }
    if (has_capability('coursereport/statistics:isteacher', $context)) {
        $num = $usercount - $offset;
        foreach ($users as $user) {
            $row3 = "<tr><td>" . $num-- . "</td><td>" . fullname($user) . " </td>";
            $row3 .= "<td>" . $user->username . "</td>";
            foreach ($course_sections as $course_section) {
                
                $lcmssql = "select cm.id, cm.instance, mo.name, ctx.id as contextid , lc.type , lt.playtime,lt.progress from {course_modules} cm "
                . "JOIN {modules} mo on mo.id = cm.module and mo.name = 'lcms' and mo.visible = 1 "
                . "JOIN {context} ctx on ctx.instanceid = cm.id and ctx.contextlevel = :contextlevel "
                . "JOIN {lcms} lc on lc.id = cm.instance "
                . "LEFT JOIN {lcms_track} lt on lt.lcms = lc.id and lt.userid = :userid "
                . "where cm.course = :course and cm.section = :section ";
                $lcmsdata = $DB->get_records_sql($lcmssql, array('course' => $id,'userid'=>$user->id, 'contextlevel' => CONTEXT_MODULE, 'section' => $course_section->id));
                
                foreach ($lcmsdata as $lcms) {
                    $mod = $DB->get_record($lcms->name, array('id' => $lcms->instance));
                 
                    $row3 .= '<td>';
                    if ($lcms->type == 'video' || $lcms->type == 'embed' || $lcms->type == 'html2' || $lcms->type == 'media') {
                        $progress = ($lcms->progress) ? $lcms->progress."%" : "-";
                    } else {
                        $progress = ($lcms->progress == '100') ? "O" : "-";
                    }
                    $row3 .= $progress;
                    $row3 .= '</td>';
                }
                if (!$lcmsdata) {
                    $row3 .= '<td>-</td>';
                }
            }
            $row3 .= "</tr>";
            $rows3[] = $row3;
        }
    } else if (has_capability('coursereport/statistics:isstudent', $context)) {
        $user = $USER;
        $row3 = "<tr><td>" . fullname($user) . " </td>";
        $row3 .= "<td>" . $user->username . "</td>";
        foreach ($course_sections as $course_section) {
            $lcmssql = "select cm.id, cm.instance, mo.name, ctx.id as contextid , lc.type , lt.playtime,lt.progress from {course_modules} cm "
                . "JOIN {modules} mo on mo.id = cm.module and mo.name = 'lcms' and mo.visible = 1 "
                . "JOIN {context} ctx on ctx.instanceid = cm.id and ctx.contextlevel = :contextlevel "
                . "JOIN {lcms} lc on lc.id = cm.instance "
                . "JOIN {lcms_track} lt on lt.lcms = lc.id and lt.userid = :userid "
                . "where cm.course = :course and cm.section = :section ";
                $lcmsdata = $DB->get_records_sql($lcmssql, array('course' => $id,'userid'=>$user->id, 'contextlevel' => CONTEXT_MODULE, 'section' => $course_section->id));
                
                foreach ($lcmsdata as $lcms) {
                    $mod = $DB->get_record($lcms->name, array('id' => $lcms->instance));
                 
                    $row3 .= '<td>';
                    if ($lcms->type == 'video' || $lcms->type == 'embed' || $lcms->type == 'html2' || $lcms->type == 'media') {
                        $progress = ($lcms->progress) ? $lcms->progress."%" : "-";
                    } else {
                        $progress = ($lcms->progress == '100') ? "O" : "X";
                    }
                    $row3 .= $progress;
                    $row3 .= '</td>';
                }
                if (!$lcmsdata) {
                    $row3 .= '<td>-</td>';
                }
        }
        $row3 .= "</tr>";
        $rows3[] = $row3;
    }
    echo '</tr><tr>' . implode('', $rows1) . '</tr><tr>' . implode('', $rows2) . "</tr></thead><tbody>";
    foreach ($rows3 as $row) {
        echo $row;
    }
    echo "</tbody></table>";
    echo "</div>";
    

    $params = array('id' => $id, 'search' => $search, 'type' => $type, 'perpage' => $perpage);
    $total_page = ceil($usercount / $perpage);
    
print_paging_navbar_script($usercount, $page, $perpage, 'javascript:cata_page(:page);');
    ?>
<div class="stat_downbtn"><button onclick="location.href = '<?php echo $url; ?>'" class="red-form"><?php echo get_string('download'); ?></button></div>

    <script type="text/javascript">
        function list_active(obj, cls) {
            if ($(obj).attr('active') == 'off') {
                $('.' + cls).attr('active', 'on');
                switch (cls) {
                    case 'view_span':
                        $('.view_span_col').attr('style', '');
                        $('.write_span_col').attr('style', 'display:none;');
                        $('.comment_span_col').attr('style', 'display:none;');

                        $('.write_span').attr('active', 'off');
                        $('.comment_span').attr('active', 'off');
                        break;
                    case 'write_span':
                        $('.view_span_col').attr('style', 'display:none;');
                        $('.write_span_col').attr('style', '');
                        $('.comment_span_col').attr('style', 'display:none;');

                        $('.view_span').attr('active', 'off');
                        $('.comment_span').attr('active', 'off');
                        break;
                    case 'comment_span':
                        $('.view_span_col').attr('style', 'display:none;');
                        $('.write_span_col').attr('style', 'display:none;');
                        $('.comment_span_col').attr('style', '');

                        $('.write_span').attr('active', 'off');
                        $('.view_span').attr('active', 'off');
                        break;
                }
            } else {
                $('.view_span').attr('active', 'off');
                $('.write_span').attr('active', 'off');
                $('.comment_span').attr('active', 'off');
                $('.view_span_col').attr('style', '');
                $('.write_span_col').attr('style', '');
                $('.comment_span_col').attr('style', '');
            }
        }
    </script>
    <?php
    include_once ('../inc/footer.php');
} else {
    $filename = 'statistics_' . $id . '.xls';

    $workbook = new MoodleExcelWorkbook('-');
    $workbook->send($filename);

    $worksheet = array();

    $worksheet[0] = $workbook->add_worksheet('');

    $worksheet[0]->write(0, 0, get_string('stat_course', 'coursereport_statistics'));

    $worksheet[0]->write(1, 0, get_string('student', 'coursereport_statistics'));
    $worksheet[0]->write(1, 1, get_string('auditor', 'coursereport_statistics'));
    $worksheet[0]->write(1, 2, get_string('sectioncnt', 'coursereport_statistics'));
    $worksheet[0]->write(1, 3, get_string('lcmscnt', 'coursereport_statistics'));
    $worksheet[0]->write(1, 4, get_string('modcnt', 'coursereport_statistics'));

    $worksheet[0]->write(2, 0, $course_data->student);
    $worksheet[0]->write(2, 1, $course_data->auditor);
    $worksheet[0]->write(2, 2, $section_cnt);
    $worksheet[0]->write(2, 3, $course_data->lcmscount);
    $worksheet[0]->write(2, 4, $course_data->modcount);

    $worksheet[0]->write(3, 0, get_string('stat_edu', 'coursereport_statistics'));

    $worksheet[0]->write(4, 0, get_string('name', 'coursereport_statistics'));
    $worksheet[0]->write(4, 1, get_string('usernumber', 'coursereport_statistics'));
    $col = 2;
    $col2 = 2;
    $rows1 = array();
    foreach ($course_sections as $course_section) {
        $title = get_section_name($course_data->id, $course_section);
        $worksheet[0]->write(4, $col, $title);
        if ($course_section->colspan) {
            $col += $course_section->colspan * 3;
        } else {
            $col += 3;
        }
        $heads = $DB->get_records_sql($headsql, array('course' => $id, 'contextlevel' => CONTEXT_MODULE, 'section' => $course_section->id));
        foreach ($heads as $cm) {
            $mod = $DB->get_record($cm->name, array('id' => $cm->instance));
            $rows1[] = $mod->name;
        }
        if (!$heads) {
            $rows1[] = ' ';
        }
    }
    foreach ($rows1 as $row => $val) {
        $worksheet[0]->write(5, $col2, $val);
        $worksheet[0]->write(6, $col2, get_string('view', 'coursereport_statistics'));
        $worksheet[0]->write(6, $col2 + 1, get_string('write', 'coursereport_statistics'));
        $worksheet[0]->write(6, $col2 + 2, get_string('comment', 'coursereport_statistics'));
        $col2 += 3;
    }


    $row = 7;
    foreach ($users as $user) {
        $col3 = 0;
        $worksheet[0]->write($row, $col3++, fullname($user));
        $worksheet[0]->write($row, $col3++, $user->username);
        foreach ($course_sections as $course_section) {
            $heads = $DB->get_records_sql($headsql, array('course' => $id, 'contextlevel' => CONTEXT_MODULE, 'section' => $course_section->id));
            foreach ($heads as $cm) {
                $mod = $DB->get_record($cm->name, array('id' => $cm->instance));
                $read_cnt = $DB->get_field_sql('select read_count from {lmsdata_log} where courseid = :courseid and modname = :modname and write_count = 0 and cmid = :cmid and userid = :userid', array('courseid' => $id, 'modname' => $cm->name, 'userid' => $user->id, 'cmid' => $cm->id));
                $write_cnt = $DB->get_field_sql('select write_count from {lmsdata_log} where courseid = :courseid and modname = :modname and read_count = 0 and cmid = :cmid and userid = :userid', array('courseid' => $id, 'modname' => $cm->name, 'userid' => $user->id, 'cmid' => $cm->id));
                $worksheet[0]->write($row, $col3++, $read_cnt);
                $worksheet[0]->write($row, $col3++, $write_cnt);
                $worksheet[0]->write($row, $col3++, ' ');
            }
            if (!$heads) {
                $col3 += 3;
            }
        }
        $rows3[] = $row3;
        $row++;
    }




    $workbook->close();
    die;
}
