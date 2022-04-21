<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once 'config.php';

$context = context_system::instance();
require_login();
$PAGE->set_context($context);

$PAGE->set_url('/local/repository/cdms.php');
$PAGE->set_pagelayout('standard');

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->jquery_plugin('migrate');

$strplural = get_string("cdms", "local_repository");
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading($strplural);
echo $OUTPUT->header();
?>

<?php 
    $status1 = $DB->count_records_sql("select count(*) from {lcms_repository} where status = :status",array('status'=>1));
    $status2 = $DB->count_records_sql("select count(*) from {lcms_repository} where status = :status",array('status'=>2));
    $status3 = $DB->count_records_sql("select count(*) from {lcms_repository} where status = :status",array('status'=>3));
?>
<div class="small_block_cdms">
    <div class="block_header_cdms"><?php echo get_string('review','local_repository');?>(<?php echo $status1; ?>) 
        <div class = "plus_btn"><a href="<?php echo $CFG->wwwroot . '/local/repository/cdms_list.php?status=1' ?>">+</a></div>
    </div>
    <table class="generaltable">
        <thead>
            <tr>
                <th><?php echo get_string('contentsname','local_repository');?></th>
                <th><?php echo get_string('type','local_repository');?></th>
                <th><?php echo get_string('lasteditdate','local_repository');?></th>
            </tr>
        </thead>
        <tbody>
                    <?php
                    $contents1 = $DB->get_records('lcms_repository', array('status' => 1), 'id asc','*',0,3);
                    foreach ($contents1 as $content) { 
                        $con = $DB->get_record('lcms_contents',array('id'=>$content->lcmsid));
                        ?>
            <tr>
                <td><div class="cdms_conname_short"><?php echo $con->con_name; ?></div></td>
                <td><?php echo $con->con_type; ?></td>
                <td><?php echo date('Y-m-d',$con->update_dt); ?></td>
            </tr>

                        <?php
                    }
                    if(!$contents1){
                        echo '<tr><td colspan="3">'.get_string('empty_review','local_repository').'</td></tr>';
                    }
                   ?>
        </tbody>
        </table>
</div>
<div class="small_block_cdms" onclick="location.href='cdms_list.php?status=2'">
    <div class="block_header_cdms"><?php echo get_string('approved','local_repository');?>(<?php echo $status2; ?>)
        <div class = "plus_btn"><a href="<?php echo $CFG->wwwroot . '/local/repository/cdms_list.php?status=2' ?>">+</a></div>
    </div>
          <table class="generaltable">
        <thead>
            <tr>
                <th><?php echo get_string('contentsname','local_repository');?></th>
                <th><?php echo get_string('type','local_repository');?></th>
                <th><?php echo get_string('lasteditdate','local_repository');?></th>
            </tr>
        </thead>
        <tbody>
                    <?php
                    $contents2 = $DB->get_records('lcms_repository', array('status' => 2), 'id asc','*',0,3);
                    foreach ($contents2 as $content) { 
                        $con = $DB->get_record('lcms_contents',array('id'=>$content->lcmsid));
                        ?>
            <tr>
                <td><div class="cdms_conname_short"><?php echo $con->con_name; ?></div></td>
                <td><?php echo $con->con_type; ?></td>
                <td><?php echo date('Y-m-d',$con->update_dt); ?></td>
            </tr>

                        <?php
                    }
                    if(!$contents2){
                        echo '<tr><td colspan="3">'.get_string('empty_approved','local_repository').'</td></tr>';
                    }
                   ?>
        </tbody>
        </table>
        
