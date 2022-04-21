<?php
require_once dirname(dirname(dirname(__FILE__))) . '/config.php';

function send_sms($user, $sms_data,$msg_no) {
    global $USER,$DB;
    $conn = sms_db_connect();
    
    $curdate = date('Ymd', time());
    $curtime = date('Hi', time());

    $content = strip_tags($sms_data->contents);
    $callback = $sms_data->callback;
    $lmsuserfrom = $DB->get_field('lmsdata_user','dept_cd', array('userid' => $USER->id));
    $lmsuserto = $DB->get_field('lmsdata_user','dept_cd', array('userid' => $user->id));
        $query = "INSERT INTO MSG_DATA  
        ( REQ_DATE, CUR_STATE, MSG_TYPE
	,SMS_TXT, SENDER, SENDER_DEPT
	,CALL_FROM ,RECEIVER ,RECEIVER_DEPT 
	,CALL_TO ,PGMID, BIZ_CD )
VALUES (
        CONVERT(DATETIME, '$curdate' ) + (SUBSTRING('$curtime' , 1, 2) + ':' + SUBSTRING('$curtime', 3, 4))
	,0
	,4
	,'$content'
	,'$USER->username'
	,'$lmsuserfrom'
	,'$callback'
	,'$user->username'
	,'$lmsuserto'
	,'$user->phone2'
        ,'CourseSMS'    
	,'01'
	)
";
        /**요청날짜*/
        if ($result = odbc_exec($conn, $query) == TRUE) {
            echo 'run';
        } else {
            echo $query;
            print_object(odbc_error($conn));
            odbc_close($conn);
            die();
        }
    
    odbc_close($conn);
}
function sms_db_connect() {
    global $SMS_CONN_ODBC;
    //Create connection
    $SMS_CONN_ODBC = odbc_connect("Driver={ODBC Driver 13 for SQL Server};Server=210.125.136.17;Database=NPro;", 'smartcampusm', 'smcm!*03)%');
    //$CONN_ODBC = odbc_connect("Driver={ODBC Driver 13 for SQL Server};Server=210.125.136.17;Database=SMARTCAMPUS;", 'smartcampus', 'smc!*02@^');
    //Check connection
    if (!$SMS_CONN_ODBC) {
        return odbc_error($SMS_CONN_ODBC);
        die();
    } else {
        return $SMS_CONN_ODBC;
    }
}