<?php

require_once("../../config.php");
require_once("./lib.php");
require_once($CFG->dirroot."/lib/coursecatlib.php");

$PAGE->set_url('/local/index.php');
$PAGE->set_pagelayout('course');

// Print the header
$strplural = get_string('template_application','local_template');
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading("template");

$course_id = required_param('id',  PARAM_INT);
$page = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
$search_val = optional_param('searchval', null, PARAM_RAW);


/*
$nav = array();
$nav[1] = $CFG->wwwroot . '/ustteaching/index.php';
$nav[2] = get_string('home', 'local_template');
$nav[3] = '#';
$nav[4] = get_string('present', 'local_template');
$nav[5] = $CFG->wwwroot . '/ustteaching/course/template.php?courseid=' . $course_id;
$nav[6] = get_string('template_application', 'local_template');
*/

$category = $DB->get_record('course_categories',array('idnumber'=>TEMPLATE_IDNUMBER));
$course = $DB->get_record('course',array('id'=>$course_id));


$PAGE->set_course($course);

echo $OUTPUT->header();


$categorys = coursecat::get($category->id);    // 템플릿 category id 값을 입력해줘야 한다.

$in_course = $categorys->get_courses();

$course_list = array();

foreach($in_course as $key => $value){
    $course_list[] = $key;
}

$param = array();

if(!empty($search_val)){
    $param['searchval'] = $search_val;
}

$course = course_get_format($course_id)->get_course();

$course_arr = array($course->id => $course);
$total_tamplate_count = jino_get_template_course($course_list, $param, 0);



?>
        <h3 class="tab_title"><?php echo get_string('template_list','local_template');?></h3>

        <form class="search_area" action="template.php" method="get">
            <input type="hidden" name="courseid" value="<?php echo $course_id ; ?>">
			<input type="submit" class="w_160 search_btn right" value="Search" onclick='searchBoard()' />
            <input type="text" name="searchval" class="w_320 search" placeholder="<?php echo get_string('template_lecture','local_template');?>">
			
        </form> <!-- Search Area End -->
		
        <table cellpadding="0" cellspacing="0" class="normal">
            <thead>
                <tr>
                    <th width="4%"></th>
                    <th width="6%"><?php echo get_string('No','local_template');?></th>
                    <th width="80%"><?php echo get_string('template_lecture','local_template');?></th>
                    <th width="10%"><?php echo get_string('registration_date','local_template');?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                    if ($total_tamplate_count > 0) {
                        $count = 0;
                        $startnum = $total_tamplate_count - (($page - 1) * $perpage);
                        $template_list = jino_get_template_course($course_list, $param, $page, $perpage);

                        foreach ($template_list as $course) {

                            
                ?>
                <tr>
                    <td style="padding-left:2%;"><input type="checkbox" class="template_checked" importid="<?php echo $course->id;?>"></td>
                    <td><?php echo ($startnum - $count); ?></td>
                    <td><a href="<?php echo $CFG->wwwroot.'/course/view.php?id='.$course->id ?>" target="_blank"><?php echo $course->fullname ; ?></td>
                    <td class="number"><?php echo date('Y.m.d', $course->timecreated) ?></td>
                </tr>
                <?php
                        $count++;
                        }
                    }else{
                        ?>
                <tr>
                    <td colspan = "4"><?php echo get_string('template_empty','local_template');?></td>
                </tr>
                <?php    
                    }
                ?>
            </tbody>
        </table>
        <form method="post" action="<?php echo $CFG->wwwroot.'/backup/import.php' ?>" id="import_submit">
            <input type="button" class="white red_bg" value="<?php echo get_string('application','local_template');?>">
            <input type="hidden" name="id" value="<?php echo $course_id;?>">
            <input type="hidden" name="target" class="template_taget" value = "1">
            <input type="hidden" name="importid" class="template_importid" value = "">
        </form>

        <?php
           // print_paging_navbar($total_tamplate_count, $page_name = "page", $page, $perpage, $CFG->wwwroot . '/ustteaching/course/template.php?courseid='.$course_id, $param);
        ?>




<?php

echo $OUTPUT->footer();
?>

<script type="text/javascript">
  $(document).ready(function () {
            
            $('.red_bg').click(function() {
                var count = 0;
               $('.template_checked').each(function(){
                   if($(this).is(":checked")){
                       count++;
                   }     
               });
               
               if(count > 1){
                   alert("한개만 선택해 주세요");
                   $('.template_checked').each(function(){
                       this.checked = false;
                   });
               }else{
                   $('.template_checked').each(function(){
                       if($(this).is(":checked")){
                           var importid = $(this).attr("importid");
                           $('.template_importid').val(importid);
                           $('#import_submit').submit();
                       }
                   });
               }
               
                   //console.log($(this).attr("importid"));
            });
        });
</script>