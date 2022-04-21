<?php
    require_once("../../config.php");
    
    
    $video_data = new stdClass();
    $video_data->fileoname = $_POST['o_file'];
    $video_data->filname = $_POST['t_file'];
    $video_data->filesize = $_POST['f_num'];
    $video_data->duration = $_POST['d_num'];
    