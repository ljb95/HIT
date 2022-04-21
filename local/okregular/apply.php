<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/okregular/lib.php';
require_once $CFG->libdir . '/formslib.php';
if(!is_siteadmin()){
    echo "<script>history.back();</script>";
}
require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->jquery_plugin('migrate');

$PAGE->set_url('/local/okregular/apply.php');

$strplural = get_string("pluginnameplural", "local_okregular");
$PAGE->navbar->add($strplural);
$PAGE->navbar->add(get_string('sititon:apply','local_okregular'));
$PAGE->set_title($strplural);
$PAGE->set_heading(get_string('sititon:apply','local_okregular'));

echo $OUTPUT->header();

$page = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
$searchtext = optional_param('searchval', '', PARAM_RAW);
$search = optional_param('searchfield', 3, PARAM_INT);
$current_course = optional_param('courseid', 1, PARAM_INT);

$currentlang = current_language();
//수강신청이 가능한 강의만 보여줌
$sql_select = "SELECT mc.id
     , mc.fullname AS course_name
     , lc.eng_lec_name AS course_name_eng
     , lc.subject_id, lc.year, lc.term 
     , lc.domain
     , lc.timeregstart 
     , lc.timeregend
     , lc.timestart 
     , lc.timeend
     , lc.prof_userid
     , ca.name AS category_name 
     , ca.path AS category_path 
     , u.firstname, u.lastname, lu.eng_name
     , en.enrol
     , ue.status as userenrolstatus 
     , (SELECT COUNT(*) 
        FROM {role_assignments} ra
        WHERE ra.contextid = ctx.id
          AND ra.userid = :userid) AS enroled ";
$sql_from = " FROM {course} mc
JOIN {context} ctx ON ctx.instanceid = mc.id AND ctx.contextlevel = :contextlevel 
JOIN {lmsdata_class} lc ON lc.course = mc.id 
JOIN {course_categories} ca ON ca.id = mc.category
JOIN {enrol} en ON en.courseid = mc.id AND en.enrol = 'manual' and en.status = 0  
LEFT JOIN {user} u ON u.id = lc.prof_userid 
LEFT JOIN {lmsdata_user} lu ON lu.userid = u.id  
LEFT JOIN {user_enrolments} ue ON ue.enrolid = en.id and ue.userid = :enroluserid";

$sql_conditions = array('lc.isnonformal = :isnonformal');
$sql_params = array(
    'contextlevel' => CONTEXT_COURSE,
    'userid' => $USER->id,
    'enroluserid' => $USER->id,
    'isnonformal' => COURSE_TYPE);

if (!empty($searchtext)) {
    switch ($search) {
        case 1: // 강의코드
            $sql_conditions[] = $DB->sql_like('lc.subject_id', ':subject_id');
            $sql_params['subject_id'] = '%' . $searchtext . '%';
            break;
        case 2: // 교수명
            $searchname = local_okregular_search_user_name($searchtext);            
            $sql_conditions[] = $searchname->where;
            $sql_params = array_merge($sql_params,$searchname->params);
            break;
        case 3; // 강의명
            $sql_conditions[] = $DB->sql_like('mc.fullname', ':course_name');
            $sql_params['course_name'] = '%' . $searchtext . '%';
            break;
        default:
            break;
    }
}

$year = get_config('moodle', 'haxa_year');
$term = get_config('moodle', 'haxa_term');

//년도가 없을 경우
if (!$year)
    $year = date('Y');

//검색으로 나오는 년도, 학기
$syear = optional_param('syear', $year, PARAM_INT);
$sterm = optional_param('sterm', $term, PARAM_INT);
$sql_conditions[] = 'lc.year = :year';
$sql_params['year'] = $syear;
if ($sterm != 0) {
    $sql_conditions[] = 'lc.term = :term';
    $sql_params['term'] = $sterm;
}

$sql_where = ' WHERE ' . implode(' AND ', $sql_conditions);

