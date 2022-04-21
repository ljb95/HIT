<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/jinoboard/lib.php';

//파라미터를 받는 부분 
$id = optional_param('id', 1, PARAM_INT);
$page = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
$search = optional_param('search', '', PARAM_RAW);
$searchfield = optional_param('searchfield', 'title', PARAM_RAW);

//jinoboared 테이블에서 정보를 가져옴 (1 - 공지사항, 2 - Q&A, 3 - FAQ, 4 - 자료실)
$board = $DB->get_record('jinoboard', array('id' => $id));
$context = context_system::instance();

//역할 체크 get_field('테이블명', '컬럼명', 조건문array)
$role = $DB->get_field('lmsdata_user', 'usergroup', array('userid' => $USER->id));
if (is_siteadmin()) {
    $role = 'ma';
} else if (empty($role)) {
    $role = 'gu';
}

//게시판에 따른 권한 체크 get_records('테이블명', 조건문) returntype = array()
$allows = $DB->get_records('jinoboard_allowd', array('board' => $board->id));
$access = array();
foreach ($allows as $allow) {
    $access[$allow->allowrole] = $allow;
}
$myaccess = $access[$role];

if ($myaccess->allowview != 'true') {
    redirect($CFG->wwwroot, 'Permission Denied');
}

//페이지 셋팅 
$PAGE->set_context($context);

$PAGE->set_url(new moodle_url('/local/jinoboard/list.php', array('id' => $id)));
$PAGE->set_pagelayout('standard');
$PAGE->add_body_class('path-local-jinoboard-' . $id);

$boardname = (current_language() == 'ko') ? $board->name : $board->engname;

$PAGE->navbar->add(get_string('guide', 'local_jinoboard'));
$PAGE->navbar->add($boardname);
$PAGE->set_title($boardname);
$PAGE->set_heading($boardname);

echo $OUTPUT->header();

$like = '';

// 검색어가 있을경우 변수의 존재 여부를 판단하여 쿼리 추가 
if (!empty($search)) {
    $like .= " and " . $DB->sql_like($searchfield, ':search', false);
}
$count_sql = "select count(id) from {jinoboard_contents} where board = :board" . $like . " and isnotice = 0 order by ref DESC, step ASC";
$totalcount = $DB->count_records_sql($count_sql, array('board' => $board->id, 'search' => '%' . $search . '%'));
$total_pages = jinoboard_get_total_pages($totalcount, $perpage);
?>

