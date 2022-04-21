<?php
require_once("../../config.php");
require_once($CFG->dirroot."/local/repository/lib.php");
require_once($CFG->dirroot . "/lib/coursecatlib.php");
require_once($CFG->dirroot . "/lib/filelib.php");



$context = context_system::instance();
$PAGE->set_context($context);

$id = optional_param('id', 0, PARAM_INT); // repository id
$userid = optional_param('userid', 0, PARAM_INT);
$ref = optional_param('ref', 0, PARAM_INT);

$PAGE->set_url('/local/repository/detail.php?id=' . $id);
$PAGE->set_pagelayout('standard');

$strplural = get_string("pluginnameplural", "local_repository");
$PAGE->navbar->add($strplural);
$PAGE->set_title($strplural);
$PAGE->set_heading($strplural);

require_login();

//echo $OUTPUT->header();

if (!$ref) {
    $sql = "select "
            . "rep.id , rep.referencecnt , "
            . "con.id as con_id,con.con_name,con.con_type,con.con_des,con.update_dt,con.data_dir,con.embed_type,con.embed_code, "
            . "rep_group.name as gname "
            . "from {lcms_repository} rep "
            . "join {lcms_contents} con on con.id= rep.lcmsid "
            . "left join {lcms_repository_groups} rep_group on rep_group.id = rep.groupid "
            . "where rep.id= :id";
} else {
    $sql = "select "
            . "rep.id , "
            . "con.id as con_id,con.con_name,con.con_type,con.con_des,con.update_dt,con.data_dir,con.embed_type,con.embed_code,"
            . "rep_group.name as gname "
            . "from {lcms_repository_reference} rep "
            . "join {lcms_contents} con on con.id= rep.lcmsid "
            . "left join {lcms_repository_groups} rep_group on rep_group.id = rep.groupid "
            . "where rep.id= :id";
}

$data = $DB->get_record_sql($sql, array('id' => $id));
insert_lcms_history($data->con_id,'Admin Page Viewed',2);
$files = $DB->get_records('lcms_contents_file', array('con_seq' => $data->con_id));

/// echo $OUTPUT->heading($data->con_name);
include_once('../inc/header.php');
?>
<div id="contents">
        <?php include_once('../inc/sidebar_contents.php'); ?>
    <div id="content">
        
        <h3 class="page_title"><?php echo get_string('lcms_management', 'local_lmsdata'); ?></h3>
        <div class="page_navbar"><a href="./notices.php"><?php echo get_string('lcms_management', 'local_lmsdata'); ?></a> > <a href="./qna.php"><?php echo get_string('contents_contentslist', 'local_lmsdata'); ?></a> > <?php echo get_string('stats_view', 'local_lmsdata'); ?></div>
        <h3 class="detail_title"><?php echo $data->con_name; ?></h3>
        
        <?php
//타이틀표시
        $output_lcms = html_writer::start_tag('div', array('class' => 'board-detail-area'));

        $output_lcms .= html_writer::start_tag('div', array('class' => 'detail-contents-area'));
        $attfile = '';