</div>
<div class="small_block_cdms" onclick="location.href='cdms_list.php?status=3'">
    <div class="block_header_cdms"><?php echo get_string('hold','local_repository');?>(<?php echo $status3; ?>)
        <div class = "plus_btn"><a href="<?php echo $CFG->wwwroot . '/local/repository/cdms_list.php?status=3' ?>">+</a></div>
    </div>
              <table class="generaltable">
        <thead>
            <tr>
                <th><?php echo get_string('contentsname','local_repository');?></th>
                <th><?php echo get_string('type','local_repository');?></th>
                <th><?php echo get_string('lasteditdate','local_repository');?></th>
            </tr>
        </thead>
        <tbody>
                    <?php
                    $contents3 = $DB->get_records('lcms_repository', array('status' => 3), 'id asc','*',0,3);
                    foreach ($contents3 as $content) { 
                        $con = $DB->get_record('lcms_contents',array('id'=>$content->lcmsid));
                        ?>
            <tr>
                <td><div class="cdms_conname_short"><?php echo $con->con_name; ?></div></td>
                <td><?php echo $con->con_type; ?></td>
                <td><?php echo date('Y-m-d',$con->update_dt); ?></td>
            </tr>

                        <?php
                    }
                    if(!$contents3){
                        echo '<tr><td colspan="3">'.get_string('empty_hold','local_repository').'</td></tr>';
                    }
                   ?>
        </tbody>
        </table>
</div>
<div class="half_block_cdms linestart">
    <div class="block_header_cdms"><?php echo get_string('mycontents','local_repository');?>
        <div class = "plus_btn"><a href="<?php echo $CFG->wwwroot . '/local/repository/index.php' ?>">+</a></div>
    </div>
    <div>
        <table class="generaltable">
            <thead>
            <tr>
                <th><?php echo get_string('list:title','local_repository');?></th>
                <th><?php echo get_string('list:isopen','local_repository');?></th>
                <th><?php echo get_string('list:timecreated','local_repository');?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $contents = $DB->get_records('lcms_contents', array('user_no' => $USER->id), 'id desc', 'id,con_name,share_yn,update_dt', 0, 5);
            foreach ($contents as $content) {
                $rid = $DB->get_field('lcms_repository', 'id', array('lcmsid' => $content->id));
                $isopen = ($content->share_yn == 'N') ? false : true;
                ?>
                <tr>
                    <td><div class="cdms_conname_short"><a href="detail.php?id=<?php echo $rid; ?>&userid=<?php echo $USER->id ?>"><?php echo $content->con_name; ?></a></div></td>
                    <td><?php echo($isopen) ? 'O' : 'X'; ?></td>
                    <td><?php echo date('Y-m-d', $content->update_dt); ?></td>
                </tr>
                <?php
            }
            if (!$contents)
                echo '<td colspan=4>'.get_string('empty_contents','local_repository').'</td>';
            ?>
            </tbody>
        </table>
    </div>
</div>
<div class="half_block_cdms">
    <div class="block_header_cdms"><?php echo get_string('sharedcontents','local_repository');?> 
        <div class = "plus_btn"><a href="<?php echo $CFG->wwwroot . '/local/repository/index.php?shared=Y' ?>">+</a></div></div>
    <div>
        <table class="generaltable">
            <thead>
            <tr>
                <th><?php echo get_string('list:title','local_repository');?></th>
                <th><?php echo get_string('owner','local_repository');?></th>
                <th><?php echo get_string('list:timecreated','local_repository');?></th>
            </tr>
            </thead>
            <tbody>
            <?php
            $contents_sc = $DB->get_records('lcms_contents', array('share_yn' => 'y'), 'id desc', 'con_name,share_yn,update_dt', 0, 5);
            foreach ($contents_sc as $content) {
                $isopen = ($content->share_yn == 'N') ? false : true;
                ?>
                <tr>
                    <td><div class="cdms_conname_short"><?php echo $content->con_name; ?></div></td>
                    <td><?php echo isset($content->teacher)? $content->teacher:'-'; ?></td>
                    <td><?php echo date('Y-m-d', $content->update_dt); ?></td>
                </tr>
                <?php
            }
            if (!$contents_sc)
                echo '<td colspan=4>'.get_string('empty_sharedcontents','local_repository').'</td>';
            ?>
            </tbody>
        </table>
    </div>
