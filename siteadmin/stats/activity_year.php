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

$modules = $DB->get_records('modules',array('visible'=>1));

$query = 'select CONCAT(m.name,lc.term),m.name,lc.year,lc.term,count(cm.id) as count from {course} c
            join {lmsdata_class} lc on lc.course = c.id 
            join {course_modules} cm on cm.course = c.id 
            join {modules} m on m.id = cm.module and m.visible = 1 
            where lc.year = :year group by lc.year,lc.term,m.name order by lc.term asc,m.name asc';
$cms = $DB->get_records_sql($query, array('year' => $year));


include_once ('../inc/header.php');
$datas = '';
$term_arr = lmsdata_get_terms();
$colors = array('1' => '#FDC12D', '2' => '#55B7F8', '3' => '#D4148B', '4' => '#00FFA8', '0' => '#54CAD3');
foreach ($cms as $contact) {
    $datas[number_format($contact->term)][$contact->name] = $contact->count;
}
foreach ($modules as $module) {
    $mod_name = get_string('pluginname', $module->name);
            $js_string .= '{
                        "mod_name": "' . $mod_name . '",';
            foreach($datas as $term => $cm){
                    $term_text = $term_arr[$term];
                if(isset($datas[$term][$module->name])){
                   $js_string .= '"'.$term_text.'" : '.$datas[$term][$module->name].',';
                } else {
                   $js_string .= '"'.$term_text.'" : 0,';
                }
            }

            $js_string .= '},';
}
?>

<div id="contents">
    <?php include_once ('../inc/sidebar_stats.php'); ?>

    <div id="content">
        <!-- HTML -->
        <div style="clear:both;"><h2> 활동 사용현황</h2></div>
        <div class="page_navbar"><a href="./contact_stats_day.php"><?php echo get_string('stats_management', 'local_lmsdata'); ?></a> > 활동 사용현황</div>
        <form name="" id="course_search" class="search_area" action="activity_year.php" method="get">
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
        <!-- Styles -->
        <style>
            #chartdiv {
                width	: 150%;
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
                "graphs": [
            <?php foreach($term_arr as $term => $val){ ?>
                    {
                        "balloonText": "[[value]]",
                        "bullet": "round",
                        "title": "<?php echo $val; ?>",
                        "valueField": "<?php echo $val; ?>",
                        "fillAlphas": 0
                    },
            <?php } ?>
                ],
                "chartCursor": {
                    "cursorAlpha": 0,
                    "zoomable": false
                },
                "categoryField": "mod_name",
                "categoryAxis": {
                    "gridPosition": "start",
                    "axisAlpha": 0,
                    "fillAlpha": 0.05,
                    "fillColor": "#000000",
                    "gridAlpha": 0,
                    "position": "top",
                },
  "export": {
    "enabled": true
  }
            });
        </script>
        <?php
            $url = $CFG->wwwroot.'/siteadmin/stats/activity_year.php?'; 
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
                <th rowspan="2">활동명</th>
                <th colspan="<?php echo count($term_arr); ?>">활동량</th>
            </tr>
            <tr>
                <?php foreach($term_arr as $k => $v){ ?>
                <th><?php echo $v; ?></th>
                <?php } ?>
            </tr>
            <?php 
           foreach ($modules as $module) {
                $mod_name = get_string('pluginname', $module->name);
                echo "<tr><td>" . $mod_name . '</td>';
                foreach($term_arr as $term => $cm){
                    $term_text = $term_arr[$term];
                if(isset($datas[$term][$module->name])){
                   echo '<td>'.$datas[$term][$module->name].'</td>';
                } else {
                  echo '<td>0</td>';
                }
            }
            }
        } else {
            ?>
        <div id="chartdiv"></div>														
        <?php } ?>
    </div>
</div>