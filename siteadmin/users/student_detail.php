<?php 
require_once dirname(dirname(dirname (__FILE__))).'/config.php';
require_once dirname(dirname (__FILE__)).'/lib/paging.php';
require_once dirname(dirname (__FILE__)).'/lib.php';
require_once $CFG->dirroot.'/siteadmin/manage/synclib.php';

$id     = optional_param('id', 0, PARAM_INT);
$currpage     = optional_param('page', 1, PARAM_INT);
$searchtext   = optional_param('searchtext', '', PARAM_TEXT);
$hyear      = optional_param('hyear', '', PARAM_RAW); 
// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/users/student_detail.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

//데이터 가져오기
$sql = 'select u.*,lu.eng_name,lu.country,lu.iphakkwa,lu.iphakgubun,lu.hyear,lu.jghyoun,lu.suno,lu.iphakymd,lu.iphakteaki,lu.jehakteaki,lu.jungno,lu.hakwino,lu.leavehakgi from {user} u join {lmsdata_user} lu on lu.userid = u.id where u.id = :userid';
$user = $DB->get_record_sql($sql,array('userid'=>$id));

$js = array(
    $CFG->wwwroot.'/siteadmin/manage/course_list.js'
);

foreach($user as $key=>$val){
    if(empty($val))$user->$key = '-';
}

include_once (dirname(dirname (__FILE__)).'/inc/header.php'); 
?>
<div id="contents">
    <?php include_once  (dirname(dirname (__FILE__)).'/inc/sidebar_users.php');?>
    
    <div id="content">
        <h3 class="page_title">학생 정보</h3>
        <div class="page_navbar"><a href="./category_list.php"><?php echo get_string('user_management', 'local_lmsdata'); ?></a> > <a href="./category_list.php"><?php echo get_string('stu_management', 'local_lmsdata'); ?></a></div>
        
        
        <table>
            <tr>
                <th>성명</th>
                <td><?php echo fullname($user); ?></td>
                <th>주민등록번호</th>
                <td><?php echo substr(siteadmin_decrypt_idnumber($user->idnumber),0,6).'-'.substr(siteadmin_decrypt_idnumber($user->idnumber),6); ?></td>
            </tr>
            <tr>
                <th><?php echo get_string('eng_name','local_lmsdata'); ?></th>
                <td><?php echo $user->eng_name; ?></td>
                <th>국적</th>
                <td><?php echo $user->country; ?></td>
            </tr>
            <tr>
                <th><?php echo get_string('email', 'local_lmsdata'); ?></th>
                <td colspan='3'><?php echo $user->email; ?></td>
            </tr>
            <tr>
                <th>전화번호</th>
                <td><?php echo $user->phone1; ?></td>
                <th>핸드폰</th>
                <td><?php echo $user->phone2; ?></td>
            </tr>
            <tr>
                <th>우편번호 주소</th>
                <td colspan='3'><?php echo $user->address; ?></td>
            </tr>
            <tr>
                <th>입학학과</th>
                <td><?php echo $user->iphakkwa; ?></td>
                <th>입학<?php echo get_string('gubun','local_lmsdata'); ?></th>
                <td><?php echo $user->iphakgubun; ?></td>
            </tr>
            <tr>
                <th><?php echo get_string('class','local_lmsdata'); ?></th>
                <td><?php echo $user->hyear; ?><?php echo get_string('class','local_lmsdata'); ?></td>
                <th>진학<?php echo get_string('class','local_lmsdata'); ?></th>
                <td><?php echo $user->jghyoun; ?><?php echo get_string('class','local_lmsdata'); ?></td>
            </tr>
            <tr>
                <th>수험번호</th>
                <td><?php echo $user->suno; ?></td>
                <th>입학년월일</th>
                <td><?php echo $user->iphakymd; ?></td>
            </tr>
            <tr>
                <th>입학특기</th>
                <td><?php echo $user->iphakteaki; ?></td>
                <th>재학특기</th>
                <td><?php echo $user->jehakteaki; ?></td>
            </tr>
            <tr>
                <th>증서번호</th>
                <td><?php echo $user->jungno; ?></td>
                <th>학위번호</th>
                <td><?php echo $user->hakwino; ?></td>
            </tr>
            <tr>
                <th>현 휴학학기</th>
                <td colspan='3'><?php echo $user->leavehakgi; ?></td>
            </tr>
        </table><!--Table End-->

        <div class="btn_area">
            <input type="button" value="돌아가기" onclick="location.href = 'info.php?page=<?php echo $currpage; ?>&hyear=<?php echo $hyear; ?>&searchtext=<?php echo $searchtext; ?>'" class="blue_btn" style="float:right;"/>
        </div>  
    </div><!--Content End-->
    
</div> <!--Contents End-->

 <?php include_once ('../inc/footer.php');?>
