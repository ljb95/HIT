<?php 
require_once dirname(dirname(dirname (__FILE__))).'/config.php';
require_once dirname(dirname (__FILE__)).'/lib/paging.php';
require_once dirname(dirname (__FILE__)).'/lib.php';
require_once $CFG->dirroot . '/local/jinoboard/lib.php';

// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/users/inftemp.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$perpage      = optional_param('perpage', 10, PARAM_INT);
$page         = optional_param('page', 1, PARAM_INT);
$search       = optional_param('search', '', PARAM_RAW);
$searchtext   = optional_param('searchtext', '', PARAM_TEXT);

//데이터 가져오기

$like = '';
if (!empty($searchtext)) {
    $like = " and " . $DB->sql_like('username', ':search', false);
}

$where = '';
if(!empty($search)){
    $where = " and lu.usergroup = '".$search."'";
}

$sql = 'SELECT count(lu.id) FROM {lmsdata_user} lu JOIN {user} u ON lu.userid = u.id WHERE lu.b_temp = 1'.$where.$like;
$totalcount = $DB->count_records_sql($sql,array('search' => '%' . $searchtext . '%'));
$total_pages = jinoboard_get_total_pages($totalcount,$perpage);

$offset = ($page -1) * $perpage;

$js = array(
    $CFG->wwwroot.'/siteadmin/manage/course_list.js'
);

