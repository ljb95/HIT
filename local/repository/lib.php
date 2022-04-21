<?php

define('LCMS_CAPTION_KO_PATTERN', '/[_]{1}ko[.]{0,1}srt\z$/');
define('LCMS_CAPTION_EN_PATTERN', '/[_]{1}en[.]{0,1}srt\z$/');

/**
 * 
 * @global type $DB
 * @global type $USER
 * @param type $contents
 */
function upload_lcms_contents($contents, $id) {
    global $LCFG, $DB, $USER;
    switch ($contents->con_type) {
        case 'video':
            $newconid = lcms_insert_db($contents, $id);
            $DB->set_field_select('lcms_contents_file', 'con_seq', $newconid, " id = :fileid ", array('fileid' => $contents->video_file_id));
            $extarr = $LCFG->allowextvideo;
            $n = 0;
            break;
        case 'html':
            $contents->data_dir = 'lms/html/' . $USER->id . '/' . date('YmdHis');
            $extarr = $LCFG->allowexthtml;
            $n = 1;
            break;
        case 'word':
            $contents->data_dir = 'lms/files/' . $USER->id . '/' . date('YmdHis');
            $extarr = $LCFG->allowextword;
            $n = 0;
            break;
        case 'ref':
            $contents->data_dir = 'lms/ref/' . $USER->id . '/' . date('YmdHis');
            $extarr = $LCFG->allowextref;
            $n = 0;
            break;
        case 'embed':
            $newconid = lcms_insert_db($contents, $id);
            break;
    }
    if ($contents->con_type != "embed" && $contents->con_type != "video") {
        $filecount_cnt = lcms_temp_dir_allow_filecount($extarr, $n, $contents->con_type);

        if ($filecount_cnt == 1) {
            $newconid = lcms_insert_db($contents, $id);
            $storage = ($contents->con_type == 'video') ? 'contents/' . $contents->data_dir : $contents->data_dir;
            lcms_temp_dir_fileupload($extarr, $contents->con_type, $newconid, $storage);
        } else {
            echo $filecount_cnt;
            exit;
        }
    }
    if ($newconid) {
        return $newconid;
    }
}

