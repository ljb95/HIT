<?php
require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
require_once dirname(dirname(__FILE__)) . '/lib/paging.php';
require_once dirname(dirname(__FILE__)) . '/lib.php';

// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string) new moodle_url('/siteadmin/manage/course_list.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);
?>

<?php include_once (dirname(dirname(__FILE__)) . '/inc/header.php'); ?>
<div id="contents">
    <?php include_once (dirname(dirname(__FILE__)) . '/inc/sidebar_users.php'); ?>
    <form name="sync_form" method="post" action="sync_submit.php" enctype="multipart/form-data">   
        <div id="content">

            <h3 class="page_title">엑셀 사용자 등록</h3>
            <div class="page_navbar">
                <a href="<?php echo $CFG->wwwroot . '/siteadmin/users/info.php'; ?>"><?php echo get_string('user_management', 'local_lmsdata'); ?></a> >
                <a href="<?php echo $CFG->wwwroot . '/siteadmin/users/sync.php'; ?>">엑셀 사용자 등록</a>
            </div>
            <div style="clear:both;">
                <div class="div-box">
                    <input type="hidden" name="user" value="<?php echo $USER->id ?>" />
                    <input type="file" name="user" /> 
                    <p><strong><a style="color:red;" href="sample.xlsx" alt="Sample" alt="Sample">[Sample]</a></strong>양식에 맞춰서 사용자를 추가해주세요.</p>
                </div>
                <input type="submit" class="gray_btn" value="업데이트" />
            </div>
        </div><!--Content End-->
    </form>

</div> <!--Contents End-->

<?php include_once ('../inc/footer.php'); ?>

<script type="text/javascript">
    function course_list_excel() {
        var url = "course_list.excel.php<?php echo $query_string; ?>";

        document.location.href = url;
    }
</script>    
