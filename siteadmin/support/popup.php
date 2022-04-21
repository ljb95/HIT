<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once(dirname(dirname(__FILE__)) . '/lib.php');

// Check for valid admin user - no guest autologin 
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/manage/course_list.php');
    redirect(get_login_url());
}
$context = context_system::instance();

require_once $CFG->dirroot . '/local/jinoboard/lib.php';
require_once dirname(dirname(__FILE__)) . '/lib/paging.php';

$type = optional_param('type', 1, PARAM_INT);
$page = optional_param('page', 1, PARAM_INT);
$search = optional_param('searchvalue', null, PARAM_RAW);
$perpage = optional_param('perpage', 10, PARAM_INT);

$PAGE->set_context($context);
$PAGE->set_url('/siteadmin/support/popup.php');

$popups = local_popup_get_popups("id DESC ", $search, $page - 1, $perpage);

$totalcount = local_popup_get_popups_count($search);

$total_pages = jinoboard_get_total_pages($totalcount, $perpage);

$perpages = define_perpages();

$offset = ($page -1) * $perpage;

?>
<?php include_once('../inc/header.php'); ?>
<div id="contents">
    <?php include_once ('../inc/sidebar_support.php'); ?>

    <div id="content">
        <h3 class="page_title"><?php echo get_string('popup_manage', 'local_lmsdata'); ?></h3>
        <div class="page_navbar"><a href="<?php echo $CFG->wwwroot . '/siteadmin/support/popup.php'; ?>"><?php echo get_string('site_management', 'local_lmsdata'); ?></a> > <a href="<?php echo $CFG->wwwroot . '/siteadmin/support/popup.php'; ?>"><?php echo get_string('popup_manage','local_lmsdata'); ?></a> </div>


        <form id="frm_guide_search" class="search_area" action="<?php echo $CFG->wwwroot ?>/siteadmin/support/popup.php" method="POST">
            <label><?php echo get_string('popup','local_lmsdata'); ?></label>
            <input type="text" title="serch" class="w_120" name='searchvalue' value="<?php echo $search; ?>" placeholder="팝업명" style="color: #8E9094; margin:0 0 5px 15px;"/>
            <input type="submit" class="search_btn" value="<?php echo get_string('search','local_lmsdata'); ?>" style="margin:0 0 5px 5px;"/>
            <input type="hidden" id="menusearchfield" class="select menusearchfield" value="0" name="searchfield">
        </form><!--Search Area2 End-->

        <form method='post' id='form' action='<?php echo $CFG->wwwroot ?>/siteadmin/support/popup_del.php'>
            <table cellpadding="0" cellspacing="0" class="normal" width="100%">
                <caption class="hidden-caption">팝업관리</caption>
                <thead>
                    <tr>
                        <th scope="row" width="5%"><input type="checkbox" title="allcheck" id="allcheck" class="chkbox" /></th>
                        <th scope="row" width="5%">No.</th>
                        <th scope="row" width="45%"><?php echo get_string('popup','local_lmsdata'); ?></th>
                        <th scope="row" width="10%"><?php echo get_string('author','local_lmsdata'); ?></th>
                        <th scope="row" width="15%"><?php echo get_string('post_period','local_lmsdata'); ?></th>
                        <th scope="row" width="10%"><?php echo get_string('datecreated', 'local_lmsdata'); ?></th>
                        <th scope="row" width="10%"><?php echo get_string('post_visible','local_lmsdata'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $now = date("Y-m-d");
                    $startnum = $totalcount - (($page - 1) * $perpage);
                    foreach ($popups as $popup) {
                        $user = $DB->get_record('user',array('id'=>$popup->user));
                        ?>
                        <tr>
                            <td class="chkbox"><input type="checkbox" title="check"  name="id[<?php echo $popup->id; ?>]" value="<?php echo $popup->id; ?>" /></td>
                            <td class="number"><?php echo $startnum; ?></td>
                            <td class="title"><a href='<?php echo $CFG->wwwroot ?>/siteadmin/support/popup_view.php?id=<?php echo $popup->id ?>'><?php echo $popup->title ?></a></td>
                            <td><?php echo fullname($user); ?></td> 
                            <td class="number"><?php echo date("Y-m-d", $popup->timeavailable); ?><br/>~ <?php echo date("Y-m-d", $popup->timedue); ?></td>
                            <td class="number"><?php echo date("Y-m-d", $popup->timecreated); ?></td>
                            <td>
                                <?php 
                                if ($now >= date("Y-m-d", $popup->timeavailable) && $now <= date("Y-m-d", $popup->timedue)) { 
                                    echo get_string('active','local_lmsdata');
                                } else if ($now < date("Y-m-d", $popup->timeavailable)) {
                                    echo get_string('notaperiod','local_lmsdata');
                                 } else { 
                                    echo get_string('status3','local_lmsdata');
                                 } 
                                 ?>
                            </td>
                        </tr>
                        <?php $startnum--;
                         } 
                    if (!$totalcount) { ?>
                        <tr><td colspan="7"><span><?php echo get_string('empty_popup','local_lmsdata'); ?></span></td></tr>
                     <?php } ?>
                </tbody>
            </table>
            <div id="btn_area">
                <input type="button" class="blue_btn" onclick='document.location.href = "<?php echo $CFG->wwwroot ?>/siteadmin/support/popup_write.php"' value="<?php echo get_string('add', 'local_lmsdata'); ?>"  />
                <input type="button" id="popup_delete_button" class="red_btn" value="<?php echo get_string('delete', 'local_lmsdata'); ?>" /> 
            </div>
        </form>
        <div class="pagination">
        <?php
            print_paging_navbar($totalcount, $page, $perpage, $CFG->wwwroot . '/siteadmin/support/popup.php', array("search" => $search));
        ?>
        </div><!-- Pagination End -->

    </div><!--Content End-->

</div> <!--Contents End-->

<?php include_once ('../inc/footer.php'); ?>

<script>
    $(function() {
        $("#allcheck").click(function() {
            var chk = $("#allcheck").is(":checked");

            if (chk) {
                $(".chkbox input").each(function() {
                    this.checked = true;
                });
            } else {
                $(".chkbox input").each(function() {
                    this.checked = false;
                });
            }
        });
        
        $("#popup_delete_button").click(function() {
            if(confirm("<?php echo get_string('delete_confirm', 'local_lmsdata');?>")){
                $('#form').submit();
            }
        });
    });
</script>


