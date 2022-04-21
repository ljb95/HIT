<?php
//require_once $CFG->dirroot.'/local/haksa/config.php';
//require_once $CFG->dirroot.'/local/haksa/lib.php';
require_once dirname(dirname(__FILE__)) . '/lib/paging.php';

$tabs = participant_sync_tabs();

$year = optional_param('year', $year, PARAM_INT);
$term = optional_param('term', $term, PARAM_INT);

$currpage = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
$search = optional_param('search', 2, PARAM_INT);
$searchtext = optional_param('searchtext', '', PARAM_TEXT);

$tab2 = optional_param('tab2', '0', PARAM_INT);

$haksa = $DB->get_record('haksa', array('year' => $year, 'term' => $term));
?>
<div class="content_navigation">
    <?php
        foreach($tabs AS $i=>$t) {
            $css_class = $t['class'];
            if($tab2 == $i) {
                $css_class .= ' '.$css_class.'_selected';
            }
            echo '<a href="sync.php?tab=2&tab2='.$i.'"><p class="'.$css_class.'">'.$t['text'].'</p></a>';
        }
    ?>
</div>
<?php 
if($tab2 == 0){
?>
    <h4 class="page_sub_title">강의 참여자 동기화</h4>
    <form name="" id="course_search" class="search_area" action="sync.php" method="get">
        <input type="hidden" name="page" value="1" />
        <input type="hidden" name="tab" value="<?php echo $tab; ?>" />

        <label style="margin-right: 5px;"><font color="#F00A0D" size="3px;"><strong>*</strong></font> <?php echo get_string('year2','local_lmsdata'); ?></label>
        <select class="w_90" onchange="#" name="year">
            <?php
            $years = siteadmin_get_years();
            foreach ($years as $v => $y) {
                $selected = '';
                if ($v == $year) {
                    $selected = ' selected';
                }
                echo '<option value="' . $v . '"' . $selected . '> ' . $y . '</option>';
            }
            ?>
        </select>

        <label style="margin-right: 5px;"><font color="#F00A0D" size="3px;"><strong>*</strong></font> <?php echo get_string('stats_terms','local_lmsdata'); ?></label>
        <select class="w_90" onchange="#" name="term">
            <?php
            $terms = siteadmin_get_terms_sync();
            foreach ($terms as $v => $t) {
                $selected = '';
                if ($v == $term) {
                    $selected = ' selected';
                }
                echo '<option value="' . $v . '"' . $selected . '> ' . $t . '</option>';
            }
            ?>
        </select>
        <input type="submit" class="blue_btn" style="margin-right: 10px;" value="<?php echo get_string('okay','local_lmsdata'); ?>"/>

        <br/>
        <!--label style="margin-right: 40px;">&nbsp;</label-->
        <div id ="participant_label" class = "left" style = "padding-top: 5px;">
            <label><input type="radio" name="search" value="2"<?php if ($search == 2) { ?> checked<?php } ?>/>과목코드</label>
            <label><input type="radio" name="search" value="3"<?php if ($search == 3) { ?> checked<?php } ?>/>과목명 <label>
        </div>
        <input type="text" name="searchtext" value="<?php echo $searchtext; ?>" placeholder=" <?php echo get_string('search','local_lmsdata'); ?> " class="w_260" style="color: #8E9094; margin:0 0 5px 15px;" />
        <input type="submit" class="blue_btn" value="<?php echo get_string('search','local_lmsdata'); ?>" onclick="#" style="margin:0 0 5px 5px;"/>
    </form><!--Search Area2 End-->

    <?php
    if ($haksa === false || $haksa->timesynccourse == 0) {
        $terms = siteadmin_get_terms_sync();
        ?>
        <div class="extra_information">
            <p>학사시스템에서 <?php echo $year; ?> <?php echo get_string('year2','local_lmsdata'); ?> <?php echo $terms[$term]; ?> 강의를 가져오지 않았습니다.</p>
            <p>지금 가져오려면 "강의 가져오기" 버튼을 클릭하세요.</p>
        </div>
        <div id="btn_area">
            <input type="button" class="blue_btn" style="float:left; margin-right: 10px;" value="강의 가져오기" onclick="location.href = 'sync.course.import.php?year=<?php echo $year; ?>&term=<?php echo $term; ?>'"/>
        </div>
        <?php
    } else if ($haksa->timesyncparticipant == 0) {
        ?>
        <div class="extra_information">
            <p>학사시스템에서 <?php echo $year; ?> <?php echo get_string('year2','local_lmsdata'); ?> <?php echo $terms[$term]; ?> 강의 참여자를 가져오지 않았습니다.</p>
            <p>지금 가져오려면 "강의 참여자 가져오기" 버튼을 클릭하세요.</p>
        </div>
        <div id="btn_area">
            <input type="button" class="blue_btn" style="float:left; margin-right: 10px;" value="강의 참여자 가져오기" onclick="location.href = 'sync.participant.import.php?year=<?php echo $year; ?>&term=<?php echo $term; ?>'"/>
        </div>
        <?php
    } else {
        $sql_where = "";

        $conditions = array('year = :year', 'term = :term', 'deleted = :deleted');
        $params = array('year' => $year, 'term' => $term, 'deleted' => 0);
        if (!empty($searchtext)) {
            switch ($search) {
    //            case 1: // 강의코드
    //                $conditions[] = $DB->sql_like('hakno', ':hakno');
    //                $params['hakno'] = '%' . $searchtext . '%';
    //                break;
                case 2: // 과목코드
                    $conditions[] = $DB->sql_like('hakno', ':hakno');
                    $params['hakno'] = '%' . $searchtext . '%';
                    break;
                case 3; // 강의명
                    $conditions[] = $DB->sql_like('kor_lec_name', ':kor_lec_name');
                    $params['kor_lec_name'] = '%' . $searchtext . '%';
                    break;
                default:
                    break;
            }
        }

        if (!empty($conditions)) {
            $sql_where = ' WHERE ' . implode(' AND ', $conditions);
        }
        $sql_orderby = ' ORDER BY hakno, hakbun, name';

        $classes = $DB->get_records_sql('SELECT * FROM {haksa_class_student} ' . $sql_where . $sql_orderby, $params, ($currpage - 1) * $perpage, $perpage);
        $count_classes = $DB->count_records_sql('SELECT COUNT(*) FROM {haksa_class_student} ' . $sql_where, $params);
        ?>

        <table>
            <thead>
                <tr>
                    <th><?php echo get_string('number', 'local_lmsdata'); ?></th>
                    <th>과목코드</th>
                    <th>과목명</th>
                    <th><?php echo get_string('student_number','local_lmsdata'); ?></th>
                    <th><?php echo get_string('name','local_lmsdata'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                if ($count_classes) {
                    $startnum = $count_classes - (($currpage - 1) * $perpage);
                    foreach ($classes as $class) {
                        ?>
                        <tr>
                            <td><?php echo $startnum--; ?></td>
                            <td><?php echo $class->hakno; ?></td>
                            <td class="left"><?php echo $class->kor_lec_name; ?></td>
                            <td><?php echo $class->hakbun; ?></td>
                            <td><?php echo $class->name; ?></td>
                        </tr>
                        <?php
                    }
                } else {
                    echo '<tr><td colspan="9">'.get_string('nodata','local_lmsdata').'</td></tr>';
                }
                ?>
            </tbody>
        </table>

        <div id="btn_area">
            <input type="button" class="blue_btn" style="float:left; margin-right: 10px;" value="강의 참여자 가져오기" onclick="location.href = 'sync.participant.import.php?year=<?php echo $year; ?>&term=<?php echo $term; ?>'"/>
            <input class="red_btn" value="강의참여자 등록" id="create_course" style="float:right; margin-left: 10px;" type="button" onclick="location.href = 'sync.participant.assign.php?year=<?php echo $year; ?>&term=<?php echo $term; ?>'" />
        </div>

        <?php
        print_paging_navbar_script($count_classes, $currpage, $perpage, 'javascript:sync_goto_page(:page);');
    }

}else if($tab2 == 1){ 
    $participant_sql = " FROM {haksa} ";
    $participant_lists = $DB->get_records_sql(' SELECT * '.$participant_sql,array());
    $totalcount = $DB->count_records_sql(' SELECT count(*) '.$participant_sql,array());
    ?>
    <h4 class="page_sub_title">강의 참여자 동기화 이력</h4>
    <table>
        <thead>
            <th><?php echo get_string('number', 'local_lmsdata'); ?></th>
            <th>년도</th>
            <th>학기</th>
            <th>최근 동기화 시간</th>
        </thead>
        <tbody>
<?php
        foreach($participant_lists as $participant_list){
            echo html_writer::start_tag('tr');
            echo '<td>'.$totalcount--.'</td>';
            echo html_writer::start_tag('td');
            echo $participant_list->year;    
            echo html_writer::end_tag('td');
            echo html_writer::start_tag('td');
            if($participant_list->term == 20){
                echo '2학기';
            }else if($participant_list->term == 21){
                echo '겨울학기';
            }else if($participant_list->term == 11){
                echo '여름학기';
            }else if($participant_list->term == 10){
                echo '1학기';
            }else{
                echo '-';
            }
            echo html_writer::end_tag('td');
            echo html_writer::start_tag('td');
                echo $participant_list->timeassignparticipant ? date('Y-m-d H:i:s',$participant_list->timeassignparticipant) : '-';
            echo html_writer::end_tag('td');
            echo html_writer::end_tag('tr');
        }
?>
        </tbody>
    </table>
<?php
}