<div class="tab-table-section" class="white-bg">
    <!-- 검색 폼 시작 -->
    <form class="table-search-option">
        <input type="hidden" name="id" value="<?php echo $id; ?>">
        <select name="searchfield" title="title" >
            <option value="title" <?php if ($searchfield == 'title') { ?> selected="selected"<?php } ?>><?php echo get_string('title', 'local_jinoboard'); ?></option>
            <option value="contents" <?php if ($searchfield == 'contents') { ?> selected="selected"<?php } ?>><?php echo get_string('content', 'local_jinoboard'); ?></option>
        </select>
        <input type="text" title="search" name="search" value="<?php echo $search; ?>" class="search-text" placeholder="<?php echo get_string('input', 'local_jinoboard'); ?>">
        <input type="submit" value="<?php echo get_string('search', 'local_jinoboard'); ?>" class="board-search"/>
    </form>
    <!-- 검색 폼 끝 --> 
    <div class="table-header-area">     
        <!-- 게시글 수 정보 표시 및 게시글 표시 갯수 설정 -->
        <form>
            <input type="hidden" name="id" value="<?php echo $id; ?>">
            <input type="hidden" name="search" value="<?php echo $search; ?>">
            <select name="perpage" onchange="this.form.submit();" title="page">
                <?php
                //perpage 몇개의 컨텐츠를 보여줄지 선택
                $nums = array(10, 20, 30, 50);
                foreach ($nums as $num) {
                    $selected = ($num == $perpage) ? 'selected' : '';

                    echo '<option value="' . $num . '" ' . $selected . '>' . get_string('showperpage', 'local_jinoboard', $num) . '</option>';
                }
                ?>
            </select>
            <span class="table-count">

                <?php echo '(' . $page . '/' . $total_pages . get_string('page', 'local_jinoboard') . ',' . get_string('total', 'local_jinoboard') . $totalcount . get_string('case', 'local_jinoboard') . ')'; ?>
            </span>
        </form>
        <!-- 게시글 수 정보 표시 및 게시글 표시 갯수 설정 끝 -->
    </div>
    <div class="table-filter-area">
        <?php
        if ($myaccess->allowwrite == 'true') {
            ?>
            <input type="button" class="right" value="<?php echo get_string('writepost', 'local_jinoboard') ?>" onclick="location.href = 'write_uncore.php?board=<?php echo $id; ?>'" />
        <?php } ?>
    </div>
    <?php
    $offset = 0;
    if ($page != 0) {
        $offset = ($page - 1) * $perpage;
    }
    $list_num = $offset;
    $num = $totalcount - $offset;
    $sql = "select * from {jinoboard_contents} where board = :board " . $like . " and isnotice = 0 order by ref DESC, step ASC";
    
    //게시판 리스트 가져옴 get_records_sql('쿼리', 조건문)
    $contents = $DB->get_records_sql($sql, array('board' => $board->id, 'search' => '%' . $search . '%'), $offset, $perpage);
    ?>
    <div class="thread-style">
        <ul class="thread-style-lists">
            <?php
            if ($board->allownotice == 1) {
                $sql = "select * from {jinoboard_contents} where board = :board " . $like . " and isnotice = 1 order by ref DESC, step ASC";
                $notices = $DB->get_records_sql($sql, array('board' => $board->id, 'search' => '%' . $search . '%'));
                foreach ($notices as $content) {
                    
                    //user 정보를 저장하는 테이블에서 정보를 가져옴 
                    $postuser = $DB->get_record('user', array('id' => $content->userid));
                    $fullname = fullname($postuser);
                    $userdate = userdate($content->timecreated);
                    
                    $by = new stdClass();
                    $by->name = $fullname;
                    $by->date = $userdate;
                    $fs = get_file_storage();
                    if (!empty($content->id)) {
                        $files = $fs->get_area_files($context->id, 'local_jinoboard', 'attachment', $notice->id, 'timemodified', false);
                    } else {
                        $files = array();
                    }

                    $filecheck = (count($files) > 0) ? '<img src="' . $CFG->wwwroot . '/local/jinoboard/pix/icon-attachment.png" alt="' . get_string('content:file', 'local_jinoboard') . '">' : "";


                    $step = ($content->lev <= 4) ? $content->lev : 4;
                    $date_left_len = $step * 30;
                    $calcwidth = ($content->lev <= 4) ? $content->lev * 30 : 120;
                    $date_left = 'style="padding-left:' . $date_left_len . 'px; width:100% !important;"';
                    $step_icon = ($content->lev) ? '<img src="' . $OUTPUT->pix_url('icon_reply', 'mod_jinotechboard') . '" alt="" /> ' : '';
                    $new = ($content->timemodified + (60 * 60 * 24 * $board->newday) >= time() && $board->allownew == 1 ) ? '<span class="boardred">New</span>' : '';
                    echo "<li class='isnotice' " . $date_left . ">";
                    echo '<div class="thread-left">' . $OUTPUT->user_picture($postuser) . '</div>';
                    echo "<div class='thread-content'><span class='post-title'>" . $step_icon;
                    if ($content->issecret && (($USER->id != $content->userid) || ($parent->userid != $USER->id) || ($myaccess->allowsecret != 'true'))) {
                        echo $content->title . $new;
                    } else if ($myaccess->allowdetail != 'true') {
                        echo '<a href="#" onclick="alert(' . "'" . get_string('nopermission', 'local_jinoboard') . "'" . ')">' . $content->title . $new . '"</a>"';
                    } else {
                        echo "<a href='" . $CFG->wwwroot . "/local/jinoboard/detail_uncore.php?id=" . $content->id . "&page=" . $page . "&perpage=" . $perpage . "&list_num=" . $list_num . "&search=" . $search . "&board=" . $id . "&searchfield=" . $searchfield . "'>" . $content->title . $new . "</a>";
                    }
                    echo "  " . $filecheck;

                    if ($content->issecret) {
                        echo "<img src='" . $CFG->wwwroot . "/local/jinoboard/pix/lock.png' width='15' height='15' alt='" . get_string('secreticon', 'local_jinoboard') . "' title='" . get_string('secreticon', 'local_jinoboard') . "'>";
                    }
                    echo '<br/><span class="post-date"><a href="' . $CFG->wwwroot . '/local/lmsdata/user_info.php?id=' . $postuser->id . '">' . get_string("bynameondate", "local_jinoboard", $by) . '</a></span>';
                    echo "</span></div><div class='thread-right'>";
                    echo "<span class='post-viewinfo area-right'>" . $content->viewcnt . "<br/><span>" . get_string('viewcount', 'local_jinoboard') . "</span></span>";
                    echo "</div></li>";
                }
            }
            foreach ($contents as $content) {
                if ($board->allowrental == '1') {
                    switch ($content->status) {
                        case 0: $content->title .= '&nbsp;[' . get_string('apply', 'local_jinoboard') . ']';
                            break;
                        case 1: $content->title .= '&nbsp;[' . get_string('yes', 'local_jinoboard') . ']';
                            break;
                        case 2: $content->title .= '&nbsp;[' . get_string('refuse', 'local_jinoboard') . ']';
                            break;
                    }
                }
                $list_num++;
                $parent = $DB->get_record('jinoboard_contents', array('id' => $content->ref));
                $fs = get_file_storage();
                $files = $fs->get_area_files($context->id, 'local_jinoboard', 'attachment', $content->id, 'timemodified', false);
                if (count($files) > 0) {
                    $filecheck = '<img src="' . $CFG->wwwroot . '/local/jinoboard/pix/icon-attachment.png" alt="' . get_string('content:file', 'local_jinoboard') . '">';
                } else {
                    $filecheck = "";
                }

                $step = ($content->lev <= 4) ? $content->lev : 4;
                $date_left_len = $step * 30;
                $calcwidth = ($content->lev <= 4) ? $content->lev * 30 : 120;
                $date_left = 'style="padding-left:' . $date_left_len . 'px; width:100% !important;"';
                $step_icon = ($content->lev) ? '<img src="' . $OUTPUT->pix_url('icon_reply', 'mod_jinotechboard') . '" alt="" /> ' : '';


                $postuser = $DB->get_record('user', array('id' => $content->userid));
                $fullname = fullname($postuser);
                $userdate = userdate($content->timecreated);
                $by = new stdClass();
                $by->name = $fullname;
                $by->date = $userdate;
                $new = ($content->timemodified + (60 * 60 * 24 * $board->newday) >= time() && $board->allownew == 1 ) ? '<span class="boardred">New</span>' : '';
                echo "<li " . $date_left . ">";
                echo '<div class="thread-left">' . $OUTPUT->user_picture($postuser) . '</div>';
                echo "<div class='thread-content'><span class='post-title'>" . $step_icon;
                if ((($content->issecret && $USER->id != $content->userid && $parent->userid != $USER->id) || ($content->issecret && $myaccess->allowsecret != 'true')) && !is_siteadmin()) {
                    echo $content->title . $new;
                } else if ($myaccess->allowdetail != 'true') {
                    echo '<a href="#" onclick="alert(' . "'" . get_string('nopermission', 'local_jinoboard') . "'" . ')">' . $content->title . $new . '"</a>"';
                } else {
                    echo "<a href='" . $CFG->wwwroot . "/local/jinoboard/detail_uncore.php?id=" . $content->id . "&page=" . $page . "&perpage=" . $perpage . "&list_num=" . $list_num . "&search=" . $search . "&board=" . $id . "&searchfield=" . $searchfield . "'>" . $content->title . $new . "</a>";
                }
                echo "  " . $filecheck;
                if ($content->issecret) {
                    echo "<img src='" . $CFG->wwwroot . "/local/jinoboard/pix/lock.png' width='15' height='15' alt='" . get_string('secreticon', 'local_jinoboard') . "' title='" . get_string('secreticon', 'local_jinoboard') . "'>";
                }

                echo '<br/><span class="post-date">' . get_string("bynameondate", "local_jinoboard", $by) . '</span>';
                echo "</span></div><div class='thread-right'>";
                echo "<span class='post-viewinfo area-right'>" . $content->viewcnt . "<br/><span>" . get_string('viewcount', 'local_jinoboard') . "</span></span>";
                echo "</div></li>";
                $num--;
            }
            ?>
            <?php
            if (empty($notices) && empty($contents)) {
                ?>
                <li style="padding-left:0px; width:calc(100% - 0px) !important;">
                    <div class="thread-empty">
                    <?php echo get_string('nocontent', 'local_jinoboard'); ?>
                    </div>
                    <?php
                }
                ?>
        </ul>
    </div>
</div>
<div class="table-footer-area">
    <?php
    $page_params = array();
    $page_params['id'] = $id;
    $page_params['perpage'] = $perpage;
    $page_params['search'] = $search;
    $page_params['searchfield'] = $searchfield;
    
    //페이징 함수
    jinoboard_get_paging_bar($CFG->wwwroot . "/local/jinoboard/list.php", $page_params, $total_pages, $page, 0);
    ?>
    <!-- Breadcrumbs End -->
</div> <!-- Table Footer Area End -->
<?php echo $OUTPUT->footer(); ?>
