<?php
require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
require_once dirname(dirname(__FILE__)) . '/lib.php';
//require_once $CFG->dirroot.'/local/courselist/lib.php';
require_once $CFG->dirroot . '/lib/coursecatlib.php';

// Check for valid admin user - no guest autologin

$LMSUSER = $DB->get_record('lmsdata_user', array('userid' => $USER->id));

if(($LMSUSER->usergroup != 'de' && $LMSUSER->usergroup != 'pr') || $LMSUSER->menu_auth == 9){
        $roleadmin = true;
    } else {
        $roleadmin = false;
    }

require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string) new moodle_url('/siteadmin/manage/course_list_add.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$courseid = optional_param('id', 0, PARAM_INT);
$coursetype = optional_param('coursetype', 1, PARAM_INT); //0:교과, 1:비교과, 2:이러닝

// 학과 리스트 출력
$dept_sql = "select distinct dept_cd, dept from {lmsdata_user} ";
$dept_lists = $DB->get_records_sql($dept_sql,array());

$numsections = 0;
if (!empty($courseid)) {
    $course_sql = " SELECT co.*,
                           lc.year, lc.term, lc.subject_id, lc.kor_lec_name, lc.eng_lec_name, lc.univ_type , cc.path,
                           lc.timeregstart, lc.timeregend, lc.timestart, lc.timeend, lc.prof_userid, lc.isnonformal, lc.certificateid, lc.isreged, lc.certiform, lc.certiform_en, lc.bunban, lc.tag, lc.learningtime, 
                           ur.firstname, ur.lastname 
                    FROM {course} co
                    JOIN {lmsdata_class} lc ON co.id = lc.course 
                    JOIN {course_categories} cc on cc.id = co.category 
                    LEFT JOIN {user} ur ON lc.prof_userid = ur.id
                    WHERE co.id = :courseid ";
    $params = array('courseid' => $courseid);
    $course = $DB->get_record_sql($course_sql, $params); 
    $coursetype = $course->isnonformal;
    $path_arr = explode('/', $course->path);

    $numsections = $DB->get_field_sql('SELECT max(section) from {course_sections}
     WHERE course = ?', array($courseid));
}

// 현재 년도, 학기
if ($coursetype == 0) {
    $year = get_config('moodle', 'haxa_year');
    $term = get_config('moodle', 'haxa_term');
}
//관리자 course 설정 옵션
$course_option = get_config('moodle', 'siteadmin_course_option_set');
$course_option = unserialize($course_option);

$js = array(
    '../js/ckeditor-4.3/ckeditor.js',
    '../js/ckfinder-2.4/ckfinder.js',
    $CFG->wwwroot . '/siteadmin/manage/course_list.js'
);
$arr = array(0 => 'oklass_regular', 1 => 'oklass_irregular', 2 => 'oklass_selfcourse');
?>

<?php include_once (dirname(dirname(__FILE__)) . '/inc/header.php'); ?>
<div id="contents">
    <?php include_once (dirname(dirname(__FILE__)) . '/inc/sidebar_manage.php'); ?>

    <div id="content">
        <h3 class="page_title"><?php echo empty($courseid) ? get_string('create_course', 'local_lmsdata') : get_string('edit_course', 'local_lmsdata'); ?></h3>
        <div class="page_navbar"><a href="./category_list.php"><?php echo get_string('course_management', 'local_lmsdata'); ?></a> > <a href="./course_list.php"><?php echo get_string('opencourse', 'local_lmsdata'); ?></a> > <?php echo empty($courseid) ? get_string('create_course', 'local_lmsdata') : get_string('edit_course', 'local_lmsdata'); ?></div>

        <form name="" id="course_search" action="course_list_add.execute.php" method="post" enctype="multipart/form-data">
            <?php
            if (!empty($course)) {
                echo '<input type="hidden" name="courseid" value="' . $courseid . '" />';
            }
            ?>
            <table cellpadding="0" cellspacing="0" class="detail">
                <tbody>
                    <?php if ($roleadmin) { ?>
                        <tr>
                            <td class="field_title"><?php echo get_string('gubun', 'local_lmsdata'); ?></td>
                            <td class="field_value">
                                <?php
                                switch ($coursetype) {
                                    case 0: $coursetypetext = get_string('regular_course', 'local_lmsdata');
                                        break;
                                    case 1: $coursetypetext = get_string('irregular_activity', 'local_lmsdata');
                                        break;
                                    case 2: $coursetypetext = get_string('elearning_course', 'local_lmsdata');
                                        break;
                                }
                                echo $coursetypetext;
                                echo '<input type="hidden" name="isnonformal" value="' . $coursetype . '"/>';
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="field_title"> <?php echo get_string('case', 'local_lmsdata'); ?></td>
                            <td class="field_value">
                                <input type="hidden" name="coursetype" value="<?php echo $coursetype; ?>" />
                                <?php
                                switch ($coursetype) {
                                    case 0:
                                        // echo get_string('stats_regular', 'local_lmsdata'); 
                                        $cata1 = $DB->get_record('course_categories', array('idnumber' => 'oklass_regular'));
                                        break;
                                    case 1:
                                        //    echo get_string('irregular_course', 'local_lmsdata');
                                        $cata1 = $DB->get_record('course_categories', array('idnumber' => 'oklass_irregular'));
                                        break;
                                    case 2:
                                        //echo get_string('elearning_course', 'local_lmsdata');
                                        $cata1 = $DB->get_record('course_categories', array('idnumber' => 'oklass_selfcourse'));
                                        break;
                                }
                                ?>
                                <input type="hidden" id="cate1" value="<?php echo $cata1->id; ?>">
                                <select title="category02" name="cata2" id="course_search_cata2" onchange="cata2_changed(this)" class="w_160">
                                    <option value="0"> - <?php echo get_string('all', 'local_lmsdata'); ?> -</option>
    <?php
    if ($cata1) {
        $catagories = $DB->get_records('course_categories', array('visible' => 1, 'id' => $cata1->id), 'sortorder', 'id, idnumber, name');
        if (!empty($path_arr[2])) {
            $cata2 = $path_arr[2];
        }
        foreach ($catagories as $catagory) {
            $selected = '';
            if ($courseid != 0) {
                $selected = ' selected';
            }
            echo '<option value="' . $catagory->id . '"' . $selected . '> ' .$catagory->name . '</option>';
        }
    }
    ?>
                                </select>
                                <select title="category03" name="cata3" id="course_search_cata3" class="w_160">
                                    <option value="0"> - <?php echo get_string('all', 'local_lmsdata'); ?> -</option>
    <?php
    if ($cata1 && $cata2) {
        $catagories = $DB->get_records('course_categories', array('visible' => 1, 'parent' => $cata2), 'sortorder', 'id, idnumber, name');
        if (!empty($path_arr[3])) {
            $cata3 = $path_arr[3];
        }
        foreach ($catagories as $catagory) {
            if($catagory->name == ' ' || $catagory->name == '' || $catagory->name == null){ continue; }
            $selected = '';
            if ($catagory->id == $cata3) {
                $selected = ' selected';
            }
            echo '<option value="' . $catagory->id . '"' . $selected . '> ' . $catagory->name . '</option>';
        }
    }
    ?>
                                </select>
                            </td>
                        </tr>
<?php } ?>
                    <tr class="display_cousetype_0">
                        <td class="field_title"><?php echo get_string('year2', 'local_lmsdata'); ?></td>
                        <td class="field_value">
                            <select title="year" name="year" class="w_160">
<?php
$years = lmsdata_get_years();
if (!empty($course->year)) {
    $year = $course->year;
}
if (empty($year))
    $year = date('Y');
foreach ($years as $v => $y) {
    $selected = '';
    if ($v == $year) {
        $selected = ' selected';
    }
    echo '<option value="' . $v . '"' . $selected . '> ' . $y . '</option>';
}
?>      
                            </select>
                            <select title="term" name="term" class="w_160">
                                <?php
                                $terms = lmsdata_get_terms();
                                if (!empty($course->year)) {
                                    $term = $course->term;
                                }

                                if (empty($course->term)) {
                                    $term = get_config('moodle', 'haxa_term');
                                }

                                foreach ($terms as $v => $t) {
                                    $selected = '';
                                    if ($v == $term) {
                                        $selected = ' selected';
                                    }
                                    echo '<option value="' . $v . '"' . $selected . '> ' . $t . '</option>';
                                }
                                ?>     
                            </select>
                        </td>
                    </tr>
                    <tr> 
                        <td class="field_title"><?php echo get_string('course_code', 'local_lmsdata'); ?></td>
                        <td class="field_value">
                            <p>
                                <input type="text" title="강의코드" name="subject_id" placeholder="<?php echo get_string('err11', 'local_lmsdata'); ?>" size="40" value="<?php echo!empty($course->subject_id) ? $course->subject_id : ''; ?>"/>
                            </p>
                        </td>
                    <tr> 
<?php
if ($coursetype == 1) {
    ?>
                        <tr> 
                            <td class="field_title"><?php echo get_string('course_number1', 'local_lmsdata'); ?></td>
                            <td class="field_value">
                                <p>
                                    <input type="text" title="강좌번호" name="idnumber" placeholder="<?php echo get_string('err14', 'local_lmsdata'); ?>" size="40" value="<?php echo!empty($course->idnumber) ? $course->idnumber : ''; ?>"/>
                                </p>
                            </td>
                        </tr>
    <?php
}
?>
                    <tr> 
                        <td class="field_title"><?php echo get_string('course_name', 'local_lmsdata'); ?></td>
                        <td class="field_value">
                            <p>
                                <input type="text" title="국문강의명" name="kor_lec_name" placeholder="<?php echo get_string('placeholder1', 'local_lmsdata'); ?>" size="40" value="<?php echo!empty($course->fullname) ? $course->fullname : ''; ?>"/>
                                <input type="text" title="영문강의명" name="eng_lec_name" placeholder="<?php echo get_string('placeholder2', 'local_lmsdata'); ?>" size="40" value="<?php echo!empty($course->eng_lec_name) ? $course->eng_lec_name : ''; ?>"/>
                            </p>
                        </td>
                    </tr>
                    <tr> 
                        <td class="field_title"><?php echo get_string('course_type', 'local_lmsdata'); ?></td>
                        <td class="field_value">
                            <select title="format" name="format">
                                <option value="weeks" <?php echo $course->format == 'weeks' ? "selected" : ""; ?>><?php echo get_string('pluginname', 'format_weeks'); ?></option>
                                <option value="oklass_days" <?php echo $course->format == 'oklass_days' ? "selected" : ""; ?>><?php echo get_string('pluginname', 'format_oklass_days'); ?></option>
                                <option value="okmindmap" <?php echo $course->format == 'okmindmap' ? "selected" : ""; ?>><?php echo get_string('pluginname', 'format_okmindmap'); ?></option>
                            </select>
                            <select title="section" name="section" class="w_160">
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
                                <?php if ($courseid) { ?>
                                <span class="required">섹션이 삭제되면 등록되어있던 활동들도 삭제됩니다.</span>
                            <?php } ?>
                        </td>
                    </tr>

                    <tr>
                        <td class="field_title">학습시간</td>
                        <td class="field_value">
                            <input type="text" title="학습시간" name="learningtime" placeholder="" size="5" value="<?php
                            if (isset($course)) {
                                echo $course->learningtime;
                            }
                            ?>" maxlength="6" onkeydown='return onlyNumber(event)' onkeyup='removeChar(event)' style='ime-mode:disabled;'/> 시간
                        </td>
                    </tr>
                    <tr>
                        <td class="field_title"><?php echo get_string('prof_1', 'local_lmsdata'); ?></td>
                        <td class="field_value"> 
                            <input title="담당교수" type="text" name="prof_name" placeholder="<?php echo get_string('prof_search', 'local_lmsdata'); ?>" size="30" disabled  value="<?php echo!empty($course->prof_userid) ? fullname($course) : ''; ?>"/>
                            <input type="hidden" name="prof_userid" value="<?php echo!empty($course->prof_userid) ? $course->prof_userid : 0; ?>"/>
                            <input type="button" value="<?php echo get_string('search', 'local_lmsdata'); ?>" class="gray_btn" onclick="search_prof_popup()"/>
                        </td>
                    </tr>
                    <?php
                    $certificate = $DB->get_record('lmsdata_certificate', array('id' => $course->certificateid));
                    $allcertifi = $DB->get_records('lmsdata_certificate');
                    ?>
                    <tr class="display_cousetype_1">
                        <td class="field_title"><?php echo get_string('courseenrol_term', 'local_lmsdata'); ?></td>
                        <td class="field_value"> 
                            <input type="text" title="시간" name="timeregstart" id="timeregstart" class="w_120" value="<?php echo empty($course->timeregstart) ? date('Y-m-d', time()) : date('Y-m-d', $course->timeregstart); ?>" placeholder="yyyy-mm-dd"/> ~ 
                            <input type="text" title="시간" name="timeregend" id="timeregend" class="w_120" value="<?php echo empty($course->timeregend) ? date('Y-m-d', time()) : date('Y-m-d', $course->timeregend); ?>" placeholder="yyyy-mm-dd"/> 
                        </td>
                    </tr>
                    <tr class="display_cousetype_1">
                        <td class="field_title"><?php echo get_string('opencourse_term', 'local_lmsdata'); ?></td>
                        <td class="field_value"> 
                            <input type="text" title="시간" name="timestart" id="timestart" class="w_120" value="<?php echo empty($course->timestart) ? date('Y-m-d', time()) : date('Y-m-d', $course->timestart); ?>" placeholder="yyyy-mm-dd"/> ~ 
                            <input type="text" title="시간" name="timeend" id="timeend" class="w_120" value="<?php echo empty($course->timeend) ? date('Y-m-d', time()) : date('Y-m-d', $course->timeend); ?>" placeholder="yyyy-mm-dd"/> 
                        </td>
                    </tr>
                    
                    <tr>
                        <td class="field_title"><?php echo get_string('courseimg', 'local_lmsdata'); ?></td>
                        <td class="field_value"> 
                            <input type="file" title="file" name="overviewfiles" onchange="filename_del()" size="50"/>
                            <?php
                            if (!empty($course)) {
                                $courseimage = new course_in_list($course);
                                foreach ($courseimage->get_course_overviewfiles() as $file) {
                                    $filename = $file->get_filename();
                                }
                                if (!empty($filename)) {
                                    echo ' <span name="filename">' . $filename . '</span>';
                                }
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="field_title"><?php echo get_string('learning_objectives', 'local_lmsdata'); ?></td>
                        <td class="field_value"> 
                            <textarea name="objective" title="학습목표" class="w_100" rows="5" ><?php echo!empty($course->summary) ? $course->summary : ''; ?></textarea>
                        </td>
                    </tr>
                    <?php
                    if ($coursetype == 1) {
                        ?>
                        <tr> 
                            <td class="field_title"><?php echo get_string('course_tag', 'local_lmsdata'); ?></td>
                            <td class="field_value">
                                <div id="tag_box_list" style="width:100%; min-height: 1px; float: left; margin-bottom: 10px;">
                                </div>
                                <p>
                                    <input style="clear: both;" type="text" title="태그" name="tag" size="40" />
                                    <input type="hidden" name="tag_hidden" value="<?php echo!empty($course->tag) ? $course->tag : ''; ?>"/>
                                </p>
                            </td>
                        </tr>
                        <?php
                    }
                    ?>
                </tbody>
            </table>

        </form><!--Search Area2 End-->
        <div id="btn_area">
            <input type="submit" class="blue_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('save', 'local_lmsdata'); ?>" onclick="course_create_submit()"/>
            <?php if (!empty($courseid)) { ?>
                <input type="button" class="normal_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('delete', 'local_lmsdata'); ?>" onclick="course_delete('<?php echo $courseid; ?>')"/>
            <?php } ?>
            <input type="submit" class="normal_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('list2', 'local_lmsdata'); ?>" onclick="javascript:location.href = 'course_list.php?coursetype=<?php echo $coursetype; ?>';"/>
        </div>
    </div><!--Content End-->

</div> <!--Contents End-->
<!-- 양식선택 다이얼로그 시작 -->
<div id="form_search_dialog">
</div>
<!-- 양식선택 다이얼로그 끝 -->
<script type="text/javascript">
    var coursetype = '<?php echo $coursetype; ?>';
    var editor = CKEDITOR.replace('objective', {
        language: '<?php echo current_language(); ?>',
        filebrowserBrowseUrl: '../js/ckfinder-2.4/ckfinder.html',
        filebrowserImageBrowseUrl: '../js/ckfinder-2.4/ckfinder.html?type=Images',
        filebrowserFlashBrowseUrl: '../js/ckfinder-2.4/ckfinder.html?type=Flash',
        filebrowserUploadUrl: '../js/ckfinder-2.4/core/connector/php/connector.php?command=QuickUpload&type=Files',
        filebrowserImageUploadUrl: '../js/ckfinder-2.4/core/connector/php/connector.php?command=QuickUpload&type=Images',
        filebrowserFlashUploadUrl: '../js/ckfinder-2.4/core/connector/php/connector.php?command=QuickUpload&type=Flash'
    });
    CKFinder.setupCKEditor(editor, '../');

    function filename_del() {
        $('span[name=filename]').text("");
    }

    function course_create_submit() {
        <?php if (!$LMSUSER->usergroup == 'de' && !$LMSUSER->usergroup == 'pr') { ?>
        if ($("select[name=cata2]").val() == '0') {
            alert("<?php echo get_string('err10', 'local_lmsdata'); ?>");
            return false;
        }
        <?php } ?>
        if ($.trim($("input[name='kor_lec_name']").val()) == '') {
            alert("<?php echo get_string('alert5', 'local_lmsdata'); ?>");
            return false;
        }
        if ($.trim($("input[name='eng_lec_name']").val()) == '') {
            alert("<?php echo get_string('alert6', 'local_lmsdata'); ?>");
            return false;
        }

        var year = $.trim($("select[name=year]").val());
        var term = $.trim($("select[name=term]").val());
        var hyear_count = 0;

        $(".hyear").each(function (index, element) {
            if ($(this).is(":checked")) {
                hyear_count++;
            }
        });

        if (!$("input:input[name=isreged]").is(":checked")) {
            if (($.trim($("input[name='timeregstart']").val()) == '') || ($.trim($("input[name='timeregend']").val()) == '')) {
                alert("<?php echo get_string('alert11', 'local_lmsdata'); ?>");
                return false;
            }
        }

        if (($.trim($("input[name='timestart']").val()) == '') || ($.trim($("input[name='timeend']").val()) == '')) {
            alert("<?php echo get_string('alert12', 'local_lmsdata'); ?>");
            return false;
        }


        if ($.trim($("input[name='overviewfiles']").val()) != '') {
            var filename = $.trim($("input[name='overviewfiles']").val());
            var extension = filename.replace(/^.*\./, '');
            if (extension == filename) {
                extension = "";
            } else {
                extension = extension.toLowerCase();
            }
            if ($.inArray(extension, ["jpg", "png"]) == -1) {
                alert("<?php echo get_string('onlyimg', 'local_lmsdata'); ?>");
                return false;
            }
            ;
        }
        var course_code = $('input[name=subject_id]').val();
        var bunban = $('input[name=bunban]').val();
        if (coursetype == '1' && bunban != '' && course_code != '') {
            $.ajax({
                url: '<?php echo $CFG->wwwroot . "/siteadmin/manage/course_overlap_chk.php" ?>',
                method: 'POST',
                data: {
                    courseid: <?php echo $courseid; ?>,
                    course_code: course_code,
                    bunban: bunban
                },
                success: function (data) {
                    if (data == 'ok') {
                        $('#course_search').submit();
                    } else {
                        if(bunban === undefined ){
                            bunban = '';
                        }
                        alert('입력한강의코드에 '+bunban+' 합반코드가 이미 존재합니다.');
                    }
                },
                error: function (jqXHR, textStatus, errorThrown) {
                }
            });
        } else {
            $('#course_search').submit();
        }
    }

    function course_delete(courseid) {
        if (confirm("<?php echo get_string('deletecoursecheck'); ?>") == true) {
            $.ajax({
                url: '<?php echo $CFG->wwwroot . "/siteadmin/manage/course_delete.execute.php" ?>',
                method: 'POST',
                data: {
                    id: courseid,
                },
                success: function (data) {
                    alert('삭제되었습니다');
                    document.location.href = "<?php echo $CFG->wwwroot . "/siteadmin/manage/course_list.php?coursetype=" . $coursetype; ?>";                
                }
            });
        }
    }
    function search_prof_popup() {
        var tag = $("<div id='course_prof_popup'></div>");
        $.ajax({
            url: '<?php echo $CFG->wwwroot . '/siteadmin/manage/course_prof.php'; ?>',
            method: 'POST',
            success: function (data) {
                tag.html(data).dialog({
                    title: '<?php echo get_string('prof_search', 'local_lmsdata'); ?>',
                    modal: true,
                    width: 800,
                    resizable: false,
                    height: 400,
                    buttons: [{id: 'close',
                            text: '<?php echo get_string('cancle', 'local_lmsdata'); ?>',
                            disable: true,
                            click: function () {
                                $(this).dialog("close");
                            }}],
                    close: function () {
                        $('#frm_course_prof').remove();
                        $(this).dialog('destroy').remove()
                    }
                }).dialog('open');
            }
        });
    }
    function display_coursetype_option(type) {
        $('.display_cousetype_' + type).show();
        if (type == 0) {
            $('.display_cousetype_1').hide();
        } else {
            $('.display_cousetype_0').hide();
        }
    }
    $(document).ready(function () {
        $("#timestart").datepicker({
            dateFormat: "yy-mm-dd",
            onClose: function (selectedDate) {
                $("#timeend").datepicker("option", "minDate", selectedDate);
            }
        });
        $("#timeend").datepicker({
            dateFormat: "yy-mm-dd",
            onClose: function (selectedDate) {
                $("#timestart").datepicker("option", "maxDate", selectedDate);
            }
        });
        $("#timeregstart").datepicker({
            dateFormat: "yy-mm-dd",
            onClose: function (selectedDate) {
                $("#timeregend").datepicker("option", "minDate", selectedDate);
            }
        });
        $("#timeregend").datepicker({
            dateFormat: "yy-mm-dd",
            onClose: function (selectedDate) {
                $("#timeregstart").datepicker("option", "maxDate", selectedDate);
            }
        });
        display_coursetype_option(<?php echo $coursetype; ?>);
        
        print_tags();
        $('input[name=tag]').keyup(function (event) {
            if (event.keyCode == 188) {
                var tags_hidden = $('input[name=tag_hidden]').val();
                var tags = $('input[name=tag]').val();
                $('input[name=tag_hidden]').val(tags_hidden + tags)
                $('input[name=tag]').val('');
                print_tags();
            }
        });
    });

    function print_tags() {
        var tags_hidden = $('input[name=tag_hidden]').val();
        if (!tags_hidden) {
            $("#tag_box_list").html('');
            return;
        }
        var tagarray = tags_hidden.split(',');
        var tag_box = '';
        for (var i in tagarray) {
            if (tagarray[i] != '') {
                tag_box += '<div class="tag_box" id="tag_' + i + '"><span style="float:left; font-weight:bold;">' + tagarray[i] + '</span><span onclick="remove_tag(' + i + ');" style="float:left; margin-left:5px; cursor:pointer;">x</span></div>';
            }
        }
        $("#tag_box_list").html(tag_box);
    }

    function remove_tag(num) {
        var tags = $('input[name=tag_hidden]').val();
        var tagarray = tags.split(',');
        tagarray.splice(num, 1);
        var tagval = tagarray.join(',');
        $('input[name=tag_hidden]').val(tagval);
        print_tags();
    }
    /**
     * 숫자만 입력
     * @param {type} event
     * @returns {Boolean}     */
    function onlyNumber(event) {
        event = event || window.event;
        var keyID = (event.which) ? event.which : event.keyCode;
        if ((keyID >= 48 && keyID <= 57) || (keyID >= 96 && keyID <= 105) || keyID == 8 || keyID == 46 || keyID == 37 || keyID == 39)
            return;
        else
            return false;
    }
    /**
     * 숫자가 아닌 문자 제거
     * @param {type} event
     * @returns {undefined}     */
    function removeChar(event) {
        event = event || window.event;
        var keyID = (event.which) ? event.which : event.keyCode;
        if (keyID == 8 || keyID == 46 || keyID == 37 || keyID == 39)
            return;
        else
            event.target.value = event.target.value.replace(/[^0-9]/g, "");
    }

    function form_selete(id, name) {
        $('#form_search_dialog').dialog('close');
        $('input[name=formid]').val(id);
        $('input[name=formname]').val(name);
    }
    $(
            /**
             * 다이얼로그 팝업창
             * @returns {undefined}
             */
                    function () {
                        $("#form_search_dialog").css('zIndex', 100);
                        $("#form_search_dialog").dialog({
                            maxWidth: 800,
                            maxHeight: 500,
                            width: 800,
                            height: 500,
                            autoOpen: false,
                            hide: {
                                duration: 1000,
                            },
                            buttons: {
                                "<?php echo get_string('close', 'local_lmsdata'); ?>": function () {
                                    $(this).dialog("close");
                                }
                            }
                        });

                        $("#opener_form").click(
                                /**
                                 * 다이얼로그 오픈
                                 * @returns {undefined}
                                 */
                                        function () {
                                            $("#form_search_dialog").dialog("open");
                                            $.ajax({url: "./get_form.ajax.php",
                                                success: function (result) {
                                                    $("#form_search_dialog").html(result);
                                                }
                                            });
                                        });

                                $("#searchbtn_form").click(
                                        /**
                                         * 다이얼로그 검색버튼 클릭
                                         * @returns {undefined}
                                         */
                                                function () {

                                                });
                                    });
</script>
<?php
include_once ('../inc/footer.php');
