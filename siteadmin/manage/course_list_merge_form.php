<?php
require_once dirname(dirname(dirname (__FILE__))).'/config.php';
require_once dirname(dirname (__FILE__)).'/lib.php';
//require_once $CFG->dirroot.'/local/courselist/lib.php';
require_once $CFG->dirroot.'/lib/coursecatlib.php';

// Check for valid admin user - no guest autologin

require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/manage/course_list_add.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$course_list       = optional_param_array('course', array(), PARAM_INT);

// 현재 년도, 학기
$year = get_config('moodle', 'haxa_year');
$term = get_config('moodle', 'haxa_term');

$js = array(
    $CFG->wwwroot.'/siteadmin/manage/course_list.js'
);

?>

<?php include_once (dirname(dirname (__FILE__)).'/inc/header.php'); ?>
<div id="contents">
    <?php include_once  (dirname(dirname (__FILE__)).'/inc/sidebar_manage.php');?>

    <div id="content">
        <h3 class="page_title">강의 생성</h3>
        <div class="page_navbar"><a href="./category_list.php"><?php echo get_string('course_management', 'local_lmsdata'); ?></a> > <a href="./course_list.php"><?php echo get_string('opencourse', 'local_lmsdata'); ?></a> > 통합분반생성</div>

        <form name="" id="course_search" action="course_list_merge.execute.php" method="post" enctype="multipart/form-data">
            <?php
                if(!empty($course)) {
                    echo '<input type="hidden" name="courseid" value="'.$courseid.'" />';
                }
            ?>
            <table cellpadding="0" cellspacing="0" class="detail">
                <tbody>
                    <tr>
                        <td class="field_title"><?php echo get_string('year2','local_lmsdata'); ?></td>
                        <td class="field_value">
                            <select name="year" class="w_160">
                            <?php 
                                $years = lmsdata_get_years();
                                foreach($years as $v=>$y) {
                                    $selected = '';
                                    if($v == $year) {
                                        $selected = ' selected';
                                    }
                                    echo '<option value="'.$v.'"'.$selected.'> '.$y.'</option>';
                                }
                            ?>      
                            </select>
                            <select name="term" class="w_160">
                            <?php 
                                $terms = lmsdata_get_terms();
                                foreach($terms as $v=>$t) {
                                    $selected = '';
                                    if($v == $term) {
                                        $selected = ' selected';
                                    }
                                    echo '<option value="'.$v.'"'.$selected.'> '.$t.'</option>';
                                }
                            ?>     
                            </select>
                        </td>
                    </tr>
                     <tr>
                        <td class="field_title"><?php echo get_string('course_name', 'local_lmsdata'); ?></td>
                        <td class="field_value">
                            <p style="margin-bottom: 7px;">
                            <input type="text" name="category_name" placeholder="<?php echo get_string('cate_search','local_lmsdata'); ?>" size="30" disabled />
                            <input type="hidden" name="category" />
                            <input type="hidden" name="categoryname" />
                            <input type="button" value="<?php echo get_string('search','local_lmsdata'); ?>" class="gray_btn" onclick="search_category_popup()"/>
                            </p>
                            <p>
                            <input type="text" name="kor_lec_name" placeholder="<?php echo get_string('placeholder1','local_lmsdata'); ?>" size="60" />
                            <input type="text" name="eng_lec_name" placeholder="<?php echo get_string('placeholder2','local_lmsdata'); ?>" size="60" />
                            </p>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_title"><?php echo get_string('prof_1', 'local_lmsdata'); ?></td>
                        <td class="field_value"> 
                            <input type="text" name="prof_name" placeholder="<?php echo get_string('prof_search','local_lmsdata'); ?>" size="30" disabled />
                            <input type="hidden" name="prof_userid" />
                            <input type="button" value="<?php echo get_string('search','local_lmsdata'); ?>" class="gray_btn" onclick="search_prof_popup()"/>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_title">실습분반</td>
                        <td class="field_value"> 
                            <input type="text" name="sbb" size="10" value="00"/>
                            * 실습분반이 없을 경우 00, 있을 경우 분반번호 입력
                        </td>
                    </tr>
                    <tr>
                        <td class="field_title">종별</td>
                        <td class="field_value"> 
                            <select name="required" class="w_90">
                                <option value="1" >전필</option>
                                <option value="2" >전선</option>
                                <option value="3" >기타</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_title">학점</td>
                        <td class="field_value"> 
                            <input type="text" name="grade" size="10" v/>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_title"><?php echo get_string('class','local_lmsdata'); ?></td>
                        <td class="field_value">
                            <input type="checkbox" name="hyear[]" value="1" /> 1<?php echo get_string('class','local_lmsdata'); ?>
                            <input type="checkbox" name="hyear[]" value="2" /> 2<?php echo get_string('class','local_lmsdata'); ?>
                            <input type="checkbox" name="hyear[]" value="3" /> 3<?php echo get_string('class','local_lmsdata'); ?>
                            <input type="checkbox" name="hyear[]" value="4" /> 4<?php echo get_string('class','local_lmsdata'); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_title"><?php echo get_string('enrolment', 'local_lmsdata'); ?></td>
                        <td class="field_value"> 
                            <input type="text" name="timeregstart" id="timeregstart" class="w_120" disabled value="" placeholder="yyyy-mm-dd"/> ~ 
                            <input type="text" name="timeregend" id="timeregend" class="w_120" disabled value="" placeholder="yyyy-mm-dd"/> 
                            <input type="checkbox" name="isreged" value="1" checked onclick="text_disable(this, 'timeregstart', false);text_disable(this, 'timeregend', false)"/> 수강신청 받지 않음
                        </td>
                    </tr>
                    <tr>
                        <td class="field_title"><?php echo get_string('opencourse_term', 'local_lmsdata'); ?></td>
                        <td class="field_value"> 
                            <input type="text" name="timestart" id="timestart" class="w_120" value="" placeholder="yyyy-mm-dd"/> ~ 
                            <input type="text" name="timeend" id="timeend" class="w_120" value="" placeholder="yyyy-mm-dd"/> 
                        </td>
                    </tr>
                    <tr>
                        <td class="field_title">강의시간</td>
                        <td class="field_value"> 
                            <input type="text" name="lectime1" size="10" value="<?php echo !empty($lectime[1]) ? $lectime[1] : ''; ?>"/>
                            <select name="lectime2" class="w_90">
                                <option value="1" >주</option>
                                <option value="2" >시간</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_title">강의유형</td>
                        <td class="field_value"> 
                            <select name="lectype" class="w_90">
                                <option value="1" >강의형</option>
                                <option value="2" >실습형</option>
                                <option value="3" >기타</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_title"><?php echo get_string('courseimg', 'local_lmsdata'); ?></td>
                        <td class="field_value"> 
                            <input type="file" name="overviewfiles" onchange="filename_del()" size="50"/>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_title">분반 리스트</td>
                        <td class="field_value">
                        <?php
                            list($sql_in, $params) = $DB->get_in_or_equal($course_list, SQL_PARAMS_NAMED, 'course');
                            $sql_where = " WHERE course ".$sql_in;
                            $sql_select = "SELECT id, course, kor_lec_name, eng_lec_name, subject_id, sbb from {lmsdata_class} ";

                            $courses = $DB->get_records_sql($sql_select.$sql_where, $params);
                            foreach ($courses as $course) {
                                echo '<label class="w100">'.$course->sbb.'분반</label> ['.$course->subject_id.'] '.$course->kor_lec_name.'</br>';
                                echo '<input type="hidden" name="course[]" value="'.$course->course.'" />';
                            }
                        ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_title">기존강의숨김</td>
                        <td class="field_value"> 
                            <input type="radio" name="visible" value="0" checked/>기준 외 분반 비활성 : 학생이 강의 접근을 못하나 교수자는 접근할 수 있음.
                            </br>
                            <input type="radio" name="visible" value="1" /><?php echo get_string('msg3','local_lmsdata'); ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_title"><?php echo get_string('learning_objectives', 'local_lmsdata'); ?></td>
                        <td class="field_value"> 
                            <textarea name="objective" class="w_100" rows="5" ><?php echo !empty($course->summary) ? $course->summary : ''; ?></textarea>
                        </td>
                    </tr>
                </tbody>
            </table>

        </form><!--Search Area2 End-->
        <div id="btn_area">
            <input type="submit" class="blue_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('save','local_lmsdata'); ?>" onclick="course_create_submit()"/>
            <?php if(!empty($courseid)) { ?>
                <input type="button" class="blue_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('delete', 'local_lmsdata'); ?>" onclick="course_delete('<?php echo $courseid;?>')"/>
            <?php } ?>
            <input type="submit" class="blue_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('list2','local_lmsdata'); ?>" onclick="javascript:location.href='course_list.php';"/>
        </div>
    </div><!--Content End-->

