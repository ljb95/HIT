<?php 
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot.'/lib/coursecatlib.php';
require_once $CFG->dirroot."/siteadmin/lib.php";
require_once $CFG->dirroot."/local/courselist/lib.php";
require_login();

$type = optional_param('type', 1, PARAM_INT);
$coursetype = optional_param('coursetype', 1, PARAM_INT);
$courseid = optional_param('id', 0, PARAM_INT);
$numsections = 0;

if(!empty($courseid)) {
    $course_sql = " SELECT co.*,
                           lc.year, lc.term, lc.subject_id, lc.kor_lec_name, lc.eng_lec_name,cc.path,
                           lc.timeregstart, lc.timeregend, lc.timestart, lc.timeend,lc.prof_userid, lc.isnonformal, lc.certificate, lc.isreged, lc.certiform, lc.certiform_en,
                           ur.firstname,ur.lastname
                    FROM {course} co
                    JOIN {lmsdata_class} lc ON co.id = lc.course 
                    JOIN {course_categories} cc on cc.id = co.category 
                    LEFT JOIN {user} ur ON lc.prof_userid = ur.id
                    WHERE co.id = :courseid "  ;
    $params = array('courseid' => $courseid);
    $course = $DB->get_record_sql($course_sql, $params);
    
    $subject = explode('-', $course->subject_id);
    $path_arr = explode('/', $course->path);
    $category_name = $subject[0];
    
    $numsections = $DB->get_field_sql('SELECT max(section) from {course_sections}
     WHERE course = ?', array($courseid));
}

$context = context_system::instance();
$PAGE->set_context($context);

$PAGE->set_url('/local/courselist/course_add.php');
$PAGE->set_pagelayout('standard');

if(empty($courseid)) {
    $courseacttext =  get_string('course:add', 'local_courselist');
} else {
    $courseacttext =  get_string('course:edit', 'local_courselist');
}
$coursetypetext = ($coursetype==1)? get_string('irregular','local_courselist'):get_string('regular','local_courselist');
$strplural = get_string('pluginnameplural','local_courselist');
$PAGE->navbar->ignore_active();
$PAGE->navbar->add($strplural);
$PAGE->navbar->add($coursetypetext);
$PAGE->navbar->add($courseacttext);
$PAGE->set_title($strplural);
$PAGE->set_heading($courseacttext);
$PAGE->requires->css('/local/courselist/style.css');
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->jquery_plugin('migrate');
$PAGE->requires->js('/siteadmin/manage/course_list.js');
$PAGE->requires->js('/siteadmin/js/lib/jquery.ui.datepicker_lang.js');

echo $OUTPUT->header();

$user = $DB->get_record('lmsdata_user', array('userid'=>$USER->id));
if(!is_siteadmin($USER) && ($user->usergroup != 'pr') && ($user->usergroup != 'sa')){
    redirect($CFG->wwwroot); 
}

//관리자 course 설정 옵션
$course_option = get_config('moodle', 'siteadmin_course_option_set');
$course_option = unserialize($course_option);

$course_start = date('Y-m-d', time());
$course_end = date('Y-m-d', time()+(60*60*24*30));
?>

<form name="" id="course_search" action="<?php echo $CFG->wwwroot.'/local/courselist/course_add.execute.php';?>" method="post" enctype="multipart/form-data">
    <?php
        if(!empty($course)) {
            echo '<input type="hidden" name="courseid" value="'.$courseid.'" />';
        }
        if(!$category = $DB->get_field('course_categories','id',array('visible'=>1, 'parent'=>0, 'idnumber'=>SELF_COURSE_CATEGORY))){
            $category = 1;
        }
    ?>
    <input type="hidden" name="isnonformal" value="<?php echo $course->isnonformal ? $course->isnonformal:$coursetype;?>"/>
    <input type="hidden" name="category" value="<?php echo $course->category ? $course->category:$category;?>"/>
    <table cellpadding="0" cellspacing="0" class="detail">
        <tbody>
             <tr> 
                <td class="field_title"><?php print_string('course:name', 'local_courselist');?></td>
                <td class="field_value">                   
                    <input type="text" name="kor_lec_name" placeholder="<?php print_string('subject:ko_input', 'local_courselist');?>" size="40" value="<?php echo !empty($course->kor_lec_name) ? $course->kor_lec_name : ''; ?>"/>
                    <input type="text" name="eng_lec_name" placeholder="<?php print_string('subject:en_input', 'local_courselist');?>" size="40" value="<?php echo !empty($course->eng_lec_name) ? $course->eng_lec_name : ''; ?>"/>                 
                </td>
            </tr>
            <tr> 
                <td class="field_title"><?php print_string('course:section', 'local_courselist');?></td>
                <td class="field_value">
                   <select name="format">
                       <option value="weeks" <?php echo $course->format == 'weeks' ? "selected" : "" ;?>><?php echo get_string('format:weeks','local_courselist');?></option>
                       <option value="topics" <?php echo $course->format == 'topics' ? "selected" : "" ;?>><?php echo get_string('format:topics','local_courselist');?></option>
                       <option value="oklass_grid" <?php echo $course->format == 'oklass_grid' ? "selected" : "" ;?>><?php echo get_string('format:oklass_grid','local_courselist');?></option>
                   </select>
                   <select name="section" class="w_160">
                        <?php
                        $max = ($numsections > 30) ? $numsections : 30;
                        for ($i = 1; $i <= $max; $i++) {
                            $selected = '';
                            if ((empty($numsections) && $i == 16) || ($numsections == $i)) {
                                $selected = ' selected';
                            }
                            echo '<option value="' . $i . '"' . $selected . '> ' . $i . '</option>';
                        }
                        ?>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="field_title"><?php print_string('course:professor', 'local_courselist');?></td>
                <td class="field_value"> 
                    <input type="text" name="prof_name" size="30" disabled  value="<?php echo !empty($course->prof_userid) ? fullname($course) : fullname($USER); ?>"/>
                    <input type="hidden" name="prof_userid" value="<?php echo !empty($course->prof_userid) ? $course->prof_userid : $USER->id; ?>"/>
                    <input type="button" value="<?php echo get_string('search','local_courselist');?>" class="gray_btn" onclick="search_prof_popup()"/>
                </td>
            </tr>
            <tr>
                <td class="field_title"><?php echo get_string('course:apply','local_courselist');?></td>
                <td class="field_value"> 
                    <?php
                        $isreged = "checked";
                        $disable = "disabled";
                        if(isset($course->isreged) && $course->isreged == 1) {
                            $isreged = "";
                            $disable = "";
                        }
                    ?>
                    <input type="text" name="timeregstart" id="timeregstart" class="w_120" <?php echo $disable ;?> value="<?php echo empty($course->timeregstart) ?   date('Y-m-d', time()) : date('Y-m-d', $course->timeregstart); ?>" placeholder="yyyy-mm-dd"/> ~ 
                    <input type="text" name="timeregend" id="timeregend" class="w_120" <?php echo $disable ;?> value="<?php echo empty($course->timeregend) ?   date('Y-m-d', time()) : date('Y-m-d', $course->timeregend); ?>" placeholder="yyyy-mm-dd"/> 
                    <input type="checkbox" name="isreged" value="1" <?php echo $isreged;?> onclick="text_disable(this, 'timeregstart', false);text_disable(this, 'timeregend', false)"/> <?php echo get_string('apply:isnotreged','local_courselist');?>
                </td>
            </tr>
            <tr>
                <td class="field_title"><?php echo get_string('course:open','local_courselist');?></td>
                <td class="field_value"> 
                    <input type="text" name="timestart" id="timestart" class="w_120" value="<?php echo empty($course->timestart) ?  date('Y-m-d', time()) : date('Y-m-d', $course->timestart); ?>" placeholder="yyyy-mm-dd"/> ~ 
                    <input type="text" name="timeend" id="timeend" class="w_120" value="<?php echo empty($course->timeend) ?  date('Y-m-d', time()) : date('Y-m-d', $course->timeend); ?>" placeholder="yyyy-mm-dd"/> 
                </td>
            </tr>
            <?php 
                if($course_option->certificate){ 
            ?>
            <tr>
                    <td class="field_title"><?php echo get_string('certificate:sel', 'local_courselist'); ?></td>
                    <td class="field_value">
                        <select name="certificate">
                            <option value="1" <?php echo $course->certificate == 1 ? "selected" : ""; ?>><?php echo get_string('sel:yes', 'local_courselist'); ?></option>
                            <option value="0" <?php echo $course->certificate == 0 ? "selected" : ""; ?>><?php echo get_string('sel:no', 'local_courselist'); ?></option>
                        </select>
                        <select name="certiform" style="max-width:none !important;">
                            <option value=""><?php echo get_string('certiform:selko','local_courselist');?></option>
                            <?php
                            $certisql = "select lc.*,lcc.name as gubun from {lmsdata_certificate} lc join {lmsdata_certificate_code} lcc on lcc.id=lc.codeid where lc.lang=:lang order by lc.id asc";
                            $certiforms = $DB->get_records_sql($certisql, array('lang'=>'ko'));
                            foreach ($certiforms as $certiform) {
                                $selected = $certiform->id == $course->certiform ? 'selected' : '';
                                echo '<option value="' . $certiform->id . '" ' . $selected . '>' . '[' . $certiform->gubun . '] ' . $certiform->name . '</option>';
                            }
                            ?>
                        </select>
                        <select name="certiform_en" style="max-width:none !important;">
                            <option value=""><?php echo get_string('certiform:selen','local_courselist');?></option>
                            <?php
                            $certisql = "select lc.*,lcc.name as gubun from {lmsdata_certificate} lc join {lmsdata_certificate_code} lcc on lcc.id=lc.codeid where lc.lang=:lang order by lc.id asc";
                            $certiforms = $DB->get_records_sql($certisql, array('lang'=>'en'));
                            foreach ($certiforms as $certiform) {
                                $selected = $certiform->id == $course->certiform_en ? 'selected' : '';
                                echo '<option value="' . $certiform->id . '" ' . $selected . '>' . '[' . $certiform->gubun . '] ' . $certiform->name . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>
            <?php 
                } else{ 
                    echo '<input type="hidden" name="isnonformal" value="'.($course->isnonformal ? $course->isnonformal:$coursetype).'"/>';
                }
            ?>
            <tr>
                <td class="field_title"><?php echo get_string('course:thumnail','local_courselist');?></td>
                <td class="field_value"> 
                    <input type="file" name="overviewfiles" onchange="filename_del()" size="50"/>
                    <?php
                    if(!empty($course)) {
                        $courseimage = new course_in_list($course);
                        foreach ($courseimage->get_course_overviewfiles() as $file) {
                            $filename = $file->get_filename();
                        }
                        if(!empty($filename)){
                            echo ' <span name="filename">'.$filename.'</span>';
                        }
                    }
                    ?>
                </td>
            </tr>
            <tr>
                <td class="field_title"><?php echo get_string('course:goal','local_courselist');?></td>
                <td class="field_value"> 
                    <textarea name="objective" style="width: 100%;" rows="5" ><?php echo !empty($course->summary) ? $course->summary : ''; ?></textarea>
                </td>
            </tr>
        </tbody>
    </table>

</form><!--Search Area2 End-->
<div class="table-footer-area">
    <div class="btn-area course_add_btn center"> 
        <input type="submit" class="course_add_submit" style="float:left; margin-right: 10px;" value="<?php echo $courseacttext;?>" onclick="course_create_submit()"/>
        <input type="submit" class="course_add_cancel" style="float:left; margin-right: 10px;" value="<?php echo get_string('cancel', 'local_courselist');?>" onclick="javascript:location.href='<?php echo $CFG->wwwroot."/local/courselist/course_manage.php?coursetype=".$coursetype;?>';"/> 
    </div>
</div>
    
<script type="text/javascript">

    function filename_del(){
        $('span[name=filename]').text("");
    }

    function course_create_submit() {
        
        if($("select[name=year]").val() == '0') {
            alert("<?php echo get_string('course:alert01','local_courselist');?>");
            return false;
        }
        if($("select[name=cata1]").val() == '0') {
            alert("<?php echo get_string('course:alert02','local_courselist');?>");
            return false;
        }       
        if( $.trim($("input[name='kor_lec_name']").val()) == '' ) {
            alert("<?php echo get_string('course:alert03','local_courselist');?>");
            return false;
        }
        if( $.trim($("input[name='eng_lec_name']").val()) == '' ) {
            alert("<?php echo get_string('course:alert04','local_courselist');?>");
            return false;
        }
        
        if(!$("input:input[name=isreged]").is(":checked")) {
            if( ($.trim($("input[name='timeregstart']").val()) == '') || ($.trim($("input[name='timeregend']").val()) == '')) {
                alert("<?php echo get_string('course:alert05','local_courselist');?>");
                return false;
            }
        }
 
        if( ($.trim($("input[name='timestart']").val()) == '') || ($.trim($("input[name='timeend']").val()) == '')) {
            alert("<?php echo get_string('course:alert06','local_courselist');?>");
            return false;
        }
        
        if($('select[name=certificate] option:selected').val()==1 && $.trim($('select[name=certiform] option:selected').val()) == ''){
            alert('<?php echo get_string('course:alert07','local_courselist');?>');
            return false;
        }
        
        if($.trim($("input[name='overviewfiles']").val()) != '') {
             var filename = $.trim($("input[name='overviewfiles']").val());
             var extension = filename.replace(/^.*\./, '');
             if(extension == filename) {
                 extension = "";
             } else {
                 extension = extension.toLowerCase();
             }
             if($.inArray( extension, [ "jpg", "png" ] ) == -1) {
                 alert("<?php echo get_string('course:alert08','local_courselist');?>");
                 return false;
             };
        }
         
        $('#course_search').submit();
    }
    
    function course_delete(courseid){
        if(confirm("<?php echo get_string('deletecoursecheck');?>") == true) {
            $.ajax({
              url: '<?php echo $CFG->wwwroot."/siteadmin/manage/course_delete.execute.php"?>',
              method: 'POST',
              data : {
                id : courseid,  
              },
              success: function(data) {
                document.location.href = "<?php echo $CFG->wwwroot."/siteadmin/manage/course_list.php"?>";
              }
            });
        }
    }
    
    function search_prof_popup() {
        var tag = $("<div id='course_prof_popup'></div>");
        $.ajax({
          url: '<?php echo $CFG->wwwroot.'/siteadmin/manage/course_prof.php'; ?>',
          method: 'POST',
          success: function(data) {
            tag.html(data).dialog({
                title: '<?php echo get_string('professorsearch','local_courselist');?>',
                modal: true,
                width: 800,
                resizable: false,
                height: 400,
                buttons: [ {id:'close',
                            text:'<?php echo get_string('cansel','local_courselist');?>',
                            disable: true,
                            click: function() {
                                $( this ).dialog( "close" );
                            }}],
                close: function () {
                    $('#frm_course_prof').remove();
                    $( this ).dialog('destroy').remove()
                }
            }).dialog('open');
          }
        });
    }

    $(document).ready(function() {
        $( "#timestart" ).datepicker({
            dateFormat: "yy-mm-dd",
            onClose: function( selectedDate ) {
                $( "#timeend" ).datepicker( "option", "minDate", selectedDate );
            }
        });
        $( "#timeend" ).datepicker({
            dateFormat: "yy-mm-dd",
            onClose: function( selectedDate ) {
                $( "#timestart" ).datepicker( "option", "maxDate", selectedDate );
            }
        });
        $( "#timeregstart" ).datepicker({
            dateFormat: "yy-mm-dd",
            onClose: function( selectedDate ) {
                $( "#timeregend" ).datepicker( "option", "minDate", selectedDate );
            }
        });
        $( "#timeregend" ).datepicker({
            dateFormat: "yy-mm-dd",
            onClose: function( selectedDate ) {
                $( "#timeregstart" ).datepicker( "option", "maxDate", selectedDate );
            }
        });
        $('select[name=certificate]').on('change',function(){
            if($('select[name=certificate] option:selected').val()==1){
                $('select[name=certiform]').show();
                $('select[name=certiform_en]').show();
            }else{
                $('select[name=certiform]').hide();
                $('select[name=certiform_en]').hide();
            }
        });
    });
</script>

<?php
echo $OUTPUT->footer();
?>
