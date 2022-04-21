<?php
define('TRANS','');
define('TRANS_THUMB','');
define('MEDIA','');
define('STORAGE','/appdata/lcmsdata');
define('STORAGE2','/appdata');

//define('STORAGE','/appdata/lcmsdata');
//define('STORAGE2','/appdata');

$LCFG = new stdClass();
$LCFG->allowexthtml = array('zip', 'html');
$LCFG->allowextword = array('hwp', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'pdf');
$LCFG->allowextref = array('hwp', 'doc', 'docx', 'ppt', 'pptx', 'xls', 'xlsx', 'pdf', 'mp4', 'mp3', 'wmv');
$LCFG->notallowfile = array('sh','exe','js','php','sql','jsp','asp','cgi','php3','php4','php5','unknown');
/**
 * 
 * @param type int $userid
 * @param type int $groupid
 * @param type string $search
 * @param type int $page
 * @param type int $perpage 
 * @return stdClass total_count , total_pages , files , num
 */
function get_contents($userid = 0,$groupid = 0,$search = "" , $page = 1 , $perpage = 10) {
    global $DB;
    $like = '';
    if (!empty($search)) {
        $like .= " and " . $DB->sql_like('con.con_name', ':search', false);
    }
    $group_like = "";
    if ($groupid) {
        $group_like .= " and " . $DB->sql_like('groups.id', ':groupid', false);
    }
    $userwhere = "";
    if (is_siteadmin()) {
        if ($userid != 0) {
            $userwhere = "and rep.userid = :userid";
        }
        $sql = "select "
                . "rep.id , rep.referencecnt, "
                . "groups.name as gname, "
                . "con.id as conid , con.share_yn,con.con_type, con.con_name, con.update_dt "
                . "from {lcms_repository} rep "
                . "left join {lcms_repository_groups} groups on groups.id = rep.groupid "
                . "join {lcms_contents} con on con.id = rep.lcmsid " . $like
                . " where con.con_type!=:type " . $userwhere . " " . $group_like . " order by con.update_dt desc";
        $count_sql = "select "
                . "count(rep.id) "
                . "from {lcms_repository} rep "
                . "left join {lcms_repository_groups} groups on groups.id = rep.groupid "
                . "join {lcms_contents} con on con.id = rep.lcmsid " . $like
                . " where con.con_type!=:type " . $userwhere . " " . $group_like;
    } else {
        $sql = "select "
                . "rep.id , rep.referencecnt, "
                . "groups.name as gname, "
                . "con.id as conid , con.share_yn,con.con_type, con.con_name, con.update_dt "
                . "from {lcms_repository} rep "
                . "left join {lcms_repository_groups} groups on groups.id = rep.groupid "
                . "join {lcms_contents} con on con.id = rep.lcmsid and con.course_cd = :luid " . $like
                . " where con.con_type!=:type " . $group_like . " order by con.update_dt desc";
        $count_sql = "select "
                . "count(rep.id) "
                . "from {lcms_repository} rep "
                . "left join {lcms_repository_groups} groups on groups.id = rep.groupid "
                . "join {lcms_contents} con on con.id = rep.lcmsid and con.course_cd = :luid " . $like
                . " where con.con_type!=:type " . $group_like . " order by con.update_dt desc";
    }
    
    $return = new stdClass();
    $return->total_count = $DB->count_records_sql($count_sql, array('luid' => $userid, 'search' => '%' . $search . '%', 'type'=>'ref' , 'groupid'=>$groupid ,'userid'=>$userid));
    $return->total_pages = repository_get_total_pages_inadmin($return->total_count, $perpage);

            $offset = 0;
            if ($page != 0) {
                $offset = ($page - 1) * $perpage;
            }

    $return->files = $DB->get_records_sql($sql, array('luid' => $userid, 'search' => '%' . $search . '%', 'type'=>'ref', 'groupid'=>$groupid ,'userid'=>$userid), $offset, $perpage);
    $return->num =  $return->total_count - (($page - 1) * $perpage);
    
    return $return;
}
function repository_get_total_pages_inadmin($rows, $limit = 10) {
    if ($rows == 0) {
        return 1;
    }

    $total_pages = (int) ($rows / $limit);

    if (($rows % $limit) > 0) {
        $total_pages += 1;
    }

    return $total_pages;
}
function upload_lcms_contents_inadmin($contents, $id = 0) {
    global $LCFG, $DB;
    $contents = (object)$contents;
    $contents->userid = 2;
    switch ($contents->con_type) {
        case 'video':
            $newconid = lcms_insert_db_inadmin($contents, $id);
            $DB->set_field_select('lcms_contents_file', 'con_seq', $newconid, " id = :fileid ", array('fileid'=>$contents->video_file_id));
            $extarr = $LCFG->allowextvideo;
            $n = 0;
            break;
        case 'html':
            $contents->data_dir = 'lms/html/' . $contents->userid . '/' . date('YmdHis');
            $extarr = $LCFG->allowexthtml;
            $n = 1;
            break;
        case 'word':
            $contents->data_dir = 'lms/files/' . $contents->userid . '/' . date('YmdHis');
            $extarr = $LCFG->allowextword;
            $n = 0;
            break;
        case 'ref':
            $contents->data_dir = 'lms/ref/' . $contents->userid . '/' . date('YmdHis');
            $extarr = $LCFG->allowextref;
            $n = 0;
            break;
        case 'embed':
            $newconid = lcms_insert_db_inadmin($contents, $id);
            break;
    }
    if ($contents->con_type != "embed" && $contents->con_type != "video") {
        $filecount_cnt = lcms_temp_dir_allow_filecount_inadmin($extarr, $n, $contents->con_type);

        if ($filecount_cnt == 1) {

            $newconid = lcms_insert_db_inadmin($contents, $id);

            lcms_temp_dir_fileupload_inadmin($extarr, $contents->con_type, $newconid, $contents->data_dir);
        } else {
            echo $filecount_cnt;
            exit;
        }
    }
    if ($newconid) {
        return $newconid;
    }
}