</div> <!--Contents End-->
<script type="text/javascript">

    function filename_del(){
        $('span[name=filename]').text("");
    }

    function course_create_submit() {
        if($("select[name=year]").val() == '0') {
            alert("<?php echo get_string('alert3','local_lmsdata'); ?>");
            return false;
        }
        if($("select[name=term]").val() == '0') {
            alert("<?php echo get_string('alert4','local_lmsdata'); ?>");
            return false;
        }

        if($.trim($("input[name='category']").val()) == '0') {
            alert("<?php echo get_string('alert2','local_lmsdata'); ?>");
            return false;
        }
        if( $.trim($("input[name='kor_lec_name']").val()) == '' ) {
            alert("<?php echo get_string('alert5','local_lmsdata'); ?>");
            return false;
        }
        if( $.trim($("input[name='eng_lec_name']").val()) == '' ) {
            alert("<?php echo get_string('alert6','local_lmsdata'); ?>");
            return false;
        }
        
        if( $.trim($("input[name='sbb']").val()) == '' ) {
            alert("실습분반을 입력하세요");
            return false;
        }
        
        <?php if(empty($courseid)) { ?>
            
         // 과목코드 중복 체크
        var year = $.trim($("select[name=year]").val());
        var term = $.trim($("select[name=term]").val());
        var categoryname = $.trim($("input[name=categoryname]").val());
        var sbb = $.trim($("input[name=sbb]").val());
        var bool_subject = check_subject_id(year, term, categoryname, sbb);
        if(bool_subject) {
            alert(year+'년도 '+term+' 과정코드('+categoryname+')의 실습분반('+sbb+')이 이미 존재합니다.');
            return false;
        };
        <?php } ?>
        
        if(!$("input:input[name=isreged]").is(":checked")) {
            if( ($.trim($("input[name='timeregstart']").val()) == '') || ($.trim($("input[name='timeregend']").val()) == '')) {
                alert("<?php echo get_string('alert11','local_lmsdata'); ?>");
                return false;
            }
        }
        if( ($.trim($("input[name='timestart']").val()) == '') || ($.trim($("input[name='timeend']").val()) == '')) {
            alert("<?php echo get_string('alert12','local_lmsdata'); ?>");
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
                 alert("<?php echo get_string('onlyimg','local_lmsdata'); ?>");
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

    function check_subject_id(year, term, categoryname, sbb) {
        var duplicated = true;

        $.ajax({
            url : "./course_list_subject.ajax.php",
            type: "post",
            data : {
                year : year,
                term : term,
                categoryname : categoryname,
                sbb : sbb
            },
            async: false,
            success: function(data){
               duplicated = (data  === 'true');
            },
            error:function(e){
                console.log(e.responseText);
            }
        });

        return duplicated;
    }
    
    function search_prof_popup() {
        var tag = $("<div id='course_prof_popup'></div>");
        $.ajax({
          url: '<?php echo $CFG->wwwroot.'/siteadmin/manage/course_prof.php'; ?>',
          method: 'POST',
          success: function(data) {
            tag.html(data).dialog({
                title: '<?php echo get_string('prof_search','local_lmsdata'); ?>',
                modal: true,
                width: 800,
                resizable: false,
                height: 400,
                buttons: [ {id:'close',
                            text:'<?php echo get_string('cancle','local_lmsdata'); ?>',
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
    
    function search_category_popup(){
        var tag = $("<div id='search_category_popup'></div>");
        $.ajax({
          url: '<?php echo $CFG->wwwroot.'/siteadmin/manage/search_category.php'; ?>',
          method: 'POST',
          success: function(data) {
            tag.html(data).dialog({
                title: '<?php echo get_string('cate_search','local_lmsdata'); ?>',
                modal: true,
                width: 800,
                resizable: false,
                height: 400,
                buttons: [ {id:'close',
                            text:'<?php echo get_string('cancle','local_lmsdata'); ?>',
                            disable: true,
                            click: function() {
                                $( this ).dialog( "close" );
                            }}],
                close: function () {
                    $('#frm_search_category').remove();
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
    });
</script>
 <?php
 include_once ('../inc/footer.php');
            
  