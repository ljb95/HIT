<?php
require_once("../../config.php");
require_once("./lib.php");
require_once($CFG->dirroot . "/lib/coursecatlib.php");



// Print the header
$strplural = get_string('sample_list', 'local_template');
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading($strplural);

$page = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
$search_val = optional_param('searchval', null, PARAM_RAW);


$PAGE->set_url('/local/template/sample.php');
$PAGE->set_pagelayout('standard');

$category = $DB->get_record('course_categories', array('idnumber' => SAMPLE_IDNUMBER));

require_login();


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



$total_tamplate_count = jino_get_template_course($course_list, $param, 0);
?>

<div class="tabGroup">
    <input type="radio" name="tabGroup1" id="rad1" class="tab1">
    <label for="rad1" onclick="location.href = '<?php echo $CFG->wwwroot . "/local/jinoboard/index.php?type=2"; ?>'"><?php echo get_string('QnA', 'local_jinoboard'); ?></label>
    <input type="radio" name="tabGroup1" id="rad2" class="tab2">
    <label for="rad2" onclick="location.href = '<?php echo $CFG->wwwroot . "/local/jinoboard/index.php?type=3"; ?>'"><?php echo get_string('FAQ', 'local_jinoboard'); ?></label>
    <input type="radio" name="tabGroup1" id="rad3" class="tab3">
    <label for="rad3" onclick="location.href = '<?php echo $CFG->wwwroot . "/local/jinoboard/index.php?type=4"; ?>'"><?php echo get_string('usermanual', 'local_jinoboard'); ?></label>
    <input type="radio" name="tabGroup1" id="rad4" class="tab4" checked="checked">
    <label for="rad4" onclick="alert('<?php echo get_string('gettingready', 'local_jinoboard'); ?>');
            location.href = '<?php echo $CFG->wwwroot . "/local/jinoboard/index.php?type=" . $type; ?>'"><?php echo get_string('sample', 'local_jinoboard'); ?></label>
    <br>
</div>
<h4 class="page-sub-title"><?php echo get_string('sample_list', 'local_template'); ?></h3>

<form class="table-search-option" action="template.php" method="get">
    <input type="text" name="searchval" class="w-50" placeholder="<?php echo get_string('sample_lecture', 'local_template'); ?>" style="margin-top: 1em;">
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
            <li>
                <?php
                $courseimage = new course_in_list($course);
                $contentimages = "";
                foreach ($courseimage->get_course_overviewfiles() as $file) {
                    $isimage = $file->is_valid_image();
                    $url = file_encode_url("$CFG->wwwroot/pluginfile.php", '/' . $file->get_contextid() . '/' . $file->get_component() . '/' .
                            $file->get_filearea() . $file->get_filepath() . $file->get_filename(), !$isimage);
                    if ($isimage) {
                        $contentimages .= html_writer::tag('div', html_writer::empty_tag('img', array('src' => $url, 'class' => 'course-description-thumb')), array('class' => 'template-thumb-area'));
                    }
                }

                if (!empty($contentimages)) {
                    echo $contentimages;
                } else {
                    ?>
                    <img src="<?php echo $OUTPUT->pix_url('course_thumb_small', 'theme'); ?>" class="course-description-thumb" alt="Course Title" />
                <?php } ?>
                <h4 class="template-title"><?php echo $course->fullname; ?></h4>
                <span class="template-date"><?php echo date('Y.m.d', $course->timecreated) ?></span>
                <div class="template_btn_area">
                    <p class="button preview"><a href="<?php echo $CFG->wwwroot . '/course/view.php?id=' . $course->id ?>" target="_blank"><?php echo get_string('preview','local_template'); ?></a></p>
                </div>
            </li>

            <?php
            $count++;
        }
    } else {
        ?>

        <p><?php echo get_string('sample_empty', 'local_template'); ?></p>

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