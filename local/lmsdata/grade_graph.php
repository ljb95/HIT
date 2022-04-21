<?php
require_once(dirname(__FILE__) . '/../../config.php');

$id = required_param('id', PARAM_INT);

$lmsdata_class = $DB->get_record('lmsdata_class',array('course'=>$id));

$conn = odbc_connect($CFG->local_haksa_sid, $CFG->local_haksa_username, $CFG->local_haksa_password);

if ($conn) {
    $dates = "SELECT SUM(GPA) / count(GPA) as gpa FROM V_HAK_SCOR_INFO WHERE LEC_CD = ".$lmsdata_class->subject_id;
    $param = array();
    $res = odbc_exec($conn,$dates);
    
    $row = odbc_fetch_array($res);
//    echo '<pre>';
//      print_r(number_format($row['GPA'],2));  // 평점 
//        echo '</pre>';
        
    $dates = "SELECT GRADE,COUNT(USER_NM) as POINT  FROM V_HAK_SCOR_INFO WHERE LEC_CD = 100762 GROUP BY GRADE ORDER BY GRADE ASC";
    $param = array();
    $res = odbc_exec($conn,$dates);
    
    
    //$row = odbc_fetch_array($res);
    
    $grade = '';
    
    while ($row = odbc_fetch_array($res)) {
        $grade .= '{
    "grade": "'.$row['GRADE'].'",
    "point": "'.$row['POINT'].'"
        },';
    }
}




?>
<style>
#chartdiv {
	width		: 100%;
	height		: 500px;
	font-size	: 11px;
}					
</style>


<script src="https://www.amcharts.com/lib/3/amcharts.js"></script>
<script src="https://www.amcharts.com/lib/3/serial.js"></script>
<script src="https://www.amcharts.com/lib/3/plugins/export/export.min.js"></script>
<link rel="stylesheet" href="https://www.amcharts.com/lib/3/plugins/export/export.css" type="text/css" media="all" />
<script src="https://www.amcharts.com/lib/3/themes/light.js"></script>

<!-- Chart code -->
<script>
var chart = AmCharts.makeChart( "chartdiv", {
  "type": "serial",
  "theme": "light",
  "dataProvider": [ <?php echo $grade; ?>  ],
  "valueAxes": [ {
    "gridColor": "#FFFFFF",
    "gridAlpha": 0.2,
    "dashLength": 0
  } ],
  "gridAboveGraphs": true,
  "startDuration": 1,
  "graphs": [ {
    "balloonText": "[[category]]: <b>[[value]]</b>",
    "fillAlphas": 0.8,
    "lineAlpha": 0.2,
    "type": "column",
    "valueField": "point"
  } ],
  "chartCursor": {
    "categoryBalloonEnabled": false,
    "cursorAlpha": 0,
    "zoomable": false
  },
  "categoryField": "grade",
  "categoryAxis": {
    "gridPosition": "start",
    "gridAlpha": 0,
    "tickPosition": "start",
    "tickLength": 20
  },
  "export": {
    "enabled": false
  }

} );
</script>

<!-- HTML -->
<div id="chartdiv"></div>			