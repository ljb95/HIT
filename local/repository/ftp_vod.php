<?php
require_once("../../config.php");

$dir = required_param('dir', PARAM_RAW);
$id = required_param('id', PARAM_RAW);

$filename = $_FILES['ftp_file']['name'];//tobe uploaded 
$tmpfile = $_FILES['ftp_file']['tmp_name'];  

$ftp_server= get_config('local_repository','ftp_server');  
$ftp_user_name= get_config('local_repository','ftp_user'); 
$ftp_user_pass= get_config('local_repository','ftp_pw');  
$conn_id = ftp_connect($ftp_server);  


$login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass); 
$buff = ftp_rawlist($conn_id, '.'); 

ftp_pasv($conn_id, true); 

if (!$conn_id) { die('Connection attempt failed!!!'); }
if (!$login_result) { die('Login failed!!!'); }
ftp_mkdir($conn_id,$dir);
 if ($status = ftp_put($conn_id, '/'.$dir.'/video.mp4', $tmpfile, FTP_BINARY)) {  
    echo "Successfully uploaded $filename\n"; 
 } else {  
    echo "There was a problem while uploading $filename\n";  
 }  
  ftp_close($conn_id);  
  
  redirect('detail.php?id='.$id);
