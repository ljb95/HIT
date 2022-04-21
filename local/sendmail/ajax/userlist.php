<?php
require(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');

$id = optional_param('id', 0, PARAM_INT);  // Course ID
$juya = optional_param('juya', '', PARAM_RAW);
$hyear = optional_param('hyear', '', PARAM_RAW);
$dept = optional_param('dept', '', PARAM_RAW);
$search = trim(optional_param('search', '', PARAM_CLEAN));  // Course ID
$selected_users = optional_param_array('selected_users',array(),PARAM_INT);
$context = get_context_instance(CONTEXT_COURSE, $id);

$sql = "select u.id,u.email,u.firstname,u.lastname,u.username, lu.dept, lu.hyear,  lu.day_tm_cd "
        . "from {role_assignments} ra "
        . "join {user} u on u.id = ra.userid "
        . "join {lmsdata_user} lu on u.id = lu.userid "
        . "join {context} c on c.contextlevel = :contextlevel and c.id = ra.contextid "
        . "where ra.contextid = c.id and c.id = :contextid ";
$where = '';
if ($selected_users) {
    $where .= ' and u.id not in (';
    $u = '';
    foreach ($selected_users as $selected_user => $val) {
        $u .= $val . ',';
    }
    $where .= rtrim($u, ',') . ' )';
}
$param = array('contextid' => $context->id, 'contextlevel' => CONTEXT_COURSE);

if ($search) {
    $where .= ' and  ((u.firstname like :searchtxt1 or u.lastname like :searchtxt2 or concat(u.firstname,u.lastname) like :searchtxt3) or u.username like :searchtxt4 )';
    $param['searchtxt1'] = $param['searchtxt2'] = $param['searchtxt3'] = $param['searchtxt4'] = '%' . $search . '%';
}
if($dept) {
    $where .= ' and lu.dept like :dept ';
    $param['dept'] = '%'.$dept.'%';
}
if($hyear) {
    $where .= ' and lu.hyear like :hyear ';
    $param['hyear'] = '%'.$hyear.'%';
}
if($juya) {
    $where .= ' and lu.day_tm_cd like :juya ';
    $param['juya'] = '%'.$juya.'%';
}

    $users = $DB->get_records_sql($sql . $where . ' order by u.username asc', $param, $offset, $perpage);

    $index = 0;
    echo '<div class="send_users">'
    . '<div class = "nextline left w5p">선택</div>'
    . '<div class = "left w10p">프로필 사진</div>'
    . '<div class = "left w30p">이름(학번 및 교번)</div>'
    . '<div class = "left w10p">학년</div>'
    . '<div class = "left w15p">학과</div>'
    . '<div class = "left w10p">주야구분</div>'
    . '</div>';
    foreach ($users as $user) {
        $index++;
        $allCount = count($users);
        $nUser = $allCount - ($allCount % 3);
        if($allCount % 3 == 0){$nUser = $allCount-3;}

        $roles = get_user_roles($context, $user->id);
        $rolename = '';
        foreach ($roles as $role) {
            $rolename .= role_get_name($role) . ',';
        }
        $rolename = rtrim($rolename, ',');
        $disabled = FALSE;
        if(empty($user->email)){
            $disabled = TRUE;
        }

        //마지막 리스트 클래스 추가
        $lastClass = "";
        if($index > $nUser){$lastClass = "lastrow";}
        if($index == $allCount && $allCount % 3 == 1){$lastClass = "lastrow last1";}
        if($index == $allCount && $allCount % 3 == 2){$lastClass = "lastrow last2";}

        echo '<div class="send_users '.$lastClass.'" id="utd'.$user->id.'">';
        if($disabled){
    //        $chekbox = html_writer::empty_tag('input',array('type'=>'checkbox','disabled'=>'disabled','title'=>'Empty Mail','class'=>'emptymail'));
            echo '<div class = "nextline left w5p"><input type="checkbox" disabled title="Empty Mail" class="emptymail" username="'.fullname($user).'"  value="' . $user->id . '"></div>';
        } else {
    //        $chekbox = html_writer::tag('input',array('type'=>'checkbox', 'title'=>'User Select','class'=>'usercheck','username'=>fullname($user),'value'=>$user->id));
            echo '<div class = "nextline left w5p"><input type="checkbox"  class="usercheck" title="User Select" username="'.fullname($user).'"  value="' . $user->id . '"></div>';
        }
        echo '<div class = "left w10p">' . $OUTPUT->user_picture($user, array('courseid' => $id)) . '</div>';
        echo '<div class = "left w30p">' . fullname($user) . '('.$user->username.') </div>';
        if ($user->hyear == '0' || $user->hyear == null) { $user->hyear = '-'; 
        }else{$user->hyear = $user->hyear. '학년';}
        echo '<div class = "left w10p">' .$user->hyear.'</div>';
        echo '<div class = "left w15p">' .$user->dept.'</div>';
        if($user->day_tm_cd == '0' || $user->day_tm_cd == null){ $user->day_tm_cd = '-'; 
        }else if($user->day_tm_cd == '10'){$user->day_tm_cd = '주간';
        }else if($user->day_tm_cd == '20'){$user->day_tm_cd = '야간';}
        echo '<div class = "left w10p">' .$user->day_tm_cd.'</div>';
        echo '</div>';
    }
    if(!$users){
        echo '<div>No Searching User</div>';
    }