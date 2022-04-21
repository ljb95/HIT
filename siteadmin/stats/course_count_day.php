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

$month = optional_param('month', date('m'), PARAM_RAW); 

$view_type = optional_param('view_type', 'graph', PARAM_RAW);

$years = $DB->get_records_sql("select distinct from_unixtime(startdate, '%Y') as year from {course} where startdate != 0");

$query = "select from_unixtime(startdate, '%Y%m%d') as ymd,from_unixtime(startdate, '%d') as day,from_unixtime(startdate, '%Y') as year ,count(id) as count from {course} where startdate != 0 and  from_unixtime(startdate, '%m') = :month  group by from_unixtime(startdate, '%d')";
$courses = $DB->get_records_sql($query, array('month' => $month));
include_once ('../inc/header.php');
$datas = array();
foreach ($courses as $course) { 
    $datas[number_format($course->day)][$course->year] = $course->count;
}
?>

<div id="contents">
    <?php include_once ('../inc/sidebar_stats.php'); ?>

    <div id="content">
        <div style="clear:both;"><h2>강의 개설현황</h2></div><br>
                <div class="siteadmin_tabs">
            <a href="course_count_year.php"><p class="black_btn">년도별</p></a>
            <a href="course_count_month.php"><p class="black_btn">월별</p></a>
            <a href="course_count_day.php"><p class="black_btn black_btn_selected">일별</p></a>
            <a href="course_count_major.php"><p class="black_btn">학과별</p></a>
        </div><br>
        <?php

        $graphs = '';
        foreach ($years as $year => $val) {
        $graphs .= '{
                        "balloonText": "[[value]]",
                        "bullet": "round",
                        "title": "'.$year.'",
                        "valueField": "'.$year.'",
                        "fillAlphas": 0
                    },';
        }
        $js_string = '';
        for ($i = 1; $i <= 31; $i++) {
            $js_string .= '{
                        "day": ' . $i . ',';
            foreach ($years as $year => $val) {
                if(isset($datas[$i][$year])){
                    $js_string .= '"'.$year.'" : '.$datas[$i][$year].',';
                } else {
                    $js_string .= '"'.$year.'" : 0,';
                }
            }

            $js_string .= '},';
        }
        ?>
        <!-- Styles -->
        <style>
            #chartdiv {
                width	: 100%;
                height	: 500px;
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
                "graphs": [<?php echo $graphs; ?>],
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
        
        <form name="" id="course_search" class="search_area" action="course_count_day.php" method="get">
            <select title="month" name="month" class="w_160">
                <?php 
                    for($i =1; $i <=31; $i++){
                        
                        $selected  = ($month == $i)?'selected':'';
                        echo '<option '.$selected.' value="'.str_pad($i,2,0,STR_PAD_LEFT).'">'.$i.' 월</option>';
                    }
                ?>
            </select>
            <input type="submit" class="search_btn" value="<?php echo get_string('search', 'local_lmsdata'); ?>"/>          
        </form><!--Search Area2 End-->
        <!-- HTML -->
                <?php
            $url = $CFG->wwwroot.'/siteadmin/stats/course_count_day.php?'; 
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
                <th rowspan="2">일</th>
                <th colspan="<?php echo  count($years); ?>">년도</th>
            </tr>
            <tr>
                <?php 
                foreach ($years as $year => $val) {
                    echo '<th>'.$year.'</th>';
                }
                ?>
            </tr>
            <?php 
           for ($i = 1; $i <= 31; $i++) {
            echo "<tr><td>" . $i . '</td>';
            foreach ($years as $year => $val) {
                echo '<td>';
                if(isset($datas[$i][$year])){
                    echo $datas[$i][$year];
                } else {
                    echo '0';
                }
                            echo '</td>';
            }

        }
        } else {
            
     ?>
        <div id="chartdiv"></div>												
        <?php } ?>
    </div>
</div>