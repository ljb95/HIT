<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/jinoboard/lib.php';

$type = optional_param('type', 1, PARAM_INT);
$page = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
$search = optional_param('search', '', PARAM_RAW);
$market = optional_param('market', 3, PARAM_INT);
$searchfield = optional_param('searchfield', 'title', PARAM_RAW);

$board = $DB->get_record('jinoboard', array('type' => $type));
$context = context_system::instance();

require_login();

$PAGE->set_context($context);
if ($type == 1) {
    $PAGE->set_url(new moodle_url('/local/jinoboard/index.php', array('type' => $type)));
} else {
    $PAGE->set_url(new moodle_url('/local/jinoboard/index2.php', array('type' => $type)));
}
$PAGE->set_pagelayout('standard');

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');

$boardname = (current_language() == 'ko') ? $board->name : $board->engname;

$PAGE->navbar->add($boardname);
$PAGE->set_title($boardname);
$PAGE->set_heading($boardname);

echo $OUTPUT->header();

$like = '';
if (!empty($search)) {
    $like .= " and " . $DB->sql_like($searchfield, ':search', false);
}

$sql = "select count(id) from {jinoboard_contents} where board = :board" . $like . " and isnotice = 0 order by ref DESC, step ASC";
$totalcount = $DB->count_records_sql($sql, array('board' => $board->id, 'search' => '%' . $search . '%'));
$total_pages = jinoboard_get_total_pages($totalcount, $perpage);

if($type!=1){
//tab
if ($type == 2) {
    $currenttab = 'qna';
    $param["type"] = 2;
} else if($type == 3){
    $currenttab = 'faq';
    $param["type"] = 3;
} else if($type == 4){
    $currenttab = 'sample';
    $param["type"] = 4;
}

$rows = array (
    new tabobject('qna', "$CFG->wwwroot/local/jinoboard/index.php?type=2", get_string('QnA', 'local_jinoboard')),
    new tabobject('faq', "$CFG->wwwroot/local/jinoboard/index.php?type=3", get_string('FAQ', 'local_jinoboard')),
    //new tabobject('sample', "$CFG->wwwroot/local/jinoboard/index.php?type=4", get_string('sample', 'local_jinoboard'))
    );
print_tabs(array($rows), $currenttab);
}
?>

