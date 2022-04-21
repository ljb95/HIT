<?php

require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once $CFG->dirroot . '/local/coursenote/locallib.php';
require_once $CFG->dirroot . '/lib/filestorage/file_storage.php';

$forderid = required_param('forderid', PARAM_INT);
$count = required_param('filecount', PARAM_INT);

$forder = $DB->get_record('coursenote_forder', array('id' => $forderid));
for($i =0;$i<$count;$i++){
    
    $context = context_course::instance($forder->course);

    $filename = $_FILES['coursenotefile'.$i]['name'];
    $filetype = $_FILES['coursenotefile'.$i]['type'];
    $filesize = $_FILES['coursenotefile'.$i]['size'];

    if($filesize > 52428800){
        header('HTTP/1.1 500 Internal Server Booboo');
        header('Content-Type: application/json; charset=UTF-8');
        echo '용량은 50MB를 초과할 수 없습니다';
        die(); 
    }

    $courseinfo = $DB->get_record('lmsdata_class', array('course' => $forder->course));

    $dir = $courseinfo->subject_id;
    $dir = str_replace('-', '', $dir);

    $lastchar = substr($dir, -1);

    if ($CFG->webdiskuse == 'Y') {
        $copydir = '/webdisk/cdata/' . $lastchar . '/C_' . $dir . '/';
    }
    $uploaddir = $CFG->dirroot.'/local/coursenote/files';

    if (!is_dir($uploaddir)) {
        mkdir($uploaddir, 0755);
    }

    if (is_dir($uploaddir)) {

        $uploadfile = $uploaddir . basename($filename);

        $newfile = new stdClass();
        $newfile->forderid = $forderid;
        $newfile->filename = $filename;
        $newfile->userid = $USER->id;
        $newfile->filesize = $filesize;
        $newfile->timemodified = time();
        $file = $DB->insert_record('coursenote_file', $newfile);

        if (move_uploaded_file($_FILES['coursenotefile'.$i]['tmp_name'], $uploadfile)) {

            if ($CFG->webdiskuse == 'Y') {
                $copyfile = '/webdisk/cdata/' . $lastchar . '/C_' . $dir . '/'.$filename;
                copy($uploadfile,$copyfile);
            }


            $fs = get_file_storage();
            $fileinfo = array(
                'contextid' => $context->id, // ID of context
                'component' => 'coursenote', // usually = table name
                'filearea' => 'coursenote', // usually = table name
                'itemid' => $file,
                'filepath' => '/', // any path beginning and ending in /
                'filename' => $filename, // any filename
                'userid' => $UESR->id
            );
            $moodlefile = $fs->create_file_from_pathname($fileinfo, $uploadfile);


            if ($CFG->webdiskuse == 'Y') {
                unlink($uploadfile);
            }

            $htmlwriter   = new html_writer();
            $addbtnopt    = array('class'=>'btn add-note','type'=>'button','value'=>"추가하기");     // 추가버튼 옵션
            $downbtnopt   = array('class'=>'btn down',     'type'=>'button','value'=>"다운로드"); // 다운로드 버튼 옵션
            $delbtnopt    = array('class'=>'btn del' , 'onclick'=>'note_del(this)',      'type'=>'button','value'=>"삭제");     // 삭제 버튼 옵션

            $downbtnopt['fileid'] = $delbtnopt['fileid'] = $file;
            $downbtnopt['onclick'] = 'location.href="'.$CFG->wwwroot.'/local/coursenote/download.php?fileid='.$file.'"';
            $downbtn = $htmlwriter->empty_tag('input',$downbtnopt);  // 다운로드 버튼
            $delbtn = ($lmsdata_user->usergroup != 'rs')?$htmlwriter->empty_tag('input',$delbtnopt):''; // 삭제 버튼
            $btn_group = $htmlwriter->div($downbtn.$delbtn,'btn-group'); // 버튼그룹
            echo $htmlwriter->tag('li',$filename.$btn_group,array('class'=>'file-li'.$file));

        } else {
            echo 'Nope';
            return false;
        }
    } else {
        echo 'is not forder';
        return false;
    }
}

