<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once 'config.php';

$context = context_system::instance();
require_login();
$PAGE->set_context($context);

$userid = required_param('userid', PARAM_INT);
$contentid = optional_param('contentid', 0, PARAM_INT);

if ($contentid == 0) {
    ?>
    <div class="filemanager-toolbar" style="border-bottom:1px solid #acacac;">
        <div class="fp-toolbar">
            <div class="fp-btn-mkdir" style="float:left;">
                <a role="button" title="" onclick="mkdir_fn()"><img src="<?php echo $OUTPUT->pix_url('a/create_folder'); ?>" alt="<?php echo get_string('addfile', 'repository'); ?>" /></a>
            </div>
        </div>
    </div>
    <div class="fp-pathbar">
        <span class="fp-path-folder first last odd">
            <a class="fp-path-folder-name" href="#" onclick="esacape()">내 파일</a>
        </span>
    </div>
    <div class="filemanager-container" >
        <div class="fm-content-wrapper">
            <div class="fp-content">
                <div class="fp-iconview">
                    <?php
                    $contents = $DB->get_records('lcms_contents', array('user_no' => $userid), 'id asc');
                    foreach ($contents as $content) {
                        ?>
                        <div class="fp-file fp-folder fp-hascontextmenu">
                             <a href="#">
                            <span href="#" onclick="get_files(<?php echo $content->id; ?>)">
                                <div style="position:relative;">                                                                                                                                                                                                               
                                    <div class="fp-thumbnail" style="width: 110px; height: 110px;"><img title="<?php echo $content->con_name; ?>" alt="<?php echo $content->con_name; ?>" src="<?php echo $OUTPUT->pix_url('f/folder-64'); ?>" style="max-width: 90px; max-height: 90px;"></div>
                                    <div class="fp-reficons1"></div>
                                    <div class="fp-reficons2"></div>
                                </div>
                            </span>
                                <div class="fp-filename-field">
                                    <div class="fp-filename" style="width: 112px;">
                                        <span href="#" onclick="get_files(<?php echo $content->id; ?>)"><?php echo $content->con_name; ?></span>
                                        <span style="color:red; font-size: 1em;" onclick="delete_con(<?php echo $content->id; ?>,1)">x</span>
                                        <br />
                                        <?php
                                            $repository = $DB->get_record('lcms_repository',array('lcmsid'=>$content->id)); 
                                            switch($repository->status){
                                                case 1: echo '검토중'; break; 
                                                case 2: echo '승인완료'; break; 
                                                case 3: echo '보류'; break;  
                                            }
                                        ?>
                                    </div>
                                </div>
                             </a>
                            <a class="fp-contextmenu" href="#"><img alt="▶" class="smallicon" title="▶" src="<?php echo $OUTPUT->pix_url('i/menu'); ?>"></a>
                        </div>

                        <?php
                    }
                    ?> </div></div></div></div>
    <?php
} else {
     $content = $DB->get_record('lcms_contents', array('id' => $contentid));
    ?>
    <div class="filemanager-toolbar" style="border-bottom:1px solid #acacac;">
        <div class="fp-toolbar">
            <div class="fp-btn-add" style="float:left;">
                <a role="button" title="" href="#" onclick="mkfile_fn()"><img src="<?php echo $OUTPUT->pix_url('a/add_file'); ?>" alt="<?php echo get_string('addfile', 'repository'); ?>" /></a>
            </div>
            <div class="fp-btn-download" style="float:left;">
                <a role="button" title="" href="#" onclick="location.href='down_all_file.ajax.php?contentid=<?php echo $contentid; ?>'"><img src="<?php echo $OUTPUT->pix_url('a/download_all'); ?>" alt="<?php echo get_string('addfile', 'repository'); ?>" /></a>
            </div>
        </div>
    </div>
    <div class="fp-pathbar">
        <span class="fp-path-folder first odd">
            <a class="fp-path-folder-name" href="#" onclick="esacape()">내 파일</a>
        </span>
        <span class="fp-path-folder last">
            <a class="fp-path-folder-name" href="#"><?php echo $content->con_name; ?></a>
                        <input type="hidden" id="hidden_con_id" value="<?php echo $contentid; ?>" />
        </span>
    </div>
    <div class="filemanager-container" >
        <div class="fm-content-wrapper">
            <div class="fp-content">
                <div class="fp-iconview">              
                    <?php
                    $files = $DB->get_records('lcms_contents_file', array('con_seq' => $contentid),'id asc');
                    foreach ($files as $file) {
                        $filename = $file->fileoname;
                        $filepath = explode('/', $file->filepath);
                        if ($filepath[0] == 'lms' || $filepath[0] == 'lcms')
                            $lcmsdata = '/lcmsdata/';
                        else
                            $lcmsdata = '/';
                        $mimetype = mime_content_type(STORAGE2 . $lcmsdata . $file->filepath . '/' . $file->filename);
                        $path = 'viewer/download.php?id=' . $contentid . '&fileid=' . $file->id;
                        $filename = format_text(s($filename), FORMAT_HTML, array('context' => $context));
                        $formats = str_replace('jpg','jpeg',substr($file->filename, strrpos($file->filename, '.') + 1));
                        
                        
                if ($formats == 'xls' || $formats == 'xlsx') {
                    $format = 'spreadsheet';
                } else if ($formats == 'ppt' || $formats == 'pptx') {
                    $format = 'powerpoint';
                } else if ($formats == 'txt') {
                    $format = 'text';
                } else if ($formats == 'doc' || $formats == 'docx') {
                    $format = 'document';
                } else if ($formats == 'mp4' || $formats == 'avi' || $formats == 'wmv') {
                    $format = 'video';
                } else if ($formats == 'jpg' || $formats == 'jpeg' || $formats == 'png' || $formats == 'gif') {
                    $format = 'image';
                } else {
                    $format = 'sourcecode';
                }
                        
                        ?>
                        <div class="fp-file fp-folder fp-hascontextmenu">
                            <a href="#">
                                <span onclick="location.href='<?php echo $path; ?>'">
                                <div style="position:relative;">                                                                                                                                                                                                               
                                    <div class="fp-thumbnail" style="width: 110px; height: 110px;"><img title="<?php echo $content->con_name; ?>" alt="<?php echo $content->con_name; ?>" src="<?php echo $OUTPUT->pix_url('f/' . $format . '-64'); ?>" style="max-width: 90px; max-height: 90px;"></div>
                                    <div class="fp-reficons1"></div>
                                    <div class="fp-reficons2"></div>
                                </div>
                                </span>
                                <div class="fp-filename-field">
                                    <div class="fp-filename" style="width: 112px;"><span onclick="location.href='<?php echo $path; ?>'"><?php echo $filename; ?></span><span style="color:red; font-size: 1em;" onclick="delete_con(<?php echo $file->id; ?>,2)">x</span></div>
                                </div>
                            </a>
                            <a class="fp-contextmenu" href="#"><img alt="▶" class="smallicon" title="▶" src="<?php echo $OUTPUT->pix_url('i/menu'); ?>"></a>
                        </div>
                        <?php
                    }
                    ?>
                </div></div>
        </div>
    </div>
    <?php
}
?>
