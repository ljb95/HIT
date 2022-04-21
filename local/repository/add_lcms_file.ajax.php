<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once 'config.php';
require_once 'lib.php';


$context = context_system::instance();
require_login();
$PAGE->set_context($context);
?>
<script src="contents_upload/js/jquery.fileupload.js"></script>
<script src="contents_upload/js/bootstrap.min.js"></script>

<script>
    $(function () {
        'use strict';
        // Change this to the location of your server-side upload handler:
        var url = window.location.hostname === 'blueimp.github.io' ?
                '//jquery-file-upload.appspot.com/' : 'contents_upload/server/php/';

        $('#fileupload').fileupload({
            url: url,
            dataType: 'json',
            done: function (e, data) {
                $.each(data.result.files, function (index, file) {
                    if (file.error) {
                        $('<li/>').html(file.name + '[Error: ' + file.error + ']').appendTo('#files');
                    } else {
                        $('<li/>').html('<button type="button" class="btn btn-delete delete" data-type="DELETE" data-url="' + file.deleteUrl + '">del</button>' + file.name).appendTo('#files');
                    }
                });
            },
            progress: function (e, data) {

            },
            progressall: function (e, data) {
                var progress = parseInt(data.loaded / data.total * 100, 10);
                $('#progress .progress-bar').css(
                        'width',
                        progress + '%'
                        );
            }
        }).prop('disabled', !$.support.fileInput)
                .parent().addClass($.support.fileInput ? undefined : 'disabled');

    });

    $(".files button.delete").click(function () {

        var filedir = $(this).attr('data-url');
        var papa = $(this).parent();

        $.ajax({
            url: '<?php echo $CFG->wwwroot; ?>/local/repository/delete_contents.php',
            type: 'POST',
            async: true,
            data: {
                mode: 'filedel',
                filedir: filedir
            },
            success: function (data, textStatus, jqXHR) {
                papa.hide();
            },
            error: function (jqXHR, textStatus, errorThrown) {

            }
        });

    });

    $(".cfiles button.delete").click(function () {

        var fileid = $(this).attr('data-url');
        var papa = $(this).parent();

        $.ajax({
            url: '<?php echo $CFG->wwwroot; ?>/local/repository/delete_contents.php',
            type: 'POST',
            async: true,
            data: {
                mode: 'cfiledel',
                fileid: fileid
            },
            success: function (data, textStatus, jqXHR) {
                papa.hide();
            },
            error: function (jqXHR, textStatus, errorThrown) {

            }
        });
    });
</script>
<div id="lcms_upload_component">
    <span class="btn fileinput-button"><span><?php echo get_string('fileadd', 'local_repository'); ?></span>
        <input id="fileupload" type="file" name="files[]" multiple></span>
    <div id="progress" class="progress">
        <div class="progress-bar progress-bar-success">
        </div>
    </div>
    <ul id="files" class="files"><?php echo lcms_temp_dir_filemode('li'); ?></ul>
</div>
