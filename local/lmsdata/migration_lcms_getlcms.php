<?php

//define('CLI_SCRIPT', true);
//define('CACHE_DISABLE_ALL', true);
ini_set('memory_limit','-1');
require(__DIR__.'/../../config.php');
require_once($CFG->dirroot . "/local/lmsdata/migration_lib.php");
require_once($CFG->dirroot . "/course/lib.php");



define('OLDPATH','/contents/migration_content/lcmsdata');
define('NEWPATH','/contents');

// lcms DB 접속해서 값을 가져옴

$conn = oci_connect('lcmsuser', 'lcms0416', '210.98.46.40:1521/ust', 'AL32UTF8');

$sql=" select 
          lc.*, 
          lui.user_id,
          lm.cc_mark,lm.cc_type, lm.embed_code,embed_type, lm.con_total_time,
          ld.data_dir, ld.data_kind 
        FROM lcms_contents lc
        JOIN lcms_user_info lui ON lc.user_no = lui.user_no
        JOIN lcms_metadata lm ON lm.con_seq = lc.con_seq
        LEFT JOIN lcms_data ld ON lc.con_seq = ld.con_seq ";

$parse = oci_parse($conn, $sql);

oci_execute($parse);

$row_num = oci_fetch_all($parse, $row);

$count = 0;

$lcmssys_datas = array();
for($i=0; $i < $row_num; $i++){
    $data = new stdClass();
    $data->con_seq = $row['CON_SEQ'][$i];
    $data->con_name = $row['CON_NAME'][$i];
    $data->con_type = $row['CON_TYPE'][$i];
    $data->con_des = $row['CON_DES'][$i];
    $data->teacher = $row['TEACHER'][$i];
    $data->share_yn = $row['SHARE_YN'][$i];
    $data->user_no = $row['USER_NO'][$i];
    $data->update_dt = $row['UPDATE_DT'][$i];
    $data->area_cd = $row['AREA_CD'][$i];
    $data->major_cd = $row['MAJOR_CD'][$i];
    $data->con_hit = $row['CON_HIT'][$i];
    $data->reg_dt = $row['REG_DT'][$i];
    $data->logo_yn = $row['LOGO_YN'][$i];
    $data->user_id = $row['USER_ID'][$i];
    $data->cc_mark = $row['CC_MARK'][$i];
    $data->cc_type = $row['CC_TYPE'][$i];
    $data->embed_code = $row['EMBED_CODE'][$i];
    $data->embed_type = $row['EMBED_TYPE'][$i];
    $data->con_total_time = $row['CON_TOTAL_TIME'][$i];
    $data->data_dir = $row['DATA_DIR'][$i];
    $data->data_kind = $row['DATA_KIND'][$i];
    
    $lcmssys_datas[$data->con_seq] = $data;
}

oci_free_statement($parse);

oci_close($conn);

