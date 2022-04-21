<?php

require_once('../../config.php');
//$perpage      = optional_param('perpage', 8, PARAM_INT); // How many per page.
$PAGE->set_pagelayout('okocw');
//
$PAGE->set_title('OCW');
//$PAGE->set_heading('OCW');
echo $OUTPUT->header();

?>
<script type="text/javascript">
 var perpage = 8;//더보기 개수
 var count = 0;
$(document).ready(function(){
    //callMooc(12);
    $("#add").click(function(){
        count++;
        callMooc(12+perpage*count);
    });
});
 function callMooc(perpage){
      $.ajax({
      type: "get",
      url: "/local/okcourse/mooc_ajax.php",
      data: {perpage: perpage},
      success:function(html) {
        $( "#view" ).html(html);
      }
    });
 }
   
</script>
<link href="http://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
<div id="search_header">
    <input id="mooc_search" type="text"/>
    <label for="mooc_search" class="accesshide">검색 질의를 입력하세요</label>
    <span><i class="fa fa-search" aria-hidden="true"></i></span>
</div>
    
<div id="view">
<?php
    $cnt = 0;
    $perpage = 12;
    $resp = file_get_contents("https://www.udacity.com/public-api/v0/courses");
    $json_response = json_decode($resp, true);
    echo '<div class="ocw front_list">';
    echo '<div  class="front_list_header"><p><span>Udacity</span><a href="https://www.udacity.com/">Udacity 바로가기</a></p></div>';
    foreach ($json_response["courses"] as $course) {
        echo '<div class="edx_course list_style01">';
        echo '<a href="'.$course["homepage"].'" target="_blank">';
        if($course["image"] == ""){
                echo '<img src="/local/okcourse/pix/no_img.png" class="no_img" alt="thubnail" title="Course thubnail" />';
        } else {
                echo '<img src="'.$course["image"].'" alt="thubnail" title="Course thubnail" />';
        }
        //echo '<p class="school">'.$school.'</p>';
        //cho '<p class="code">'.$code.'</p>';
        echo '<div class="edx_title" title="'.$course["title"].'">'.$course["title"].'</div>';
        //echo '<p>개강예정</p>';
        //echo '<p class="date">개강일: '.$course["starter"].'</p>';
        echo '</a>';
        echo '</div>';
        $cnt++;
        if($perpage == $cnt){
                break;
        }
    }
    echo '</div>'; 
?>
</div>
<div id="add">
    <span><i class="fa fa-caret-down" aria-hidden="true"></i></span>
</div>
<?php echo $OUTPUT->footer();?>