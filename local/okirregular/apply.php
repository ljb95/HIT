<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/okirregular/lib.php';
require_once $CFG->libdir . '/formslib.php';

require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->jquery_plugin('migrate');

$PAGE->set_url('/local/okirregular/apply.php');

$strplural = get_string("pluginnameplural", "local_okirregular");
$PAGE->navbar->add($strplural);
$PAGE->navbar->add(get_string('course:apply','local_okirregular'));
$PAGE->set_title($strplural);
$PAGE->set_heading(get_string('course:apply','local_okirregular'));

echo $OUTPUT->header();

$page = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
$searchtext = optional_param('searchval', '', PARAM_RAW);
$search = optional_param('searchfield', 3, PARAM_INT);
$current_course = optional_param('courseid', 1, PARAM_INT);
$type = optional_param('type', 1, PARAM_INT); //1: 진행, 2: 종료

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

$sql_conditions = array('lc.isnonformal = :isnonformal','lc.isreged = 1');
$sql_params = array(
    'contextlevel' => CONTEXT_COURSE,
    'userid' => $USER->id,
    'enroluserid' => $USER->id,
    'isnonformal' => COURSE_TYPE);

$currentdate = time();
if($type == 1){
    //진행
    $sql_conditions[] = "lc.timeregstart <= :timestart and lc.timeregend >= :timeend";
}else if($type == 2){
    //종료
    $sql_conditions[] = "lc.timeregend < :timeend";
}

$sql_params['timestart'] = $currentdate;
$sql_params['timeend'] = $currentdate;
//강의명 검색
if($searchtext){
    $sql_conditions[] = $DB->sql_like('mc.fullname', ':course_name');
    $sql_params['course_name'] = '%' . $searchtext . '%';
}

$sql_where = ' WHERE ' . implode(' AND ', $sql_conditions);

$sql_orderby = ' ORDER BY mc.id desc';

$totalcount = $DB->count_records_sql('SELECT COUNT(*) ' . $sql_from . $sql_where, $sql_params);
$courses = $DB->get_records_sql($sql_select . $sql_from . $sql_where . $sql_orderby, $sql_params, ($page - 1) * $perpage, $perpage);
$context = context_system::instance();
$isadmin = has_capability('moodle/site:config', $context);

//tab
$row = array();
$row[] = new tabobject('1', "$CFG->wwwroot/local/okirregular/apply.php?type=1", get_string('ongoing', 'local_okirregular'));
$row[] = new tabobject('2', "$CFG->wwwroot/local/okirregular/apply.php?type=2", get_string('finish', 'local_okirregular'));
$rows[] = $row;

print_tabs($rows, $type);

?>
<!-- Table Area Start -->
<form class="table-search-option" id="frm_course">
    <input type="text" title="search" name="searchval" placeholder="<?php echo get_string('search:coursename', 'local_okirregular'); ?>" value="<?php echo $searchtext; ?>"/>
    <input type="submit" value="<?php echo get_string('search', 'local_okirregular'); ?>"  class="board-search" onclick="javascript:course_all_select_submit();"/>
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
            <select class="select perpage" title="perpage" name="perpage" onchange="change_perpage(this.options[this.selectedIndex].value);">