function update_lcms_contents_inadmin($contents) {
    global $LCFG, $DB, $USER;

    $contents = (object)$contents;
    switch ($contents->con_type) {
        case 'video':
            $newconid = lcms_update_db_inadmin($contents,$contents->stay_file);
            if ($contents->stay_file == 1) {
                $DB->delete_records('lcms_contents_file', array('con_seq' => $contents->con_id));
                $DB->set_field_select('lcms_contents_file', 'con_seq', $newconid, " id = :fileid ", array('fileid'=>$contents->video_file_id));
            }
            break;
        case 'html':
            $contents->data_dir = 'lms/html/' . $USER->id . '/' . date('YmdHis');
            $extarr = $LCFG->allowexthtml;
            $n = 1;
            break;
        case 'word':
            $contents->data_dir =  'lms/files/' . $USER->id . '/' . date('YmdHis');
            $extarr = $LCFG->allowextword;
            $n = 0;
            break;
        case 'ref':
            $contents->data_dir = 'lms/ref/' . $USER->id . '/' . date('YmdHis');
            $extarr = $LCFG->allowextref;
            $n = 0;
            break;
        case 'embed':
            lcms_update_db_inadmin($contents, $contents->stay_file);
            break;
    }

    if ($contents->con_type != "embed" && $contents->con_type != "video") {
        if ($contents->stay_file == 1) {
            $filecount_cnt = lcms_temp_dir_allow_filecount_inadmin($extarr, $n, $contents->con_type);
        } else if ($contents->stay_file == 0) {
            $filecount_cnt = 1;
        }
        if ($filecount_cnt == 1) {
            if ($contents->stay_file == 1) {
                $filepath = explode('/', $contents->data_dir);
                if ($filepath[0] == 'storage'){
                    $contents->data_dir = $data_dir;
                }
                $conid = lcms_update_db_inadmin($contents);

                if ($filepath[0] != 'storage' && $contents->con_type == 'html') {
                    $path_dir = STORAGE . '/' . $contents->data_dir;
                    $copy_dir_inadmin = STORAGE . '/trash/' . $contents->data_dir;
                    exec("rm -rf {$path_dir}");
                    $DB->delete_records('lcms_contents_file', array('con_seq' => $conid));
                }

                lcms_temp_dir_fileupload_inadmin($extarr, $contents->con_type, $conid, $contents->data_dir);
            } else {
                $conid = lcms_update_db_inadmin($contents,$contents->stay_file);
            }
        } else {
            echo $filecount_cnt;
            exit;
        }
    }
    return $contents->con_id;
}

