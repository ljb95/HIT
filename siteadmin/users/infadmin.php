<?php 
require_once dirname(dirname(dirname (__FILE__))).'/config.php';
require_once dirname(dirname (__FILE__)).'/lib/paging.php';
require_once dirname(dirname (__FILE__)).'/lib.php';

// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/users/infadmin.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

$currpage     = optional_param('page', 1, PARAM_INT);
$perpage      = optional_param('perpage', 20, PARAM_INT);
$search       = optional_param('search', 'username', PARAM_RAW);
$searchtext   = optional_param('searchtext', '', PARAM_TEXT);

//데이터 가져오기
$like = '';
if (!empty($searchtext)) {
    $like = " and " . $DB->sql_like($search, ':search', false);
}

$js = array(
    $CFG->wwwroot.'/siteadmin/manage/course_list.js'
);


include_once (dirname(dirname (__FILE__)).'/inc/header.php'); 
?> 
<div id="contents">
    <?php include_once  (dirname(dirname (__FILE__)).'/inc/sidebar_users.php');?>
    
    <div id="content">
        <h3 class="page_title"><?php echo get_string('admin_management', 'local_lmsdata'); ?></h3>
        <div class="page_navbar"><a href="<?php echo $CFG->wwwroot . '/siteadmin/users/info.php'; ?>"><?php echo get_string('user_management', 'local_lmsdata'); ?></a> > <a href="<?php echo $CFG->wwwroot . '/siteadmin/users/infadmin.php'; ?>"><?php echo get_string('admin_management', 'local_lmsdata'); ?></a></div>
        <form id="frm_notices_search" class="search_area">
            <select name="search" title="category" class="w_160">
                <option value="username" <?php if($search == 'username') echo 'selected';?>><?php echo get_string('user_id', 'local_lmsdata'); ?></option>
                <option value="firstname" <?php if($search == 'firstname') echo 'selected';?>><?php echo get_string('name','local_lmsdata'); ?></option>
            </select>
            <input type="text" title="serch" name="searchtext" value="<?php echo $searchtext; ?>" class="search-text" placeholder="<?php echo get_string('input', 'local_jinoboard'); ?>">
            <input type="submit" class="search_btn" id="search" value="<?php echo get_string('search', 'local_jinoboard'); ?>">
        </form>
        <table>
            <caption class="hidden-caption"><?php echo get_string('admin_management', 'local_lmsdata'); ?></caption>
            <thead>
            <tr>
                <th scope="row" width="5%"><?php echo get_string('number', 'local_lmsdata'); ?></th>
                <th scope="row" width="10%"><?php echo get_string('user_id', 'local_lmsdata'); ?></th>
                <th scope="row" width="10%"><?php echo get_string('name','local_lmsdata'); ?></th>
                <th scope="row"><?php echo get_string('email', 'local_lmsdata'); ?></th>
                <th scope="row"><?php echo get_string('contact', 'local_lmsdata'); ?></th>
            </tr>
            </thead>
            <tbody>
            <?php
                $admins = explode(',',$CFG->siteadmins);
                $superadmin = $admins[0];
                $number = 1;
                foreach($admins as $admin){
                    $sql = 'SELECT * FROM {user} WHERE id='.$admin.$like;
                    $content = $DB->get_record_sql($sql,array('search' => '%' . $searchtext . '%'));
                    if(empty($content)){
                        continue;
                    }
            ?>
                <tr>
                    <td><?php echo $number;?></td>
                    <td><?php if($admin == $USER->id || $superadmin == $USER->id){echo '<a href="./infadmin_add.php?id='.$content->id.'&mod=edit" style="color:#00769A;">'.$content->username.'</a>'; } else {echo $content->username;}?></td>
                    <td><?php echo fullname($content);?></td>
                    <td><?php echo $content->email;?></td>
                    <td><?php 
                        $str = $content->phone1;
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
                            echo $content->phone1;
                        }
                        ?></td>
                </tr>
            <?php
                $number++;
                }
                if($number == 1){
            ?>
                <tr>
                    <td colspan="5">등록된 관리자가 없습니다.</td>
                </tr>
            <?php
                }
            ?>
            </tbody>
        </table><!--Table End-->
        <div class="btn_area">
            <input type="button" value="<?php echo get_string('board_regist', 'local_lmsdata'); ?>" onclick="location.href = 'infadmin_add.php'" class="blue_btn" style="float:right;"/>
        </div>  
          
        
    </div><!--Content End-->
    
</div> <!--Contents End-->

 <?php include_once ('../inc/footer.php');?>