<div class="tab1"> 
    <div class="tab-table-section" class="white-bg">
            <form class="table-search-option">
                <input type="hidden" name="type" value="<?php echo $type; ?>">
                <input type="hidden" name="market" value="<?php echo $market; ?>">
                <select name="searchfield" >
                    <option value="title" <?php if ($searchfield == 'title') { ?> selected="selected"<?php } ?>><?php echo get_string('title', 'local_jinoboard'); ?></option>
                    <option value="contents" <?php if ($searchfield == 'contents') { ?> selected="selected"<?php } ?>><?php echo get_string('content', 'local_jinoboard'); ?></option>
                </select>
                <input type="text" name="search" value="<?php echo $search; ?>" class="search-text" placeholder="<?php echo get_string('input', 'local_jinoboard'); ?>">
                <input type="submit" value="<?php echo get_string('search', 'local_jinoboard'); ?>" class="board-search"/>
            </form>

            <div class="table-header-area">       
                <form>
                    <input type="hidden" name="type" value="<?php echo $type; ?>">
                    <input type="hidden" name="search" value="<?php echo $search; ?>">
                    <select name="perpage" onchange="this.form.submit();">
                        <?php
                        $nums = array(10, 20, 30, 50);
                        foreach ($nums as $num) {
                            $selected = ($num == $perpage) ? 'selected' : '';

                            echo '<option value="' . $num . '" ' . $selected . '>' . get_string('showperpage', 'local_jinoboard', $num) . '</option>';
                        }
                        ?>
                    </select>
                    <span class="table-count">
                        (<?php echo $page; ?>/<?php echo $total_pages; ?> <?php echo get_string('page', 'local_jinoboard'); ?>, <?php echo get_string('total', 'local_jinoboard'); ?> <?php echo $totalcount; ?><?php echo get_string('case', 'local_jinoboard'); ?>)
                    </span>
                </form>
            </div>
            <div class="table-filter-area">
                <?php
                if (has_capability('local/jinoboard:write', $context) || $type == 7 || $type == 2) {
                    ?>
                    <input type="button" value="<?php echo get_string('writepost', 'local_jinoboard') ?>" onclick="location.href = 'write.php?type=<?php echo $type; ?>'" />
                    <?php
                }
                ?>
            </div>



        <?php
        if ($type == 3) {
            echo ' <div class="board-list-area ';
            echo '">';
        }
        $offset = 0;
        if ($page != 0) {
            $offset = ($page - 1) * $perpage;
        }
        $list_num = $offset;
        $num = $totalcount - $offset;
        $sql = "select * from {jinoboard_contents} where board = :board " . $like . " and isnotice = 0 order by ref DESC, step ASC";
        $contents = $DB->get_records_sql($sql, array('board' => $board->id, 'search' => '%' . $search . '%'), $offset, $perpage);
        if ($type != 3) {
            ?>
            <div class="thread-style">
                <ul class="thread-style-lists">
                    <?php
                    if ($board->allownotice == 1) {
                        $sql = "select * from {jinoboard_contents} where board = :board " . $like . " and isnotice = 1 order by ref DESC, step ASC";
                        $notices = $DB->get_records_sql($sql, array('board' => $board->id, 'search' => '%' . $search . '%'));
                        foreach ($notices as $content) {
                            $postuser = $DB->get_record('user', array('id' => $content->userid));
                            $fullname = fullname($postuser);
                            $userdate = userdate($content->timecreated);
                            $by = new stdClass();
                            $by->name = $fullname;
                            $by->date = $userdate;
                            $fs = get_file_storage();
                            if (!empty($notice->id)) {
                                $files = $fs->get_area_files($context->id, 'local_jinoboard', 'attachment', $notice->id, 'timemodified', false);
                            } else {
                                $files = array();
                            }
                            if (count($files) > 0) {
                                $filecheck = '<img src="' . $CFG->wwwroot . '/local/jinoboard/pix/lock.png" alt="' . get_string('content:file', 'local_jinoboard') . '">';
                            } else {
                                $filecheck = "";
                            }
                            if ($content->lev) {
                                if ($content->lev <= 4) {
                                    $step = $content->lev;
                                    $date_left_len = $step * 30;
                                } else {
                                    $step = 4;
                                    $date_left_len = $step * 30;
                                }
                                switch ($content->lev) {
                                    case 1 : $calcwidth = 30;
                                        break;
                                    case 2 : $calcwidth = 60;
                                        break;
                                    case 3 : $calcwidth = 90;
                                        break;
                                    case 4 : $calcwidth = 120;
                                        break;
                                    default : $calcwidth = 120;
                                        break;
                                }
                                $date_left = 'style="padding-left:' . $date_left_len . 'px; width:calc(100% - ' . $calcwidth . 'px) !important;"';
                                $step_icon = '<img src="' . $OUTPUT->pix_url('icon_reply', 'mod_jinotechboard') . '" alt="" /> ';
                            } else {
                                $date_left = 'style="width:calc(100% - 0px) !important;"';
                                $step_icon = '';
                            }

                            echo "<li class='isnotice' " . $date_left . ">";
                            echo '<div class="thread-left">' . $OUTPUT->user_picture($postuser) . '</div>';
                            echo "<div class='thread-content'><span class='post-title'>" . $step_icon;
                            if ($content->issecret && $USER->id != $content->userid && !is_siteadmin() && !$parent->userid != $USER->id) {
                                echo $content->title;
                            } else {
                                echo "<a href='" . $CFG->wwwroot . "/local/jinoboard/detail.php?id=" . $content->id . "&page=" . $page . "&perpage=" . $perpage . "&list_num=" . $list_num . "&search=" . $search . "&type=" . $type . "&searchfield=" . $searchfield . "'>" . $content->title . "</a>";
                            }
                            echo "  " . $filecheck;
                            if ($content->issecret) {
                                echo "<img src='" . $CFG->wwwroot . "/local/jinoboard/pix/lock.png' width='15' height='15' alt='" . get_string('secreticon', 'local_jinoboard') . "' title='" . get_string('secreticon', 'local_jinoboard') . "'>";
                            }

                            if ($type == 2)
                                echo '<br/><span class="post-date"><a href="' . $CFG->wwwroot . '/local/lmsdata/user_info.php?id=' . $postuser->id . '">' . get_string("bynameondate", "local_jinoboard", $by) . '</a></span>';
                            echo "</span></div><div class='thread-right'>";
                            if ($type == 1)
                                echo "<span class='post-date area-right'>" . date("Y-m-d", $content->timemodified) . "</span>";
                            if ($type == 2)
                                echo "<span class='post-viewinfo area-right'>" . $content->viewcnt . "<br/><span>" . get_string('viewcount', 'local_jinoboard') . "</span></span>";
                            echo "</div></li>";
                        }
                    }
                    foreach ($contents as $content) {
                        $list_num++;
                        $parent = $DB->get_record('jinoboard_contents', array('id' => $content->ref));
                        $fs = get_file_storage();
                        $files = $fs->get_area_files($context->id, 'local_jinoboard', 'attachment', $content->id, 'timemodified', false);
                        if (count($files) > 0) {
                            $filecheck = '<img src="' . $CFG->wwwroot . '/local/jinoboard/pix/icon-attachment.png" alt="' . get_string('content:file', 'local_jinoboard') . '">';
                        } else {
                            $filecheck = "";
                        }

                        if ($content->lev) {
                            if ($content->lev <= 4) {
                                $step = $content->lev;
                                $date_left_len = $step * 30;
                            } else {
                                $step = 4;
                                $date_left_len = $step * 30;
                            }
                            switch ($content->lev) {
                                case 1 : $calcwidth = 30;
                                    break;
                                case 2 : $calcwidth = 60;
                                    break;
                                case 3 : $calcwidth = 90;
                                    break;
                                case 4 : $calcwidth = 120;
                                    break;
                                default : $calcwidth = 120;
                                    break;
                            }
                            $date_left = 'style="padding-left:' . $date_left_len . 'px; width:calc(100% - ' . $calcwidth . 'px) !important;"';
                            $step_icon = '<img src="' . $OUTPUT->pix_url('icon_reply', 'mod_jinotechboard') . '" alt="" /> ';
                        } else {
                            $date_left = 'style="width:calc(100% - 0px) !important;"';
                            $step_icon = '';
                        }

                        $postuser = $DB->get_record('user', array('id' => $content->userid));
                        $fullname = fullname($postuser);
                        $userdate = userdate($content->timecreated);
                        $by = new stdClass();
                        $by->name = $fullname;
                        $by->date = $userdate;

                        echo "<li " . $date_left . ">";
                        echo '<div class="thread-left">' . $OUTPUT->user_picture($postuser) . '</div>';
                        echo "<div class='thread-content'><span class='post-title'>" . $step_icon;
                        if ($content->issecret && $USER->id != $content->userid && !is_siteadmin() && $parent->userid != $USER->id) {
                            echo $content->title;
                        } else {
                            echo "<a href='" . $CFG->wwwroot . "/local/jinoboard/detail.php?id=" . $content->id . "&page=" . $page . "&perpage=" . $perpage . "&list_num=" . $list_num . "&search=" . $search . "&type=" . $type . "&searchfield=" . $searchfield . "'>" . $content->title . "</a>";
                        }
                        echo "  " . $filecheck;
                        if ($content->issecret) {
                            echo "<img src='" . $CFG->wwwroot . "/local/jinoboard/pix/lock.png' width='15' height='15' alt='" . get_string('secreticon', 'local_jinoboard') . "' title='" . get_string('secreticon', 'local_jinoboard') . "'>";
                        }

                        if ($type == 2)
                            echo '<br/><span class="post-date">' . get_string("bynameondate", "local_jinoboard", $by) . '</span>';
                        echo "</span></div><div class='thread-right'>";
                        if ($type == 1)
                            echo "<span class='post-date area-right'>" . date("Y-m-d", $content->timemodified) . "</span>";
                        if ($type == 2)
                            echo "<span class='post-viewinfo area-right'>" . $content->viewcnt . "<br/><span>" . get_string('viewcount', 'local_jinoboard') . "</span></span>";
                        echo "</div></li>";
                        $num--;
                    }
                    ?>
                </ul>
            </div>
            <?php
        } else if ($type == 3) {
            require_once $CFG->dirroot . '/local/jinoboard/faq.php';
        }
        ?>
    </div>
    <?php
    if ($type != 6) {
        ?>
        <div class="table-footer-area">
            <?php
            $page_params = array();
            $page_params['type'] = $type;
            $page_params['perpage'] = $perpage;
            $page_params['search'] = $search;
            $page_params['searchfield'] = $searchfield;
            jinoboard_get_paging_bar($CFG->wwwroot . "/local/jinoboard/index.php", $page_params, $total_pages, $page, $market);
            ?>
            <!-- Breadcrumbs End -->
        </div> <!-- Table Footer Area End -->
    <?php } ?>
