<?php
require_once dirname(dirname(dirname(__FILE__))) . '/config.php';
require_once $CFG->dirroot . '/siteadmin/lib/paging.php';
require_once $CFG->dirroot . '/siteadmin/lib.php';
// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string) new moodle_url('/siteadmin/support/main_menu.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$menu_number = optional_param('number', 0, PARAM_INT);

//$page = optional_param('page', 1, PARAM_INT);
//$perpage = optional_param('perpage', 15, PARAM_INT);

$totalcount = $DB->count_records('main_menu');

$offset = ($page - 1) * $perpage;

include_once ($CFG->dirroot . '/siteadmin/inc/header.php');
?>
<div id="contents">
    <?php include_once ($CFG->dirroot . '/siteadmin/inc/sidebar_support.php'); ?>
    <div id="content">
        <h3 class="page_title"><?php echo get_string('menu_manage', 'local_lmsdata'); ?></h3>

        <div class="page_navbar">
            <a href="<?php echo $CFG->wwwroot . '/siteadmin/support/popup.php'; ?>" ><?php echo get_string('site_management', 'local_lmsdata'); ?></a> > 
            <a href="<?php echo $CFG->wwwroot . '/siteadmin/support/main_menu.php'; ?>" > <?php echo get_string('menu_manage', 'local_lmsdata'); ?></a>
        </div>
        <div class="main-menu-table">
            <table class="generaltable">
                <thead>
                    <tr>
                        <th style="width:5%;">번호</th>
                        <th style="width:10%;">형식</th>
                        <th style="">이름</th>
                        <th style="width:5%;">링크</th>
                        <th style="width:12%">권한</th>
                        <th style="width:8%;">생성일</th>
                        <th style="width:8%;">생성자</th>
                        <th style="width:8%;">변경일</th>
                        <th style="width:8%;">변경자</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query = 'select mm.* from {main_menu} mm '
                            . 'join {main_menu} pm on pm.id = mm.parent  '
                            . 'order by LPAD(CONCAT(pm.step * 10, (CASE mm.depth WHEN 2 THEN mm.step+1 else 0 end)),3,0) asc ';
                    $menus = $DB->get_records_sql($query,array(),$offset,$perpage); 
                    $num = $totalcount - $offset;
                    foreach ($menus as $menu) {
                       
                        $lang = $DB->get_field('main_menu_name','name',array('menuid'=>$menu->id,'lang'=> current_language()));
                        $pname = $DB->get_field('main_menu_name','name',array('menuid'=>$menu->parent,'lang'=> current_language()));
                        switch ($menu->type) {
                            case 1: 
                                $link = '';
                                $type_txt = '상위메뉴';
                                $target = '';
                                break;
                            case 2: 
                                $link = (preg_match('/http/i', $menu->url))?$menu->url:$CFG->wwwroot.$menu->url;
                                $type_txt = '하위메뉴';
                                if($menu->ispopup == 2){
                                    $target = 'target="_blank"';
                                } else {
                                    $target ='';
                                }
                                break;
                            case 3: 
                                $link = $menu->url;
                                $type_txt = '링크';
                                $target ='';
                                break;
                            case 4: 
                                $link = $menu->url;
                                $type_txt = '팝업';
                                $target = 'target="_blank"';
                                break;
                        }
                        ?>
                        <tr>
                            <td><?php echo $num--; ?></td>
                            <td><?php echo $type_txt; ?></td>
                            <td style="text-align:left; padding-left:15px;">
                                <a href="main_menu_add.php?id=<?php echo $menu->id; ?>" title="Edit">
                                <?php 
                                if($menu->type == 2){
                                    echo '<span class="child_icon">'.$pname.' > </span> '; 
                                } else {
                                ?>
                                <i class="fa <?php echo $menu->icon; ?>" aria-hidden="true"></i> <?php } 
                                echo $lang; ?>
                                </a>
                            </td>
                            <td>
                                <?php echo ($menu->type == 1) ? '' : '<a target="_blank" href="'.$link.'" '.$target.' class="red">[Link]</a>'; ?></td>
                            <td>
                                <?php
                                $usergroups = $DB->get_records('main_menu_apply', array('menuid' => $menu->id), '', 'usergroup');
                                foreach ($usergroups as $usergroup) {
                                    echo '<div class="menu_usergroups ' . $usergroup->usergroup . '">' 
                                            . get_string('role:'.$usergroup->usergroup,'local_lmsdata') 
                                            . '</div>';
                                }
                                ?>
                            </td>
                            <td>
                            <?php 
                                echo date('Y-m-d', $menu->timecreated); 
                            ?></td>
                            <td>
                            <?php 
                                echo fullname($DB->get_record('user',array('id'=>$menu->userid)));
                            ?></td>
                            <td>
                                <?php 
                                echo date('Y-m-d', $menu->timemodified); 
                                ?>
                            </td>
                             <td>
                            <?php 
                                echo fullname($DB->get_record('user',array('id'=>$menu->edituserid)));
                            ?></td>
                        </tr>
                    <?php
                    }
                    if (!$menus) {
                        echo '<tr><td align="center" colspan="9">등록된 메뉴가 없습니다</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
        
        <div style="clear:both;">
            <input type="button" value="메뉴 추가" onclick="location.href='main_menu_add.php'">
        </div>
                <div class="pagination">
        <?php
//            print_paging_navbar($totalcount, $page, $perpage, $CFG->wwwroot . '/siteadmin/support/main_menu.php', array());
        ?>
        </div><!-- Pagination End -->
    </div>
</div><!--Content End-->
</div> <!--Contents End-->

<?php include_once ($CFG->dirroot . '/siteadmin/inc/footer.php'); ?>

<script type="text/javascript">

</script>
