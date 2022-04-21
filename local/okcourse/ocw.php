<?php

require_once('../../config.php');
$perpage      = optional_param('perpage', 8, PARAM_INT); // How many per page.
$PAGE->set_pagelayout('okocw');
//
$PAGE->set_title('OCW');
//$PAGE->set_heading('OCW');
echo $OUTPUT->header();

$category      = optional_param('category', '', PARAM_TEXT); // How many per page.
$category_id      = optional_param('category_id', '', PARAM_TEXT); // How many per page.

?>
<link href="http://maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
<div id="search_header">
    <input id="ocw_search" type="text"/>
    <label for="ocw_search" class="accesshide">검색 질의를 입력하세요</label>
    <span><i class="fa fa-search" aria-hidden="true"></i></span>
</div> 

          <!--OCW-->
             <?php
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, "http://www.kocw.net/home/api/handler.do?key=cbfdd2be800141a5426e8570c39e8341811ba14b50663ee6&from=20160101&end_num=8&category_type=".$category."&category_id=".$category_id);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_HEADER, 0);
                $xml = curl_exec($ch);
                if (curl_error($ch)) {
                    echo curl_error($ch);
                }
                curl_close($ch);
                $xml = new SimpleXmlElement($xml);

                 echo ' <div class="ocw front_list menu_list">';
                 echo' <div class="side-menu">
                            <ul><h2>'.get_string('category', 'local_okcourse').'</h2>
                                <li><a href="/local/okcourse/ocw.php?category=인문&category_id=1">'.get_string('humanities', 'local_okcourse').'</a></li>
                                <li><a href="/local/okcourse/ocw.php?category=사회&category_id=2">'.get_string('social', 'local_okcourse').'</a></li>
                                <li><a href="/local/okcourse/ocw.php?category=공학&category_id=3">'.get_string('engineering', 'local_okcourse').'</a></li>
                                <li><a href="/local/okcourse/ocw.php?category=자연과학&category_id=4">'.get_string('sciences', 'local_okcourse').'</a></li>
                                <li><a href="/local/okcourse/ocw.php?category=교육학&category_id=5">'.get_string('education', 'local_okcourse').'</a></li>
                                <li><a href="/local/okcourse/ocw.php?category=의약학&category_id=6">'.get_string('medicine', 'local_okcourse').'</a></li>
                                <li><a href="/local/okcourse/ocw.php?category=예술체육&category_id=7">'.get_string('artssports', 'local_okcourse').'</a></li>
                            </ul>
                        </div>';
                 echo '<div class="right-contents">';
                 echo '<div class="front_list_header">';
                 echo '<p><span>KOCW</span><a href="http://www.kocw.net/home/index.do" target="_blank">KOCW '.get_string('shortcuts', 'local_okcourse').'</a></p>';
                 echo '</div>';
                for($i=0; $i<8; $i++)
                {
                    $url    = $xml->list->list_item[$i]->course_url;
                    $title    = $xml->list->list_item[$i]->course_title;
                    $thumbnail = $xml->list->list_item[$i]->thumbnail_url;
                    $provider = $xml->list->list_item[$i]->provider;
                    $lecturer = $xml->list->list_item[$i]->lecturer;
                    echo '<div class="kocw_course list_style01">';
                    echo '<a href="'.$url.'" target="_blank">';
                    if($thumbnail != "")echo '<img src="'.$thumbnail.'" alt="thubnail" title="Course thubnail" />';
                    if($thumbnail == "")echo '<img src="/local/okcourse/pix/no_img.png" class="no_img" alt="thubnail" title="Course thubnail" />';
                    echo '<div class="edx_title"><a href="'.$url.'">'.$title.'</a></div>';
                    echo '<p class="name"><sapn>'.$provider.'</span>&nbsp;|&nbsp;<span>'.$lecturer.'</span></p>';
                    echo '</a>';
                    echo '</div>';
                }
                 echo '</div>';
                echo'</div>';
            ?>
           
           
           
           
           
           
