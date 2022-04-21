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

$view_type = optional_param('view_type', 'graph', PARAM_RAW);


$query = 'select IF(lc.isnonformal = 1 ,concat(lc.isnonformal ,lc.term),concat(lc.isnonformal ,lc.term)) as term,count(c.id) as count , lc.isnonformal,lc.univ_type from {course} c '
        . 'join {lmsdata_class} lc on isnonformal != 2 and lc.course = c.id where startdate != 0  and lc.year = :year group by lc.term ,lc.isnonformal,lc.univ_type';
$courses = $DB->get_records_sql($query, array('year' => $year));
$ary = array('010','011','020','021','110','111','120','121');
foreach($ary as $int){
            if(!isset($courses[$int])){
            $courses[$int]->term = $int;
            $courses[$int]->count = 0;
            }
}  
include_once ('../inc/header.php');
$datas = '';
$term_arr = lmsdata_get_terms2();
$colors = array('1' => '#FDC12D', '2' => '#55B7F8', '3' => '#D4148B', '4' => '#00FFA8', '0' => '#54CAD3');
foreach ($courses as $course) {
    $term = $term_arr[$course->term];
    $color = $colors[$course->term];
    $datas .= '{
    "course": "' . $term . '",
    "count": ' . $course->count . ',
    "color": "' . $color . '"
  },';
}
?>

<div id="contents">
    <?php include_once ('../inc/sidebar_stats.php'); ?>

    <div id="content">
        <div style="clear:both;"><h2>강의 개설현황</h2></div><br>
        <div class="siteadmin_tabs">
            <a href="course_count_year.php"><p class="black_btn black_btn_selected">년도별</p></a>
            <a href="course_count_month.php"><p class="black_btn">월별</p></a>
            <a href="course_count_day.php"><p class="black_btn">일별</p></a>
            <a href="course_count_major.php"><p class="black_btn">학과별</p></a>
        </div><br>
        <div class="page_navbar"><a href="./contact_stats_day.php"><?php echo get_string('stats_management', 'local_lmsdata'); ?></a> > 강의 개설현황</div>
        <!-- Styles -->
        <style>
            #chartdiv {
                width: 100%;
                height: 500px;
            }

            .amcharts-export-menu-top-right {
                top: 10px;
                right: 0;
            }
        </style>
        <script src="https://www.amcharts.com/lib/3/amcharts.js"></script>
        <script src="https://www.amcharts.com/lib/3/serial.js"></script>
        <script src="https://www.amcharts.com/lib/3/plugins/export/export.min.js"></script>
        <link rel="stylesheet" href="https://www.amcharts.com/lib/3/plugins/export/export.css" type="text/css" media="all" />
        <script src="https://www.amcharts.com/lib/3/themes/light.js"></script>

        <!-- Chart code -->
        <script type="text/javascript">
            var chart = AmCharts.makeChart("chartdiv", {
                "type": "serial",
                "theme": "light",
                "marginRight": 70,
                "dataProvider": [<?php echo $datas; ?>],
                "startDuration": 1,
                "graphs": [{
                        "balloonText": "<b>[[category]]: [[value]]</b>",
                        "fillColorsField": "color",
                        "fillAlphas": 0.9,
                        "lineAlpha": 0.2,
                        "type": "column",
                        "valueField": "count"
                    }],
                "chartCursor": {
                    "categoryBalloonEnabled": false,
                    "cursorAlpha": 0,
                    "zoomable": false
                },
                         "export": {
    "enabled": true
  },
                "categoryField": "course",
                "categoryAxis": {
                    "gridPosition": "start",
                    "labelRotation": 45
                }

            });
        </script>

        <!-- HTML -->
        <div>
        <form name="" id="course_search" class="search_area" action="course_count_year.php" method="get">
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
            <input type="submit" class="search_btn" value="<?php echo get_string('search', 'local_lmsdata'); ?>"/>          
        </form><!--Search Area2 End-->
        </div>
        <?php
            $url = $CFG->wwwroot.'/siteadmin/stats/course_count_year.php?'; 
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
                <th>학기</th>
                <th>개설된 강의 수</th>
            </tr>
            <?php 
            foreach ($courses as $course) {
                echo '<tr>';
                $term = $term_arr[$course->term];
                echo '<td>'. $term .'</td>';
                 echo '<td>'. $course->count.'</td>';
                echo '</tr>';
            }
        } else {
                        ?>
                    <div id="chartdiv"></div>	
        <?php } ?>
        </table>
												
    </div>
</div>