$totaldata_arr = array();
// 해당 폴더를 복사하여 새로운 경로에 만들어줌
foreach($lcmssys_datas as $lcmsdata) {
    $user = $DB->get_record('user', array('username'=>trim($lcmsdata->user_id)));
    switch ($lcmsdata->con_type) {
        case 'Embed' :
            break;
        case 'Flash' :
            $contentfolder = '/package/';
            $olddata_dir = OLDPATH.$contentfolder.$lcmsdata->data_dir;
            $data_dir = date('YmdHis').'r'.mt_rand(1, 99);
            $dirname = get_pakage_dirname($olddata_dir);
            $newdata_dir = NEWPATH.'/oldlcms/'.$user->id.'/'.$data_dir.'/'.$dirname;
            $content_dir = 'oldlcms/'.$user->id.'/'.$data_dir.'/'.$dirname;
            
            if(!is_dir (NEWPATH.'/oldlcms')) {
                @mkdir(NEWPATH.'/oldlcms');
            }
            if(!is_dir (NEWPATH.'/oldlcms/'.$user->id)) {
                @mkdir(NEWPATH.'/oldlcms/'.$user->id);
            }
            if(!is_dir (NEWPATH.'/oldlcms/'.$user->id.'/'.$data_dir)) {
                @mkdir(NEWPATH.'/oldlcms/'.$user->id.'/'.$data_dir);
            }
            if(!is_dir ($newdata_dir)) {
                @mkdir($newdata_dir);
            }
            fileCopy($olddata_dir, $newdata_dir);
            break;
        case 'Video' :
            $contentfolder = '/content/';
            $olddata_dir = OLDPATH.$contentfolder.$lcmsdata->data_dir;
            $data_dir = date('YmdHis').'r'.mt_rand(1, 99);
            $newdata_dir = NEWPATH.'/oldlcms/'.$user->id.'/'.$data_dir;
            $content_dir = 'oldlcms/'.$user->id.'/'.$data_dir;
            if(!is_dir (NEWPATH.'/oldlcms')) {
                @mkdir(NEWPATH.'/oldlcms');
            }
            if(!is_dir (NEWPATH.'/oldlcms/'.$user->id)) {
                @mkdir(NEWPATH.'/oldlcms/'.$user->id);
            }
            if(!is_dir ($newdata_dir)) {
                @mkdir($newdata_dir);
            }
            fileCopy($olddata_dir, $newdata_dir);
            break;
    }
    // 새로운 경로에 있는 thumbnail 이미지 파일명 변경
    if($lcmsdata->con_type == 'Video') {
        set_thumbnail_filename($newdata_dir);
    }
    $test = 0;
    // index.html 또는 mp4파일
    if($lcmsdata->con_type == 'Video' || $lcmsdata->con_type == 'Flash') {
        $indexdata = get_indexfile($newdata_dir);
        $indexdata->copyfilepath = $content_dir;
        
    } else {
        $indexdata = null;
    }
    
    if((($lcmsdata->con_type == 'Video' || $lcmsdata->con_type == 'Flash') && !empty($indexdata->copyfilename)) || ($lcmsdata->con_type == 'Embed')){
        $con_db = new stdClass();
        $con_db->area_cd = $lcmsdata->area_cd;
        $con_db->major_cd = $lcmsdata->major_cd;
        $con_db->course_cd = $user->id;

        $con_db->teacher = fullname($user);
        if (empty($lcmsdata->share_yn == 1)) {
            $con_db->share_yn = 'Y';
        } else {
            $con_db->share_yn = 'N';
        }

        $con_db->con_name = htmlspecialchars($lcmsdata->con_name, ENT_QUOTES);

        if($lcmsdata->con_type == 'Embed') {
            $con_db->con_type = 'embed';
        } else if($lcmsdata->con_type == 'Video') {
            $con_db->con_type = 'video';

        }else if($lcmsdata->con_type == 'Flash') {
            $con_db->con_type = 'html';

        }

        $con_db->con_des = $lcmsdata->con_des;

        $con_db->con_tag = null;
        if ($lcmsdata->con_total_time) {
            $totaltime_arr = explode(':', $lcmsdata->con_total_time);
            $totaltime_leng = sizeof($totaltime_arr) - 1;
            $total_second = 0;
            foreach($totaltime_arr as $time) {
                $time = (int)$time;
                $total_second += $time * pow(60, $totaltime_leng);
                $totaltime_leng--;
            }
            $con_db->con_total_time = $total_second;
        } else {
            $con_db->con_total_time = 0;
        }
        $con_db->author = "";
        $con_db->cc_type = $lcmsdata->cc_type;
        $con_db->cc_mark = $lcmsdata->cc_mark;
        if ($con_db->con_type == "embed") {
            $con_db->embed_type = $lcmsdata->embed_type;
            $con_db->embed_code = $lcmsdata->embed_code;
        }

        if($con_db->con_type == 'video' || $con_db->con_type == 'html') {
            $con_db->data_dir = $indexdata->copyfilepath;
        }else {
            $con_db->data_dir = null;
        }
        $con_db->user_no = $user->id;
        $con_db->con_hit = $lcmsdata->con_hit;
        $con_db->reg_dt = strtotime(trim($lcmsdata->reg_dt));
        $con_db->update_dt = strtotime(trim($lcmsdata->update_dt));;

        $new_conid = $DB->insert_record('lcms_contents', $con_db);


        if(($con_db->con_type == 'video' || $con_db->con_type == 'html')) {
            $cfile_db = new stdClass();
            $cfile_db->con_seq = $new_conid;
            $cfile_db->user_no = $user->id;
            $cfile_db->filepath = $indexdata->copyfilepath;
            $cfile_db->filename = $indexdata->copyfilename;
            $cfile_db->filesize = $indexdata->copyfilesize;
            $cfile_db->fileoname = $indexdata->copyfileoname;
            $cfile_db->duration = $con_db->con_total_time;
            $cfile_db->con_type = $con_db->con_type;
            $DB->insert_record('lcms_contents_file', $cfile_db);
        }
        $rep_con_db = new stdClass();
        $rep_con_db->lcmsid = $new_conid;
        $rep_con_db->userid = $user->id;
        $rep_con_db->groupid = 0;
        $rep_con_db->referencecnt = 0;

        $new_rep_conid = $DB->insert_record('lcms_repository', $rep_con_db);

        $totaldata = new stdClass();
        $totaldata->old_conid = $lcmsdata->con_seq;
        $totaldata->new_conid = $new_conid;
        $totaldata_arr[$lcmsdata->con_seq] = $totaldata;

    }
}

    $old_lcmses = $DB->get_records('templcms');
    $soltkey = 'lms@tech!*isnu';

    $lcmsmoduleid = $DB->get_field('modules', 'id', array('name'=>'lcms'));
    $totallcms_arr = array();
    foreach($old_lcmses as $old_lcms) {
        // 기존 lcms content id 복호화
        $encrypt_string = $old_lcms->viewurl;

        $result = new stdClass();
        $string = base64_decode(str_pad(strtr($encrypt_string, '-_', '+/'), strlen($data) % 4, '=', STR_PAD_RIGHT));
        for($i=0; $i<strlen($string); $i++) {
            $result->char = substr($string, $i, 1);
            $result->keychar = substr($soltkey, ($i % strlen($soltkey))-1, 1);
            $char = chr(ord($result->char)-ord($result->keychar));
            $result->data .= $char;
        }

        $con_id =  $result->data;

        $con_id = str_replace('/', '', $con_id);
        $con_len = strlen($con_id);
        $num = 0;
        for($i=0; $i<$con_len; $i++) {
            $char = substr($con_id, $i, 1);

        if($char=='0') $num++;
            else break;
        }

        $con_seq = trim(substr($con_id,$num,$con_len-$num));

        $newcontentid = $totaldata_arr[$con_seq]->new_conid;

        $repository_data = $DB->get_record('lcms_contents', array('id'=>$newcontentid));

        if(!empty($repository_data)) {
            //lcms activity 생성
            $newlcms_object = new stdClass();
            $newlcms_object->name = $old_lcms->name;
            $newlcms_object->introeditor = array(
                                    'text' => $old_lcms->intro,
                                    'format' => 1
                                );
            $newlcms_object->insert_file_yn = 0;
            $newlcms_object->con_type = $repository_data->con_type;
            $newlcms_object->data_dir = $repository_data->data_dir;
            $newlcms_object->video_file_id = 0; 
            $newlcms_object->emb_type = $repository_data->embed_type;      //youtube, vimeo
            $newlcms_object->emb_code = $repository_data->embed_code;      //url
            $newlcms_object->con_id = $repository_data->id;      //contentid
            $newlcms_object->groupid = 0;    
            $newlcms_object->timestart = $repository_data->update_dt;      
            $newlcms_object->timeend = $repository_data->update_dt + (60*60*24*7);      
            $newlcms_object->islock = 2;      //lock
            $newlcms_object->contents = $repository_data->id;      //content id
            $newlcms_object->title = $repository_data->con_name;      //content title
            $newlcms_object->type = $repository_data->con_type;      //content type
            $newlcms_object->progress = 1;      //progress
            $newlcms_object->visible = 1;      
            $newlcms_object->groupmode = 0;      
            $newlcms_object->groupingid = 0;      
            $newlcms_object->completionunlocked = 1;      
            $newlcms_object->completion = 1;      
            $newlcms_object->completionprogress = 100;      
            $newlcms_object->completionexpected = 0;      
            $newlcms_object->course = $old_lcms->course;      
            $newlcms_object->section = $old_lcms->section;      
            $newlcms_object->module = $lcmsmoduleid;      
            $newlcms_object->modulename = 'lcms';      

            $newlcms_info = create_module($newlcms_object);

            $totoallcms = new stdClass();
            $totoallcms->old_lcmsid = $old_lcms->id;
            $totoallcms->new_lcmsid = $newlcms_info->id;
            $totallcms_arr[$old_lcms->id] = $totoallcms;
        }
        
    }