/**
 * 
 * @global type $CFG
 * @global type $USER
 * @param type $extarr
 * @param type $n
 * @return boolean|string
 */
function lcms_temp_dir_allow_filecount_inadmin($extarr, $n = 0, $type) {
    global $CFG, $USER, $LCFG;
    $temp_dir = $CFG->dirroot . '/siteadmin/contents/contents_upload/server/php/files/' . $USER->id;
    $count = 0;
    if (is_dir($temp_dir)) {
        $dirs = dir($temp_dir);
        while (false !== ($entry = $dirs->read())) {
            if (($entry != '') && ($entry != '.') && ($entry != '..')) {
                if (is_dir($temp_dir . '/' . $entry)) {
                    return get_string('error:notallowfolder', 'local_repository');
                } else {
                    //파일정보가져오기
                    $path = pathinfo($temp_dir . $entry);
                    echo $type;
                    if ($type == 'word' || $type == 'ref') {
                        if (in_array(strtolower($path['extension']), $LCFG->notallowfile)) {
                            return get_string('error:notallowextfile', 'local_repository');
                        } else {
                            $count++;
                        }
                    } else {
                        if (!in_array(strtolower($path['extension']), $extarr)) {
                            return get_string('error:notallowextfile', 'local_repository').'--';
                        } else {
                            $count++;
                        }
                    }
                }
            }
        } // while end
    }
    if ($count == 0) {
        return get_string('error:notaddfile', 'local_repository');
    } else if ($n > 0 && $count > $n) {
        return get_string('error:addfilecount', 'local_repository', $n);
    } else {
        return 1;
    }
}

function lcms_insert_db_inadmin($contents, $id = 0) {
    global $DB; 
    
    $con_db = new stdClass();
    $con_db->area_cd = 1;
    $con_db->major_cd = 1;
    $con_db->course_cd = $contents->userid;

    $user_name = $DB->get_record('user',array('id'=>$contents->userid));
    
    $con_db->teacher = $contents->prof_name;
    
    $con_db->share_yn = $contents->share_yn;

    $con_db->con_name = htmlspecialchars($contents->con_name, ENT_QUOTES);
    $con_db->con_type = $contents->con_type;

    $con_db->con_des = $contents->con_des;
    
    // $con_db->con_tag = $contents->con_tag;
    if (isset($contents->con_total_time)) {
        $con_db->con_total_time = $contents->con_total_time;
    } else {
        $con_db->con_total_time = 0;
    }
    if(!empty($contents->author)){
        $con_db->author = $contents->author;
    } else {
        $con_db->author = "";
    }
    if($contents->con_type != 'ref'){
        
    $con_db->cc_type = $contents->cc_type;
   
        if($contents->cc_type != 3){
            if(!empty($contents->cc_mark)){
                 $con_db->cc_mark = $contents->cc_mark;
            } else {
                 $con_db->cc_mark = '';
            }
        } else {
            $con_db->cc_mark = $contents->cc_text;
        }
    } else {
           $con_db->cc_type = '';
           $con_db->cc_mark = '';
    }
    
    $con_db->embed_type = "";
    $con_db->embed_code = "";
    if ($contents->con_type == "embed") {
        $con_db->embed_type = $contents->emb_type;
        $con_db->embed_code = $contents->emb_code;
    }
    if ($contents->con_type == "media") {
        $con_db->embed_type = "mediaid";
        $con_db->embed_code = $contents->mediaid;
    }
    $con_db->data_dir = $contents->data_dir;
    $con_db->user_no = $contents->prof_userid;
    $con_db->con_hit = 0;
    $con_db->reg_dt = time();
    $con_db->update_dt = time();

    $new_conid = $DB->insert_record('lcms_contents', $con_db);


    if ($id != 0) {
        $ref_con_db = new stdClass();
        $ref_con_db->lcmsid = $new_conid;
        $ref_con_db->repository = $id;
        $ref_con_db->userid = $contents->userid;
        $ref_con_db->groupid = 0;
        $new_ref_conid = $DB->insert_record('lcms_repository_reference', $ref_con_db);
        $rep = $DB->get_record('lcms_repository', array('id' => $id), 'referencecnt');
        $DB->set_field('lcms_repository', 'referencecnt', $rep->referencecnt + 1, array('id' => $id));
    } else {
        $rep_con_db = new stdClass();
        $rep_con_db->lcmsid = $new_conid;
        $rep_con_db->userid = $contents->userid;
        if(empty($contents->groupid))$contents->groupid=0;
        $rep_con_db->groupid = $contents->groupid;
        $rep_con_db->referencecnt = 0;

        $new_rep_conid = $DB->insert_record('lcms_repository', $rep_con_db);
    }

    return $new_conid;
}