<?php
$nums = array(10, 20, 30, 50);
foreach ($nums as $num) {
    $selected = '';
    if ($num == $perpage) {
        $selected = ' selected';
    }
    echo '<option value="' . $num . '"' . $selected . '>' . get_string('showperpage', 'local_okirregular', $num) . '</option>';
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
            <th scope="row" width="5%"><?php echo get_string('no', 'local_okirregular'); ?></th>
            <th scope="row"><?php echo get_string('course:name', 'local_okirregular'); ?></th>
            <th scope="row" width="25%"><?php echo get_string('course:open', 'local_okirregular'); ?></th>
            <th scope="row" width="15%"><?php echo get_string('course:professor', 'local_okirregular'); ?></th>
            <?php if($type==1){?>
            <th scope="row" width="10%"><?php echo get_string('apply:status', 'local_okirregular'); ?></th>
            <th scope="row" width="15%"><?php echo get_string('course:apply', 'local_okirregular'); ?></th>
            <?php }?>
        </tr>
    </thead>
    <tbody>
        <?php
        if ($totalcount > 0) {
            $root_categories = $DB->get_records_menu('course_categories', array('parent' => 0), '', 'id, name');

            $possible = get_string('possible', 'local_okirregular');
            $impossible = get_string('impossible', 'local_okirregular');

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
                    $regedstatustext = get_string('apply:possible','local_okirregular');
                    $regedbutton = '<button onclick="apply_enrol(' . $course->id . ');" class="blue_btn_small">' . get_string('apply', 'local_okirregular') . '</button>';
                } else {
                    if ($course->userenrolstatus == 0) {
                        $regedstatustext = get_string('course:registered', 'local_okirregular');
                        $regedbutton = '<button class="gray_btn_small">'.get_string('course:registered','local_okirregular').'</button>';
                    } else if ($course->userenrolstatus == 1) {
                        $regedstatustext = get_string('course:approval', 'local_okirregular');
                        $regedbutton = '<button class="gray_btn_small" onclick="apply_enrol_cancel(' . $course->id . ');">'.get_string('cancel','local_okirregular').'</button>';
                    }
                }
                ?>
                <tr>
                    <td scope="col"><?php echo $rowno--;?></td>
                    <td scope="col" class="title">
                    <a href="courseinfo.php?id=<?php echo $course->id?>"><?php echo $course->course_name; ?></a>
                    </td>
                    <td scope="col">
                        <?php
                        $timestart = date('Y-m-d',$course->timestart);
                        $timeend = date('Y-m-d',$course->timeend);
                        echo $timestart.' ~ '.$timeend;
                        ?>
                    </td>
                    <td scope="col">
                        <?php
                        $user_query = "select u.* ";
                        $user_queryfrom = "from {course} c
                            join {context} ctx on ctx.contextlevel = 50 and ctx.instanceid = c.id 
                            join {role_assignments} ra on ra.contextid = ctx.id AND ra.roleid = (SELECT id FROM {role} WHERE shortname = 'editingteacher')
                            join {user} u on u.id = ra.userid and u.deleted = 0
                        where c.id = :courseid 
                           ";
                        $teachers = $DB->get_records_sql($user_query.$user_queryfrom, array('courseid' => $course->id));
                        /*
                        if (!empty($course->prof_userid)) {
                            $prof_name = (current_language()=='ko')? fullname($course):$course->eng_name;
                            echo '<div class="prof_name" style="padding-bottom: 4px">' . $prof_name . '</div>';
                        } else {
                            echo '-';
                        }
                         */
                        if($teachers){
                            $teacherlist = "";
                            foreach($teachers as $teacher){
                                $teacherlist .= $teacher->firstname.', ';
                            }
                            echo '<div class="prof_name" style="padding-bottom: 4px">'.substr($teacherlist , 0, -2).'</div>';
                        }else{
                            echo '-';
                        }
                        ?>
                    </td>
                    <?php if($type==1){?>
                    <td scope="col" id="reg_status_<?php echo $course->id?>"><?php echo $regedstatustext;?></td>
                    <td scope="col" id="reg_button_<?php echo $course->id?>"><?php echo $regedbutton;?></td>
                    <?php }?>
                </tr>
                <?php
            }
        } else {
            $colspan = $type==1? '6':'4';
            echo '<tr><td colspan="'.$colspan.'">' . get_string('course:empty', 'local_okirregular') . '</td></tr>';
        }
        ?>
    </tbody>
</table>
<!-- Table End -->

<div class="table-footer-area">
    <?php
    local_okirregular_print_paging_navbar_script($totalcount, $page, $perpage, 'javascript:goto_page(:page);', 10);
    ?>
</div>

<script type="text/javascript">
    
    function apply_enrol(id){
        
        if(!confirm('<?php echo get_string('apply:doyouwantto', 'local_okirregular'); ?>')) {
            return false;
        }
        
        jQuery.ajax({
            url: "<?php echo $CFG->wwwroot.'/local/okirregular/apply_enrol.ajax.php'; ?>",
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
                    $("td#reg_status_"+id).html("<?php echo get_string('course:approval', 'local_okirregular'); ?>");
                    $("td#reg_button_"+id).html("<button class='gray_btn_small' onclick='apply_enrol_cancel("+id+");'><?php echo ''.get_string('cancel','local_okirregular');?></button>");
                }else{
                    alert(data.message);
                }
            }
	});
    }
    
    function apply_enrol_cancel(id){
        
        if(!confirm('<?php echo get_string('apply:doyouwantcancel', 'local_okirregular'); ?>')) {
            return false;
        }
        
        jQuery.ajax({
            url: "<?php echo $CFG->wwwroot.'/local/okirregular/apply_enrol.ajax.php'; ?>",
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
                    $("td#reg_status_"+id).html("<?php echo get_string('apply:possible', 'local_okirregular'); ?>");
                    $("td#reg_button_"+id).html("<button class='blue_btn_small' onclick='apply_enrol("+id+");'><?php echo ''.get_string('apply','local_okirregular');?></button>");
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
		url: "<?php echo $CFG->wwwroot.'/local/okirregular/parent.ajax.php'; ?>",
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