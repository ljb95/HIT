<?php 
error_reporting(E_ALL);
ini_set("display_errors", 1);

require (dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once dirname(dirname (__FILE__)).'/lib/paging.php';
require_once dirname(dirname (__FILE__)).'/lib.php';



// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/stats/contact_stats_term.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$currpage     = optional_param('page', 1, PARAM_INT);
$perpage      = optional_param('perpage', 100, PARAM_INT);
$startyear         = optional_param('startyear', 0, PARAM_INT);
$startmon         = optional_param('startmon', 0, PARAM_INT);
$startday         = optional_param('startday', 0, PARAM_INT);
$endyear         = optional_param('endyear', 0, PARAM_INT);
$endmon         = optional_param('endmon', 0, PARAM_INT);
$endday         = optional_param('endday', 0, PARAM_INT);
$target         = optional_param('target', 'all', PARAM_RAW);
$username     = optional_param('username', null, PARAM_RAW);

// 현재 년도, 학기
if(!$startyear || !$endyear) {
    $startyear = get_config('moodle', 'haxa_year'); 
    $endyear = get_config('moodle', 'haxa_year'); 
}
if(!$startmon || !$startday || !$endmon || !$endday){
    $date = date("Y-m-d");
    $startmon = date("m");
    $startday = date("d");
    $endmon = date("m");
    $endday = date("d");
}else{
    $startdate = $startyear.'-'.$startmon.'-'.$startday;
    $enddate = $endyear.'-'.$endmon.'-'.$endday;
} 

$sql_select  = "SELECT CONCAT(FROM_UNIXTIME(lsl.log_date, '%Y/%m/%d'),mobile) as pk, FROM_UNIXTIME(lsl.log_date, '%Y/%m/%d') as hour,mobile, COUNT(*) AS count, log_date ";

$sql_from    = " FROM {siteadmin_loginfo} lsl
                 JOIN {user} mu on mu.username = lsl.username 
                 JOIN {lmsdata_user} lu on lu.userid = mu.id ";

$page_params = array();
$sql_where = '';
$conditions = array('lsl.action = :isloggedin');
$param['isloggedin'] = 'logged';

   
    $date_start =  strtotime($startdate.' 00:00:00');
    $date_end =  strtotime($enddate.' 23:59:59');
    $conditions[] = "(lsl.log_date > :date_start AND lsl.log_date < :date_end)";
    $param['date_start'] = $date_start;
    $param['date_end'] = $date_end;
//    echo date('H', 1479340676);
    
    if($target != 'all'){
        if($target == 'target'){
            if(!empty($username)){
                $conditions[] = "mu.username = :username";
                $param['username'] = $username;
            }
        } else {
            $conditions[] = "lu.usergroup = :usergroup";
            $param['usergroup'] = $target;
        }
    }
    
if($conditions) $sql_where = ' WHERE '.implode(' AND ',$conditions);
$sql_groupby = " GROUP BY FROM_UNIXTIME(lsl.log_date, '%Y/%m/%d'),mobile";

$contact_stats = $DB->get_records_sql($sql_select.$sql_from.$sql_where.$sql_groupby, $param);
//$count_contact_stats = $DB->count_records_sql("SELECT COUNT(*) ".$sql_from.$sql_where, $param);

$js = array(
    $CFG->wwwroot.'/siteadmin/manage/course_list.js'
);
?>

<?php include_once ('../inc/header.php');?>
<div id="contents">
    <?php include_once ('../inc/sidebar_stats.php');?>
    <div id="content">
        <h3 class="page_title"><?php echo get_string('stats_loginstats', 'local_lmsdata'); ?></h3>
        <p class="page_sub_title"> <?php echo get_string('stats_longtext1', 'local_lmsdata'); ?></p>
        <div class="siteadmin_tabs">
            <a href="contact_stats_day.php"><p class="black_btn"><?php echo get_string('stats_daily', 'local_lmsdata'); ?></p></a>
            <a href="contact_stats_day_week.php"><p class="black_btn"><?php echo get_string('stats_day_week', 'local_lmsdata'); ?></p></a>
            <a href="contact_stats_week.php"><p class="black_btn"><?php echo get_string('stats_week', 'local_lmsdata'); ?></p></a>
            <a href="contact_stats_month.php"><p class="black_btn"><?php echo get_string('stats_monthly', 'local_lmsdata'); ?></p></a>
            <a href="contact_stats_year.php"><p class="black_btn"><?php echo get_string('stats_yearly', 'local_lmsdata'); ?></p></a>
            <a href="contact_stats_term.php"><p class="black_btn black_btn_selected"><?php echo get_string('stats_periodsearch', 'local_lmsdata'); ?></p></a>
        </div>
        <div class="page_navbar"><a href="./contact_stats.php"><?php echo get_string('stats_management', 'local_lmsdata'); ?></a> > <?php echo get_string('stats_loginstats', 'local_lmsdata'); ?></div>
       
        
        <form name="" id="course_search" class="search_area" action="contact_stats_term.php" method="get">
            <input type="hidden" name="page" value="1" />
            <label><?php echo get_string('stats_periodsearch', 'local_lmsdata'); ?> &nbsp;</label>
            <select name="startyear" class="w_80">
                <option value="0"> - <?php echo get_string('contents_now', 'local_lmsdata'); ?> -</option>
                <?php 
                $years = lmsdata_get_years();
                foreach($years as $v=>$y) {
                    $selected = '';
                    if($v == $startyear) {
                        $selected = ' selected';
                    }
                    echo '<option value="'.$v.'"'.$selected.'> '.$y.'</option>';
                }
                ?>
            </select>
            <label><?php echo get_string('contents_year', 'local_lmsdata'); ?> &nbsp;</label>
            <select name="startmon" class="w_80">
                <option value="0"> - <?php echo get_string('contents_now', 'local_lmsdata'); ?> -</option>
                <?php 
                $mons = lmsdata_get_mons($startmon);
                echo $mons;
                ?>
            </select>
            <label><?php echo get_string('contents_month', 'local_lmsdata'); ?> &nbsp;</label>
            <select name="startday" class="w_80">
                <option value="0"> - <?php echo get_string('contents_now', 'local_lmsdata'); ?> -</option>
                <?php 
                $days = lmsdata_get_days($startday);
                echo $days;
                ?>
            </select>
            <label><?php echo get_string('contents_day', 'local_lmsdata'); ?> &nbsp; ~ </label>
            <select name="endyear" class="w_80">
                <option value="0"> - <?php echo get_string('contents_now', 'local_lmsdata'); ?> -</option>
                <?php 
                $years = lmsdata_get_years();
                foreach($years as $v=>$y) {
                    $selected = '';
                    if($v == $endyear) {
                        $selected = ' selected';
                    }
                    echo '<option value="'.$v.'"'.$selected.'> '.$y.'</option>';
                }
                ?>
            </select>
            <label><?php echo get_string('contents_year', 'local_lmsdata'); ?> &nbsp;</label>
            <select name="endmon" class="w_80">
                <option value="0"> - <?php echo get_string('contents_now', 'local_lmsdata'); ?> -</option>
                <?php 
                $mons = lmsdata_get_mons($endmon);
                echo $mons;
                ?>
            </select>
            <label><?php echo get_string('contents_month', 'local_lmsdata'); ?> &nbsp;</label>
            <select name="endday" class="w_80">
                <option value="0"> - <?php echo get_string('contents_now', 'local_lmsdata'); ?> -</option>
                <?php 
                $days = lmsdata_get_days($endday);
                echo $days;
                ?>
            </select>
            <label><?php echo get_string('contents_day', 'local_lmsdata'); ?> &nbsp;</label>
            <select name="target" onchange="setusernameinput();">
                <option value="all" <?php if($target == 'all') echo 'selected';?>><?php echo get_string('all', 'local_lmsdata'); ?></option>
                <option value="rs" <?php if($target == 'rs') echo 'selected';?>><?php echo get_string('role:rs', 'local_lmsdata'); ?></option>
                <option value="pr" <?php if($target == 'pr') echo 'selected';?>><?php echo get_string('role:pr', 'local_lmsdata'); ?></option>
                <option value="target" <?php if($target == 'target') echo 'selected';?>><?php echo get_string('specific_target', 'local_lmsdata'); ?></option>
            </select>
            <input value="<?php echo $username;?>" type="text" name="username" onclick="search_prof_popup()"> 
            <input type="submit" class="blue_btn" value="<?php echo get_string('stats_search', 'local_lmsdata'); ?>" onclick="#" style="margin:0 0 5px 5px;"/>          
        </form><!--Search Area2 End-->
        <table>
            <tr>
                <th style='width:15%;'><?php echo get_string('stats_accesstime', 'local_lmsdata'); ?></th>
                <th style='width:10%;'>디바이스</th>
                <th style='width:15%;'><?php echo get_string('stats_accesscount', 'local_lmsdata'); ?></th>
                <th style='width:50%;'><?php echo get_string('stats_accesschat', 'local_lmsdata'); ?></th>
                <th style='width:10%;'><?php echo get_string('stats_accesslog', 'local_lmsdata'); ?></th>
            </tr>
            <?php
            if(!$contact_stats) { ?>
            <tr>
                <td colspan="24">통계내용이 없습니다.</td>
            </tr>
            <?php } else {
     foreach($contact_stats as $contact_stat) {         
         $a2 = $contact_stat->count;
         if($a1<$a2){
             $a1 = $a2;
         }         
     }
            foreach($contact_stats as $contact_stat) {
                switch($contact_stat->mobile){
                    case 'P': $device = 'PC'; break;
                    case 'M': $device = 'Mobile'; break;
                    default : $device = '기타';  break;
                }
            ?>
            <tr>
                <td style='width:15%;'><?php echo $contact_stat->hour; ?></td>
                <td style='width:10%;'><?php echo $device; ?></td>
                <td style='width:15%;'><?php echo $contact_stat->count; ?></td>
                <td style='width:50%;'><div style="width: <?php echo ($contact_stat->count/$a1)*100?>%; height: 50%;background-color: #a1a1a8;"></div></td>
                <td style='width:10%;'><input type="button" title="<?php echo get_string('stats_view', 'local_lmsdata'); ?>" alt="<?php echo get_string('stats_view', 'local_lmsdata'); ?>" id="view" class="blue_btn" value="<?php echo get_string('stats_view', 'local_lmsdata'); ?>" onclick="contact_log(<?php echo $contact_stat->log_date?>, '<?php echo $contact_stat->mobile;?>')"></td>
            </tr>
            <?php }} ?>
        </table><!--Table End-->
        <?php
         print_paging_navbar_script($count_courses, $currpage, $perpage, 'javascript:cata_page(:page);');
        ?>
    </div><!--Content End-->
    
</div> <!--Contents End-->
<script type="text/javascript">
    $(window).ready(function(){
        setusernameinput();
    });
    function course_edit_popup(id) {
        var tag = $("<div></div>");
        $.ajax({
          url: '<?php echo $SITECFG->wwwroot.'/siteadmin/stats/course_form.php'; ?>',
          data: {
              parent: $('[name=parent]').val(),
              category: $('[name=category]').val(),
              id: id
          },
          success: function(data) {
            tag.html(data).dialog({
                title: '<?php echo get_string('stats_learningactivitystatus', 'local_lmsdata'); ?>',
                modal: true,
                width: 600,
                maxHeight: getWindowSize().height - 20,
                close: function () {
                    $( this ).dialog('destroy').remove()
                }
            }).dialog('open');
          }
        });
    }
        function course_all_excel() {
        <?php
        $query_string = '';
        if(!empty($param)) {
            $query_array = array();
            foreach($param as $key=>$value) {
                $query_array[] = urlencode( $key ) . '=' . urlencode( $value );
            }
            $query_string = '?'.implode('&', $query_array);
        }
        ?>
        var url = "course_all.excel.php<?php echo $query_string; ?>";
        
        document.location.href = url;
    }
    function contact_log(time, mobile) {
        var tag = $("<div id='contact_log_popup'></div>");
        $.ajax({
          url: '<?php echo $SITECFG->wwwroot.'/siteadmin/stats/contact_view.ajax.php'; ?>',
          method: 'POST',
          data: {
              time: time,
              timestart : <?php echo $date_start?>,
              timeend : <?php echo $date_end?>,
              stat : 'term',
              mobile : mobile
          },
          success: function(data) {
            tag.html(data).dialog({
                title: '접속로그',
                modal: true,
                width: 800,
                maxHeight: getWindowSize().height - 20,
                buttons: [ {id:'close',
                            text:'닫기', 
                            disable: true,
                            click: function() {
                                $( this ).dialog( "close" );
                            }}],
                open : function() {
                    var t = $(this).parent();
                    var w = $(window);
                    var s = getWindowSize();
                    
                    var x = (s.width / 2) - (t.width() / 2) + w.scrollLeft();
                    if(x < 0) x = 0;
                    var y = (s.height / 2) - (t.height() / 2) + w.scrollTop();
                    if(y < 0) y = 0;
                    t.offset({
                        top: y,
                        left: x
                    });
                },
                close: function () {
                    $( this ).dialog('destroy').remove()
                }
            }).dialog('open');
          }
        });
    }
    
    function setusernameinput(){
        if($('select[name=target]').val() == 'target'){
            $('input[name=username]').css('display', '');
        } else {
            $('input[name=username]').css('display', 'none');
        }
    }
    
    function search_prof_popup() {
        var tag = $("<div id='search_user_popup'></div>");
        $.ajax({
          url: '<?php echo $CFG->wwwroot.'/siteadmin/stats/search_user.php'; ?>',
          method: 'POST',
          success: function(data) {
            tag.html(data).dialog({
                title: '<?php echo get_string('user_search','local_lmsdata'); ?>',
                modal: true,
                width: 800,
                resizable: false,
                height: 400,
                buttons: [ {id:'close',
                            text:'<?php echo get_string('cancle','local_lmsdata'); ?>',
                            disable: true,
                            click: function() {
                                $( this ).dialog( "close" );
                            }}],
                close: function () {
                    $('#frm_search_user').remove();
                    $( this ).dialog('destroy').remove()
                }
            }).dialog('open');
          }
        });
    }
</script>
 <?php include_once ('../inc/footer.php');?>