function lcms_update_db_inadmin($contents, $file_change = 0) {

    global $DB, $USER;
    $con_db = new stdClass();
    $con_db->id = $contents->con_id;
    //$con_db->course_cd = $USER->id;
    $con_db->teacher = $contents->prof_name;
    
    $con_db->share_yn = $contents->share_yn;
    $con_db->con_name = htmlspecialchars($contents->con_name, ENT_QUOTES);
    $con_db->con_des = $contents->con_des;
    $con_db->con_tag = $contents->con_tag;
    if ($contents->con_total_time) {
        $con_db->con_total_time = $contents->con_total_time;
    } else {
        $con_db->con_total_time = 0;
    }
    if ($contents->con_type == "embed" && $file_change == 1) {
        $con_db->embed_type = $contents->emb_type;
        $con_db->embed_code = $contents->emb_code;
    }
    if ($contents->con_type != "video") {
        $con_db->data_dir = $contents->data_dir;
    }
    $con_db->user_no = $contents->prof_userid;
    $con_db->update_dt = time();
    
    if(!empty($contents->author)){
        $con_db->author = $contents->author;
    } else {
        $con_db->author = "";
    }
    if($contents->cc_type){
    $con_db->cc_type = $contents->cc_type;
    } else {
      $con_db->cc_type = '';  
    }
    if($contents->cc_type != 3){
        if(!empty($contents->cc_mark)){
             $con_db->cc_mark = $contents->cc_mark;
        } else {
             $con_db->cc_mark = '';
        }
    } else {
        $con_db->cc_mark = $contents->cc_text;
    }
    $conid = $DB->update_record('lcms_contents', $con_db);
    if ($contents->con_type != 'ref') {
        $repository = $DB->get_record('lcms_repository', array('lcmsid' => $contents->con_id));
    } else {
        $repository = $DB->get_record('lcms_repository_reference', array('lcmsid' => $contents->con_id));
    }
    $rep_con_db = new stdClass();
    $rep_con_db->id = $repository->id;
    $rep_con_db->userid = $USER->id;
    $rep_con_db->groupid = $contents->groupid;
    if ($contents->con_type != 'ref') {
        $new_rep_conid = $DB->update_record('lcms_repository', $rep_con_db);
    } else {
        $new_rep_conid = $DB->update_record('lcms_repository_reference', $rep_con_db);
    }

    return $contents->con_id;
}

