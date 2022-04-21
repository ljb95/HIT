<?php 
require_once dirname(dirname(dirname (__FILE__))).'/config.php';
require_once dirname(dirname (__FILE__)).'/lib/paging.php';
require_once dirname(dirname (__FILE__)).'/lib.php';
require_once $CFG->dirroot.'/siteadmin/manage/synclib.php';

$id     = optional_param('id', 0, PARAM_INT);
$currpage     = optional_param('page', 1, PARAM_INT);
$search       = optional_param('search', 1, PARAM_INT);
$searchtext   = optional_param('searchtext', '', PARAM_TEXT);
$usergroup   = optional_param('usergroup', '', PARAM_RAW);
// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/users/professor_detail.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

//데이터 가져오기
$sql = 'select u.*,lu.eng_name,lu.gubun,lu.persg,lu.fax,lu.indate,lu.psosok,lu.persk,lu.internaltel from {user} u join {lmsdata_user} lu on lu.userid = u.id where u.id = :userid';
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
        <h3 class="page_title">교수 정보</h3>
        <div class="page_navbar"><a href="./category_list.php"><?php echo get_string('user_management', 'local_lmsdata'); ?></a> > <a href="./category_list.php"><?php echo get_string('prof_management', 'local_lmsdata'); ?></a></div>
        
        
        <table>
            <tr>
                <th>성명</th>
                <td><?php echo fullname($user); ?></td>
                 <th>영문성명</th>
                <td><?php echo $user->eng_name; ?></td>
            </tr>
            <tr>
                <th>직번</th>
                <td><?php echo $user->username; ?></td>
                <th>주민등록번호</th>
                <td><?php echo substr(siteadmin_decrypt_idnumber($user->idnumber),0,6).'-'.substr(siteadmin_decrypt_idnumber($user->idnumber),6); ?></td>
            </tr>
            <tr>
                <th>사원그룹(재직여부)</th>
                <td><?php echo $user->persg; ?></td>
                <th>입사일자</th>
                <td><?php echo $user->indate; ?></td>
            </tr>
            <tr>
                <th>소속 기관</th>
                <td><?php echo $user->psosok; ?></td>
                <th>사원서브그룹</th>
                <td><?php echo $user->persk; ?></td>
            </tr>
            <tr>
                <th>교직원 소속명</th>
                <td colspan='3'><?php echo $user->psosok; ?></td>
            </tr>
            <tr>
                <th><?php echo get_string('email', 'local_lmsdata'); ?></th>
                <td><?php echo $user->email; ?></td>
                <th>사무실 전화번호</th>
                <td><?php echo $user->internaltel; ?></td>
            </tr>
            <tr>
                <th>FAX번호</th>
                <td><?php echo $user->fax; ?></td>
                <th>집전화번호</th>
                <td><?php echo $user->phone1; ?></td>
            </tr>
            <tr>
                <th>우편번호 집주소</th>
                <td><?php echo $user->address; ?></td>
                <th>핸드폰번호</th>
                <td><?php echo $user->phone2; ?></td>
            </tr>
        </table><!--Table End-->

        <div class="btn_area">
            <input type="button" value="돌아가기" onclick="location.href = 'infpro.php?page=<?php echo $currpage; ?>&search=<?php echo $search; ?>&usergroup=<?php echo $usergroup; ?>&searchtext=<?php echo $searchtext; ?>'" class="blue_btn" style="float:right;"/>
        </div>  
    </div><!--Content End-->
    
</div> <!--Contents End-->

 <?php include_once ('../inc/footer.php');?>
