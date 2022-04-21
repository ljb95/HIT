<?php
require_once("../../config.php");
require_once("./lib.php");
require_once($CFG->dirroot . "/lib/coursecatlib.php");



// Print the header
$strplural = get_string('template_list', 'local_template');
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading($strplural);

$course_id = required_param('id', PARAM_INT);
$page = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
$search_val = optional_param('searchval', null, PARAM_RAW);


$PAGE->set_url('/local/template/index.php?id=' . $course_id);
$PAGE->set_pagelayout('standard');

/*
  $nav = array();
  $nav[1] = $CFG->wwwroot . '/ustteaching/index.php';
  $nav[2] = get_string('home', 'local_template');
  $nav[3] = '#';
  $nav[4] = get_string('present', 'local_template');
  $nav[5] = $CFG->wwwroot . '/ustteaching/course/template.php?courseid=' . $course_id;
  $nav[6] = get_string('template_application', 'local_template');
 */

$category = $DB->get_record('course_categories', array('idnumber' => TEMPLATE_IDNUMBER));
$course = $DB->get_record('course', array('id' => $course_id));

require_login();

$PAGE->set_course($course);

echo $OUTPUT->header();


$categorys = coursecat::get($category->id);    // 템플릿 category id 값을 입력해줘야 한다.

$in_course = $categorys->get_courses();

$course_list = array();

foreach ($in_course as $key => $value) {
    $course_list[] = $key;
}

$param = array();

if (!empty($search_val)) {
    $param['searchval'] = $search_val;
}

$course = course_get_format($course_id)->get_course();

$course_arr = array($course->id => $course);
$total_tamplate_count = jino_get_template_course($course_list, $param, 0);
?>
<h4 class="page-sub-title"><?php echo get_string('template_list', 'local_template'); ?></h3>

<form class="table-search-option" action="template.php" method="get">
    <input type="hidden" name="courseid" value="<?php echo $course_id; ?>">
    <input type="text" name="searchval" class="" placeholder="<?php echo get_string('template_lecture', 'local_template'); ?>" />
    <input type="submit" class="board-search" value="<?php echo get_string('search','local_template');?>" onclick='searchBoard()' />


</form> <!-- Search Area End -->

<ul class="template-loop">

<?php
if ($total_tamplate_count > 0) {
    $count = 0;
    $startnum = $total_tamplate_count - (($page - 1) * $perpage);
    $template_list = jino_get_template_course($course_list, $param, $page, $perpage);

    foreach ($template_list as $course) {
        ?>
    <form method="post" action="<?php echo $CFG->wwwroot . '/backup/import.php' ?>" id="form<?php echo $course->id?>">
                <input type="hidden" name="id" value="<?php echo $course_id; ?>">
                <input type="hidden" name="target" class="template_taget" value = "1">
                <input type="hidden" name="importid" class="template_importid" value = "<?php echo $course->id ?>">
                <li>
		        <?php
			        $courseimage = new course_in_list($course);
			        $contentimages = "";
			        foreach ($courseimage->get_course_overviewfiles() as $file) {
			            $isimage = $file->is_valid_image();
			            $url = file_encode_url("$CFG->wwwroot/pluginfile.php", '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
			                    $file->get_filearea() . $file->get_filepath() . $file->get_filename(), !$isimage);
			            if ($isimage) {
			                $contentimages .= html_writer::tag('div', html_writer::empty_tag('img', array('src' => $url, 'class' => 'course-description-thumb','style' => 'width: 140px;')), array('class' => 'template-thumb-area'));
			            }
			        }
			
			        if (!empty($contentimages)) {
			            echo $contentimages;
			        } else {
	            ?>
                    <div class="template-thumb-area"><img src="<?php echo $OUTPUT->pix_url('course_thumb_small', 'theme'); ?>" class="course-description-thumb" alt="Course Title" /></div> <!-- Template Thumb Area End -->
                    <?php } ?>
                    <div class="template-meta-area">
                    	<h1 class="template-title"><?php echo $course->fullname; ?></h1>
						<span class="template-date"><?php echo $course->summary; ?></span>
	                    <div class="template_btn_area">
	                        <p class="button preview"><a href="<?php echo $CFG->wwwroot . '/course/view.php?id=' . $course->id ?>" target="_blank"><?php echo get_string('preview','local_template');?></a></p>
	                        <p class="button apply"><a onclick="$('#form<?php echo $course->id?>').submit();" href="#" ><?php echo get_string('application','local_template');?></a></p>
	                    </div> <!-- Button Area End -->
                    </div> <!-- Template Meta Area End -->
                </li>
            </form>

        <?php
        $count++;
    }
} else {
    ?>

        <p><?php echo get_string('template_empty', 'local_template'); ?></p>

    <?php } ?>

</ul>





<?php
echo $OUTPUT->footer();
?>

<!--script type="text/javascript">
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
</script-->