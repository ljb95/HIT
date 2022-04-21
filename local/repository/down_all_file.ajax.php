<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once 'config.php';
require_once 'lib.php';
require_once 'pclzip.lib.php';

$con_id = required_param('contentid',PARAM_INT);
$content = $DB->get_record('lcms_contents',array('id'=>$con_id));
$files = $DB->get_records('lcms_contents_file',array('con_seq'=>$con_id));


$zipfile = new PclZip(STORAGE.'/'.$content->data_dir.'/download_content.zip');
$zipfiles = array();
foreach($files as $file){
    $filepath = explode('/',$file->filepath);
    if($filepath[0]=='storage') $lcmsdata = '/'; else $lcmsdata = '/lcmsdata/';
    $f = STORAGE2 . $lcmsdata . $file->filepath . '/' . $file->filename;
    $zipfiles[] = $f;
}

$create = $zipfile->create($zipfiles,PCLZIP_OPT_REMOVE_ALL_PATH); 

$file = STORAGE.'/'.$content->data_dir.'/download_content.zip';
$filesize = filesize($file);


$mimetype = "application/octet-stream";
header("Pragma: public");
header('Content-Type: '.$mimetype);
header('Content-Disposition: attachment; filename="download_content.zip"');
header("Content-Description: File Transfer");
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

unlink($file);