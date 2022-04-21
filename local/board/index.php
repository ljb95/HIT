<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/board/lib.php';
require_once $CFG->libdir . '/formslib.php';

$type = optional_param('type', BOARD_NOTICE, PARAM_INT);
$page = optional_param('page', 1, PARAM_INT);
$search = optional_param('search', '', PARAM_RAW);
$perpage = optional_param('perpage', 10, PARAM_INT);
$current_course = optional_param('courseid', 1, PARAM_INT);

$context = context_system::instance();

require_login();

$PAGE->set_context($context);

$PAGE->set_url('/local/board/index.php');
$PAGE->set_pagelayout('standard');


$strplural = get_string("pluginnameplural", "local_board");
$PAGE->navbar->add(get_string("regular", "local_okirregular"), new moodle_url($CFG->wwwroot . '/local/okregular/my.php'));
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading($strplural);
echo $OUTPUT->header();

$courses_all = enrol_get_my_courses();

$contents = board_my_courses_in_board($courses_all, $type);


if (!empty($type) && $type === BOARD_QNA) {
    $currenttab = 'qna';
    $param["type"] = BOARD_QNA;
} else {
    $currenttab = 'notice';
    $param["type"] = BOARD_NOTICE;
}

if (!empty($search)) {
    $param["search"] = $search;
}

if (!empty($current_course)) {
    $param["courseid"] = $current_course;
}

if (!empty($perpage)) {
    $param["perpage"] = $perpage;
}

//tab
$row[] = new tabobject('notice', "$CFG->wwwroot/local/board/index.php?type=" . BOARD_NOTICE, get_string('course:notice', 'local_board'));
$row[] = new tabobject('qna', "$CFG->wwwroot/local/board/index.php?type=" . BOARD_QNA, get_string('course:qna', 'local_board'));
//$row[] = new tabobject('bookmark', "$CFG->wwwroot/local/bookmark/index.php", get_string('pluginname', 'local_bookmark'));
$rows[] = $row;

print_tabs($rows, $currenttab);

//course select box
$course_arr[1] = get_string("course:selecttitle", "local_board");

if (!empty($contents)) {
    foreach ($contents as $course) {
        $course_arr[$course->course] = $course->coursename . " [" . $course->subject_id . "]";
    }
}


//perpage
$perpage_arr = array();

$nums = array(10, 20, 30, 50);
foreach ($nums as $num) {
    $perpage_arr[$num] = get_string("content:perpage", "local_board", $num);
}

$perpage_url = new moodle_url($CFG->wwwroot . '/local/board/index.php?type=' . $type . '&courseid=' . $current_course);
$select_perpage = new single_select($perpage_url, 'perpage', $perpage_arr, $perpage, null);
$select_perpage->class = 'perpage';


if ($current_course === 1) {
    $courses = $course_arr;
} else {
    $courses = array($current_course => $current_course);
}

$totalcount = board_get_contents_count($courses, $type, $search);

//search input
?>
<form class="table-search-option" method="post" action="<?php echo $CFG->wwwroot . '/local/board/index.php'; ?>">
    <select title="category" class="w-150" name="courseid" id="coursesearch">
        <?php
        foreach ($course_arr as $id => $fullname) {
            if ($id == $current_course) {
                $selected = "selected";
            } else {
                $selected = "";
            }
            echo '<option value="' . $id . '" ' . $selected . '>' . $fullname . '</option>';
        }
        ?>

    </select>
    <input type="text" title="search" class="w-200" name="search" placeholder="<?php echo get_string('keyword:input', 'local_board'); ?>"  value="<?php echo $search; ?>">
    <input type="submit" value="<?php echo get_string('search'); ?>" class="board-search">
    <input type="hidden" name="type" value="<?php echo $type; ?>">
</form>
<div class="table-header-area">
    <?php
    echo $OUTPUT->render($select_perpage);
    ?>
</div>

