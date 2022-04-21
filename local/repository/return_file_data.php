<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');


$path = optional_param('path', '', PARAM_RAW);
$t_file = optional_param('t_file', '', PARAM_RAW);
$o_file = optional_param('o_file', '', PARAM_RAW);
$user_id = optional_param('user_id', 0, PARAM_INT);
$d_num = optional_param('d_num', 0, PARAM_INT);
$f_num = optional_param('f_num', '', PARAM_RAW);

if ($path && $t_file && $user_id) {
    $data = new stdClass();
    $data->con_seq = 0;
    $data->filepath = $path;
    $data->fileoname = $o_file;

    $name_ary = explode('.', $o_file);
    $ext = $name_ary[count($name_ary) - 1];
    $mp4file = preg_replace('/\.' . $ext . '$/', '.mp4', $o_file);
    $mp4file = str_replace(' ', '',$mp4file); 
    $mp4file = preg_replace ("/[ #\&\+\-%@=\/\\\:;,'\"\^`~\_|\!\?\*$#<>()\[\]\{\}]/i", "", $mp4file); 
    $data->filename = $t_file;
    $data->filesize = '0';
    $data->duration = 0;
    $data->con_type = 'video';
    $data->user_no = $user_id;
    $up = $DB->insert_record('lcms_contents_file', $data);
}
if ($up) {
    ?>
    <script type="text/javascript">
        parent.document.getElementById('video_frame').style.display = 'none';
        parent.document.getElementById('video_file_name').style.display = 'block';
        parent.document.getElementById('video_file_name').value = '<?php echo $mp4file; ?>';
        parent.document.getElementById('video_file_id').value = '<?php echo $up; ?>';
    </script>  
<?php } else {
    echo 'Error 500';
    echo $t_file . ' is used';
} ?>