//콘텐츠 정보
        if ($data->con_type == 'word' || $data->con_type == 'ref') {
            //파일정보(문서일경우 파일명 가져오기)
            $files = $DB->get_records('lcms_contents_file', array('con_seq' => $data->con_id, 'con_type' => $data->con_type));
            foreach ($files as $file) {

                $filename = $file->fileoname;
                $filepath = explode('/', $file->filepath);
                if ($filepath[0] == 'lms' || $filepath[0] == 'lcms')
                    $lcmsdata = '/lcmsdata/';
                else
                    $lcmsdata = '/';
                $mimetype = mime_content_type(STORAGE2 . $lcmsdata . $file->filepath . '/' . $file->filename);
                $path = 'viewer/download.php?id=' . $id . '&fileid=' . $file->id;

                $iconimage = '<img src="' . $OUTPUT->pix_url(file_mimetype_icon($mimetype)) . '" class="icon cont_icon" alt="' . $mimetype . '" />';
                $attfile .= '<li>';
                $attfile .= "<a href=\"$path\">$iconimage</a> ";
                $attfile .= format_text("<a href=\"$path\">" . s($filename) . "</a></li>", FORMAT_HTML, array('context' => $context)) . "</li>";
            }
            $output_lcms .= html_writer::tag('ul', $attfile, array('class' => "detail-attachment"));
        }else if ($data->con_type == 'html') {
            //html일경우 콘텐츠 보기
            $viewer_url = 'viewer/package.php';
            $width = '1000';
            $height = '700';
            $attfile .= '<li><a href="#viewer" class="blue_btn" onclick="load_viewer_popup(' . $data->con_id . ');">' . get_string('viewcontent', 'lcms') . '</a></li>';
            $output_lcms .= html_writer::tag('ul', $attfile, array('class' => "detail-attachment"));
        } else if ($data->con_type == 'video') {
            //동영상 콘텐츠 보기
            $viewer_url = 'viewer/video_player.php?id=' . $data->con_id;
            $attfile .= '<iframe style="width:100%; height:525px;" id="vod_viewer" src="' . $viewer_url . '"></iframe>';
            $output_lcms .= html_writer::tag('div', $attfile, array('class' => ""));
        } else if  ($data->con_type == 'embed')  {

    $cid = $data->con_id;

    if ($cid) {
        if (!$contents = $DB->get_record('lcms_contents', array('id' => $cid))) {
            print_error('lcms contents is incorrect');
        }

        if ($contents->embed_type == 'youtube') {

            $embs = explode('/', $contents->embed_code);
            $emb_code = trim(str_replace("watch?v=", "", $embs[sizeof($embs) - 1]));
            $emb1 = explode('&', $emb_code);
            $cid = trim($emb1[0]);
            $img_nm = 'http://img.youtube.com/vi/' . $cid . '/mqdefault.jpg';

            $auto_play = 0; //자동재생여부: 1이면 자동재생
//자막파일영역
            $subtitles = get_subtitle_list($context->id, $contents->id);
            ?>
            <script type="text/javascript" src="viewer/jquery-1.10.2.min.js"></script>
            <script type="text/javascript" src="viewer/jquery-ui-1.10.3.custom.min.js"></script>
            <?php if ($contents->embed_type == 'youtube') { ?>
                <script type="text/javascript" src="viewer/player/youtube.js"></script>
            <?php } ?>
            <script type="text/javascript">
                                var height = '800';
                                function videoplayer(type) {


                                //디바이스별 플레이어 사용
                                if (type == 'youtube'){
                                YouTube_Init("mediaplayer", "100%", "400", "<?php echo $emb_code; ?>", "0");
                                } else{
                                embedplayer();
                                }
                                }

                                function embedplayer(){
                                var videolist = '<iframe src="https://vimeo.com/video/<?php echo $cid; ?>" width="100%" height="300" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
                                $('#mediaplayer').empty().append(videolist);
                                }

            </script>
            </head>

            <div id="player_area">
                <div id="mediaplayer"><p>잠시만 기다려주세요. 로딩중입니다.</p></div>
            </div>

            <script>videoplayer('<?php echo $contents->embed_type; ?>');</script>
            <?php
        } else {

            $embs = explode('/', $contents->embed_code);
            $cid = trim($embs[sizeof($embs) - 1]);
            $embinfo = vimeoinfo($cid);
            $img_nm = $embinfo->thumbnail;
            ?>
            <style type="text/css">
                #made-in-ny iframe {
                    width:100%;
                }
            </style>
            <div id="made-in-ny"></div>

            <script src="https://player.vimeo.com/api/player.js"></script>
            <script>
                var options = {
                id: <?php echo $cid; ?>,
                        loop: true
                };
                var player = new Vimeo.Player('made-in-ny', options);
                player.setVolume(0);
                player.on('play', function() {
                console.log('played the video!');
                });</script>
                <?php
            }
        }
    }else if ($data->con_type == 'html2') {
            //html2일경우 콘텐츠 보기
            $viewer_url = 'viewer/package2.php';
            $width = '990'; 
            $height = '741';
            $attfile .= '<li><a href="#viewer" class="blue_btn" onclick="load_viewer_popup(' . $data->con_id . ');">' . get_string('viewcontent', 'lcms') . '</a></li>';
            $output_lcms .= html_writer::tag('ul', $attfile, array('class' => "detail-attachment"));
        }

        $output_lcms .= html_writer::end_tag('div');