function lcms_temp_dir_fileupload_inadmin($extarr, $type, $con_seq, $storage) {

    global $CFG, $USER, $LCFG;

    $temp_dir = $CFG->dirroot . '/siteadmin/contents/contents_upload/server/php/files/' . $USER->id;
    $count = 0;

    if (is_dir($temp_dir)) {

        $dirs = dir($temp_dir);

        while (false !== ($entry = $dirs->read())) {
            if (($entry != '') && ($entry != '.') && ($entry != '..')) {
                if (is_dir($temp_dir . '/' . $entry)) {
                    //return '파일 폴더구조가 포함되어 있으면 안됩니다.';
                } else {
                    //임시 파일
                    $file = $temp_dir . '/' . $entry;
                    //파일정보가져오기
                    $path = pathinfo($file);
                    $ext = strtolower($path['extension']);
                    if ($type == 'word' || $type == 'ref') {
                        if (in_array($ext, $LCFG->notallowfile)) {
                            return get_string('error:notallowextfile', 'local_repository').'--';
                        } else {
                            $filename = $entry;
                            $count++;
                            lcms_register_files_inadmin($type, $con_seq, $storage, $filename, $count);
                        }
                    } else {
                        if (!in_array($ext, $extarr)) {
                            return get_string('error:notallowextfile', 'local_repository');
                        } else {
                            $filename = $entry;
                            $count++;
                            lcms_register_files_inadmin($type, $con_seq, $storage, $filename, $count);
                        }
                    }
                }
            }
        }
    }
    rmdir($temp_dir);
}

function lcms_register_files_inadmin($type, $con_seq, $storage, $filename, $count) {

    global $CFG, $USER, $DB;

    //임시 폴더를 열어 파일이 있는지 확인한다.
    $temp_dir = $CFG->dirroot . '/siteadmin/contents/contents_upload/server/php/files/' . $USER->id .'/';
    $path_dir = STORAGE . '/' . $storage . '/';

    if (!is_dir($path_dir)) {
        if (!mkdir($path_dir, 0777, true)) {
            return 'create folder fail';
        }
    }
    if (file_exists($temp_dir . $filename)) {
        $file = $temp_dir . $filename;

        //파일정보가져오기
        $path = pathinfo($file);
        $filesize = filesize($file);
        $ext = strtolower($path['extension']);
        $basename = $path['basename'];
        $filenm = $path['filename'];
        $filenms = explode('_', $filenm);
        $k = sizeof($filenms) - 1;
        if ($type == 'html' && $ext != 'zip') {
            $newfilename = $basename;
        } else {
            $newfilename = date('YmdHis') . '_' . $count . '.' . $ext;
        }
        $newfile = $path_dir . $newfilename;

        rename($file, $newfile);

        if ($type == 'html' && $ext == 'zip') {
            //압축파일 해제	
            $zipfile = new PclZip($newfile);
            $extract = $zipfile->extract(PCLZIP_OPT_PATH, $path_dir);
            @unlink($newfile);
            $basename = 'index.html';
            $newfilename = $basename;
        }

        //파일 정보에 등록
        $cfile_db = new stdClass();
        $cfile_db->con_seq = $con_seq;
        $cfile_db->user_no = $USER->id;
        $cfile_db->filepath = $storage;
        $cfile_db->filename = $newfilename;
        $cfile_db->filesize = $filesize;
        $cfile_db->fileoname = $basename;
        $cfile_db->duration = 0;
        $cfile_db->con_type = $type;

        $DB->insert_record('lcms_contents_file', $cfile_db);
    }
}

function lcms_temp_dir_filemode_inadmin($mode) {

    global $CFG, $USER;

    $temp_dir = $CFG->dirroot . '/siteadmin/contents/contents_upload/server/php/files/' . $USER->id;

    if (is_dir($temp_dir)) {

        $dirs = dir($temp_dir);
        $returnvalue = '';

        while (false !== ($entry = $dirs->read())) {
            if (($entry != '') && ($entry != '.') && ($entry != '..')) {
                if (is_dir($temp_dir . '/' . $entry)) {
                    //return '파일 폴더구조가 포함되어 있으면 안됩니다.';
                } else {
                    //임시 파일
                    $file = $temp_dir . '/' . $entry;
                    //파일 삭제하기
                    if ($mode == 'del')
                        @unlink($file);
                    if ($mode == 'li') {
                        $encodefile = rawurlencode($file);
                        $returnvalue .= '<li><button type="button" class="btn btn-delete delete" data-type="DELETE" data-url="' . $encodefile . '">del</button>' . $entry . '</li>';
                    }
                }
            }
        }
    }
    if ($mode == 'del')
        rmdir($temp_dir);
    if ($mode == 'li')
        return (!empty($returnvalue)) ? $returnvalue : "";
}

