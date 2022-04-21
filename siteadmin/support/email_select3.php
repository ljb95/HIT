<?php
require_once dirname(dirname(dirname (__FILE__))).'/config.php';

global $DB,$CFG;

function print_paging_nav($totalcount, $page, $perpage, $baseurl, $params = null, $maxdisplay = 18, $paramname = 'page') {
    global $CFG, $SITECFG;
    
    $pagelinks = array();
    
    $lastpage = 1;
    if($totalcount > 0) {
        $lastpage = ceil($totalcount / $perpage);
    }
    
    if($page > $lastpage) {
        $page = $lastpage;
    }
            
    if ($page > round(($maxdisplay/3)*2)) {
        $currpage = $page - round($maxdisplay/2);
        if($currpage > ($lastpage - $maxdisplay)) {
            $currpage = $lastpage - $maxdisplay;
        }
    } else {
        $currpage = 1;
    }
    
    
    
    if($params == null) {
        $params = array();
    }
    
    $prevlink = '';
    if ($page > 1) {
        $params[$paramname] = $page - 1;
        $prevlink = html_writer::link(new moodle_url($baseurl, $params), '<img src="'.$CFG->wwwroot.'/siteadmin/img/pagination_left.png"/>', array('class'=>'next'));
    } else {
        $prevlink = '<a href="#" class="next"><img src="'.$CFG->wwwroot.'/siteadmin/img/pagination_left.png"/></a>';
    }
    
    $nextlink = '';
     if ($page < $lastpage) {
        $params[$paramname] = $page + 1;
        $nextlink = html_writer::link(new moodle_url($baseurl, $params), '<img src="'.$CFG->wwwroot.'/siteadmin/img/pagination_right.png"/>', array('class'=>'prev'));
    } else {
        $nextlink = '<a href="#" class="prev"><img src="'.$CFG->wwwroot.'/siteadmin/img/pagination_right.png"/></a>';
    }
    
    
    echo '<div class="pagination">';
    
    $pagelinks[] = $prevlink;
    
    if ($currpage > 1) {
        $params['page'] = 1;
        $firstlink = html_writer::link(new moodle_url($baseurl, $params), 1);
        
        $pagelinks[] = $firstlink;
        if($currpage > 2) {
            $pagelinks[] = '...';
        }
    }
    
    $displaycount = 0;
    while ($displaycount <= $maxdisplay and $currpage <= $lastpage) {
        if ($page == $currpage) {
            $pagelinks[] = '<strong>'.$currpage.'</strong>';
        } else {
            $params[$paramname] = $currpage;
            $pagelink = html_writer::link(new moodle_url($baseurl, $params), $currpage);
            $pagelinks[] = $pagelink;
        }
        
        $displaycount++;
        $currpage++;
    }
    
    if ($currpage - 1 < $lastpage) {
        $params['page'] = $lastpage;
        $lastlink = html_writer::link(new moodle_url($baseurl, $params), $lastpage);
        
        if($currpage != $lastpage) {
            $pagelinks[] = '...';
        }
        $pagelinks[] = $lastlink;
    }
    
    $pagelinks[] = $nextlink;
   
    
    echo implode('&nbsp;', $pagelinks);
    
    echo '</div>';
}

$gubun = optional_param('gubun','',PARAM_ALPHA);

//코스와 관련된 데이터 가져오기

$parent = optional_param('parent', 0, PARAM_INT); // 과정 구분
$category = optional_param('category', 0, PARAM_INT); // 과정
$key = optional_param('key', '', PARAM_ALPHA); // 검색키
$val = optional_param('val', '', PARAM_RAW); // 검색키

$currpage = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);

$select = "SELECT u.* FROM {user} u 
    join {lmsdata_user} ui on u.id=ui.userid ";

$conditions = array();
$params = array();

$conditions[] = 'u.deleted=0';

