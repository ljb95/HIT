<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/enrol/externallib.php');

require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/support/sms_state.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);
require_once dirname(dirname(__FILE__)) . '/lib/paging.php';

$id = optional_param('id', '', PARAM_RAW);

$currpage = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);

$smsdata = $DB->get_record('lmsdata_sms', array('id' => $id));
$smsusers = $DB->get_records_sql("SELECT * FROM {lmsdata_sms_data} WHERE sms = :sms ORDER BY FULLNAME ASC", array('sms' => $id), ($currpage - 1) * $perpage, $perpage);

$totalcount = $DB->count_records_sql("SELECT count(*) FROM {lmsdata_sms_data} WHERE sms = ".$id, $params);
?>
<?php include_once('../inc/header.php'); ?>
<div id="contents">
<?php include_once('../inc/sidebar_support.php'); ?>
    <div id="content">
        <h3 class="page_title">SMS 상세보기</h3>
        <div class="page_navbar"><a href="./notices.php"><?php echo get_string('site_management', 'local_lmsdata'); ?></a> > <a href="./sms.php">SMS 발송관리</a> > 상세보기</div>
        <table cellpadding="0" cellspacing="0" class="detail">
            <tbody>
                <tr>
                    <td class="field_title">제목</td>
                    <td class="field_value number"><?php echo $smsdata->subject; ?></td>
                </tr>
                <tr>
                    <td class="field_title">발송자 명</td>
                    <td class="field_value"><?php echo $smsdata->sender; ?></td>
                </tr>
                <tr>
                    <td class="field_title">발송자 연락처</td>
                    <td class="field_value"><?php echo $smsdata->callback; ?></td>
                </tr>
                <tr>
                    <td class="field_title">발송시간</td>
                    <td class="field_value"><?php echo date('Y-m-d',$smsdata->sendtime); ?></td>
                </tr>
                <tr>
                    <td class="field_title">발송타입</td>
                    <td class="field_value"><?php if($smsdata->schedule_type == 1) echo "예약발송";  else echo "즉시발송"; ?></td>
                </tr>
                <tr>
                    <td class="field_title">내용</td>
                    <td class="field_value">
                        <div class="editor">
                            <p><?php echo $smsdata->contents; ?></p>
                        </div>
                    </td>
                </tr>
            </tbody>
        </table>

        <p class="search_result">(<?php echo ceil($totalcount/$perpage);?>페이지, 총 <?php echo $totalcount?>건)</p>

        <table cellpadding="0" cellspacing="0">
            <tbody>
                <tr>
                    <th width="10%">번호</th>
                    <th width="30%">이름</th>
                    <th>전화번호</th>
                </tr>
                <?php
                if ($totalcount > 0) {
                    $startnum = $totalcount - ($currpage - 1) * $perpage;
                    $count = 0;
                    foreach ($smsusers as $smsuser) {
                    echo '<tr>
                    <td class="number">' . ($startnum-$count) . '</td>
                    <td>' . $smsuser->fullname . '</td>
                    <td class="number">'.$smsuser->phone. '</td>
                    </tr>';
                        $count++;
                    }
                }else {
                    echo '<tr><td colspan="5">데이터가 없습니다.</td></tr>';
                }
                ?>
            </tbody>
        </table>
        
        <div id="btn_area">
            <input type="button" id="add_user" class="blue_btn" value="목록" style="float: left; border: 1px solid #999" onclick="location.href='sms.php';"/>
        </div>   
        <?php
        $page_params = array();
        $page_params['id'] = $id;

        print_paging_navbar($totalcount, $currpage, $perpage, 'sms_state.php', $page_params);
        //    print_paging_nav(200, 16, 10, $SITECFG->wwwroot.'/admin/course/category.php', $page_params);
        ?>
    </div>
</div>

<?php
include_once '../footer.php';
?>
