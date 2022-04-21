<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');

// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/support/sms.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);
require_once dirname(dirname(__FILE__)) . '/lib/paging.php';

$field = optional_param('field', '', PARAM_RAW);
$search_val = optional_param('search_val', '', PARAM_RAW);
$startd = optional_param('startd', '', PARAM_ALPHANUMEXT);
$endd = optional_param('endd', '', PARAM_ALPHANUMEXT);

$currpage = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);

$PAGE->set_context($context);
$PAGE->set_url('/siteadmin/support/sms.php');

$query = "SELECT * from {lmsdata_sms} ";

$conditions = array();
$params = array();

$value = $search_val;

if($search_val) {
    if($field=='all'){
        $value1 = $value;
        $value2 = $value;
    $conditions[] = $DB->sql_like('contents',':value1');
    $conditions[] = $DB->sql_like('subject',':value2');
    $params['value1'] = '%'.$value1.'%';
    $params['value2'] = '%'.$value2.'%';
    }else{
    $conditions[] = $DB->sql_like($field,':search_val');
    $params['search_val'] = '%'.$value.'%';
    }
}

if($startd) {
    $startds = explode('-',$startd);
    $sy = $startds[0];
    $sm = $startds[1];
    $sd = $startds[2];

    $sendtime1 = mktime(0,0,0,$sm,$sd,$sy);
    
    $conditions[] = 'sendtime >= :sendtime1';
    $params['sendtime1'] = $sendtime1;
}

if($endd) {
    $endds = explode('-',$endd);
    $sy = $endds[0];
    $sm = $endds[1];
    $sd = $endds[2]+1;

    $sendtime2 = mktime(0,0,0,$sm,$sd,$sy);
    
    $conditions[] = 'sendtime <= :sendtime2';
    $params['sendtime2'] = $sendtime2;
}


$where = '';
if(!empty($conditions)) {
    $where = ' WHERE '.implode(' AND ', $conditions);
}

$sort = ' order by id desc';

$totalcount = $DB->count_records_sql("SELECT count(*) from {lmsdata_sms} ".$where.$sort, $params);
$datas = $DB->get_records_sql($query.$where.$sort, $params, ($currpage - 1) * $perpage, $perpage);

?>
<?php include_once('../inc/header.php'); ?>
<div id="contents">
    <?php include_once('../inc/sidebar_support.php'); ?>
    <div id="content">
        <h3 class="page_title">문자발송</h3>
        <div class="page_navbar"><a href="<?php echo $CFG->wwwroot . '/siteadmin/support/popup.php'; ?>"><?php echo get_string('site_management', 'local_lmsdata'); ?></a> > <a href="<?php echo $CFG->wwwroot . '/siteadmin/support/sms.php'; ?>">문자발송</a></div>
        
        <form name="search_form" class="search_area">
            <label>기간</label>
            <input type="text" name="startd" id="id_startd" size="10" value="<?php echo $startd;?>"/> <span>~</span> <input type="text" name="endd" id="id_endd" size="10" value="<?php echo $endd;?>"/>
            <br/>
            <label>검색조건</label>
            <select class="w_160" name="field" style="margin: 5px 10px 5px 0">
                <option value="subject" <?php if($field=='subject') echo 'selected';?>>제목</option>
                <option value="contents" <?php if($field=='contents') echo 'selected';?>>내용</option>
                <option value="all" <?php if($field=='all') echo 'selected';?>>제목+내용</option>
            </select>
            <input type="text" name="search_val" value="<?php echo $value;?>" class="w_300" placeholder="검색어를 입력하세요."/>
            <input type="submit" class="blue_btn" id="search" value="검색" />
        </form> <!-- Search Form End -->
        
        <table cellspacing="0" cellpadding="0">
            <tr>
                <th style="width:5%;"><?php echo get_string('number', 'local_lmsdata'); ?></th>
                <th style="width:10%;"><?php echo get_string('sms:sender','local_lmsdata'); ?></th>
                <th><?php echo get_string('sms:subject', 'local_lmsdata'); ?></th>
                <th style="width:10%;"><?php echo get_string('sms:sendtime','local_lmsdata'); ?></th>
                <th style="width:10%;"><?php echo get_string('sms:recipients_count', 'local_lmsdata'); ?></th>
                <th style="width:10%;"><?php echo get_string('sms:sendtype','local_lmsdata'); ?></th>
            </tr>
            
            <?php
                if($totalcount>0){
                    $startnum = $totalcount - ($currpage - 1) * $perpage;
                    $count = 0;
                    foreach($datas as $data){
                        if($data->sender == ""){
                            $data->sender = "관리자 사용자";
                        }
                        $dest_count = $DB->count_records_sql("SELECT count(*) from {lmsdata_sms_data} where sms = :sms",array('sms' => $data->id));
                        
                        if($data->schedule_type == 1){
                            $schedule_type = "예약발송";
                        } else {
                            $schedule_type = "즉시발송";
                        }

                        $sendtime = date('Y-m-d H:i:s',$data->sendtime);
                        $now = date('YmdHis');

                        echo '<tr>

                        <td class="number">'.($startnum-$count).'</td>
                            
                        <td>'.$data->sender.'</td>

                        <td style="text-align:left;"><a href="sms_state.php?id='.$data->id.'">'.$data->subject.'</a></td>

                        <td class="number">'.$sendtime.'</td>

                        <td class="number">'.$dest_count.'</td>

                        <td class="number">'.$schedule_type.'</td>

                        </tr>';
                        $count++;
                    }

                }else{
                    echo '<tr><td colspan="9">발송한 문자가 없습니다.</td></tr>';
                }
            ?>
        </table>
        
        <div id="btn_area">
            <input type="button" value="메세지 작성" onclick="location.href = 'sms_write.php'" class="blue_btn" style="float:right;"/>
        </div>    
            <?php
                $page_params = array();
                if($field) {
                    $page_params['field'] = $field;
                }
                if($search_val) {
                    $page_params['search_val'] = $search_val;
                }
                if($startd) {
                    $page_params['startd'] = $startd;
                }
                if($endd) {
                    $page_params['endd'] = $endd;
                }

                print_paging_navbar($totalcount, $currpage, $perpage, 'sms.php', $page_params);
            ?>
            <!-- Breadcrumbs End -->
    </div> <!-- Table Footer Area End -->
</div>
<script type="text/javascript">
  $(function() {
    $( "#id_startd" ).datepicker({
      showOn: "focus",
      dateFormat: "yy-mm-dd",
      onClose: function( selectedDate ) {
        $( "#id_endd" ).datepicker( "option", "minDate", selectedDate );
      }
    });
    $( "#id_endd" ).datepicker({
      showOn: "focus",
      dateFormat: "yy-mm-dd",
      onClose: function( selectedDate ) {
        $( "#id_startd" ).datepicker( "option", "maxDate", selectedDate );
      }
    });
});
</script>
<?php include_once('../inc/footer.php'); ?>