</div>
<div class="half_block_cdms linestart">
    <div class="block_header_cdms"><?php echo get_string('mycontentsfile','local_repository');?>
        <div class="plus_btn"><a href="<?php echo $CFG->wwwroot . '/local/repository/index.php' ?>">+</a></div>
    </div>
    <!--div>office(엑겔 워드 피피티..)군이랑 동영상 텍스트 파일 이티시</div-->
    <div>
        <table class="generaltable">
            <thead>
            <tr>
                <th><?php echo get_string('filename','local_repository');?></th>
                <th><?php echo get_string('file_type','local_repository');?></th>
                <th><?php echo get_string('group','local_repository');?></th>
                <th><?php echo get_string('list:timecreated','local_repository');?></th>
            </tr>
            </thead>
            <?php
            $format_count = $DB->get_records('lcms_contents_file', array('user_no' => $USER->id), 'id desc', 'id,filename');
            $formats = array();
            foreach ($format_count as $content) {
                $format = substr($content->filename, strrpos($content->filename, '.') + 1);
                if (!isset($formats[$format]))
                    $formats[$format] = 1;
                else
                    $formats[$format] ++;
            }
            list($xls, $ppt, $txt, $doc, $vod, $etc) = array(0, 0, 0, 0, 0, 0);
            foreach ($formats as $key => $val) {
                if ($key == 'xls' || $key == 'xlsx') {
                    $xls += $val;
                } else if ($key == 'ppt' || $key == 'pptx') {
                    $ppt += $val;
                } else if ($key == 'txt') {
                    $txt += $val;
                } else if ($key == 'doc' || $key == 'docx') {
                    $doc += $val;
                } else if ($key == 'mp4' || $key == 'avi' || $key == 'wmv') {
                    $vod += $val;
                } else {
                    $etc += $val;
                }
            }
            ?>
            <div class="cdms_icons">
                <?php
                echo '<img src="' . $OUTPUT->pix_url('f/spreadsheet') . '" class="icon">';
                echo $xls . '&nbsp;';
                echo '<img src="' . $OUTPUT->pix_url('f/powerpoint') . '" class="icon">';
                echo $ppt . '&nbsp;';
                echo '<img src="' . $OUTPUT->pix_url('f/text') . '" class="icon">';
                echo $txt . '&nbsp;';
                echo '<img src="' . $OUTPUT->pix_url('f/document') . '" class="icon">';
                echo $doc . '&nbsp;';
                echo '<img src="' . $OUTPUT->pix_url('f/mpeg') . '" class="icon">';
                echo $vod . '&nbsp;';
                echo '<img src="' . $OUTPUT->pix_url('f/sourcecode') . '" class="icon">';
                echo $etc;
                ?>
            </div>
            <tbody>
            <?php
            $contents_mf = $DB->get_records('lcms_contents_file', array('user_no' => $USER->id), 'id desc', 'id,con_seq,filename,fileoname', 0, 5);
            foreach ($contents_mf as $content) {
                $update_dt = $DB->get_field('lcms_contents', 'update_dt', array('id' => $content->con_seq));
                $gid = $DB->get_field('lcms_repository', 'groupid', array('lcmsid' => $content->con_seq));
                $gname = $DB->get_field('lcms_repository_groups', 'name', array('id' => $gid));
                // 5개의 컬럼만 고정적으로 가져오는거라 join을 이용하지않고 필드로 구해옴 :)
                $format = substr($content->filename, strrpos($content->filename, '.') + 1);
                ?>
                <tr>
                    <td><div class="cdms_conname_short"><?php echo $content->fileoname; ?></div></td>
                    <td><?php echo $format; ?></td>
                    <td><?php echo $gname; ?></td>
                    <td><?php echo date('Y-m-d', $update_dt); ?></td>
                </tr>
                <?php
            }
            if (!$contents_mf)
                echo '<td colspan=4>'.get_string('empty_file','local_repository').'</td>';
            ?>
        </table>
    </tbody>
    </div>
