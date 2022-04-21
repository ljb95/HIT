<?php

require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once 'lib.php';
require_once $CFG->dirroot . '/local/okmanage/manage_form.php';

$id = optional_param('id', 0, PARAM_INT);  // Course ID
$grouping = optional_param('grouping', 0, PARAM_INT);  // grouping ID
$search = optional_param('search', '', PARAM_CLEAN);  
$page = optional_param('page', 1, PARAM_INT);   // active page
$perpage = optional_param('perpage', 10, PARAM_INT);

$offset = ($page -1) * $perpage;

$context = get_context_instance(CONTEXT_COURSE, $id);

require_login();

$PAGE->set_context($context);
$PAGE->set_url('/local/okteam/index.php?id=' . $id);
$PAGE->set_pagelayout('incourse');


$course = get_course($id);
$PAGE->set_course($course);


if (!has_capability('moodle/course:manageactivities', $context)) {
    return;
}

$strplural = get_string("pluginname", "local_okteam");
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading($strplural);

$courseurl = new moodle_url('/course/view.php', array('id' => $course->id));

echo $OUTPUT->header();

$row[] = new tabobject('team', "$CFG->wwwroot/local/okteam/index.php?id=" . $id, get_string('team', 'local_okteam'));
$row[] = new tabobject('team_grouping', "$CFG->wwwroot/local/okteam/team_groups.php?id=" . $id, get_string('team_grouping', 'local_okteam'));
$rows[] = $row;

print_tabs($rows, 'team_grouping');
 

$where = 'where courseid = :courseid ';
$param = array('courseid'=>$course->id);
if($search){
    $where .= ' and name like :search';
    $param['search'] = '%'.$search.'%';
}

$offset = ($page - 1) * $perpage;

$team_groups = $DB->get_records_sql('select * from {groupings} '.$where ,$param,$offset,$perpage);
$grouping_count = $DB->count_records('groupings',array('courseid'=>$id));
$total_pages = ceil($grouping_count / $perpage);
$num = $grouping_count - $offset;
?>
            <form class="table-search-option">
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <input type="text" name="search" value="<?php echo $search; ?>" class="search-text" placeholder="<?php echo get_string('team_groupname','local_okteam'); ?>">
                <input type="submit" value="<?php echo get_string('search', 'local_okteam'); ?>" class="board-search"/>
                <input class="fl_right" type="button" value="<?php echo get_string('add_teamgroup', 'local_okteam'); ?>" onclick="location.href='grouping.php?courseid=<?php echo $id; ?>'">
            </form> 
<table class="generaltable"> 
    <thead>
        <th><?php echo get_string('num','local_okteam'); ?></th>
        <th><?php echo get_string('grouping','local_okteam'); ?></th>
        <th><?php echo get_string('teamname','local_okteam'); ?></th>
        <th><?php echo get_string('management','local_okteam'); ?></th>
    </thead>
    <tbody>
        <?php
            foreach($team_groups as $grouping){ 
        ?>
        <tr>
            <td><?php echo $num--; ?></td>
            <td><?php echo $grouping->name; ?></td>
            <td>
               <?php 
                    $sql = 'select * from {groups} g '
                            . 'join {groupings_groups} mg on mg.groupid = g.id and groupingid = :groupingid';
                    $groups = $DB->get_records_sql($sql,array('groupingid'=>$grouping->id));
                    $group_name = '';
                    foreach($groups as $group){
                        $group_name .= $group->name.',';
               ?> 
               <?php } echo rtrim($group_name,','); ?>
            </td>
            <td>
                <input type="button" value="<?php echo get_string('addteam','local_okteam'); ?>" onclick="location.href='assign.php?id=<?php echo $grouping->id;?>'">
                <input type="button" value="<?php echo get_string('edit','local_okteam'); ?>" onclick="location.href='grouping.php?courseid=<?php echo $id;?>&id=<?php echo $grouping->id; ?>'">
                <input type="button" value="<?php echo get_string('delete'); ?>" onclick="location.href='delete.php?courseid=<?php echo $id;?>&groups=<?php echo $grouping->id; ?>'">
            </td>
        </tr>
        <?php
        } 
        if(!$team_groups){
        ?>
        <tr><td colspan="4"><?php echo get_string('empty_grouping','local_okteam'); ?></td></tr>
        <?php } ?>
    </tbody>
    <tfoot>
        
    </tfoot>
    </table>
<?php
    $page_params = array();
    $page_params['id'] = $courseid;
    $page_params['perpage'] = $perpage;
    $page_params['search'] = $search;
    $page_params['searchfield'] = $searchfield;
    echo '<div class="table-footer-area">';
    okteam_get_paging_bar($CFG->wwwroot . "/grade/report/yieldgrader/index.php", $page_params, $total_pages, $page);
    echo '</div>';
    
echo $OUTPUT->footer();