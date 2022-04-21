<?php
require (dirname(dirname(dirname(__FILE__))) . '/config.php');

$id = optional_param('id', 0, PARAM_INT);
$modules = $DB->get_records_sql(" select md.id,md.name from {modules} md JOIN {course_modules} mcm ON mcm.module = md.id where mcm.course= :id ", array('id' => $id));
$course = $DB->get_record('course', array('id' => $id));
foreach ($modules as $module) {
    if ($module->name == 'jinotechboard') {
        $modules1 = 1;
    } else if ($module->name == 'teamboard') {
        $modules2 = 1;
    } else if ($module->name == 'jinoforum') {
        $modules3 = 1;
    } else if ($module->name == 'forum') {
        $modules4 = 1;
    } else if ($module->name == 'assign') {
        $modules5 = 1;
    } else if ($module->name == 'quiz') {
        $modules6 = 1;
    } else if ($module->name == 'chat') {
        $modules7 = 1;
    } else if ($module->name == 'data') {
        $modules8 = 1;
    } else if ($module->name == 'wiki') {
        $modules9 = 1;
    } else if ($module->name == 'glossary') {
        $modules10 = 1;
    } else if ($module->name == 'lesson') {
        $modules11 = 1;
    } else if ($module->name == 'lti') {
        $modules12 = 1;
    } else if ($module->name == 'workshop') {
        $modules13 = 1;
    } else if ($module->name == 'scorm') {
        $modules14 = 1;
    } else if ($module->name == 'choice') {
        $modules15 = 1;
    } else if ($module->name == 'survey') {
        $modules16 = 1;
    }
}
?>
<div class="popup_content">
    <h2><?php echo $course->fullname; ?></h2>
    <p class="page_sub_title"> * 공지사항은 default옵션으로 사용/비사용을 선택할 수 없습니다.</p>
    <form id="frm_course_new" name="frm_course_new">
        <input type="hidden" id="id_id" name="id" value="<?php echo $id; ?>" />
        <table cellpadding="0" cellspacing="0" class="detail">
            <tbody>
                <tr>
                    <th class="field_title">메뉴명</th>
                    <th class="field_title">사용중</th>
                </tr>
                <?php if ($modules1) { ?>
                    <tr>
                        <td class="field_title">게시판</td>
                        <td>
                            <img src="./Tick_Mark.png" alt="Used" title="Used" />
                        </td>
                    </tr>
                <?php } if ($modules2) { ?>
                    <tr>
                        <td class="field_title">조모임 게시판</td>
                        <td>
                            <img src="./Tick_Mark.png" alt="Used" title="Used" />
                        </td>
                    </tr>
                <?php } if ($modules3) { ?>
                    <tr>
                        <td class="field_title">토론 게시판</td>
                        <td>
                            <img src="./Tick_Mark.png" alt="Used" title="Used" />
                        </td>
                    </tr>
                <?php } if ($modules4) { ?>
                    <tr>
                        <td class="field_title">포럼</td>
                        <td>
                            <img src="./Tick_Mark.png" alt="Used" title="Used" />
                        </td>
                    </tr>
                <?php } if ($modules5) { ?>
                    <tr>
                        <td class="field_title"><?php echo get_string('stats_assignment', 'local_lmsdata'); ?></td>
                        <td>
                            <img src="./Tick_Mark.png" alt="Used" title="Used" />
                        </td>
                    </tr>
                <?php } if ($modules6) { ?>
                    <tr>
                        <td class="field_title"><?php echo get_string('stats_quiz', 'local_lmsdata'); ?></td>
                        <td>
                            <img src="./Tick_Mark.png" alt="Used" title="Used" />
                        </td>
                    </tr>
                <?php } if ($modules7) { ?>
                    <tr>
                        <td class="field_title">대화방</td>
                        <td>
                            <img src="./Tick_Mark.png" alt="Used" title="Used" />
                        </td>
                    </tr>
                <?php } if ($modules8) { ?>
                    <tr> 
                        <td class="field_title">데이터베이스</td>
                        <td>
                            <img src="./Tick_Mark.png" alt="Used" title="Used" />
                        </td>
                    </tr>
                <?php } if ($modules9) { ?>
                    <tr>
                        <td class="field_title">위키</td>
                        <td>
                            <img src="./Tick_Mark.png" alt="Used" title="Used" />
                        </td>
                    </tr>
                <?php } if ($modules10) { ?>
                    <tr>
                        <td class="field_title">용어집</td>
                        <td>
                            <img src="./Tick_Mark.png" alt="Used" title="Used" />
                        </td>
                    </tr>
                <?php } if ($modules11) { ?>
                    <tr>
                        <td class="field_title">완전학습</td>
                        <td>
                            <img src="./Tick_Mark.png" alt="Used" title="Used" />
                        </td>
                    </tr>
                <?php } if ($modules12) { ?>
                    <tr>
                        <td class="field_title">외부도구</td>
                        <td>
                            <img src="./Tick_Mark.png" alt="Used" title="Used" />
                        </td>
                    </tr>
                <?php } if ($modules13) { ?>
                    <tr>
                        <td class="field_title">상호평가</td>
                        <td>
                            <img src="./Tick_Mark.png" alt="Used" title="Used" />
                        </td>
                    </tr>
                <?php } if ($modules14) { ?>
                    <tr>
                        <td class="field_title">스콤패키지</td>
                        <td>
                            <img src="./Tick_Mark.png" alt="Used" title="Used" />
                        </td>
                    </tr>
                <?php } if ($modules15) { ?>
                    <tr>
                        <td class="field_title">간편설문</td>
                        <td>
                            <img src="./Tick_Mark.png" alt="Used" title="Used" />
                        </td>
                    </tr>
                <?php } if ($modules16) { ?>
                    <tr>
                        <td class="field_title">조사</td>
                        <td>
                            <img src="./Tick_Mark.png" alt="Used" title="Used" />
                        </td>
                    </tr>        
                <?php } ?>
            </tbody>
        </table>

    </form>
</div>