</div>
<div class="half_block_cdms">
    <div class="block_header_cdms">
        <?php echo get_string('sharedcontentsfile','local_repository');?>
    <div class = "plus_btn"><a href="<?php echo $CFG->wwwroot . '/local/repository/index.php?shared=Y' ?>">+</a></div>
    </div>
    <div>
        <table class="generaltable">
            <thead>
            <tr>
                <th><?php echo get_string('list:title','local_repository');?></th>
                <th><?php echo get_string('file_type','local_repository');?></th>
                <th><?php echo get_string('owner','local_repository');?></th>
                <th><?php echo get_string('list:timecreated','local_repository');?></th>
            </tr>
            </thead>
            <?php
            $sql = "select lcf.id, lcf.filename from {lcms_contents_file} lcf "
                    . "join {lcms_contents} lc on lc.id = lcf.con_seq "
                    . "where lc.share_yn = 'Y' order by lcf.id desc";
            $format_count = $DB->get_records_sql($sql);
            $formats = array();
            foreach ($format_count as $content) {
                $format = substr($content->filename, strrpos($content->filename, '.') + 1);
                if (!isset($formats[$format]))
                    $formats[$format] = 1;
                else
                    $formats[$format] ++;
            }
            list($xls, $ppt, $txt, $doc, $vod, $etc) = array(0, 0, 0, 0, 0, 0);
            foreach ($formats as $key => $val) {
                if ($key == 'xls' || $key == 'xlsx') {
                    $xls += $val;
                } else if ($key == 'ppt' || $key == 'pptx') {
                    $ppt += $val;
                } else if ($key == 'txt') {
                    $txt += $val;
                } else if ($key == 'doc' || $key == 'docx') {
                    $doc += $val;
                } else if ($key == 'mp4' || $key == 'avi' || $key == 'wmv') {
                    $vod += $val;
                } else {
                    $etc += $val;
                }
            }
            ?>
            <div class="cdms_icons">
                <?php
                echo '<img src="' . $OUTPUT->pix_url('f/spreadsheet') . '" class="icon">';
                echo $xls . '&nbsp;';
                echo '<img src="' . $OUTPUT->pix_url('f/powerpoint') . '" class="icon">';
                echo $ppt . '&nbsp;';
                echo '<img src="' . $OUTPUT->pix_url('f/text') . '" class="icon">';
                echo $txt . '&nbsp;';
                echo '<img src="' . $OUTPUT->pix_url('f/document') . '" class="icon">';
                echo $doc . '&nbsp;';
                echo '<img src="' . $OUTPUT->pix_url('f/mpeg') . '" class="icon">';
                echo $vod . '&nbsp;';
                echo '<img src="' . $OUTPUT->pix_url('f/sourcecode') . '" class="icon">';
                echo $etc;
                ?>
            </div>
            <tbody>
            <?php
            $sql = "select lcf.id, con_seq,filename,fileoname from {lcms_contents_file} lcf "
                    . "join {lcms_contents} lc on lc.id = lcf.con_seq "
                    . "where lc.share_yn = 'Y' order by lcf.id desc";
            $contents_sf = $DB->get_records_sql($sql, array(), 0, 5);
            foreach ($contents_sf as $content) {
                $update_dt = $DB->get_field('lcms_contents', 'update_dt', array('id' => $content->con_seq));
                $gid = $DB->get_field('lcms_repository', 'groupid', array('lcmsid' => $content->con_seq));
                $gname = $DB->get_field('lcms_repository_groups', 'name', array('id' => $gid));
                // 5개의 컬럼만 고정적으로 가져오는거라 join을 이용하지않고 필드로 구해옴 :)
                $format = substr($content->filename, strrpos($content->filename, '.') + 1);
                ?>
                <tr>
                    <td><div class="cdms_conname_short"><?php echo $content->fileoname; ?></div></td>
                    <td><?php echo $format; ?></td>
                    <td><?php echo $gname; ?></td>
                    <td><?php echo date('Y-m-d', $update_dt); ?></td>
                </tr>
                <?php
            }
            if (!$contents_sf)
                echo '<td colspan=4>'.get_string('empty_sharedcontentsfile','local_repository').'</td>';
            ?>
            </tbody>
        </table>
    </div>
