<?php 
error_reporting(E_ALL);
ini_set("display_errors", 1);

require (dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once dirname(dirname (__FILE__)).'/lib/paging.php';
require_once dirname(dirname (__FILE__)).'/lib.php';



// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/stats/contact_stats_month.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$currpage     = optional_param('page', 1, PARAM_INT);
$perpage      = optional_param('perpage', 31, PARAM_INT);
$year         = optional_param('year', 0, PARAM_INT);
$mon          = optional_param('mon', 0, PARAM_INT);
$target       = optional_param('target', 'all', PARAM_RAW);
$username     = optional_param('username', null, PARAM_RAW);

$view_type = optional_param('view_type', 'graph', PARAM_RAW);

// 현재 년도, 학기
if(!$year) {
    $year = get_config('moodle', 'haxa_year'); 
}
if(!$mon){
    $date = date("Y-m");
    $mon = date("m");
}else{
    $date = $year.'-'.$mon;
} 



$sql_select  = "SELECT CONCAT(FROM_UNIXTIME(lsl.log_date,'%d'),mobile) as dt,FROM_UNIXTIME(lsl.log_date,'%d') as day, COUNT(*) AS count ,mobile, lsl.log_date ";

$sql_from    = " FROM {siteadmin_loginfo} lsl
                 JOIN {user} mu on mu.username = lsl.username 
                 JOIN {lmsdata_user} lu on lu.userid = mu.id ";

$page_params = array();
$sql_where = '';
$conditions = array('lsl.action = :isloggedin');
$param['isloggedin'] = 'logged';

    $date_start =  strtotime($date.' 00:00:00');
    
    $last_day = date('t',$date_start);    
    $date_end =  strtotime($date.'-'.$last_day .' 23:59:59');
    $conditions[] = "(lsl.log_date > :date_start AND lsl.log_date < :date_end)";
    $param['date_start'] = $date_start;
    $param['date_end'] = $date_end;
    
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
$sql_groupby = " GROUP BY FROM_UNIXTIME(lsl.log_date,'%d'),mobile";

$contact_stats = $DB->get_records_sql($sql_select.$sql_from.$sql_where.$sql_groupby, $param);

$datas = array();
foreach ($contact_stats as $contact) {
    $datas[number_format($contact->day)][$contact->mobile] = $contact->count;
    $datas[number_format($contact->day)]['logdate'] = $contact->log_date;
}

$last_day = date('t',strtotime("$year-$mon-1"));
for ($i = 1; $i <= $last_day; $i++) {
            $js_string .= '{
                        "day": "' . $i . '일",';
            if(isset($datas[$i]['P'])){
                 $js_string .= '"PC" : '.$datas[$i]['P'].',';
            } else {
                $js_string .= '"PC" : 0,';
            }
            
            if(isset($datas[$i]['M'])){
                $js_string .= '"Mobile" : '.$datas[$i]['M'].',';
            } else {
                 $js_string .= '"Mobile" : 0,';
            }

            $js_string .= '},';
}

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
            <a href="contact_stats_month.php"><p class="black_btn black_btn_selected"><?php echo get_string('stats_monthly', 'local_lmsdata'); ?></p></a>
            <a href="contact_stats_year.php"><p class="black_btn"><?php echo get_string('stats_yearly', 'local_lmsdata'); ?></p></a>
            <a href="contact_stats_term.php"><p class="black_btn"><?php echo get_string('stats_periodsearch', 'local_lmsdata'); ?></p></a>
        </div>
        <div class="page_navbar"><a href="./contact_stats_month.php"><?php echo get_string('stats_management', 'local_lmsdata'); ?></a> > <?php echo get_string('stats_loginstats', 'local_lmsdata'); ?></div>
        
        <form name="" id="course_search" class="search_area" action="contact_stats_month.php" method="get">
            <input type="hidden" name="page" value="1" />
            
            <select name="year" class="w_80">
                <option value="0"> - <?php echo get_string('contents_now', 'local_lmsdata'); ?> -</option>
                <?php 
                $years = lmsdata_get_years();
                foreach($years as $v=>$y) {
                    $selected = '';
                    if($v == $year) {
                        $selected = ' selected';
                    }
                    echo '<option value="'.$v.'"'.$selected.'> '.$y.'</option>';
                }
                ?>
            </select>
            <label><?php echo get_string('contents_year', 'local_lmsdata'); ?> &nbsp;</label>
            <select name="mon" class="w_80">
                <option value="0"> - <?php echo get_string('contents_now', 'local_lmsdata'); ?> -</option>
                <?php 
                $mons = lmsdata_get_mons($mon);
                echo $mons;
                ?>
            </select>
            <label><?php echo get_string('contents_month', 'local_lmsdata'); ?> &nbsp;</label>
            <select name="target" onchange="setusernameinput();">
                <option value="all" <?php if($target == 'all') echo 'selected';?>><?php echo get_string('all', 'local_lmsdata'); ?></option>
                <option value="rs" <?php if($target == 'rs') echo 'selected';?>><?php echo get_string('role:rs', 'local_lmsdata'); ?></option>
                <option value="pr" <?php if($target == 'pr') echo 'selected';?>><?php echo get_string('role:pr', 'local_lmsdata'); ?></option>
                <option value="target" <?php if($target == 'target') echo 'selected';?>><?php echo get_string('specific_target', 'local_lmsdata'); ?></option>
            </select>
            <input value="<?php echo $username;?>" type="text" name="username" onclick="search_prof_popup()"> 
            <input type="submit" class="blue_btn" value="<?php echo get_string('stats_search', 'local_lmsdata'); ?>" onclick="#" style="margin:0 0 5px 5px;"/>          
        </form><!--Search Area2 End-->
        
        <?php
            $url = $CFG->wwwroot.'/siteadmin/stats/contact_stats_month.php?'; 
            foreach($_REQUEST as $key => $val){
               $url.= $key.'='.$val.'&';
            }
            $url .= 'view_type='; 
        ?>
        <div class="jb_toggle">
            <div class="list_toggle toggle <?php if($view_type == 'list'){ echo 'selected'; } ?>" onclick="location.href='<?php echo $url.'list'; ?>'"></div>
        <div class="graph_toggle toggle <?php if($view_type == 'graph'){ echo 'selected'; } ?>" onclick="location.href='<?php echo $url.'graph'; ?>'"></div>
        </div>
         <?php if($view_type == 'list'){ ?>
         <table>
            <tr>
                <th rowspan="2" style='width:20%;'><?php echo get_string('stats_accesstime', 'local_lmsdata'); ?></th>
                <th colspan="2" style='width:20%;'><?php echo get_string('stats_accesscount', 'local_lmsdata'); ?></th>
                <th rowspan="2" style='width:10%;'><?php echo get_string('stats_accesslog', 'local_lmsdata'); ?></th>
            </tr>
            <tr><th>PC</th><th>Mobile</th></tr>
            <?php for ($i = 0; $i <= 6; $i++) {
                 $day = get_string('day_week'.$i, 'local_lmsdata');
                echo "<tr>";
            echo '<td> ' . $day . '</td>';
            if(isset($datas[$i]['P'])){
                 echo '<td>'.$datas[$i]['P'].'</td>';
            } else {
                echo '<td>0</td>';
            }
            
            if(isset($datas[$i]['M'])){
                echo '<td>'.$datas[$i]['M'].'</td>';
            } else {
                 echo '<td>0</td>';
            }
            ?>
               <td style='width:10%;'><input type="button" title="<?php echo get_string('stats_view', 'local_lmsdata'); ?>" alt="<?php echo get_string('stats_view', 'local_lmsdata'); ?>" id="view" class="blue_btn" value="<?php echo get_string('stats_view', 'local_lmsdata'); ?>" onclick="contact_log(<?php echo $datas[$i]['logdate']; ?>)"></td>
            <?php
            echo '</tr>';
            }  ?>
         </table>
         <?php
         } else { ?>
                         <!-- Styles -->
        <style>
            #chartdiv {
                width	: 100%;
                height	: 500px;
            }					
                        .amcharts-main-div { 
                margin-top: 192px;
            }
        </style>

        <!-- Resources -->
        <script src="https://www.amcharts.com/lib/3/amcharts.js"></script>
        <script src="https://www.amcharts.com/lib/3/serial.js"></script>
        <script src="https://www.amcharts.com/lib/3/themes/light.js"></script>
        <script src="https://www.amcharts.com/lib/3/plugins/export/export.min.js"></script>
        <link rel="stylesheet" href="https://www.amcharts.com/lib/3/plugins/export/export.css" type="text/css" media="all" />

        <!-- Chart code -->
        <script>
            var chart = AmCharts.makeChart("chartdiv", {
                "type": "serial",
                "theme": "light",
                "legend": {
                    "useGraphSettings": true
                },
                "dataProvider": [<?php echo $js_string; ?>],
                "valueAxes": [{
                        "integersOnly": true,
                        "minimum": 0,
                        "axisAlpha": 0,
                        "dashLength": 5,
                        "gridCount": 10,
                        "position": "left",
                        "title": ""
                    }],
                "startDuration": 0.5,
                "graphs": [
                    {
                        "balloonText": "[[value]]",
                        "bullet": "round",
                        "title": "Mobile",
                        "valueField": "Mobile",
                        "fillAlphas": 0
                    },{
                        "balloonText": "[[value]]",
                        "bullet": "round",
                        "title": "PC",
                        "valueField": "PC",
                        "fillAlphas": 0
                    }
                ],
                "chartCursor": {
                    "cursorAlpha": 0,
                    "zoomable": false
                },
                "categoryField": "day",
                "categoryAxis": {
                    "gridPosition": "start",
                    "axisAlpha": 0,
                    "fillAlpha": 0.05,
                    "fillColor": "#000000",
                    "gridAlpha": 0,
                    "position": "top"
                },
  "export": {
    "enabled": true
  }

            });

        </script>
        <div id="chartdiv"></div>
         <?php } ?>
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
    function contact_log(time) {
        var tag = $("<div id='contact_log_popup'></div>");
        $.ajax({
          url: '<?php echo $SITECFG->wwwroot.'/siteadmin/stats/contact_view.ajax.php'; ?>',
          method: 'POST',
          data: {
              time: time,
              timestart : <?php echo $date_start?>,
              timeend : <?php echo $date_end?>,
              stat : 'month'
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