<div class="table-filter-area">

    <form name="write_submit_form" action="<?php echo $CFG->wwwroot; ?>/local/board/write.php" <?php if ($type == BOARD_QNA) echo 'onsubmit="return write_submit();"'; ?>>
        <?php
        if ($type == BOARD_QNA) {
            ?>    
            <select class="w-150" title="category" name="courseid" id="coursesubmit">
                <?php
                foreach ($course_arr as $id => $fullname) {
                    if ($id == $current_course) {
                        $selected = "selected";
                    } else {
                        $selected = "";
                    }
                    echo '<option value="' . $id . '" ' . $selected . '>' . $fullname . '</option>';
                }
                ?>
            </select>
            <?php
        } else {
            echo '<input type="hidden" name="courseid" value="' . $id . '">';
        }

        if ($type == BOARD_NOTICE) {
            $csql = "select count(r.id) from {role_assignments} ra 
             join {role} r on r.id = ra.roleid and (r.archetype = 'teacher' or r.archetype = 'editingteacher')
             join {context} c on c.id = ra.contextid
             join {course} cou on cou.id = c.instanceid
             where ra.userid = :userid";
            $param = array();
            $param['userid'] = $USER->id;
            $add_cap = $DB->count_records_sql($csql, $param);
        } else {
            $add_cap = 1;
        }
        if ($add_cap >= 1) {
            echo '<input type="submit" class="red-form" value="' . get_string('content:new', 'local_board') . '"/>';
        }
        ?>
        <input type="hidden" name="type" value="<?php echo $type; ?>">
    </form>

</div>

