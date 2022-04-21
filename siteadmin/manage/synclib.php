<?php

function siteadmin_get_years() {
    global $DB;

    $current = date("Y");

    $max = $current;
    $min = 2017;

    if (empty($min)) {
        $min = $current - 1;
    }

    $years = array();
    for ($i = $max; $i >= $min; $i--) {
        $years[$i] = $i;
    }

    return $years;
}

function siteadmin_get_terms() {
    $currentlang = current_language();
    if ($currentlang != 'ko') {
        return array(
            '1' => '1st Semester', // 1st Semester
            '2' => '2nd Semester' // 2nd Semester
        );
    } else {
        return array(
            '10' => '1학기',
            '11' => '여름학기',
            '20' => '2학기',
            '21' => '겨울학기'
        );
    }
}

function siteadmin_get_terms_sync() {
    $terms = array(
        '10' => '1학기',
        '11' => '여름학기',
        '20' => '2학기',
        '21' => '겨울학기'
    );

    return $terms;
}

function siteadmin_get_sync_tabs() {
    return array(
        array(
            'class' => 'black_btn',
            'text' => get_string('user'),
            'page' => 'user'),
        array(
            'class' => 'black_btn',
            'text' => get_string('course'),
            'page' => 'course'),
        array(
            'class' => 'black_btn',
            'text' => get_string('courseuser', 'local_lmsdata'),
            'page' => 'participant'),
//        array(
//            'class' => 'black_btn',
//            'text'  => '학사일정',
//            'page'  => 'schedule'),
//        array(
//            'class' => 'black_btn',
//            'text'  => '연구내역',
//            'page'  => 'research'),
        array(
            'class' => 'red_btn',
            'text' => 'setting',
            'page' => 'config')
    );
}

function participant_sync_tabs() {
    return array(
        array(
            'class' => 'black_btn',
            'text' => '동기화',
            'page' => 'participant'),
        array(
            'class' => 'black_btn',
            'text' => '이력',
            'page' => 'list')
    );
}

function course_sync_tabs() {
    return array(
        array(
            'class' => 'black_btn',
            'text' => '동기화',
            'page' => 'participant'),
        array(
            'class' => 'black_btn',
            'text' => '이력',
            'page' => 'list')
    );
}

function siteadmin_get_category_path_names($category_path, $delimiter = '/') {
    global $DB;

    $paths = array_filter(explode($delimiter, $category_path));
    list($insql, $inparams) = $DB->get_in_or_equal($paths, SQL_PARAMS_NAMED);

    $sql = "SELECT * FROM {course_categories} c
              WHERE c.id $insql";

    $categories = $DB->get_records_sql($sql, $inparams);

    $path_names = array();
    foreach ($paths as $path) {
        if (!empty($categories[$path])) {
            $path_names[] = $categories[$path]->name;
        }
    }

    return $path_names;
}

function siteadmin_insert_or_update_lmsuserdata($lmsuserdata) {
    global $DB;

    if (empty($lmsuserdata->userid)) {
        return false;
    }

    if ($id = $DB->get_field('lmsdata_user', 'id', array('userid' => $lmsuserdata->userid))) {
        $lmsuserdata->id = $id;

        return $DB->update_record('lmsdata_user', $lmsuserdata);
    } else {
        return $DB->insert_record('lmsdata_user', $lmsuserdata);
    }
}

function siteadmin_encrypt_idnumber($idnumber) {
    $key = siteadmin_generate_encrypt_key();

    $iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
    $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);

    $encrypted = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $idnumber, MCRYPT_MODE_CBC, $iv));

    return $key . '@' . bin2hex($iv) . '@' . $encrypted;
}

function siteadmin_decrypt_idnumber($idnumber) {
    $strs = explode('@', $idnumber);

    $decrypted = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $strs[0], base64_decode($strs[2]), MCRYPT_MODE_CBC, pack('H*', $strs[1]));

    return $decrypted;
}

function siteadmin_generate_encrypt_key($length = 8) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';

    $shuffed = str_shuffle($characters);

    return substr($shuffed, 0, $length);
}

function siteadmin_split_fullname($fullname) {

    $names = array();

    $pos = strpos($fullname, ' ');

    // 영문이면 0 한글이면 1 반환
    $id_eng_check = preg_match("/[\xA1-\xFE][\xA1-\xFE]/", $fullname);
    if ($pos !== false) {
        if ($id_eng_check) {
            $names['firstname'] = mb_substr($fullname, 0, $pos, 'UTF-8');
            $names['lastname'] = trim(mb_substr($fullname, $pos, mb_strlen($fullname), 'UTF-8'));
        } else {
            $pos = strrpos($fullname, ' ');
            $names['firstname'] = trim(mb_substr($fullname, $pos, mb_strlen($fullname), 'UTF-8'));
            $names['lastname'] = mb_substr($fullname, 0, $pos, 'UTF-8');
        }
    } else {
        $names['firstname'] = mb_substr($fullname, 0, 1, 'UTF-8');
        $names['lastname'] = mb_substr($fullname, 1, mb_strlen($fullname), 'UTF-8');
    }

    return $names;
}

function siteadmin_get_max_course_shortname() {
    global $DB;

    $max = $DB->get_field_sql("SELECT max(CAST(shortname AS UNSIGNED)) FROM {course}");
    //$max = $DB->get_field_sql("SELECT max_course_shortname() " . $DB->sql_null_from_clause());
    if ($max === false) {
        $max = 0;
    }

    return $max+1;
}

function siteadmin_sync_db_connect() {
    //Create connection
    //$CONN_ODBC = odbc_connect("Driver={ODBC Driver 13 for SQL Server};Server=210.125.136.90;Database=SMARTCAMPUS;", 'smartcampus', 'Tsc1802)(');
    $CONN_ODBC = odbc_connect("Driver={ODBC Driver 13 for SQL Server};Server=210.125.136.17;Database=SMARTCAMPUS;", 'smartcampus', 'smc!*02@^');
    // LMS 2번서버는 17
    //Check connection
    if (!$CONN_ODBC) {
        return odbc_error($CONN_ODBC);
    } else {
        return $CONN_ODBC;
    }
}

function siteadmin_sync_db_close($conn) {
    odbc_close($conn);
}

/**
 * 학사 DB에서 가져온 것을 UtF-8 인코딩
 * 
 * @param string $text
 * @return string
 */
function siteadmin_sync_encode($text) {
    return iconv('euc-kr', 'utf-8', $text);
}

function siteadmin_sync_validate_email($email) {
    $emailfiltered = filter_var($email, FILTER_VALIDATE_EMAIL);
    if (!$emailfiltered) {
        $emailfiltered = ' ';
    }

    return $emailfiltered;
}

function siteadmin_sync_validate_phonenumber($phonenumber) {
    if (!preg_match('/^[-)0-9 ]+$/', $phonenumber)) {
        return ' ';
    }

    return $phonenumber;
}

function siteadmin_sync_get_next_shortname() {
    global $DB;

    $max_shortname = $DB->get_field_sql("SELECT MAX(DISTINCT CAST(shortname AS UNSIGNED)) AS max_shortname FROM {course}");
    if ($max_shortname < 1000) {
        $max_shortname = 1000;
    }

    return $max_shortname + 1; //ceil ($max_shortname/1000) * 1000; 
}