<!--            <div class="ocw front_list">
                <div class="front_list_header">
                    <p><span>KOCW</span><a href="">KOCW 바로가기</a></p>
                </div>
                <div class="edx_course list_style01">
                    <a href="https://www.edx.org/course/biobased-principles-opportunities-wageningenx-bb01x">
                        <img src="https://image.freepik.com/free-photo/working-with-a-coffee_1112-145.jpg" alt="thubnail" title="Course thubnail">
                        <div class="edx_title" title="Biobased Principles and Opportunities">Evaluating Designs with Users</div>
                         <p class="name"><sapn>우송대학교</span>&nbsp;|&nbsp;<span>도경수</span></p>
                    </a>
                </div>
                <div class="edx_course list_style01">
                    <a href="https://www.edx.org/course/respiration-human-body-louvainx-louv8x-0">
                        <img src="https://image.freepik.com/free-photo/ready-for-back-to-school_1134-12.jpg" alt="thubnail" title="Course thubnail">
                        <div class="edx_title" title="Respiration in the Human Body">Introduction to Differential Equations</div>
                        <p class="name"><sapn>우송대학교</span>&nbsp;|&nbsp;<span>도경수</span></p>
                    </a>
                </div>
                 <div class="edx_course list_style01">
                    <a href="https://www.edx.org/course/respiration-human-body-louvainx-louv8x-0">
                        <img src="https://image.freepik.com/free-photo/top-view-of-fresh-pasta-with-aromatic-herbs_1220-430.jpg" alt="thubnail" title="Course thubnail">
                        <div class="edx_title" title="Respiration in the Human Body">UX Design: From Concept to Wireframe</div>
                        <p class="name"><sapn>우송대학교</span>&nbsp;|&nbsp;<span>도경수</span></p>
                    </a>
                </div>
                 <div class="edx_course list_style01">
                    <a href="https://www.edx.org/course/respiration-human-body-louvainx-louv8x-0">
                        <img src="https://image.freepik.com/free-photo/laptop-with-graphics-on-a-desk_1218-558.jpg" alt="thubnail" title="Course thubnail">
                        <div class="edx_title" title="Respiration in the Human Body">Respiration in the Human Body</div>
                        <p class="name"><sapn>우송대학교</span>&nbsp;|&nbsp;<span>도경수</span></p>
                    </a>
                </div>
                <div class="edx_course list_style01">
                    <a href="https://www.edx.org/course/biobased-principles-opportunities-wageningenx-bb01x">
                        <img src="https://image.freepik.com/free-photo/trash-with-paper-balls-and-a-recycling-symbol_1205-359.jpg" alt="thubnail" title="Course thubnail">
                        <div class="edx_title" title="Biobased Principles and Opportunities">Evaluating Designs with Users</div>
                        <p class="name"><sapn>우송대학교</span>&nbsp;|&nbsp;<span>도경수</span></p>
                    </a>
                </div>
                <div class="edx_course list_style01">
                    <a href="https://www.edx.org/course/respiration-human-body-louvainx-louv8x-0">
                        <img src="https://image.freepik.com/free-photo/top-view-of-working-tools_1134-67.jpg" alt="thubnail" title="Course thubnail">
                        <div class="edx_title" title="Respiration in the Human Body">Introduction to Differential Equations</div>
                        <p class="name"><sapn>우송대학교</span>&nbsp;|&nbsp;<span>도경수</span></p>
                    </a>
                </div>
                 <div class="edx_course list_style01">
                    <a href="https://www.edx.org/course/respiration-human-body-louvainx-louv8x-0">
                        <img src="https://image.freepik.com/free-photo/close-up-of-glass-with-lemon-slices-and-spearmint_1220-449.jpg" alt="thubnail" title="Course thubnail">
                        <div class="edx_title" title="Respiration in the Human Body">UX Design: From Concept to Wireframe</div>
                        <p class="name"><sapn>우송대학교</span>&nbsp;|&nbsp;<span>도경수</span></p>
                    </a>
                </div>
                 <div class="edx_course list_style01">
                    <a href="https://www.edx.org/course/respiration-human-body-louvainx-louv8x-0">
                        <img src="https://image.freepik.com/free-photo/freshly-uncorked-bottle-of-champagne_1252-51.jpg" alt="thubnail" title="Course thubnail">
                        <div class="edx_title" title="Respiration in the Human Body">Respiration in the Human Body</div>
                        <p class="name"><sapn>우송대학교</span>&nbsp;|&nbsp;<span>도경수</span></p>
                    </a>
                </div>
            </div>-->
          <!--OCW-->
          
        <!--OCW-->
            <div class="ocw front_list">
                <div class="front_list_header">
                    <p><span>Campus's OCW</span><a href="">Campus's OCW <?php echo get_string('shortcuts', 'local_okcourse')?></a></p>
                </div>
                <div class="edx_course list_style01">
                    <a href="https://www.edx.org/course/biobased-principles-opportunities-wageningenx-bb01x">
                        <img src="https://image.freepik.com/free-photo/worker-reading-news-with-tablet_1162-83.jpg" alt="thubnail" title="Course thubnail">
                        <div class="edx_title" title="Biobased Principles and Opportunities">Evaluating Designs with Users</div>
                        <p class="name"><sapn>사회과학부</span>&nbsp;|&nbsp;<span>김민석</span></p>
                    </a>
                </div>
                <div class="edx_course list_style01">
                    <a href="https://www.edx.org/course/respiration-human-body-louvainx-louv8x-0">
                        <img src="https://image.freepik.com/free-photo/hand-writing-next-to-some-paper-balls_1088-654.jpg" alt="thubnail" title="Course thubnail">
                        <div class="edx_title" title="Respiration in the Human Body">Introduction to Differential Equations</div>
                         <p class="name"><sapn>사회과학부</span>&nbsp;|&nbsp;<span>김민석</span></p>
                    </a>
                </div>
                 <div class="edx_course list_style01">
                    <a href="https://www.edx.org/course/respiration-human-body-louvainx-louv8x-0">
                        <img src="https://image.freepik.com/free-photo/close-up-of-appetizing-sandwich-with-lettuce_1220-335.jpg" alt="thubnail" title="Course thubnail">
                        <div class="edx_title" title="Respiration in the Human Body">UX Design: From Concept to Wireframe</div>
                         <p class="name"><sapn>사회과학부</span>&nbsp;|&nbsp;<span>김민석</span></p>
                    </a>
                </div>
                 <div class="edx_course list_style01">
                    <a href="https://www.edx.org/course/respiration-human-body-louvainx-louv8x-0">
                        <img src="https://image.freepik.com/free-photo/worker-reading-news-with-tablet_1162-83.jpg" alt="thubnail" title="Course thubnail">
                        <div class="edx_title" title="Respiration in the Human Body">Respiration in the Human Body</div>
                         <p class="name"><sapn>사회과학부</span>&nbsp;|&nbsp;<span>김민석</span></p>
                    </a>
                </div>
                <div class="edx_course list_style01">
                    <a href="https://www.edx.org/course/biobased-principles-opportunities-wageningenx-bb01x">
                        <img src="https://image.freepik.com/free-photo/coffee-beans-with-chunks-of-bitter-chocolate_1220-384.jpg" alt="thubnail" title="Course thubnail">
                        <div class="edx_title" title="Biobased Principles and Opportunities">Evaluating Designs with Users</div>
                         <p class="name"><sapn>사회과학부</span>&nbsp;|&nbsp;<span>김민석</span></p>
                    </a>
                </div>
                <div class="edx_course list_style01">
                    <a href="https://www.edx.org/course/respiration-human-body-louvainx-louv8x-0">
                        <img src="https://image.freepik.com/free-photo/close-up-of-music-mixer_1137-262.jpg" alt="thubnail" title="Course thubnail">
                        <div class="edx_title" title="Respiration in the Human Body">Introduction to Differential Equations</div>
                         <p class="name"><sapn>사회과학부</span>&nbsp;|&nbsp;<span>김민석</span></p>
                    </a>
                </div>
                 <div class="edx_course list_style01">
                    <a href="https://www.edx.org/course/respiration-human-body-louvainx-louv8x-0">
                        <img src="https://image.freepik.com/free-photo/book-turning-pages_1150-146.jpg" alt="thubnail" title="Course thubnail">
                        <div class="edx_title" title="Respiration in the Human Body">UX Design: From Concept to Wireframe</div>
                         <p class="name"><sapn>사회과학부</span>&nbsp;|&nbsp;<span>김민석</span></p>
                    </a>
                </div>
                 <div class="edx_course list_style01">
                    <a href="https://www.edx.org/course/respiration-human-body-louvainx-louv8x-0">
                        <img src="https://image.freepik.com/free-photo/blackboard-with-the-wrong-amount_1205-345.jpg" alt="thubnail" title="Course thubnail">
                        <div class="edx_title" title="Respiration in the Human Body">Respiration in the Human Body</div>
                         <p class="name"><sapn>사회과학부</span>&nbsp;|&nbsp;<span>김민석</span></p>
                    </a>
                </div>
            </div>
           
        <!--OCW-->
           
         


<?php echo $OUTPUT->footer();?>