<?php
require_once("../../config.php");



require_login();


$PAGE->set_url('/local/certi/index.php');
$PAGE->set_pagelayout('standard');

$context = context_system::instance();
$PAGE->set_context($context);

$strplural = get_string("pluginname", "local_certi");
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading($strplural);

$page = optional_param('page', 1, PARAM_INT);

 echo $OUTPUT->header();
 
 $search = optional_param('search', '', PARAM_RAW);
 $perpage = optional_param('perpage', 10, PARAM_INT);
 $page = optional_param('page', 1, PARAM_INT);
 
 $coursesql = "select lc.id, lc.timestart, lc.timeend, lc.kor_lec_name, lc.course as courseid "
         . "from {lmsdata_class} lc";
 $count_coursesql = "select count(id) "
         . "from {lmsdata_class} lc";
 
 $courses = $DB->get_records_sql($coursesql);
 $count_courses = $DB->count_records_sql($count_coursesql);
 
?>
<h3 class="tab_title"></h3>
<form class="table-search-option" method="get">
    <input type="hidden" name="page" value="1">
    <input type="hidden" name="perpage" value="<?php echo $perpage; ?>">
    <label for="search" class="hidden-label">search</label>
    <input type="text" name="search" id="search" value="<?php echo $search; ?>"  placeholder="<?php echo get_string('search'); ?>">
    <input class="btn_st01" type="submit" class="board-search" value="<?php echo get_string('search'); ?>" onclick='' />
</form> <!-- Search Area End -->

    <table cellpadding="0" cellspacing="0" class="generaltable">
        <caption class="hidden-caption">resourses</caption>
        <thead>
            <tr>
                <th scope="row" width="10%" class="mobile">번호</th>
                <th scope="row" width="10%" class="title">과정명</th>
                <th scope="row" width="10%" class="mobile">학습기간</th>
                <th scope="row" width="10%" class="mobile">수료증발급</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $offset = ($page -1) * $perpage;
            $startnum = $count_courses - $offset;
            if($courses){
                foreach($courses as $course){
                    $timeend = ($course->timeend > 100000)? date('Y-m-d',$course->timeend) : '';
                    ?>
                <tr>
                    <td scope="col" class="mobile"><?php echo $startnum--; ?></td>
                    <td scope="col" class="title"><?php echo $course->kor_lec_name; ?></td>
                    <td scope="col" class="mobile"><?php echo date('Y-m-d',$course->timestart).' ~ '. $timeend;  ?></td>
                    <td scope="col" class="mobile"><a href="/local/certi/certi_preview.php?class=<?php echo $course->courseid ?>"><input type="button"></a></td>
                </tr>
            <?php 
                }
            }else{
                echo '<td colspan="4">이수 내역이 없습니다.</td>';
            }
            ?>
            

        </tbody>
    </table>

<?php
echo $OUTPUT->footer();
?>