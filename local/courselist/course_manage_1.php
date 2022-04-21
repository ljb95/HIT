<div class="table-filter-area">
    <!--<input type="submit" value="<?php echo get_string('merge:sel', 'local_courselist'); ?>" onclick="split_course_dialog();"/>-->
    <input type="button" value="<?php echo get_string('course:add', 'local_courselist'); ?>" onclick="javascript:location.href = '<?php echo $CFG->wwwroot . "/local/courselist/course_add.php"; ?>'"/>
</div>
<table class="generaltable" id="course_manage"> 
    <caption class="hidden-caption">Irregular Course</caption>
    <thead>
        <tr>
            <!--<th width="5%"><input type="checkbox" onclick="check_course_id(this, 'courseid')"/></th>-->
            <th scope="row"><?php echo get_string('course:name', 'local_courselist'); ?></th>
            <th scope="row" width="10%"><?php echo get_string('course:status', 'local_courselist'); ?></th>
            <th scope="row" width="15%"><?php echo get_string('course:app', 'local_courselist') . '/' . get_string('course:wait', 'local_courselist'); ?></th>
            <th scope="row" width="20%"><?php echo get_string('manage', 'local_courselist'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        $count_courses = 0;
        foreach ($courses as $course) {
            $count_courses++;

            $currentdate = time();
            if ($course->timestart <= $currentdate && $course->timeend >= $currentdate) {
                $coursestatustext = get_string('course:ongoing', 'local_courselist');
            } else {
                $coursestatustext = get_string('course:finish', 'local_courselist');
            }
            ?>
            <tr>
                <!--<td><input type="checkbox" class="courseid" name="courseid" value="<?php echo $course->id; ?>"/></td>-->
                <td scope="col" class="title"><a href="<?php echo $CFG->wwwroot . '/course/view.php?id=' . $course->id; ?>"><?php echo $course->fullname; ?></a></td>
                <td scope="col"><?php echo $coursestatustext; ?></td>
                <td scope="col"><?php echo $course->app_num . '/' . $course->total_num; ?></td>
                <td scope="col">
                    <?php
                    echo '<input type="button" class="gray_btn_small student_list" value="' . get_string('student', 'local_courselist') . '" onclick="javascript:location.href = \'' . $CFG->wwwroot . '/local/courselist/course_students_list.php?id=' . $course->id . '\'"/>';
                    echo '<input type="button" class="gray_btn_small course_edit" value="' . get_string('edit', 'local_courselist') . '" onclick="javascript:location.href = \'' . $CFG->wwwroot . '/local/courselist/course_add.php?id=' . $course->id . '\'"/>';
                    echo '<input type="button" class="gray_btn_small coruse_delete" value="' . get_string('course:delete', 'local_courselist') . '" onclick="course_delete(' . $course->id . ')"/>';
                    ?>
                </td>
            </tr>
            <?php
        }
        if ($count_courses === 0) {
            ?>
            <tr>
                <td scope="col" colspan="6"><?php echo get_string('course:empty', 'local_courselist'); ?></td>
            </tr>
        <?php } ?>
    </tbody>
</table><!--Table End-->     

<script type="text/javascript">

    function split_course_dialog() {
        var count = 0;
        $('#merge_button').append('<form method="post" id="merge_course" action="course_list_merge_form.php"></form>');
        $(".courseid").each(function (index, element) {
            if ($(this).is(":checked")) {
                $('#merge_course').append('<input type="hidden" name="course[]" value="' + $(this).val() + '" />');
                count++;
            }
        });

        if (count < 2) {
            alert("<?php echo get_string('course:sel_more', 'local_courselist'); ?>");
            return false;
        }
        $('#merge_course').submit();
    }

    function course_delete(courseid) {
        if (confirm("<?php echo get_string('deletecoursecheck'); ?>") == true) {
            $.ajax({
                url: '<?php echo $CFG->wwwroot . "/local/courselist/course_delete.execute.php" ?>',
                method: 'POST',
                data: {
                    id: courseid,
                },
                success: function (data) {
                    document.location.href = "<?php echo $CFG->wwwroot . "/local/courselist/course_manage.php?coursetype=1" ?>";
                }
            });
        }
    }

    function check_course_id(check, checkClass) {
        if ($(check).is(":checked")) {
            $("." + checkClass).each(function () {
                this.checked = true;
            });
        } else {
            $("." + checkClass).each(function () {
                this.checked = false;
            });
        }
    }

    function radio_check(courseid) {
        $("input:checkbox[name=course_invisible]").each(function (index, element) {
            if ($(this).val() != courseid) {
                this.checked = true;
                this.disabled = false;
            } else {
                this.checked = false;
                this.disabled = true;
            }
        });
    }


</script>    