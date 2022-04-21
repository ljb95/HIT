<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once dirname(dirname(__FILE__)) . '/lib.php';

// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string) new moodle_url('/siteadmin/support/notices.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

require_once $CFG->dirroot . '/local/jinoboard/lib.php';
require_once dirname(dirname(__FILE__)) . '/lib/paging.php';

$year = optional_param('year', date("Y"), PARAM_INT);
$term = optional_param('term', 0, PARAM_INT);
$mode = optional_param('mode', 'list', PARAM_INT);

// 현재 년도, 학기
if (!$year) {
    $year = get_config('moodle', 'haxa_year');
}
if (empty($year)) {
    $year = date('Y');
}
if (!$term) {
    $term = get_config('moodle', 'haxa_term');
}
$startyear = 2016; //시스템 시작 년도(siteadmin/lib.php에 선언)

$PAGE->set_context($context);
$PAGE->set_url('/local/jinoboard/index.php');

$page_params = array();
$params = array(
    'year' => $year,
    'term' => $term
);

$dates = $DB->get_records('lmsdata_trust', array('year' => $year, 'term' => $term),'id asc');
?>
<?php include_once('../inc/header.php'); ?>
<div id="contents">
    <?php include_once('../inc/sidebar_support.php'); ?>
    <div id="content">
        <h3 class="page_title">산업체위탁교육기간관리</h3>
        <div class="page_navbar"><a href="./notices.php"><?php echo get_string('site_management', 'local_lmsdata'); ?></a> > <a href="./notices.php">산업체위탁교육기간관리</a> > <?php echo get_string('list','local_lmsdata'); ?></div>
        <form name="" id="course_search" class="search_area" action="period.php" method="get">
            <?php
            echo '<select name="year" class="w_160" onchange="this.form.submit()">';
            $years = lmsdata_get_years();
            foreach ($years as $v => $y) {
                $selected = '';
                if ($v == $year) {
                    $selected = ' selected';
                }
                echo '<option value="' . $v . '"' . $selected . '> ' .  get_string('year','local_lmsdata',$y) . '</option>';
            }
            echo '</select>';
            echo '<select name="term" class="w_160" onchange="this.form.submit()">';
            $terms = array('1' => '1학기', '2' => '2학기');
            foreach ($terms as $v => $y) {
                $selected = '';
                if ($v == $term) {
                    $selected = ' selected';
                }
                echo '<option value="' . $v . '"' . $selected . '> ' . $y . '</option>';
            }
            echo '</select>';

            if (empty($dates)) {
                echo '<input type="button" id="make_default" class="gray_btn" value="초기데이터 생성" />';
            }
            ?>
        </form><!--Search Area2 End-->
        <table cellspacing="0" cellpadding="0">
            <thead>
                <tr>
                    <th>주차</th>
                    <th>출석 인정 기간</th>
                </tr>
            </thead>
            <tbody id="period_period_tbody">
                <?php foreach ($dates as $data) { ?>
                <form action="period.ajax.php?id=<?php echo $data->id; ?>" method="post">
                    <input type="hidden" name="year" value="<?php echo $year; ?>" />
                    <input type="hidden" name="term" value="<?php echo $term; ?>" />
                    <input type="hidden" name="mode" value="edit" />
                    <tr id="periodid<?php echo $data->id ?>">
                        <td width="15%"><?php echo $data->section; ?> 주차</td>
                        <td class="text-left">
                            <?php if ($mode == 'edit') { ?>
                                <input type="date" name="period_start" value="<?php echo date('Y-m-d', $data->startdate); ?>" />
                                ~ 
                                <input type="date" name="period_end" value="<?php echo date('Y-m-d', $data->enddate); ?>" />
                                <input type="submit" class="red_btn" value="<?php echo get_string('save','local_lmsdata'); ?>" />
                            <?php
                            } else {
                                echo date('Y-m-d', $data->startdate) . ' ~ ' . date('Y-m-d', $data->enddate);
                            }
                            ?>
                        </td>
                    </tr>
                </form>
<?php } ?>
            </tbody>
        </table>
        <div class="btn_area">
<?php if ($dates && $mode == 'edit') { ?>
                <input type="button" onclick="add_period()"  class="blue_btn" value="<?php echo get_string('etc_string','local_lmsdata'); ?>" style="float: left; margin-left:5px;" /> 
                <input type="button" onclick="delete_period()"  class="blue_btn" value="<?php echo get_string('delete', 'local_lmsdata'); ?>" style="float: left; margin-left:5px;" /> 
            <?php } else if ($dates) { ?>
                <input type="button" onclick="location.href = 'period.php?year=<?php echo $year; ?>&term=<?php echo $term; ?>&mode=edit'" class="gray_btn" value="<?php echo get_string('edit','local_lmsdata'); ?>" style="float: left; margin-left:5px; " /> 
<?php } ?>
        </div>   
    </div> <!-- Table Footer Area End -->
</div>
</div>

<script>
    var year = <?php echo $year; ?>;
    var term = <?php echo $term; ?>;
    $('#make_default').click(function () {
        var firstdate = prompt('강좌시작 데이터를 입력해주세요 ex)3-2 or 6-20', '3-2');
        location.href = 'period_maker.php?year=' + year + '&term=' + term + '&firstdate=' + firstdate;
    });

    function add_period() {
        $.ajax({url: "period.ajax.php?mode=add&&year=" + year + '&term=' + term,
            success: function (result) {
                $('#period_period_tbody').append(result);
                alert('추가되었습니다.');
            }
        });
    }
    function delete_period() {
        if (confirm('삭제하시겠습니까?')) {
            $.ajax({url: "period.ajax.php?mode=delete&year=" + year + '&term=' + term,
                success: function (result) {
                    $('#periodid' + result).remove();
                    alert('삭제되었습니다.');
                }
            });
        }
    }
</script>
<?php include_once('../inc/footer.php'); ?>