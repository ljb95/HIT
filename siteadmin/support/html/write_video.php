<?php         
    require(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
?>
<!-- 비디오폼 시작 -->
<input type="hidden" name="con_type" value="video"/>
<?php 
        $id = $_GET['id']; 
        $path = 'lms/'.$_GET['userid'].'u'.date('YmdAhis').'r'.  mt_rand(1, 99);
        $transcording_src = $CFG->$CFG->vodserver
                . '?id='.$id.'&path='.$path.''
                . '&userid='.$_GET['userid'].''
                . '&returnpath='.$_GET['wwwroot'].'/local/repository/return_file_data.php';
?>
        <td>
        <iframe src="<?php echo $transcording_src; ?>" width="100%" id="video_frame" height="306"></iframe>
        <input type="hidden" name="data_dir" value="<?php echo $path; ?>">
        <input type="hidden" id="video_file_id" name="video_file_id">
        <input type="text" title="file" name="filename" id="video_file_name" style="display: none;" readonly="true">
        </td>
        <tr>
            <td>자막파일 <input type="file" title="file" name="script" /></td>
        </tr>
<script>
    function video_popup(){
        var type1 = $('select[name=area_cd] option:selected').val();
            var time = <?php echo date('YmdHis'); ?>; 
            window.open('http://165.132.16.42/upload_pop.php?path=1/1/'+time+'<?php echo $video_param; ?>','video_upload','width=419,height=240');
    }
</script>
<!-- 비디오폼 끝 -->  