function update_lcms_contents($contents) {
    global $LCFG, $DB, $USER;

    switch ($contents->con_type) {
        case 'video':
            $data_dir = 'lms/files/' . $USER->id . '/' . date('YmdHis');
            lcms_update_db($contents, $contents->stay_file);
            if ($contents->stay_file == 1) {
                $DB->delete_records('lcms_contents_file', array('con_seq' => $contents->con_id));
                $DB->set_field_select('lcms_contents_file', 'con_seq', $contents->con_id, " id = :fileid ", array('fileid' => $contents->video_file_id));
            }
            $extarr = $LCFG->allowextvideo;
            $n = 0;
            break;
        case 'html':
            $data_dir = 'lms/html/' . $USER->id . '/' . date('YmdHis');
            $extarr = $LCFG->allowexthtml;
            $n = 1;
            break;
        case 'word':
            $data_dir = 'lms/files/' . $USER->id . '/' . date('YmdHis');
            $extarr = $LCFG->allowextword;
            $n = 0;
            break;
        case 'ref':
            $data_dir = 'lms/ref/' . $USER->id . '/' . date('YmdHis');
            $extarr = $LCFG->allowextref;
            $n = 0;
            break;
        case 'embed':
            lcms_update_db($contents, $contents->stay_file);
            break;
    }

    if ($contents->con_type != "embed" && $contents->con_type != "video") {
        if ($contents->stay_file == 1) {
            $filecount_cnt = lcms_temp_dir_allow_filecount($extarr, $n, $contents->con_type);
        } else if ($contents->stay_file == 0) {
            $filecount_cnt = 1;
        }
        if ($filecount_cnt == 1) {
            if ($contents->stay_file == 1) {
                $filepath = explode('/', $contents->data_dir);
                if ($filepath[0] == 'storage' || !$contents->data_dir) {
                    $contents->data_dir = $data_dir;
                }
                $conid = lcms_update_db($contents);

                if ($filepath[0] != 'storage' && $contents->con_type == 'html') {
                    $path_dir = STORAGE . '/' . $contents->data_dir;
                    $copy_dir = STORAGE . '/trash/' . $contents->data_dir;
                    delete_tree($path_dir);
                    $DB->delete_records('lcms_contents_file', array('con_seq' => $conid));
                }
                $storage = ($contents->con_type == 'video') ? 'contents/' . $contents->data_dir : $contents->data_dir;
                lcms_temp_dir_fileupload($extarr, $contents->con_type, $conid, $storage);
            } else {
                $conid = lcms_update_db($contents);
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
function lcms_temp_dir_allow_filecount($extarr, $n = 0, $type) {

    global $CFG, $USER, $LCFG;

    $temp_dir = $CFG->dirroot . '/local/repository/contents_upload/server/php/files/' . $USER->id;
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

                    if ($type == 'word' || $type == 'ref') {
                        if (in_array(strtolower($path['extension']), $LCFG->notallowfile)) {
                            return get_string('error:notallowextfile', 'local_repository');
                        } else {
                            $count++;
                        }
                    } else if ($type != 'video') {
                        if (!in_array(strtolower($path['extension']), $extarr)) {
                            return get_string('error:notallowextfile', 'local_repository');
                        } else {
                            $count++;
                        }
                    } else {
                        if (!in_array(strtolower($path['extension']), $extarr)) {
                            return get_string('error:notallowextfile', 'local_repository');
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

function lcms_insert_db($contents, $id = 0) {
    global $DB, $USER;
    $con_db = new stdClass();
    $con_db->area_cd = 1;
    $con_db->major_cd = 1;
    $con_db->course_cd = $USER->id;

    $con_db->teacher = fullname($USER);
    if (empty($contents->share_yn)) {
        $con_db->share_yn = 'N';
    } else {
        $con_db->share_yn = $contents->share_yn;
    }

    $con_db->con_name = htmlspecialchars($contents->con_name, ENT_QUOTES);
    $con_db->con_type = $contents->con_type;

    $con_db->con_des = $contents->con_des['text'];

    $con_db->con_tag = $contents->con_tag;
    if ($contents->con_total_time) {
        $con_db->con_total_time = $contents->con_total_time;
    } else {
        $con_db->con_total_time = 0;
    }
    $con_db->author = "";
    $con_db->cc_type = 1;
    $con_db->cc_mark = "";
    $con_db->embed_type = "";
    $con_db->embed_code = "";
    if ($contents->con_type == "embed") {
        $con_db->embed_type = $contents->emb_type;
        $con_db->embed_code = $contents->emb_code;
    }

    $con_db->data_dir = $contents->data_dir;
    $con_db->user_no = $USER->id;
    $con_db->con_hit = 0;
    $con_db->reg_dt = time();
    $con_db->update_dt = time();

    $new_conid = $DB->insert_record('lcms_contents', $con_db);


    if ($id != 0) {
        $ref_con_db = new stdClass();
        $ref_con_db->lcmsid = $new_conid;
        $ref_con_db->repository = $id;
        $ref_con_db->userid = $USER->id;
        $ref_con_db->groupid = $contents->groupid;
        $new_ref_conid = $DB->insert_record('lcms_repository_reference', $ref_con_db);
        $rep = $DB->get_record('lcms_repository', array('id' => $id), 'referencecnt');
        $DB->set_field('lcms_repository', 'referencecnt', $rep->referencecnt + 1, array('id' => $id));
    } else {
        $rep_con_db = new stdClass();
        $rep_con_db->lcmsid = $new_conid;
        $rep_con_db->userid = $USER->id;
        $rep_con_db->groupid = $contents->groupid;
        $rep_con_db->referencecnt = 0;

        $new_rep_conid = $DB->insert_record('lcms_repository', $rep_con_db);
    }

    return $new_conid;
}

function lcms_update_db($contents, $file_change = 0) {

    global $DB, $USER;
    $con_db = new stdClass();
    $con_db->id = $contents->con_id;
    //$con_db->course_cd = $USER->id;
    $con_db->teacher = (!$contents->teacher) ? fullname($USER) : $contents->teacher;

    $con_db->con_name = htmlspecialchars($contents->con_name, ENT_QUOTES);
    $con_db->con_des = $contents->con_des['text'];
    $con_db->con_tag = $contents->con_tag;
    if ($contents->con_total_time) {
        $con_db->con_total_time = $contents->con_total_time;
    } else {
        $con_db->con_total_time = 0;
    }
    if (isset($contents->share_yn)) {
        $con_db->share_yn = $contents->share_yn;
    }
    if ($contents->con_type == "embed" && $file_change == 1) {
        $con_db->embed_type = $contents->emb_type;
        $con_db->embed_code = $contents->emb_code;
    }
    if ($contents->con_type != "video") {
        $con_db->data_dir = $contents->data_dir;
    }
    //$con_db->user_no = $USER->id;
    $con_db->update_dt = time();

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

function lcms_temp_dir_fileupload($extarr, $type, $con_seq, $storage) {

    global $CFG, $USER, $LCFG;

    $temp_dir = $CFG->dirroot . '/local/repository/contents_upload/server/php/files/' . $USER->id;
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
                            return get_string('error:notallowextfile', 'local_repository');
                        } else {
                            $filename = $entry;
                            $count++;
                            lcms_register_files($type, $con_seq, $storage, $filename, $count);
                        }
                    } else if ($type == 'video') {
                        if (!in_array(strtolower($path['extension']), $extarr)) {
                            return get_string('error:notallowextfile', 'local_repository');
                        } else {
                            $count++;
                        }
                    } else {
                        if (!in_array(strtolower($path['extension']), $extarr)) {
                            return get_string('error:notallowextfile', 'local_repository');
                        } else {
                            $filename = $entry;
                            $count++;
                            lcms_register_files($type, $con_seq, $storage, $filename, $count);
                        }
                    }
                }
            }
        }
    }
    del_dir($temp_dir);
}

function lcms_register_files($type, $con_seq, $storage, $filename, $count) {

    global $CFG, $USER, $DB;

    //임시 폴더를 열어 파일이 있는지 확인한다.
    $temp_dir = $CFG->dirroot . '/local/repository/contents_upload/server/php/files/' . $USER->id . '/';
    if ($type != 'video') {
        $path_dir = STORAGE . '/' . $storage . '/';
    } else {
        $path_dir = STORAGE2 . '/' . $storage . '/';
    }
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

function lcms_temp_dir_filemode($mode) {

    global $CFG, $USER;

    $temp_dir = $CFG->dirroot . '/local/repository/contents_upload/server/php/files/' . $USER->id;

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
function copy_dir($path, $dst) {


    if (!is_dir($dst))
        @mkdir($dst, 0777, true);
    $d = @opendir($path);
    while ($entry = @readdir($d)) {
        if ($entry != "." && $entry != "..") {
            if (is_dir($path . '/' . $entry)) {
                copy_dir($path . '/' . $entry, $dst . '/' . $entry);
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

function del_dir($dir) {
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? del_dir("$dir/$file") : unlink("$dir/$file");
    }
    rmdir($dir);
}

function scan_data($entrys, $type, $path, $json = 1) {

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

function repository_get_total_pages($rows, $limit = 10) {
    if ($rows == 0) {
        return 1;
    }

    $total_pages = (int) ($rows / $limit);

    if (($rows % $limit) > 0) {
        $total_pages += 1;
    }

    return $total_pages;
}

function repository_get_paging_bar($url, $params, $total_pages, $current_page, $max_nav = 10) {
    $total_nav_pages = repository_get_total_pages($total_pages, $max_nav);
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
function youtubeinfo($vid) {

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

//비메오 정보 가져오기
function vimeoinfo($vid) {

    $obj = new StdClass();

    $xmlfile = "http://vimeo.com/api/v2/video/" . $vid . ".xml";
    $entry = simplexml_load_file($xmlfile);

    $obj->thumbnail = $entry->video->thumbnail_medium;
    $obj->duration = $entry->video->duration;

    return $obj;
}

function mediainfo($mid) {
    $url = '';
    $ch = curl_init($url);

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $data = curl_exec($ch);
    curl_close($ch);
    return json_decode($data);
}

function local_repository_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
    global $CFG, $DB;

    $fileareas = array('attachment', 'contents', 'subtitle');
    if (!in_array($filearea, $fileareas)) {
        return false;
    }


    $fs = get_file_storage();
    $relativepath = implode('/', $args);

    $fullpath = "/$context->id/local_repository/$filearea/$relativepath";
    if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
        return false;
    }


    // finally send the file
    send_stored_file($file, 0, 0, true); // download MUST be forced - security!
}

//자막파일 정보 가져오기
function get_subtitle_list($contextid, $dataid) {

    global $CFG, $OUTPUT;

    require_once($CFG->dirroot . "/lib/filelib.php");

    $fs = get_file_storage();
    $captions = $fs->get_area_files($contextid, 'local_repository', 'subtitle', $dataid, 'timemodified', false);

    $subtitles = new stdClass();
    $pattern1 = "/[_]{1}en[.]{0,1}srt\z$/";
    $pattern2 = "/[_]{1}ko[.]{0,1}srt\z$/";

    if (count($captions) > 0) {
        $captionhtml = '<ul>';
        foreach ($captions as $caption) {
            $captionname = $caption->get_filename();
            $captiontype = $caption->get_mimetype();
            $captionicon = '<img src="' . $OUTPUT->pix_url(file_mimetype_icon($captiontype)) . '" class="icon" alt="' . $captiontype . '" />';
            $captionpath = file_encode_url($CFG->wwwroot . '/pluginfile.php', '/' . $contextid . '/local_repository/subtitle/' . $dataid . '/' . $captionname);
            $captionhtml .= '<li>';
            $captionhtml .= '<a href="' . $captionpath . '">' . $captionicon . '</a> ';
            $captionhtml .= format_text('<a href="$captionpath">' . s($captionname) . '</a>', FORMAT_HTML, array('context' => $context));
            $captionhtml .= '<input type="button" value="'. get_string('captionpreview','local_repository').'" id="captionpopup" onclick="caption_popup(\'' . $dataid . '\', \'' . $captionname . '\')">';
            $captionhtml .= '</li>';

            if (preg_match($pattern1, $captionname)) {
                $caption_en = file_encode_url($CFG->wwwroot . '/pluginfile.php', '/' . $contextid . '/local_repository/subtitle/' . $dataid . '/' . $captionname);
            }
            if (preg_match($pattern2, $captionname)) {
                $caption_ko = file_encode_url($CFG->wwwroot . '/pluginfile.php', '/' . $contextid . '/local_repository/subtitle/' . $dataid . '/' . $captionname);
            }
        }

        $captionhtml .= '</ul>';
    }

    $subtitles->list = (isset($captionhtml))?$captionhtml:'';
    $subtitles->path_ko = (isset($caption_ko))?$caption_ko:'';
    $subtitles->path_en = (isset($caption_en))?$caption_en:'';

    return $subtitles;
}

function get_subtitle_text($context, $id, $name) {

    global $CFG;

    require_once($CFG->dirroot . "/lib/filelib.php");

    $srtpath = file_encode_url($CFG->wwwroot . '/pluginfile.php', '/' . $context->id . '/local_repository/subtitle/' . $id . '/' . $name);

    if ($srtpath) {
        define('SRT_STATE_SUBNUMBER', 0);
        define('SRT_STATE_TIME', 1);
        define('SRT_STATE_TEXT', 2);
        define('SRT_STATE_BLANK', 3);

        $lines = file($srtpath);
        $total = count($lines);
        $numb = 0;

        $subs = array();
        $state = SRT_STATE_SUBNUMBER;
        $subNum = 0;
        $subText = '';
        $subTime = '';

        foreach ($lines as $line) {

            $numb++;

            switch ($state) {
                case SRT_STATE_SUBNUMBER:
                    preg_match('/[0-9]+/', $line, $numarr);
                    $subNum = trim($numarr[0]);
                    $state = SRT_STATE_TIME;
                    break;

                case SRT_STATE_TIME:
                    $subTime = trim($line);
                    $state = SRT_STATE_TEXT;
                    break;

                case SRT_STATE_TEXT:
                    if (trim($line) == '' || $numb == $total) {
                        if ($numb == $total)
                            $subText .= $line;
                        $sub = new stdClass;
                        $sub->number = $subNum;
                        if ($subNum % 10 == 1)
                            list($startTime, $stopTime) = explode(' --> ', $subTime);
                        if ($subNum % 10 == 0) {
                            $sub->text = $subText;
                            $sub->starttime = $startTime;
                            $sub->stoptime = $stopTime;
                            $subText = '';
                            $subs[] = $sub;
                        } else {
                            $subText .= $line;
                        }
                        $state = SRT_STATE_SUBNUMBER;
                    } else {
                        $subText .= $line;
                    }
                    break;
            }
        }
        if ($subNum % 10 != 0) {
            $sub->text = $subText;
            $sub->starttime = $startTime;
            $sub->stoptime = $stopTime;
            $subs[] = $sub;
        }
    }

    return $subs;
}

/**
 * 
 * @global object,object $DB,$USER
 * @param int $conid <p>
 * Give My Content Id!!!!!!
 * </p>
 * @param string $event <p>
 * Oh My~ Event~
 * </p>
 * @param int(1) $type    ( 1 : inserted , 2: viewed , 3: updated , 4 : Deleted ) or  Undefine
 * @param int $userid Moodle User id
 * @return mixed History ID or false
 * Add Lcms History
 * author Lim
 */
function insert_lcms_history($conid, $event, $type, $userid = 0) {
    global $DB, $USER;

        if (!$userid){
            $userid = $USER->id;
        }
    
    $viewdata = ($type == 2) ? $DB->get_record('lcms_history', array('event' => $event, 'userid' => $userid, 'contentid' => $conid, 'type' => 'viewed')) : false;

    $data = new stdClass();
    $data->contentid = $conid;
    $data->event = $event;
    $data->userid = $userid;
    $data->timecreated = time();
    switch ($type) {
        case '1':$data->type = 'inserted';
            break;
        case '2':$data->type = 'viewed';
            break;
        case '3':$data->type = 'updated';
            break;
        case '4':$data->type = 'deleted';
            break;
        default : $data->type = 'undefined';
            break;
    }
    if (!$viewdata) {
        $new_history = $DB->insert_record('lcms_history', $data);
        return false;
    }
    return $new_history;
}

function delete_tree($dir) {
    $files = array_diff(scandir($dir), array('.', '..'));
    foreach ($files as $file) {
        (is_dir("$dir/$file")) ? delete_tree("$dir/$file") : unlink("$dir/$file");
    }
    return rmdir($dir);
}

define('IV_SIZE', mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_ECB));
/**
 * 
 * @param type $plainText
 * @param type $key
 * @return type
 */
function jinoapp_encrypt($plainText, $key = 'jinotechappp2122'){
    $key = str_pad($key, 16, chr(0));   // 자릿수 채우기
 
    $iv = mcrypt_create_iv(IV_SIZE, MCRYPT_DEV_URANDOM);
    $data = base64_encode(mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $plainText, MCRYPT_MODE_ECB, $iv));
    return preg_replace('|\+|', 'jplusj',$data);
}

/**
 * 
 * @param type $plainText
 * @param type $key
 * @return type
 */
function jinoapp_decrypt($plainText, $key = 'jinotechappp2122'){
    $plainText = preg_replace('|jplusj|', '+',$plainText);
    $plainText = base64_decode($plainText);
    
    $key = str_pad($key, 16, chr(0));   // 자릿수 채우기
 
    $iv = mcrypt_create_iv(IV_SIZE, MCRYPT_DEV_URANDOM);
 
    $plainText = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $plainText, MCRYPT_MODE_ECB, $iv);
     
    return rtrim($plainText, "\0..\16");
}