include_once (dirname(dirname (__FILE__)).'/inc/header.php'); 
?>
<div id="contents">
    <?php include_once  (dirname(dirname (__FILE__)).'/inc/sidebar_users.php');?>
    
    <div id="content">
        <h3 class="page_title"><?php echo get_string('user_manageaccounts', 'local_lmsdata');?></h3>
        <div class="page_navbar"><a href="<?php echo $CFG->wwwroot . '/siteadmin/users/info.php'; ?>"><?php echo get_string('user_management', 'local_lmsdata'); ?></a> > <a href="<?php echo $CFG->wwwroot . '/siteadmin/users/inftemp.php'; ?>"><?php echo get_string('user_manageaccounts', 'local_lmsdata');?></a></div>
        
        <form name="" id="course_search" class="search_area" action="inftemp.php" method="get">
            <input type="hidden" name="page" value="1" />
            <input type="text" title="search" name="searchtext" value="<?php echo $searchtext; ?>" placeholder="임시번호를 입력하세요."  class="search-text"/>
            <input type="submit" class="search_btn" value="<?php echo get_string('search','local_lmsdata'); ?>"/>          
        </form><!--Search Area2 End-->
        <form id="suspended" method="post">
        <table>
            <caption class="hidden-caption"><?php echo get_string('user_manageaccounts', 'local_lmsdata');?></caption>
            <thead>
            <tr>
                <th scope="row" width="5%"><input type="checkbox" title="checkbox" id="allcheck"></th>
                <th scope="row" width="5%"><?php echo get_string('number', 'local_lmsdata'); ?></th>
                <th scope="row" width="10%"><?php echo get_string('user_temporarynumber', 'local_lmsdata'); ?></th>
                <th scope="row" width="10%"><?php echo get_string('name','local_lmsdata'); ?></th>
                <th scope="row" width="10%"><?php echo get_string('gubun','local_lmsdata'); ?></th>
                <th scope="row"><?php echo get_string('email', 'local_lmsdata'); ?></th>
                <th scope="row"><?php echo get_string('contact', 'local_lmsdata'); ?></th>
                <th scope="row"><?php echo get_string('user_state', 'local_lmsdata'); ?></th>
            </tr>
            </thead>
            <?php
                $sql = 'SELECT u.*, lu.usergroup FROM {lmsdata_user} lu JOIN {user} u ON lu.userid = u.id WHERE lu.b_temp = 1'.$where.$like;
                $csql = 'SELECT count(u.id) FROM {lmsdata_user} lu JOIN {user} u ON lu.userid = u.id WHERE lu.b_temp = 1'.$where.$like;
                $count = $DB->count_records_sql($csql,array('search' => '%' . $searchtext . '%'));
                $contents = $DB->get_records_sql($sql,array('search' => '%' . $searchtext . '%'), $offset, $perpage);
                if(!empty($contents)){
                    $num = $totalcount - $offset;
                    foreach($contents as $content){
            ?>
            <tr>
                <td class="chkbox"><input type="checkbox" title="checkbox" name="user_chk[]" value="<?php echo $content->id;?>"</td>
                <td><?php echo $num;?></td>
                <td><a href="./inftemp_add.php?id=<?php echo $content->id;?>&mod=edit" style="color:#00769A;"><?php echo $content->username;?></a></td>
                <td><?php echo fullname($content);?></td>
                <td><?php if($content->usergroup == 'pr') echo get_string('teacher', 'local_lmsdata'); else if($content->usergroup == 'rs') echo get_string('student', 'local_lmsdata');?></td>
                <td><?php echo $content->email;?></td>
                <td><?php 
                        $str = $content->phone2;
                        if(strlen($str) == 11){
                            $phone[0] = substr($str, 0, 3);
                            $phone[1] = substr($str, 3, 4);
                            $phone[2] = substr($str, 7, 4);
                            echo $phone[0].'-'.$phone[1].'-'.$phone[2];
                        }else if(strlen($str) == 10){
                            $phone[0] = substr($str, 0, 3);
                            $phone[1] = substr($str, 3, 3);
                            $phone[2] = substr($str, 6, 4);
                            echo $phone[0].'-'.$phone[1].'-'.$phone[2];
                        }else{
                            echo $content->phone2;
                        }
                        ?></td>
                <td><?php if($content->suspended == 0) echo get_string('siteadmin_act', 'local_lmsdata'); else echo get_string('siteadmin_noact', 'local_lmsdata');?></td>
            </tr>
            <?php
            $num--;
                    }
                } else {
            ?>
            <tr>
                <td colspan="8">등록된 임시계정이 없습니다.</td>
            </tr>
            <?php
                }
            ?>
        </table><!--Table End-->
        
        <div class="btn_area width100">
            <div  class="text-left">
                <input type="button" id="suspended_ok" value="<?php echo get_string('siteadmin_act', 'local_lmsdata'); ?>" onclick="" class="blue_btn" /> 
                <input type="button" id="suspended_not" value="<?php echo get_string('siteadmin_noact', 'local_lmsdata'); ?>" onclick="" class="red_btn"/> 
            </div>
            <div class="right">
                <input type="button" value="<?php echo get_string('board_regist', 'local_lmsdata'); ?>" onclick="location.href = 'inftemp_add.php'" class="blue_btn" />
            </div>
        </div>  
        </form>
        <?php
        $count_datas= $count;
            print_paging_navbar_script($count_datas, $page, $perpage, 'javascript:cata_page(:page);');
        ?>
          
        
    </div><!--Content End-->
    
</div> <!--Contents End-->

 <?php include_once ('../inc/footer.php');?>

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
        $("#suspended_ok").click(function() {
            if ($('input:checkbox[name="user_chk[]"]:checked').length == 0){
                alert('대상을 선택해주세요');
                return false;
            } else {
                if(confirm("선택된 유저를 활성 하시겠습니까?")){
                    $('#suspended').attr('action','./inftemp_submit.php?mod=suspended&suspended=0').attr('method', 'post').submit();
                }
            }
        });
        $("#suspended_not").click(function() {
            if ($('input:checkbox[name="user_chk[]"]:checked').length == 0){
                alert('대상을 선택해주세요');
                return false;
            } else {
                if(confirm("선택된 유저를 비활성 하시겠습니까?")){
                    $('#suspended').attr('action','./inftemp_submit.php?mod=suspended&suspended=1').attr('method', 'post').submit();
                }
            }            
        });
    });
</script>