$sql_orderby = ' ORDER BY ca.sortorder asc, lc.subject_id, mc.fullname asc';

$totalcount = $DB->count_records_sql('SELECT COUNT(*) ' . $sql_from . $sql_where, $sql_params);
$courses = $DB->get_records_sql($sql_select . $sql_from . $sql_where . $sql_orderby, $sql_params, ($page - 1) * $perpage, $perpage);
$context = context_system::instance();
$isadmin = has_capability('moodle/site:config', $context);

?>
<!-- Table Area Start -->
<form class="table-search-option" id="frm_course">
    <select title="year" name="syear">
        <?php
        for ($y = $year; $y >= 2015; $y--) {
            if ($y == $syear) {
                $selecte_y = "selected";
            } else {
                $selecte_y = "";
            }
            echo '<option value="' . $y . '" ' . $selecte_y . '>' . get_string('year', 'local_okregular', $y) . '</option>';
        }
        ?>
    </select>
    <select title="term" name="sterm">
        <?php
        $terms = local_okregular_get_terms();
        foreach ($terms as $k => $t) {
            if ($k == $sterm) {
                $selecte_t = "selected";
            } else {
                $selecte_t = "";
            }
            echo '<option value="' . $k . '" ' . $selecte_t . '>' . $t . '</option>';
        }
        ?>
    </select>
    <br/>
    <select title="lecture" name="searchfield">
        <option value="1" <?php echo ($search == 1) ? 'selected' : ''; ?>><?php echo get_string('course:subjectid', 'local_okregular'); ?></option>
        <option value="2" <?php echo ($search == 2) ? 'selected' : ''; ?>><?php echo get_string('professor', 'local_okregular'); ?></option>
        <option value="3" <?php echo ($search == 3) ? 'selected' : ''; ?>><?php echo get_string('course:name', 'local_okregular'); ?></option>
    </select>
    <input type="text" title="search" name="searchval" placeholder="<?php echo get_string('search:keywords', 'local_okregular'); ?>" value="<?php echo $searchtext; ?>"/>
    <input type="submit" value="<?php echo get_string('search', 'local_okregular'); ?>"  class="board-search" onclick="javascript:course_all_select_submit();"/>
    <input type="hidden" name = "page" value="1">
    <input type="hidden" name = "perpage" value="<?php echo $perpage; ?>">
</form>
<!-- Table Search Option -->
<div class="table-header-area">
</div>
<!-- Table Header -->
<div class="table-filter-area">
    <form method="get" action="#" id="frm_perpage">
        <div>
            <select title="page" class="select perpage" name="perpage" onchange="change_perpage(this.options[this.selectedIndex].value);">
<?php
$nums = array(10, 20, 30, 50);
foreach ($nums as $num) {
    $selected = '';
    if ($num == $perpage) {
        $selected = ' selected';
    }
    echo '<option value="' . $num . '"' . $selected . '>' . get_string('showperpage', 'local_okregular', $num) . '</option>';
}
?>
            </select>
        </div>
    </form>
</div>
<!-- Table Filter --> 