</div>
<div class="full_block_cdms">
    <div class="block_header_cdms"><?php echo get_string('contentsfile_manage','local_repository');?></div>
    <div id="file_body">

    </div>
</div>
<script>
    $(window).load(function () {
        $.ajax({
            method: "POST",
            url: "myfiles.ajax.php",
            data: {userid: "<?php echo $USER->id; ?>"}
        })
                .done(function (html) {
                    $('#file_body').html(html);
                });
    });
    function esacape() {
        $.ajax({
            method: "POST",
            url: "myfiles.ajax.php",
            data: {userid: "<?php echo $USER->id; ?>"}
        })
                .done(function (html) {
                    $('#file_body').html(html);
                });
    }
    function get_files(id) {
        $.ajax({
            method: "POST",
            url: "myfiles.ajax.php",
            data: {
                userid: "<?php echo $USER->id; ?>",
                contentid: id
            }
        })
                .done(function (html) {
                    $('#file_body').html(html);
                });
    }
    function mkfile_fn() {

        var tag2 = $("<div id='mkfile'></div>");

        $.ajax({
            url: '<?php echo $CFG->wwwroot . "/local/repository/add_lcms_file.ajax.php" ?>',
            success: function (data) {
                tag2.html(data).dialog({
                    title: '<?php echo get_string('add_file', 'local_repository'); ?>',
                    modal: true,
                    width: 400,
                    resizable: false,
                    height: 200,
                    buttons: [{id: 'adddir',
                            text: '<?php echo get_string('add_file', 'local_repository'); ?>',
                            disable: true,
                            click: function () {
                                $.ajax({
                                    method: "POST",
                                    url: "add_file_submit.ajax.php",
                                    data: {con_id: $('#hidden_con_id').val()}
                                }),
                                        $('#mkfile').remove();
                                get_files($('#hidden_con_id').val());
                            }},
                    ],
                    close: function () {
                        $('#mkfile').remove();
                        console.log(this);
                        $(this).dialog('destroy');
                    }
                }).dialog('open');
            }
        });
    }
    function mkdir_fn() {

        var tag = $("<div id='mkdir'></div>");

        $.ajax({
            url: '<?php echo $CFG->wwwroot . "/local/repository/add_lcms_forder.ajax.php" ?>',
            success: function (data) {
                tag.html(data).dialog({
                    title: '<?php echo get_string('add_dir', 'local_repository'); ?>',
                    modal: true,
                    width: 400,
                    resizable: false,
                    height: 200,
                    buttons: [{id: 'adddir',
                            text: '<?php echo get_string('add_dir', 'local_repository'); ?>',
                            disable: true,
                            click: function () {
                                if (!$('#forder_add').val()) {
                                    alert("<?php echo get_string('insert_foldername_msg','local_repository');?>");
                                    return;
                                }

                                $.ajax({method: "POST", url: "add_content_submit.ajax.php",
                                    data: {userid: "<?php echo $USER->id; ?>", con_name: $('#forder_add').val()}
                                }),
                                        $('#mkdir').remove();
                                esacape();
                            }},
                    ],
                    close: function () {
                        $('#mkdir').remove();
                        $(this).dialog('destroy');
                    }
                }).dialog('open');
            }
        });
    }
    function delete_con(id, ftype) {
        if (confirm('<?php echo get_string('insert:delete','local_repository');?>')) {
            $.ajax({
                method: "POST",
                url: "delete.ajax.php",
                data: {
                    ftype: ftype,
                    contentid: id
                }
            })
                    .done(function (html) {
                        if (html == '1') {
                            esacape();
                        } else {
                            get_files($('#hidden_con_id').val());
                        }
                    });
        }
    }
</script>
<?php
echo $OUTPUT->footer();
?>
