<?php
require_once '../../../config.php';
require_once $CFG->dirroot.'/local/repository/config.php';
require_once '../lib.php';

$id = required_param('id', PARAM_INT);
$fileid = required_param('fileid', PARAM_INT);

$PAGE->set_url('/local/repository/download.php', array('id'=>$id,'fileid'=>$fileid));

require_login();

$data = $DB->get_record('lcms_contents_file',array('id'=>$fileid));
$filepath = explode('/',$data->filepath);
if($filepath[0]=='storage'){
    $lcmsdata = '/';
} else {
    $lcmsdata = '/lcmsdata/';
}
$file = STORAGE2 . $lcmsdata . $data->filepath . '/' . $data->filename;
$filename = $data->fileoname;
$filesize = filesize($file);

//IE인가 HTTP_USER_AGENT로 확인
$ie= isset($_SERVER['HTTP_USER_AGENT']) && strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE') !== false;
 
//IE인경우 한글파일명이 깨지는 경우를 방지하기 위한 코드
if( $ie ){
  $filename = iconv('utf-8', 'euc-kr', $filename);
}
 

// 접근경로 확인 
//if (!eregi($_SERVER['HTTP_HOST'], $_SERVER['HTTP_REFERER'])) Error("외부에서는 다운로드 받으실수 없습니다."); 

//기본 헤더 적용
$mimetype = "application/octet-stream";
header('Content-Type: '.$mimetype);
header('Content-Disposition: attachment; filename="'.$filename.'"');
header('Content-Transfer-Encoding: binary');
header('Content-Length: '.sprintf('%d', $filesize));
header('Expires: 0');

// IE를 위한 헤더 적용
if( $ie ){
  header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
  header('Pragma: public');
} else {
  header('Pragma: no-cache');
}

if (is_file($file)) { 
$fp = fopen($file, "r"); 
if (!fpassthru($fp)) 
    fclose($fp); 
}
?>