<!-- Table Start -->
<table class="generaltable" id="table_courses">
    <caption class="hidden-caption">청강신청</caption>
    <thead>
        <tr>
            <th scope="row" width="5%"><?php echo get_string('no', 'local_okregular'); ?></th>
            <th scope="row" width="10%"><?php echo get_string('year:sel', 'local_okregular'); ?></th>
            <th scope="row" width="10%"><?php echo get_string('term:sel', 'local_okregular'); ?></th>
            <th scope="row" width="15%"><?php echo get_string('course:subjectid', 'local_okregular'); ?></th>
            <th scope="row"><?php echo get_string('course:name', 'local_okregular'); ?></th>
            <th scope="row" width="15%"><?php echo get_string('course:professor', 'local_okregular'); ?></th>
            <?php if($year == $syear && $term == $sterm){?>
            <th scope="row" width="10%"><?php echo get_string('apply:status', 'local_okregular'); ?></th>
            <th scope="row" width="15%"><?php echo get_string('sititon:apply', 'local_okregular'); ?></th>
            <?php }?>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($totalcount > 0) {
            $root_categories = $DB->get_records_menu('course_categories', array('parent' => 0), '', 'id, name');

            $possible = get_string('possible', 'local_okregular');
            $impossible = get_string('impossible', 'local_okregular');

            $rowno = $totalcount - ($page - 1) * $perpage;
            $rowcount = 0;
            foreach ($courses as $course) {
                $paths = array_filter(explode('/', $course->category_path));
                $categoryid = array_shift($paths);
                $category_name = $root_categories[$categoryid];

                $rowcount += 1;
                $disabled = 'disabled = "true"';

                $subjectids = explode('-', $course->subject_id);
                
                if ($course->enroled == 0) {
                    $regedstatustext = get_string('apply:possible','local_okregular');
                    $regedbutton = '<button onclick="apply_enrol(' . $course->id . ');" class="blue_btn_small">' . get_string('apply', 'local_okregular') . '</button>';
                } else {
                    if ($course->userenrolstatus == 0) {
                        $regedstatustext = get_string('course:registered', 'local_okregular');
                        $regedbutton = '<button class="gray_btn_small">'.get_string('course:registered','local_okregular').'</button>';
                    } else if ($course->userenrolstatus == 1) {
                        $regedstatustext = get_string('course:approval', 'local_okregular');
                        $regedbutton = '<button class="gray_btn_small" onclick="apply_enrol_cancel(' . $course->id . ');">'.get_string('cancel','local_okregular').'</button>';
                    }
                }

                ?>
                <tr>
                    <td scope="col"><?php echo $rowno--;?></td>
                    <td scope="col"><?php echo get_string('year','local_okregular',$course->year); ?></td>
                    <td scope="col">
                        <?php
                            if ($course->term == 1 || $course->term == 2) {
                             $term = $i . get_string('term', 'local_lmsdata');
                            } else if ($course->term == 3 || $course->term == 4) {
                                $term = str_replace(array(3, 4), array(get_string('summer', 'local_okregular')), $course->term);
                            } else {
                                $term = '-';
                            }
                            echo ($course->term == 0)? get_string('term:all','local_okregular'):$term; 
                        ?>
                    </td>
                    <td scope="col"><?php echo (!$course->subject_id)? '-':$course->subject_id;?></td>
                    <td scope="col" class="title"><a href="courseinfo.php?id=<?php echo $course->id?>"><?php echo $course->course_name; ?></a></td>
                    <td scope="col">
                        <?php
                        if (!empty($course->prof_userid)) {
                            $prof_name = (current_language()=='ko')? fullname($course):$course->eng_name;
                            echo '<div class="prof_name" style="padding-bottom: 4px">' . $prof_name . '</div>';
                        } else {
                            echo '-';
                        }
                        ?>

                    </td>
                    <?php if($year == $syear && $term == $sterm){?>
                    <td scope="col" id="reg_status_<?php echo $course->id?>"><?php echo $regedstatustext;?></td>
                    <td scope="col" id="reg_button_<?php echo $course->id?>"><?php echo $regedbutton;?></td>
                    <?php } else {?>
                    <td scope="col" id="reg_status_<?php echo $course->id?>">기간아님</td>
                    <td scope="col" id="reg_button_<?php echo $course->id?>">-</td>
                    <?php } ?>
                </tr>
                <?php
            }
        } else {
            $colspan = ($year == $syear && $term == $sterm)? '8':'6';
            echo '<tr><td colspan="'.$colspan.'">' . get_string('course:empty', 'local_okregular') . '</td></tr>';
        }
        ?>
    </tbody>
</table>
<!-- Table End -->

<div class="table-footer-area">
    <?php
    local_okregular_print_paging_navbar_script($totalcount, $page, $perpage, 'javascript:goto_page(:page);', 10);
    ?>
