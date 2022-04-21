<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(dirname(__FILE__)) . '/lib.php');

// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url($CFG->wwwroot);
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);
$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url($CFG->wwwroot.'/siteadmin/support/course_option.php');

$edit = optional_param('edit', 0, PARAM_INT);

$course_option = get_config('moodle', 'siteadmin_course_option_set');

if(empty($course_option)) {
    $course_option = new Stdclass();
    $course_option->irregular = 1;
    $course_option->isreged = 1;
    $course_option->certificate = 1;
    $course_option->merge = 1;
    $course_option->createcourse = 1;
    
    $course_option_serialize = serialize($course_option);
    
    set_config('siteadmin_course_option_set', $course_option_serialize);
} else {
    $course_option = unserialize($course_option);
}

if($edit) {
    $irregular = optional_param('irregular', 0, PARAM_BOOL);
    $isreged = optional_param('isreged', 0, PARAM_BOOL);
    $certificate = optional_param('certificate', 0, PARAM_BOOL);
    $merge = optional_param('merge', 0, PARAM_BOOL);
    $createcourse = optional_param('createcourse', 0, PARAM_BOOL);
    
    $course_option->irregular = $irregular;
    $course_option->isreged = $isreged;
    $course_option->certificate = $certificate;
    $course_option->merge = $merge;
    $course_option->createcourse = $createcourse;
    
    $course_option_serialize = serialize($course_option);
    set_config('siteadmin_course_option_set', $course_option_serialize);
}

?>
<?php include_once('../inc/header.php'); ?>
<div id="contents">
    <?php include_once ('../inc/sidebar_support.php'); ?>

    <div id="content">
         <h3 class="page_title"><?php echo get_string('course_opt_setting','local_lmsdata'); ?></h3>
        <div class="page_navbar">
            <a href="<?php echo $CFG->dirroot.'/support/notices.php' ;?>" ><?php echo get_string('site_management', 'local_lmsdata'); ?></a> > 
            <strong><?php echo get_string('course_opt_setting','local_lmsdata'); ?></strong>
        </div>

        <form method='POST' id='form' action='<?php echo $CFG->wwwroot.'/siteadmin/support/course_option.php'; ?>' >
            <table cellpadding="0" cellspacing="0" class="normal" width="100%">
                <caption class="hidden-caption"></caption>
                <thead>
                    <tr>
                        <th scope="row" width="15%"><?php echo get_string('function','local_lmsdata'); ?></th>
                        <th scope="row" width="45%"><?php echo get_string('func_desc','local_lmsdata'); ?></th>
                        <th scope="row" width="10%"><?php echo get_string('menu4','local_lmsdata'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><?php echo get_string('func1','local_lmsdata'); ?></td> 
                        <td><?php echo get_string('func1_desc','local_lmsdata'); ?></td>
                        <td><input type="checkbox" title="checkbox" name="irregular" class="chkbox" <?php echo $course_option->irregular ? 'checked' : ''; ?> /></td>
                    </tr>
                    <tr>
                        <td><?php echo get_string('enrolment', 'local_lmsdata'); ?></td>
                        <td><?php echo get_string('func2_desc','local_lmsdata'); ?></td>
                        <td><input type="checkbox" title="checkbox" name="isreged" class="chkbox" <?php echo $course_option->isreged ? 'checked' : ''; ?> /></td>
                    </tr>
                    <tr>
                        <td><?php echo get_string('func2','local_lmsdata'); ?></td>
                        <td><?php echo get_string('func3_desc','local_lmsdata'); ?></td>
                        <td><input type="checkbox" title="checkbox" name="certificate" class="chkbox" <?php echo $course_option->certificate ? 'checked' : ''; ?> /></td>
                    </tr>
                    <tr>
                        <td><?php echo get_string('func3','local_lmsdata'); ?></td>
                        <td><?php echo get_string('func4_desc','local_lmsdata'); ?></td>
                        <td><input type="checkbox" title="checkbox" name="merge" class="chkbox" <?php echo $course_option->merge ? 'checked' : ''; ?> /></td>
                    </tr>
                    <tr>
                        <td><?php echo get_string('func4','local_lmsdata'); ?></td>
                        <td><?php echo get_string('func5_desc','local_lmsdata'); ?></td>
                        <td><input type="checkbox" title="checkbox" name="createcourse" class="chkbox" <?php echo $course_option->createcourse ? 'checked' : ''; ?> /></td>
                    </tr>
                </tbody>
            </table>
            <div>
                <input type="hidden" name="edit" value="1" />
                <input type="submit" class="blue_btn right"  value="<?php echo get_string('edit','local_lmsdata'); ?>" style="float: right;" />
            </div>
        </form>
        <div class="pagination">
        </div><!-- Pagination End -->

    </div><!--Content End-->

</div> <!--Contents End-->

<?php include_once ('../inc/footer.php'); ?>

