<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');

require_login();

$context = context_system::instance();
$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');
$PAGE->set_url('/local/evaluation/answers_chart.php');

$strplural = get_string("pluginnameplural", "local_evaluation");
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading($strplural);
echo $OUTPUT->header();

$evaluation = $DB->get_record('lmsdata_evaluation', array('id' => $evaluation));
$evaluation_form = $DB->get_record('lmsdata_evaluation_forms', array('id' => $evaluation->formid));

$questions = $DB->get_records('lmsdata_evaluation_questions', array('formid' => $evaluation->formid), 'sortorder asc', '*');
?>
<div id="chartContainer" style="height: 300px; width: 100%;">
  </div>
<script type="text/javascript" src="./canvasjs-1.8/canvasjs.min.js"></script>
  <script type="text/javascript">
  window.onload = function () {
    var chart = new CanvasJS.Chart("chartContainer",
    {
      theme: "theme2",
      title:{
        text: "교수별 점수"
      },
      animationEnabled: true,
      axisX: {
        valueFormatString: "P0",
        interval:1,
        intervalType: "NUMBER"
        
      },
      axisY:{
        includeZero: false,
        maximum: 5.2,
        interval:1,
        minimum: 0
      },
      data: [
      {        
        type: "line",
        //lineThickness: 3,        
        dataPoints: [
 <?php 
 $evaluation_course_sql = "select DISTINCT tt.profname , max(tt.lecdate) as lectime from {lmsdata_evaluation} eva "
                            . "join {lmsdata_timetable} tt on tt.course = eva.course and profname is not null "
                            . "where eva.id = :evaluationid group by tt.profname order by lectime asc";
                    $evaluation_course_cntsql = "select DISTINCT tt.profname from {lmsdata_evaluation} eva "
                            . "join {lmsdata_timetable} tt on tt.course = eva.course and profname is not null "
                            . "where eva.id = :evaluationid group by tt.profname";
                    $evaluation_courses = $DB->get_recordset_sql($evaluation_course_sql, array('evaluationid' => $evaluation->id));
                    $i=1;
                    $data = '';
                    foreach ($evaluation_courses as $course_evaluation) {
                        $sql = 'select ans.* from {lmsdata_evaluation_answers} ans '
                                . 'join {lmsdata_evaluation_questions} que on que.id = ans.question and que.qtype = 5 '
                                . 'where ans.evaluation = :evaluation and ans.prof_userid = :prof_userid ';
                        $anses = $DB->get_records_sql($sql,array('evaluation'=>$evaluation->id));
                        $point = array();
                        foreach($anses as $ans){
                            $point[] = $ans->answers;
                        }
                        $num = !empty($point[0])?(array_sum($point) / count($point)):0;
                        $data .= '{ x:'.$i.', y: '. $num  .' },';
                        $i++;
                    }
                    echo rtrim($data,',');
 ?>
        ]
      }
      
      
      ]
    });

chart.render();
}
</script>
<div class="table-footer-area">
        <input type="button" class="red_btn" style="margin-right: 10px;" value="돌아가기" onclick="location.href = 'index.php?type=<?php echo $evaluation->type; ?>'" /> 
        <input type="button" class="red_btn" style="margin-right: 10px;" value="결과보기" onclick="location.href = 'answers.php?id=<?php echo $evaluation->id ?>&type=<?php echo $evaluation->type; ?>'" /> 
    </div>
<?php
echo $OUTPUT->footer();
?>