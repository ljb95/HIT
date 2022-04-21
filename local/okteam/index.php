<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once 'lib.php';
require_once $CFG->dirroot . '/local/okmanage/manage_form.php';

$id = optional_param('id', 0, PARAM_INT);  // Course ID
$grouping = optional_param('grouping', 0, PARAM_INT);  // grouping ID
$search = optional_param('search', '', PARAM_CLEAN);
$page = optional_param('page', 1, PARAM_INT);   // active page
$perpage = optional_param('perpage', 10, PARAM_INT);

$offset = ($page - 1) * $perpage;

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
$PAGE->requires->jquery();

$courseurl = new moodle_url('/course/view.php', array('id' => $course->id));

echo $OUTPUT->header();

$row[] = new tabobject('team', "$CFG->wwwroot/local/okteam/index.php?id=" . $id, get_string('team', 'local_okteam'));
$row[] = new tabobject('team_grouping', "$CFG->wwwroot/local/okteam/team_groups.php?id=" . $id, get_string('team_grouping', 'local_okteam'));
$rows[] = $row;

print_tabs($rows, 'team');
$team_groups = $DB->get_records('groupings', array('courseid' => $course->id));

$where = '';
$param = array('courseid' => $course->id);
if ($grouping) {
    $where = ' and gs.id = :grouping';
    $param['grouping'] = $grouping;
}
if ($search) {
    $where .= ' and g.name like :search';
    $param['search'] = '%' . $search . '%';
}

$sql = 'select g.*,(select count(id) from {groups_members} where groupid = g.id) as nou , gs.name as gname  '
        . 'from {groups} g '
        . 'left join {groupings_groups} gg on gg.groupid = g.id '
        . 'left join {groupings} gs on gs.id = gg.groupingid '
        . 'where g.courseid  = :courseid';
$group_count = $DB->count_records('groups', array('courseid' => $id));
$total_pages = ceil($group_count / $perpage);
$teams = $DB->get_records_sql($sql . $where, $param, $offset, $perpage);
?>
<form class="table-search-option">
    <input type="hidden" name="id" value="<?php echo $id; ?>">
    <label for="grouping"><?php echo get_string('team_grouping', 'local_okteam'); ?></label>
    <select name="grouping" id="grouping">
        <option value=""><?php echo get_string('all'); ?></option>
        <?php
        foreach ($team_groups as $team_group) {
            ?>
            <option <?php if ($grouping == $team_group->id) {
            echo 'selected';
        } ?> value="<?php echo $team_group->id; ?>"><?php echo $team_group->name; ?></option>
<?php } ?>
    </select>
    <br>
    <label for="search-text"><?php echo get_string('teamname', 'local_okteam'); ?></label>
    <input type="text" name="search" value="<?php echo $search; ?>" class="search-text" id="search-text" placeholder="<?php echo get_string('teamname', 'local_okteam'); ?>">
    <input type="submit" value="<?php echo get_string('search', 'local_okteam'); ?>" class="board-search"/>
</form>
<div class="options">
    <div>
        <input type="button" id="excell_upload" value="<?php echo get_string('excel_upload', 'local_okteam'); ?>">
        <input type="button" value="<?php echo get_string('manualcreation', 'local_okteam'); ?>" onclick="location.href = 'group.php?courseid=<?php echo $id; ?>'">
        <div id="excell_form" style="display: none;">
            <form action="excell.php" method="post" enctype="multipart/form-data">
                <input type="hidden" name="course" value="<?php echo $id ?>">
                <input type="file" name="excell">
                <a href="sample.xlsx" alt="Sample.xlsx" title="Sample.xlsx">[Sample] <?php echo get_string('download'); ?></a>
                <input type="submit" value="<?php echo get_string('save', 'local_okteam'); ?>">
            </form>
        </div>
    </div>
</div>
<table class="generaltable">
    <thead>
    <th><?php echo get_string('grouping', 'local_okteam'); ?></th>
    <th><?php echo get_string('teamname', 'local_okteam'); ?></th>
    <th><?php echo get_string('member', 'local_okteam'); ?></th>
    <th><?php echo get_string('management', 'local_okteam'); ?></th>
</thead>
<tbody>
<?php foreach ($teams as $team) { ?>
        <tr>
            <td><?php echo ($team->gname) ? $team->gname : '-'; ?></td>
            <td><?php echo $team->name; ?></td>
            <td><?php echo $team->nou; ?></td>
            <td>
                <input type="button" value="<?php echo get_string('add_member', 'local_okteam'); ?>" onclick="location.href = 'members.php?group=<?php echo $team->id; ?>'">
                <input type="button" value="<?php echo get_string('edit', 'local_okteam'); ?>" onclick="location.href = 'group.php?courseid=<?php echo $id; ?>&id=<?php echo $team->id; ?>'">
                <input type="button" value="<?php echo get_string('delete', 'local_okteam'); ?>" onclick="location.href = 'delete.php?courseid=<?php echo $id; ?>&groups=<?php echo $team->id; ?>'">
            </td>
        </tr>
        <?php
    }
    if (!$teams) {
        ?>
        <tr><td colspan="4"><?php echo get_string('empty_team', 'local_okteam'); ?></td></tr>
<?php } ?>
</tbody>
<tfoot>

</tfoot>
</table>
<?php
$page_params = array();
$page_params['id'] = $id;
$page_params['perpage'] = $perpage;
$page_params['search'] = $search;
$page_params['searchfield'] = $searchfield;
echo '<div class="table-footer-area">';
okteam_get_paging_bar($CFG->wwwroot . "/local/okteam/index.php", $page_params, $total_pages, $page);
echo '</div>';
?>
<script>
    var toggle = 0;
    $('#excell_upload').click(function () {
        if (toggle == 0) {
            $('#excell_form').show();
            toggle = 1;
        } else {
            $('#excell_form').hide();
            toggle = 0;
        }
    });
</script>
<?php
echo $OUTPUT->footer();