if (!empty($val)) {
    
    if($key=='name'){
        $conditionname = array();
        $conditionname[] = $DB->sql_like('u.firstname', ':firstname', false);
        $conditionname[] = $DB->sql_like('u.lastname', ':lastname', false);
        $conditionname[] = $DB->sql_like($DB->sql_fullname('u.firstname', 'u.lastname'), ':fullname', false);
        $conditionname[] = $DB->sql_like($DB->sql_concat('u.firstname', 'u.lastname'), ':fullname1', false);

        $conditions[] = '('.implode(' OR ', $conditionname).')';

        $params['firstname'] = '%'.$val.'%';
        $params['lastname'] = '%'.$val.'%';
        $params['fullname'] = '%'.$val.'%';
        $params['fullname1'] = '%'.$val.'%';

    }else{
        $conditions[] = $DB->sql_like('u.'.$key, ':val');
        $params['val'] = '%' . $val . '%';
    }
}

$where = '';
if (!empty($conditions)) {
    $where = ' WHERE ' . implode(' AND ', $conditions);
}

$sort = ' order by u.id desc';

$usercount = $DB->count_records_sql("SELECT count(*) from {user} u join {lmsdata_user} ui on u.id=ui.userid " . $where . $sort, $params);
$users = $DB->get_records_sql($select . $where . $sort, $params, ($currpage - 1) * $perpage, $perpage);
?>

<html>
    <head>
        <title>발송대상추가</title>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
        <meta http-equiv="X-UA-Compatible" content="IE=edge" />
        <link rel="stylesheet" type="text/css" href="<?php echo $CFG->wwwroot . '/siteadmin/css/style_lms_admin.css'; ?>" />
        <script src="<?php echo $CFG->wwwroot . '/siteadmin/js/lib/jquery-1.11.2.min.js'; ?>"></script>
        <script src="<?php echo $CFG->wwwroot.'/siteadmin/js/common.js'; ?>"></script>
    </head>
    <body>
      
<table cellpadding="0" cellspacing="0" class="detail">
    <tbody>
        <tr>
            <td class="field_title">사용자 검색</td>
            <td class="field_value">
            <form name="search_form">
                <select class="w_90" name="key">
                    <option value="name">이름</option>
                </select>
                <input type="text" name="val" class="w_200"  value="<?php echo $val;?>"/>
                <input type="submit" class="blue_btn" value="검색" style="float: right"/>
            </form>
            </td>
        </tr>
    </tbody>
</table>

<p class="search_result" style="margin: 0 !important">(<?php echo ceil($usercount/$perpage);?>페이지, 총 <?php echo $usercount?>건)</p>
<form name="frm_list" id="frm_list">
<table cellpadding="0" cellspacing="0">

    <tbody>

        <tr>

            <th width="10%"><input type="checkbox" name="allchk" value="1" onclick="list_all_check(this,this.form.chkbox);"/></th>

            <th width="30%">이름(ID)</th>

            <th width="30%">이메일 주소</th>

            <th width="30%">휴대폰번호</th>

        </tr>

<?php 
                        
if ($usercount > 0) {
   
   foreach ($users as $user) {
       
       $username = fullname($user);
       $userphone = ($user->phone2)? $user->phone2:'-';
       
          echo '<tr>

            <td><input type="checkbox" name="chkbox" value="user;'.$user->id .';'.$username.'"/></td>

            <td class="number">'.$username.'('.$user->username.')</td>

            <td>'.$user->email.'</td>

            <td class="number">'.$userphone.'</td>

        </tr> ';
   }
   
   if(empty($users)){
       echo '<tr><td colspan = "4" >검색된 대상자가 없습니다.</td></tr>';
   }
}
                        
?>
        

    </tbody>

</table>
    
</form>

<?php
$page_params = array();

if ($gubun) {
    $page_params['gubun'] = $gubun;
}
//2014-03-14: 검색단어 파라미터 추가 //kss
if ($val) {
	$page_params['key'] = $key;
	$page_params['val'] = $val;
}

print_paging_nav($usercount, $currpage, $perpage, 'email_select3.php', $page_params);
?>

</body>
</html>
<script>
    function list_all_check(all,chk){
        for(var i=0;i<chk.length;i++){
            if(all.checked==true){
                chk[i].checked = true;
            }else{
                chk[i].checked = false;
            }
        }
    }
</script>