</div>

<script type="text/javascript">
     function apply_enrol(id){
        
        if(!confirm('<?php echo get_string('sititon:doyouwantto', 'local_okregular'); ?>')) {
            return false;
        }
        
        jQuery.ajax({
            url: "<?php echo $CFG->wwwroot.'/local/okregular/apply_enrol.ajax.php'; ?>",
            data: {
                    id: id,
                    type: 1
            },
            dataType: "json",
            type: "POST",
            async: false,
            success: function(data) {
                if(data.status == 'success') {
                    alert(data.message);
                    $(this).attr("checked", false);
                    $(this).attr("disabled", true);                        
                    $("td#reg_status_"+id).html("<?php echo get_string('course:approval', 'local_okregular'); ?>");
                    $("td#reg_button_"+id).html("<button class='gray_btn_small' onclick='apply_enrol_cancel("+id+");'><?php echo ''.get_string('cancel','local_okregular');?></button>");
                }else{
                    alert(data.message);
                }
            }
	});
    }
    
    function apply_enrol_cancel(id){
        
        if(!confirm('<?php echo get_string('apply:doyouwantcancel', 'local_okregular'); ?>')) {
            return false;
        }
        
        jQuery.ajax({
            url: "<?php echo $CFG->wwwroot.'/local/okregular/apply_enrol.ajax.php'; ?>",
            data: {
                    id: id,
                    type: 2
            },
            dataType: "json",
            type: "POST",
            async: false,
            success: function(data) {
                if(data.status == 'success') {
                    alert(data.message);
                    $(this).attr("checked", false);
                    $(this).attr("disabled", true);                        
                    $("td#reg_status_"+id).html("<?php echo get_string('apply:possible', 'local_okregular'); ?>");
                    $("td#reg_button_"+id).html("<button class='blue_btn_small' onclick='apply_enrol("+id+");'><?php echo ''.get_string('apply','local_okregular');?></button>");
                }else{
                    alert(data.message);
                }
            }
	});
    }
    
    function course_all_select_submit(){
        $('#frm_course').submit();
    }
    
    function category_parent1_changed(parent, frm_id) {
        var mform = document.getElementById(frm_id);
        var elemCate = mform["parent2"];
        
        remove_select_options(elemCate);
        
        var categories = category_get_parent(parent);
	jQuery.each(categories, function (index, value) {
		var elOptNew = document.createElement('option');
		elOptNew.text = value.name;
                elOptNew.value = value.id;
		elemCate.add(elOptNew);
	});
        
        var elemSel = mform["parent3"];
        if(elemSel != undefined) {
            remove_select_options(elemSel);
        }
        
    }
     
    function category_parent2_changed(parent, frm_id) {
        var mform = document.getElementById(frm_id);
        var elemCate = mform["parent3"];
        
        remove_select_options(elemCate);
        
        var categories = category_get_parent(parent);
	jQuery.each(categories, function (index, value) {
		var elOptNew = document.createElement('option');
		elOptNew.text = value.name;
                elOptNew.value = value.id;
		elemCate.add(elOptNew);
	});
        
    }
    
    function category_get_parent(parent) {
	var categories = null;

	jQuery.ajax({
		url: "<?php echo $CFG->wwwroot.'/local/okregular/parent.ajax.php'; ?>",
		data: {
			"parent" : parent
		},
		dataType: "json",
		type: "POST",
		async: false,
		success: function(data) {
			categories = data;
		}
	});

	return categories;
    }
         
    function remove_select_options(elSel) {
        for(var i = elSel.length - 1; i > 0 ; i--) {
            elSel.remove(i);
        }
    }
    
    function goto_page(page) {
        $('[name=page]').val(page);
        $('#frm_course').submit();
    }
    
    function change_perpage(perpage) {
        $('[name=perpage]').val(perpage);
        $('#frm_course').submit();
    }
    
</script>

<?php
echo $OUTPUT->footer();
?>