<div class="board-list-area">
    <ul class="board-list">

        <tbody>
            <?php
            if ($totalcount > 0) {
                $rowno = $totalcount - ($page - 1) * $perpage;
                $rowcount = 0;
                $datetimeformat = "%Y.%m.%d %H:%M";

                $notices = board_get_notice_contents($courses, $type);
                $contents = board_get_contents($courses, $type, $page - 1, $perpage, $search);

                if (!empty($notices)) {
                    foreach ($notices as $content) {

                        $chk_access = board_get_access_content($content);

                        if (!empty($content->fileid)) {
                            $filecheck = '<img src="' . $CFG->wwwroot . '/local/board/pix/icon-attachment.png" alt="' . get_string('content:file', 'local_board') . '">';
                        } else {
                            $filecheck = "";
                        }

                        if ($content->lev) {
                            if ($content->lev <= 4) {
                                $step = $content->lev - 1;
                                $date_left_len = ($step + 1) * 30;
                            } else {
                                $step = 4 - 1;
                                $date_left_len = ($step + 1) * 30;
                            }
                            switch ($content->lev) {
                                case 1 : $calcwidth = 40;
                                    break;
                                case 2 : $calcwidth = 70;
                                    break;
                                case 3 : $calcwidth = 100;
                                    break;
                                case 4 : $calcwidth = 130;
                                    break;
                                default : $calcwidth = 130;
                                    break;
                            }
                            $date_left = 'style="padding-left:' . $date_left_len . 'px; width:100% !important;"';
                            $step_icon = '<img src="' . $OUTPUT->pix_url('icon_reply', 'mod_jinotechboard') . '" alt="" /> ';
                        } else {
                            $date_left = 'style="width:100% !important;"';
                            $step_icon = '';
                        }
                        echo '<li class = "isnotice" ' . $date_left . '>';
                        $cname = '<span  class="board-lecture-title">[' . $content->coursename . ']</span>';
                        echo "<span class='post-title'>" . $step_icon;
                        if ($chk_access) {
                            echo '<a href="' . $CFG->wwwroot . '/local/board/content.php?contentId=' . $content->id . '">' . $cname . $content->title . '&nbsp' . $filecheck . '</a>';
                        } else {
                            echo $cname . $content->title . '&nbsp' . $filecheck;
                        }

                        if ($content->isprivate) {
                            echo "<img src='" . $CFG->wwwroot . "/theme/creativeband/pix/images/lock.png' width='15' height='15' alt='" . get_string('isprivate', 'jinotechboard') . "' title='" . get_string('isprivate', 'jinotechboard') . "'>";
                        }
                        echo "</span>";

                        $by = new stdClass();
                        $by->name = fullname($content);
                        $by->date = userdate($content->timemodified);
                        echo "<br/>";
                        echo '<span class="post-date">' . get_string('bynameondate', 'local_board', $by) . '</span>';
                        echo '<span class="post-viewinfo area-right">' . $content->viewcnt . '<br/><span>' . get_string('content:hits', 'local_board') . '</span></span>';
                        echo '</li>';
                    }
                }

                foreach ($contents as $content) {
                    // 글을 작성한지 하루가 지나지 않고, 조회수가 0인 글에 new 표시가 나오도록 수정하였습니다 .
                    $new = ($content->timemodified + (60 * 60 * 24) >= time() && $content->viewcnt == 0 ) ? '<span class="boardred">New</span>' : '';
                    $rowcount += 1;
                    $chk_access = board_get_access_content($content);
                    if (!empty($content->fileid)) {
                        $filecheck = '<img src="' . $CFG->wwwroot . '/local/board/pix/icon-attachment.png" alt="' . get_string('content:file', 'local_board') . '">';
                    } else {
                        $filecheck = "";
                    }

                    if ($content->lev) {
                        if ($content->lev <= 4) {
                            $step = $content->lev - 1;
                            $date_left_len = ($step + 1) * 30;
                        } else {
                            $step = 4 - 1;
                            $date_left_len = ($step + 1) * 30;
                        }
                        switch ($content->lev) {
                            case 1 : $calcwidth = 40;
                                break;
                            case 2 : $calcwidth = 70;
                                break;
                            case 3 : $calcwidth = 100;
                                break;
                            case 4 : $calcwidth = 130;
                                break;
                            default : $calcwidth = 130;
                                break;
                        }
                        $date_left = 'style="padding-left:' . $date_left_len . 'px; width:100% !important;"';
                        $step_icon = '<img src="' . $OUTPUT->pix_url('icon_reply', 'mod_jinotechboard') . '" alt="" /> ';
                    } else {
                        //$date_left = 'style="width:calc(100% - 20px) !important;"';
                        $step_icon = '';
                    }
                    echo '<li ' . $date_left . '><span>';
                    // $new가 화면에 나오도록 하였습니다.
                    $cname = '<span class="board-lecture-title">[' . $content->coursename . '-' . $content->name . ']' . $new . '</span> ';
                    echo "<span class='post-title'>" . $step_icon;
                    if ($chk_access) {
                        echo '<a href="' . $CFG->wwwroot . '/local/board/content.php?contentId=' . $content->id . '">' . $cname . $content->title . '&nbsp' . $filecheck . '</a>';
                    } else {
                        echo $cname . $content->title . '&nbsp' . $filecheck;
                    }

                    if ($content->isprivate) {
                        echo "<img src='" . $CFG->wwwroot . "/theme/creativeband/pix/images/lock.png' width='15' height='15' alt='" . get_string('isprivate', 'jinotechboard') . "' title='" . get_string('isprivate', 'jinotechboard') . "'>";
                    }
                    echo '</span>';

                    $by = new stdClass();
                    $by->name = fullname($content);
                    $by->date = userdate($content->timemodified);
                    echo '<br/><span class="post-date">' . get_string('bynameondate', 'local_board', $by) . '</span>';
                    echo '<span class="post-viewinfo area-right">' . $content->viewcnt . '<br/><span>' . get_string('content:hits', 'local_board') . '</span></span>';
                    echo '</li>';
                }
            } else {
                echo '<li class="text-center">' . get_string('content:empty', 'local_board') . '</li>';
            }
            ?>

    </ul>
</div>
<div class="table-footer-area">
<?php
board_print_paging_navbar($totalcount, $page_name = "page", $page, $perpage, $CFG->wwwroot . '/local/board/index.php', $param);
?>  
</div>
<?php
echo $OUTPUT->footer();
?>

<script type="text/javascript">

    function write_submit() {
        var courseselect = $('form[name=write_submit_form]').find('#coursesubmit option:selected').val();
        if (!courseselect || courseselect == 1) {
            alert("<?php echo get_string("course:enter", "local_board") ?>");
            return false;
        }
    }

    $(document).ready(function () {

        $('#coursesearch').change(function () {
            var options = $('#coursesearch option');

            $('#coursesubmit').children().remove();

            options.each(function (index, value) {
                if (index != 0) {
                    var selected = $('#coursesearch option:selected').val();
                    if (value.value == selected) {
                        $('#coursesubmit').append('<option value="' + value.value + '" selected>' + value.text + '</option>');
                    } else {
                        $('#coursesubmit').append('<option value="' + value.value + '">' + value.text + '</option>');
                    }
                }
            });

        });

    });
</script>    

