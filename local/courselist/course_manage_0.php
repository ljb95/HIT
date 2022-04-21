<table class="generaltable" id="course_manage"> 
    <caption class="hidden-caption">Course list</caption>
    <thead>
        <tr>
            <th scope="row" width="15%"><?php echo get_string('course:subjectid', 'local_courselist'); ?></th>
            <th scope="row"><?php echo get_string('course:name', 'local_courselist'); ?></th>
            <th scope="row" width="15%"><?php echo get_string('course:app', 'local_courselist') . '/' . get_string('course:wait', 'local_courselist'); ?></th>
            <th scope="row" width="15%"><?php echo get_string('manage', 'local_courselist'); ?></th>
        </tr>
    </thead>
    <tbody>
        <?php
        $count_courses = 0;
        foreach ($courses as $course) {
            $count_courses++;
            ?>
            <tr>
                <td scope="col"><?php echo $course->subject_id; ?></td>
                <td scope="col" class="title"><a href="<?php echo $CFG->wwwroot . '/course/view.php?id=' . $course->id; ?>"><?php echo $course->fullname; ?></a></td>
                <td scope="col"><?php echo $course->app_num . '/' . $course->total_num; ?></td>
                <td scope="col">
                    <?php
                    echo '<input type="button" class="gray_btn_small student_list" value="' . get_string('student', 'local_courselist') . '" onclick="javascript:location.href = \'' . $CFG->wwwroot . '/local/courselist/course_students_list.php?id=' . $course->id . '\'"/>';
                    ?>
                </td>
            </tr>
            <?php
        }
        if ($count_courses === 0) {
            ?>
            <tr>
                <td scope="col" colspan="5"><?php echo get_string('course:empty', 'local_courselist'); ?></td>
            </tr>
<?php } ?>
    </tbody>
</table><!--Table End-->    