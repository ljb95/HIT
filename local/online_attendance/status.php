<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->libdir . '/formslib.php';

$id = required_param('id', PARAM_INT); // course id
$page = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
$searchtext = optional_param('searchtext', '', PARAM_RAW);
$type = optional_param('type', 0, PARAM_INT); 
$excell = optional_param('excell', 0, PARAM_INT);

$course = $DB->get_record('course', array('id'=>$id), '*', MUST_EXIST);

require_course_login($course, true);
$context = context_course::instance($course->id);
$PAGE->set_context($context);

$url = new moodle_url('/local/online_attendance/index.php', array('id' => $id, 'type' => $type));
$PAGE->set_url($url);

$PAGE->set_pagelayout('incourse');
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->jquery_plugin('migrate');
$PAGE->requires->css('/local/online_attendance/style.css');

$strplural = get_string("pluginnameplural", "local_online_attendance");
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);

if(has_capability('local/online_attendance:progress_viewall', $context)) {

    //tab
    $row = array();
    $row[] = new tabobject('0', new moodle_url('/local/online_attendance/index.php', array('id' => $id, 'type' => 0)), get_string('lcms:progress', 'local_online_attendance'));
    $row[] = new tabobject('1', new moodle_url('/local/online_attendance/status.php', array('id' => $id, 'type' => 1)), get_string('attendance:status', 'local_online_attendance'));
    $row[] = new tabobject('2', new moodle_url('/local/online_attendance/setting.php', array('id' => $id, 'type' => 2)), get_string('attendance:setting', 'local_online_attendance'));
    $rows[] = $row;

    $PAGE->navbar->add($rows[0][$type]->text);
    $PAGE->set_heading($rows[0][$type]->text);

    echo $OUTPUT->header();

    print_tabs($rows, $type);
}

?>
        <!-- Table Area Start -->
    <form class="table-search-option" id="frm_course">
        <input type="text" name="searchtext" placeholder="<?php echo get_string('progress:search', 'local_online_attendance'); ?>" value="<?php echo $searchtext; ?>"/>
        <input type="submit" value="<?php echo get_string('button:search', 'local_online_attendance'); ?>"  class="board-search" />
    </form>
    <div class="table-filter-area attendance-filter">
        <div class="select-perpage">
            <select class="select perpage" name="perpage" onchange="change_perpage(this.options[this.selectedIndex].value);">
                <?php
                $nums = array(5,10,15,20);
                foreach ($nums as $num) {
                    $selected = '';
                    if($num == $perpage) {
                        $selected = ' selected';
                    }
                    echo '<option value="'.$num.'"'.$selected.'>'.get_string('showperpage', 'local_online_attendance', $num).'</option>';
                } ?>
            </select>
        </div>
        <div class="excell-button">
            <input type="button" value="<?php echo get_string('grade:reflect', 'local_online_attendance');?>" class="board-search" onclick='location.href="<?php echo './index.php?excell=1&type='.$type.'&id='.$id ?>"'/>
            <input type="button" value="<?php echo get_string('export:excel', 'local_online_attendance');?>" class="board-search" onclick='location.href="<?php echo './index.php?excell=1&type='.$type.'&id='.$id ?>"'/>
        </div>
    </div>   

<?php
echo $OUTPUT->footer();
?>