$old_playtimes = $DB->get_records('templcms_playtime');

foreach($old_playtimes as $playtime) {
    $lcmsid = $totallcms_arr[$playtime->lcmsid]->new_lcmsid;
    if(!empty($lcmsid)) {
        $new_playtime = new stdClass();
        $new_playtime->userid = $playtime->userid;
        $new_playtime->lcmsid = $totallcms_arr[$playtime->lcmsid]->new_lcmsid;
        $new_playtime->positionto = $playtime->positionto;
        $new_playtime->positionfrom = $playtime->positionfrom;
        $new_playtime->positionpage = $playtime->positionpage;
        $new_playtime->positionevent = 2;
        $new_playtime->timecreated = $playtime->timereg;
        $new_playtime->timemodified = time();
        
        $new_playtime->device = 0;

        $DB->insert_record('lcms_playtime', $new_playtime);
    }
}

$old_tracks = $DB->get_records('templcms_track');

foreach($old_tracks as $track) {
    $lcmsid = $totallcms_arr[$track->lcms]->new_lcmsid;
    if(!empty($lcmsid)) {
        $new_track = new stdClass();
        $new_track->lcms = $lcmsid;
        $new_track->userid = $track->userid;
        $new_track->timeview = $track->timeview;
        $new_track->playtime = $track->playtime;
        $new_track->progress = $track->progress;
        $new_track->attempts = $track->attempts;
        $new_track->playpage = $track->playpage;
        $new_track->lasttime = $track->lasttime;
        $new_track->lastpage = $track->lastpage;
        $new_track->device = 0;

        $DB->insert_record('lcms_track', $new_track);
    }
}


