<?php
function local_attendance_editor_options($context, $contentid) {
        global $COURSE, $PAGE, $CFG;
        // TODO: add max files and max size support
        $maxbytes = get_user_max_upload_file_size($PAGE->context, $CFG->maxbytes, $COURSE->maxbytes);
        return array(
            'maxfiles' => EDITOR_UNLIMITED_FILES,
            'maxbytes' => $maxbytes,
            'trusttext'=> true,
            'return_types'=> FILE_INTERNAL | FILE_EXTERNAL,
            'subdirs' => file_area_contains_subdirs($context, 'mod_jinotechboard', 'contents', $contentid)
        );
    }
function attend_get_paging_bar($url, $params, $total_pages, $current_page, $max_nav = 10) {

    $current_nav_page = (int) ($current_page / $max_nav);
    if (($current_page % $max_nav) > 0) {
        $current_nav_page += 1;
    }
    $page_start = ($current_nav_page - 1) * $max_nav + 1;
    $page_end = $current_nav_page * $max_nav;

    if ($page_end > $total_pages) {
        $page_end = $total_pages;
    }

    if (!empty($params)) {
        $tmp = array();
        foreach ($params as $key => $value) {
            $tmp[] = $key . '=' . $value;
        }
        $tmp[] = "page=";
        $url = $url . "?" . implode('&', $tmp);
    } else {
        $url = $url . "?page=";
    }

    echo '<div class="board-breadcrumbs" >';

    if ($current_page > 1) {
        echo '<span class="board-nav-prev"><a class="prev" href="' . $url . ($current_page - 1) . '"><</a></span>';
    } else {
        echo '<span class="board-nav-prev"><a class="prev" href="#"><</a></span>';
    }
    echo '<ul>';
    for ($i = $page_start; $i <= $page_end; $i++) {
        if ($i == $current_page) {
            echo '<li class="current"><a href="#">' . $i . '</a></li>';
        } else {
            echo '<li><a href="' . $url . '' . $i . '">' . $i . '</a></li>';
        }
    }
    echo '</ul>';
    if ($current_page < $total_pages) {
        echo '<span class="board-nav-next"><a class="next" href="' . $url . ($current_page + 1) . '">></a></span>';
    } else {
        echo '<span class="board-nav-next"><a class="next" href="#">></a></span>';
    }

    echo '</div>';
}

function tran_progress($attend, $activity) {
    $att = '-';
    if ($attend) {
        switch ($activity) {
            case '출석':
            case 'SC강의':
                switch ($attend->track) {
                    case '0':
                        $att = 'X';
                        break;
                    case '1':
                        $att = 'O';
                        break;
                    case '4':
                    case '3':
                    case '2':
                        $att = '△';
                        break;
                }
                break;
            case 'VOD':
                if ($attend->track == 100) {
                    $att = 'O';
                } else if ($attend->track) {
                    $att = '△';
                } else {
                    $att = 'X';
                }
                break;
        }
    } else {
        $att = 'X';
    }
    return $att;
}


function set_smssend_local_attendance($sms_data) {
    global $CONN_ODBC;
    $select = "select NVL(MAX(TO_NUMBER(IF_MSG_NO)), 0) + 1 AS MSG_NO from EP_PNS.PNS_IF_MSG;";

    $exec = odbc_exec($CONN_ODBC, $select);

    
    $row = odbc_fetch_array($exec);

    $now = date('Ymdhis', time());
      
        $query = "INSERT INTO EP_PNS.PNS_IF_MSG (
                                    IF_MSG_NO
                                   ,IF_MSG_TIT
                                   ,IF_MSG_CTNT
                                   ,IF_MSG_SEND_DTTM
                                   ,IF_SYS_GBN
                                   ,IF_SYS_GBN_NM
                                   ,IF_MSG_PROC_YN
                                   ,IF_MSG_SEND_ID
                                   ) VALUES ( 
                                       '" . $row['MSG_NO'] . "',"
            . "'" . iconv('UTF-8','EUC-KR',$sms_data->subject) . "',"
            . "'" . iconv('UTF-8','EUC-KR',$sms_data->contents) . "',"
            . "'" . $now . "',"
            . "'227692',"
            . "'".iconv('UTF-8','EUC-KR','미래융합대학교')."',"
            . "'N',"
            . "'06931'"
            . ")";

    if ($result = odbc_exec($CONN_ODBC, $query) == TRUE) {
    } else {
        echo $query;
        print_object(odbc_error($CONN_ODBC));
    }
    
    return $row['MSG_NO'];
}

function send_sms_local_attendance($user, $sms_data,$msg_no) {
    global $CONN_ODBC;
    if ($user->username == 'admin') {
        $username = 'lms_admin';
    } else {
        $username = $user->username;
    }
    
    $now = date('Ymdhis', time());
    $send_date = date('Ymdhis', $send_date);

    $userinfo = $user->firstname . $user->lastname . '^' . $user->phone2;


    if(preg_match('/^[0-9]{10,11}$/', trim($user->phone2))){

    $query = "INSERT INTO EP_PNS.PNS_IF_MSG_USER (
         IF_MSG_NO
        ,IF_SEND_EMP_NO
        ,IF_SEND_EMP_TEL
        ,IF_RECV_USER_NM
        ,IF_RECV_USER_TEL
        ,IF_SEND_GBN
        ) VALUES ( 
            '" . iconv('UTF-8','EUC-KR',$msg_no) . "',"
            . "'".iconv('UTF-8','EUC-KR',$user->username)."',"
            . "'".iconv('UTF-8','EUC-KR',$sms_data->callback)."',"
            . "'".iconv('UTF-8','EUC-KR',fullname($user))."',"
            . "'".iconv('UTF-8','EUC-KR',$user->phone2)."',"
            . "'N'"
            . ")";

        if ($result = odbc_exec($CONN_ODBC, $query) == TRUE) {
            
        } else {
            echo $query;
            print_object(odbc_error($CONN_ODBC));
        }
    }
}

