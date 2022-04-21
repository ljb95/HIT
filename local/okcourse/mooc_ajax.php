<?php
require_once('../../config.php');
$perpage      = optional_param('perpage', 12, PARAM_INT); // How many per page.
?>
<!--OCW-->
<?php
    $cnt = 0;
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
<!--OCW-->