//폴더 이동함수
function copy_dir_inadmin($path, $dst) {


    if (!is_dir($dst))
        @mkdir($dst, 0777, true);
    $d = @opendir($path);
    while ($entry = @readdir($d)) {
        if ($entry != "." && $entry != "..") {
            if (is_dir($path . '/' . $entry)) {
                copy_dir_inadmin($path . '/' . $entry, $dst . '/' . $entry);
            } else {
                if (is_file($path . '/' . $entry)) {
                    @copy($path . '/' . $entry, $dst . '/' . $entry);
                    @chmod($dst . '/' . $entry, 0666);
                    @unlink($path . '/' . $entry);
                }
            }
        }
    }
    @closedir($d);
    @rmdir($d);
    return true;
}

function del_dir_inadmin($dir) {
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? del_dir_inadmin("$dir/$file") : unlink("$dir/$file");
    }
    rmdir($dir);
}

function scan_data_inadmin($entrys, $type, $path, $json = 1) {

    global $CFG;

    $entrys['spath'] = $path;
    $path = STORAGE . '/' . $path;

    if (is_dir($path)) {

        $scans = scandir($path);
        $entrys['path'] = $path;

        foreach ($scans as $scan) {
            if (($scan != '.') && ($scan != '..')) {
                $scan_name = iconv("euc-kr", "utf-8", $scan);
                $scan = rawurlencode($scan);
                if (is_dir($path . '/' . $scan_name)) {
                    $entrys['dir'][] = $scan_name;
                } else {
                    $entrys['file'][] = $scan_name;
                }
            }
        }
    }

    //header('Content-type: application/json');
    if ($json == 1)
        return json_encode($entrys);
    else
        return $entrys;
}

function repository_get_paging_bar_inadmin($url, $params, $total_pages, $current_page, $max_nav = 10) {
    $total_nav_pages = repository_get_total_pages_inadmin($total_pages, $max_nav);
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
    echo html_writer::start_tag('div', array('class' => 'table-footer-area'));
    echo html_writer::start_tag('div', array('class' => 'board-breadcrumbs'));
    if ($current_nav_page > 1) {
        // echo '<span class="board-nav-prev"><a class="prev" href="'.$url.(($current_nav_page - 2) * $max_nav + 1).'"><</a></span>';
    } else {
        // echo '<span class="board-nav-prev"><a class="prev" href="#"><</a></span>';
    }
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
    if ($current_nav_page < $total_nav_pages) {
        //echo '<a class="next_" href="' . $url . ($current_nav_page * $max_nav + 1) . '"></a>';
    } else {
        //echo '<a class="next_" href="#"></a>';
    }
    echo html_writer::end_tag('div');
    echo html_writer::end_tag('div');
}

//youtube 정보 가져오기
function youtubeinfo_inadmin($vid) {

    $obj = new StdClass();

    $xmlfile = "http://gdata.youtube.com/feeds/api/videos/" . $vid;
    $entry = simplexml_load_file($xmlfile);

    // get nodes in media: namespace for media information
    $media = $entry->children('http://search.yahoo.com/mrss/');

    // get video thumbnail
    $attrs = $media->group->thumbnail[0]->attributes();
    $obj->thumbnail = $attrs['url'];

    // get <yt:duration> node for video length
    $yt = $media->children('http://gdata.youtube.com/schemas/2007');
    $attrs = $yt->duration->attributes();
    $obj->duration = $attrs['seconds'];

    return $obj;
}


function mediainfo_inadmin($mid) {
    $url = '';
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $data = curl_exec($ch);
    curl_close($ch);
    return json_decode($data);
}