</div> <!-- Tab & Table Section End -->
<script src="//code.jquery.com/jquery-1.10.2.js"></script>
<script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script>
<script>
                        $(function () {
                            $("#accordion").accordion({
                                collapsible: true,
                                heightStyle: "content",
                                header: "ul",
                                active: false
                            });
                            $('.div_taps').css('display', 'none');
                            $('#information').css('display', 'block');
                            $('#information_btn').attr('src', '<?php echo $CFG->wwwroot . "/local/jinoboard/images/btnMenu1_over.png"; ?>');
                            var previous_menu = 'information_btn'
                            var previous_menu_num = '1'
                            $('#information_btn').click(function () {
                                $('.div_taps').css('display', 'none');
                                $('#information').css('display', 'block');
                                $('#information_btn').attr('src', '<?php echo $CFG->wwwroot . "/local/jinoboard/images/btnMenu1_over.png"; ?>');
                                if (previous_menu_num != '1') {
                                    $('#' + previous_menu).attr('src', '<?php echo $CFG->wwwroot . "/local/jinoboard/images/btnMenu" ?>' + previous_menu_num + '.png');
                                    previous_menu = 'information_btn'
                                    previous_menu_num = '1'
                                }
                            });
                            $('#database_btn').click(function () {
                                $('.div_taps').css('display', 'none');
                                $('#database').css('display', 'block');
                                $('#database_btn').attr('src', '<?php echo $CFG->wwwroot . "/local/jinoboard/images/btnMenu2_over.png"; ?>');
                                if (previous_menu_num != '2') {
                                    $('#' + previous_menu).attr('src', '<?php echo $CFG->wwwroot . "/local/jinoboard/images/btnMenu" ?>' + previous_menu_num + '.png');
                                    previous_menu = 'database_btn'
                                    previous_menu_num = '2'
                                }
                            });
                            $('#endnote_btn').click(function () {
                                $('.div_taps').css('display', 'none');
                                $('#endnote').css('display', 'block');
                                $('#endnote_btn').attr('src', '<?php echo $CFG->wwwroot . "/local/jinoboard/images/btnMenu3_over.png"; ?>');
                                if (previous_menu_num != '3') {
                                    $('#' + previous_menu).attr('src', '<?php echo $CFG->wwwroot . "/local/jinoboard/images/btnMenu" ?>' + previous_menu_num + '.png');
                                    previous_menu = 'endnote_btn'
                                    previous_menu_num = '3'
                                }
                            });
                        });
//	$('#accordion input[type="checkbox"]').click(function(e) {
//		e.stopPropagation();
//	});
</script>
<?php echo $OUTPUT->footer(); ?>