<?php
require (dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once dirname(dirname(__FILE__)) . '/lib.php';

// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string) new moodle_url('/siteadmin/stats/course_all.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$period = optional_param('period', 'year', PARAM_RAW);

$year = optional_param('year', date('Y'), PARAM_INT);

$term         = optional_param('term', get_config('moodle', 'haxa_term'), PARAM_INT);

$view_type = optional_param('view_type', 'graph', PARAM_RAW);

$query = 'select lc.ohakkwa,count(c.id) as count from {course} c '
        . "join {lmsdata_class} lc on lc.course = c.id where lc.ohakkwa is not null and lc.ohakkwa != '' and lc.term = :term and lc.year = :year group by lc.ohakkwa ";
$courses = $DB->get_records_sql($query, array('year' => $year,'term'=>$term));


include_once ('../inc/header.php');
$datas = '';
$term_arr = lmsdata_get_terms();
unset($term_arr[0]);
$colors = array('1' => '#FDC12D', '2' => '#55B7F8', '3' => '#D4148B', '4' => '#00FFA8', '0' => '#54CAD3');
foreach ($courses as $course) {
    $color = $colors[$course->term];
    $datas .= '{
			"major": "'.$course->ohakkwa.'",
			"count": '.$course->count.',
		},';
}
?>

<div id="contents">
    <?php include_once ('../inc/sidebar_stats.php'); ?>

    <div id="content">
        <div style="clear:both;"><h2>강의 개설현황</h2></div><br>
        <div class="siteadmin_tabs">
            <a href="course_count_year.php"><p class="black_btn">년도별</p></a>
            <a href="course_count_month.php"><p class="black_btn">월별</p></a>
            <a href="course_count_day.php"><p class="black_btn">일별</p></a>
            <a href="course_count_major.php"><p class="black_btn black_btn_selected">학과별</p></a>
        </div><br>
        <!-- Styles -->
        <!-- Styles -->
<style>
#chartdiv {
	width		: 100%;
	height		: 500px;
	font-size	: 11px;
}						
</style>

<!-- Resources -->
<script src="https://www.amcharts.com/lib/3/amcharts.js"></script>
<script src="https://www.amcharts.com/lib/3/serial.js"></script>
<script src="https://www.amcharts.com/lib/3/plugins/export/export.min.js"></script>
<link rel="stylesheet" href="https://www.amcharts.com/lib/3/plugins/export/export.css" type="text/css" media="all" />
<script src="https://www.amcharts.com/lib/3/themes/light.js"></script>

<!-- Chart code -->
<script>
var chart = AmCharts.makeChart("chartdiv", {
	"type": "serial",
     "theme": "light",
	"categoryField": "major",
	"rotate": true,
	"startDuration": 1,
	"categoryAxis": {
		"gridPosition": "start",
		"position": "left"
	},
  "export": {
    "enabled": true
  },
	"trendLines": [],
	"graphs": [
		{
		//	"balloonText": "major:[[value]]",
			"fillAlphas": 0.8,
			"id": "AmGraph-1",
			"lineAlpha": 0.2,
			"title": "major",
			"type": "column",
			"valueField": "count"
		}
	],
	"guides": [],
	"valueAxes": [
		{
			"id": "ValueAxis-1",
			"position": "top",
			"axisAlpha": 0
		}
	],
	"allLabels": [],
	"balloon": {},
	"titles": [],
	"dataProvider": [
		<?php echo $datas; ?>
	],
//    "export": {
//    	"enabled": true
//     }

});
</script>

        <!-- HTML -->
        
        <form name="" id="course_search" class="search_area" action="course_count_major.php" method="get">
            <select title="year" name="year" class="w_160">
                <option value="0"  <?php echo $year == 0 ? 'selected' : ''?>><?php echo get_string('all','local_lmsdata'); ?></option>
                <?php
                    $year_arr = lmsdata_get_years();
                    foreach($year_arr as $tg_year) {
                        $selected = "";
                        if($tg_year == $year) {
                           $selected = "selected";
                        } 
                        echo '<option value="'.$tg_year.'"  '.$selected.'>'. get_string('year','local_lmsdata',$tg_year) . '</option>';
                    }
                ?>
            </select>
            <select name="term" title="term" class="w_260" style="margin:5px 20px 5px 0;">
                <option value="<?php echo get_config('moodle', 'haxa_term'); ?>"> - <?php echo get_string('stats_nowterm', 'local_lmsdata'); ?> -</option>
                <?php 
                foreach($term_arr as $v=>$t) {
                    $selected = '';
                    if($v == $term) {
                        $selected = ' selected';
                    }
                    echo '<option value="'.$v.'"'.$selected.'> '.$t.'</option>';
                }
                ?>
            </select>
            <input type="submit" class="search_btn" value="<?php echo get_string('search', 'local_lmsdata'); ?>"/>          
        </form><!--Search Area2 End-->
        <?php
            $url = $CFG->wwwroot.'/siteadmin/stats/course_count_major.php?'; 
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
                <th>학과</th>
                <th>개설된 강의 수</th>
            </tr>
            <?php 
           foreach ($courses as $course) {
                echo "<tr><td>" . $course->ohakkwa . '</td>';
                echo '<td>'.$course->count.'</td>';
            }
        } else {
            ?>
        
            <div id="chartdiv" style="clear:both; height:1200px;"></div>														
        <?php } ?>
    </div>
</div>