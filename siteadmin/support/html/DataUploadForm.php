<div id="load_form">
<?php
$id = $_REQUEST['id'];
$filedir = $_REQUEST['dir'];
$filename = $_REQUEST['name'];
?>
<!--파일덮어쓰기-->
<form method="post" name="upload_form" enctype="multipart/form-data">

    <input type="hidden" name="amode" value="update_file"/>
    <input type="hidden" name="con_seq" value="<?php echo $id;?>"/>

    <table border="1" class="write_form">
        <caption>파일 덮어쓰기</caption>
        <tr>
            <th>경로</th>
            <td><input type="text" name="path" size="50" value="<?php echo $filedir;?>" readonly/></td>
        </tr>
        <tr>
            <th>파일명</th>
            <td><input type="text" name="extname" size="50" value="<?php echo $filename;?>" readonly/></td>
        </tr>
        <tr>
            <th><?php echo get_string('contents_upload', 'local_lmsdata'); ?></th>
            <td>
                <input type="file" name="file" id="file" size="50" required />
            </td>
        </tr>
    </table>
</form>
</div>