//강의설명표시 
        if (!empty($data->con_des)) {
            $output_lcms .= html_writer::tag('div', $data->con_des, array('class' => 'detail-contents'));
        }

        $output_lcms .= html_writer::end_tag('div');

        echo $output_lcms;
        ?>
        <div class="table-footer-area">
            <div class="left">
                <?php if (!$ref) { ?>
                    <button class="red-form blue_btn" onclick="location.href = 'add.php?mod=ref&id=<?php echo $id; ?>'"><?php echo get_string('referenceadd', 'local_repository'); ?></button>
                <?php } else { ?>
                    <button class="red-form gray_btn" onclick="location.href = 'detail.php?id=<?php echo $ref; ?>'"><?php echo get_string('back', 'local_repository'); ?></button>
<?php } ?>
            </div>
            <div class="right">
                <button class="blue-form blue_btn" onclick="location.href = 'edit.php?mode=edit&id=<?php echo $id; ?>&ref=<?php echo $ref; ?>'"><?php echo get_string('detail:edit', 'local_repository'); ?></button>
                <button class="blue-form gray_btn" onclick="if (confirm('<?php echo get_string('delete_content', 'local_repository'); ?>')) {
                            location.href = 'deletes.php?id=<?php echo $data->con_id; ?>&ref=<?php echo $ref; ?>';
                        }"><?php echo get_string('detail:delete', 'local_repository'); ?></button>
                <button class="red-form gray_btn" onclick="location.href = 'index.php?userid=<?php echo $userid; ?>'"><?php echo get_string('detail:list', 'local_repository'); ?></button>
            </div>
        </div> 
<?php if (!$ref) { ?>
            <h3 class="tab_title"><?php echo get_string('reference', 'local_repository'); ?></h3>
            <table cellpadding="0" cellspacing="0" class="normal">
                <caption class="hidden-caption">강의콘텐츠관리</caption>
                <thead>
                    <tr>
                        <th scope="row" width="6%"><?php echo get_string('list:no', 'local_repository'); ?></th>
                        <th scope="row" width=""><?php echo get_string('list:title', 'local_repository'); ?></th>
                        <th scope="row" width="10%"><?php echo get_string('list:timecreated', 'local_repository'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "select " .
                            "ref.id, con.id as con_id, con.share_yn, con.con_type, con.con_name, con.update_dt " .
                            "from {lcms_repository_reference} ref " .
                            "join {lcms_contents} con on con.id = ref.lcmsid " .
                            "where " .
                            "ref.repository = :refository_id";
                    $files = $DB->get_records_sql($sql, array('refository_id' => $data->id));
                    $num = 1;
                    foreach ($files as $file) {
                        ?>
                        <tr>
                            <td><?php echo $num; ?></td>
                            <td><a href="detail.php?id=<?php echo $file->id; ?>&ref=<?php echo $data->id; ?>"><?php echo $file->con_name; ?></a></td>
                            <td class="number"><?php echo date('Y.m.d', $file->update_dt) ?></td>
                        </tr>
                        <?php
                        $num++;
                    } if ($num <= 1) {
                        echo "<tr><td colspan='3'>" . get_string('noreference', 'local_repository') . "</td></tr>";
                    }
                }
                ?>
            </tbody>
        </table>
    </div>
</div>
<link rel="stylesheet" type="text/css" href="<?php echo $CFG->wwwroot . '/mod/lcms/jquery-ui-1.10.3.custom.css'; ?>" />
<script type="text/javascript" src="<?php echo $CFG->wwwroot; ?>/mod/lcms/jquery-1.10.2.min.js"></script>
<script type="text/javascript" src="<?php echo $CFG->wwwroot; ?>/mod/lcms/jquery-ui-1.10.3.custom.min.js"></script>
<script type="text/javascript" src="<?php echo $CFG->wwwroot; ?>/mod/lcms/player/js/jwplayer.js"></script>
<?php include_once 'loading.php'; ?>
<script type="text/javascript"> 

                    function load_viewer_popup(id, qua) {

                        var width = '<?php echo $width; ?>', height = '<?php echo $height; ?>';
                        if ($(window).width() < width) {
                            width = $(window).width();
                        }

                        if ($(window).height() < height) {
                            height = $(window).height();
                        }

                        var tag = $("<div id='viewer_popup' style='overflow:hidden;'></div>");

                        $.ajax({
                            url: '<?php echo $viewer_url; ?>',
                            data: {
                                id: id,
                                qua: qua
                            },
                            success: function (data) {

                                $('body').css({'overflow': 'hidden'});

                                tag.html(data).dialog({
                                    title: '<?php echo $data->con_name; ?>',
                                    modal: true,
                                    width: width,
                                    height: height,
                                    close: function () {
                                        //if($('video').length==0) jwplayer('mediaplayer').pause(); 
                                        $(this).dialog('destroy').remove();
                                        $('body').css({'overflow': 'auto'});
                                    }
                                }).dialog('open');

                            }

                        });
                    }

                    function close_viewer_popup() {
                        $('#viewer_popup').dialog('destroy').remove();
                        $('body').css({'overflow': 'auto'});
                    }
</script>

<?php
include_once('../inc/footer.php');
?>







