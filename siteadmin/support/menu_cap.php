<?php
require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
require_once $CFG->dirroot . '/siteadmin/lib/paging.php';
require_once $CFG->dirroot . '/siteadmin/lib.php';
// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string) new moodle_url('/siteadmin/support/menu_cap.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$menu_number = optional_param('number', 0, PARAM_INT);

$page = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 15, PARAM_INT);

$totalcount = $DB->count_records('menu_auth');

$offset = ($page - 1) * $perpage;

include_once ($CFG->dirroot . '/siteadmin/inc/header.php');
?>
<div id="contents">
    <?php include_once ($CFG->dirroot . '/siteadmin/inc/sidebar_support.php'); ?>
    <div id="content">
        <h3 class="page_title"><?php echo get_string('menu_manage', 'local_lmsdata'); ?></h3>

        <div class="page_navbar">
            <a href="<?php echo $CFG->dirroot . '/support/notices.php'; ?>" ><?php echo get_string('site_management', 'local_lmsdata'); ?></a> > 
            <a href="<?php echo $CFG->dirroot . '/support/main_menu.php'; ?>" >  <strong><?php echo get_string('menu_manage', 'local_lmsdata'); ?></strong></a>
        </div>
        <form id="frm_notices_search" class="search_area">
            <input type="text" name="search" value="<?php echo $search; ?>" class="search-text" placeholder="<?php echo get_string('input', 'local_jinoboard'); ?>">
            <input type="submit" class="search_btn" id="search" value="<?php echo get_string('search', 'local_jinoboard'); ?>">
        </form>
            <input type="button" class="gray_btn" style="float:right" value="등록하기" onclick="location.href='menu_cap_add.php'">
        <table class="generaltable">
            <thead>
                <tr>
                    <th style="width:5%">번호</th>
                    <th>권한명</th>
                    <th style="width:15%">등록자</th>
                    <th style="width:15%">등록일</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $caps = $DB->get_records('menu_auth');
                $num = $totalcount - $offset;
                foreach($caps as $cap){
                ?>
                <tr>
                    <td><?php echo $num--; ?></td>
                    <td><a href="menu_cap_add.php?id=<?php echo $cap->id; ?>"><?php echo $DB->get_record('menu_auth_name',array('authid'=>$cap->id,'lang'=> current_language()))->name; ?></a></td>
                    <td><?php echo fullname($user = $DB->get_record('user',array('id'=>$cap->userid))); ?></td>
                    <td><?php echo date('Y-m-d',$cap->timecreated); ?></td>
                </tr>
                <?php } ?>
            </tbody>
        </table>
    </div><!--Content End-->
</div> <!--Contents End-->

<?php include_once ($CFG->dirroot . '/siteadmin/inc/footer.php'); ?>

<script type="text/javascript">

</script>
