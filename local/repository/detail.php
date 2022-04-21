<?php
require_once("../../config.php");
require_once("./lib.php");
require_once("./config.php");
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

$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('ui');
$PAGE->requires->jquery_plugin('ui-css');
$PAGE->requires->jquery_plugin('migrate');

require_login();

echo $OUTPUT->header();

if (!$ref) {
    $sql = "select "
            . "rep.id , rep.referencecnt , rep.iscdms , rep.status, rep.delaymsg , "
            . "con.id as con_id,con.con_name,con.con_type,con.con_des,con.update_dt,con.data_dir,con.embed_type,con.embed_code, con.teacher,"
            . "rep_group.name as gname "
            . "from {lcms_repository} rep "
            . "join {lcms_contents} con on con.id= rep.lcmsid "
            . "left join {lcms_repository_groups} rep_group on rep_group.id = rep.groupid "
            . "where rep.id= :id";
} else {
    $sql = "select "
            . "rep.id , "
            . "con.id as con_id,con.con_name,con.con_type,con.con_des,con.update_dt,con.data_dir,con.embed_type,con.embed_code, con.teacher,"
            . "rep_group.name as gname "
            . "from {lcms_repository_reference} rep "
            . "join {lcms_contents} con on con.id= rep.lcmsid "
            . "left join {lcms_repository_groups} rep_group on rep_group.id = rep.groupid "
            . "where rep.id= :id";
}

$data = $DB->get_record_sql($sql, array('id' => $id));
insert_lcms_history($data->con_id, 'Repository Viewed', 2);
$files = $DB->get_records('lcms_contents_file', array('con_seq' => $data->con_id));
if ($data->iscdms && $data->status == 3) {
    $text = "(보류사유 : " . $data->delaymsg . ")";
} else {
    $text = "";
}
echo $OUTPUT->heading($data->con_name. '<span class="writer">' . $data->teacher .'</span>');
echo "<span style='color:red; font-weight:bold;'>" . $text . "</span>";


//자막파일영역
$subtitles = get_subtitle_list($context->id, $data->con_id);

//타이틀표시
$output_lcms = html_writer::start_tag('div', array('class' => 'board-detail-area'));

$output_lcms .= html_writer::start_tag('div', array('class' => 'detail-contents-area'));
$attfile = '';
$width = '100%';
$height = '500px';

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
        $iconimage = '<img src="' . $OUTPUT->pix_url(file_mimetype_icon($mimetype)) . '" class="icon" alt="' . $mimetype . '" />';
        $attfile .= '<li>';
        $attfile .= "<a href=\"$path\">$iconimage</a> ";
        $attfile .= format_text("<a href=\"$path\">" . s($filename) . "</a></li>", FORMAT_HTML, array('context' => $context)) . "</li>";
    }
    $output_lcms .= html_writer::tag('ul', $attfile, array('class' => "detail-attachment"));
}else if ($data->con_type == 'html') {
    //html일경우 콘텐츠 보기
    $viewer_url = 'viewer/package.php';
    $width = '1040';
    $height = '720';
    $attfile .= '<li><a href="#viewer" class="blue_btn" onclick="load_viewer_popup(' . $data->con_id . ');">' . get_string('viewcontent', 'lcms') . '</a></li>';
    $output_lcms .= html_writer::tag('ul', $attfile, array('class' => "detail-attachment"));
} else if ($data->con_type == 'video') {

    $cid = $data->con_id;

    if ($cid) {
        if (!$contents = $DB->get_record('lcms_contents', array('id' => $cid))) {
            print_error('lcms contents is incorrect');
        }
        if (!$file = $DB->get_record('lcms_contents_file', array('con_seq' => $cid))) {
            print_error('lcms contents file is incorrect');
        }
        if (!$repo = $DB->get_record('lcms_repository', array('lcmsid' => $cid))) {
            print_error('lcms repository file is incorrect');
        }
    } else {
        print_error('missingparameter');
    }

    $auto_play = 0;
    $namearray = explode('.', $file->filename);
    $n = count($namearray);
    unset($namearray[$n - 1]);
    $capture = str_replace(' ', '', implode('', $namearray) . '.png');

    if (preg_match('/xenoglobal/', $file->filepath)) {
        $capture = MEDIA . '/uploads/' . $file->filepath . '/' . str_replace('.mp4', '.jpg', $file->filename); //이미지경로
    }
//자막파일영역
    $subtitles = get_subtitle_list($context->id, $contents->id);
    $lrn_time = 0;
    ?>
    <link rel="stylesheet" type="text/css" href="jquery-ui-1.10.3.custom.css" />
    <link rel="stylesheet" href="styles.css" />
    <script type="text/javascript" src="viewer/jquery-ui-1.10.3.custom.min.js"></script>
    <script type="text/javascript" src="<?php echo $CFG->wwwroot; ?>/local/repository/viewer/jwplayer/js/jwplayer.js"></script>
    <script type="text/javascript">jwplayer.key = "aGprQX6ESojc1Ae11PLVSeQAgjkeP19KO0cgSg==";</script>
    <link rel="stylesheet" href="viewer/flowplayer/skin/skin.css">
    <script src="viewer/flowplayer/flowplayer.min.js"></script>
    <script type="text/javascript">
        var server = '<?php echo $CFG->vodserver; ?>';
        var storage = 'uploads/<?php echo $file->filepath; ?>';
        var vodname = '<?php echo str_replace(' ', '', $file->filename); ?>';
        var imagename = '<?php echo $capture; ?>';
        var ccmark = '';
        var height = '500';
        function videoplayer(qua, lrn_time) {
        //디바이스별 판별
        var _ua = window.navigator.userAgent.toLowerCase();
        var browser = {
        ipod: /webkit/.test(_ua) && /\(ipod/.test(_ua),
                ipad: /webkit/.test(_ua) && /\(ipad/.test(_ua),
                iphone: /webkit/.test(_ua) && /\(iphone/.test(_ua),
                android: /webkit/.test(_ua) && /android/.test(_ua),
                msie: /msie/.test(_ua)
        };
        //디바이스별 플레이어 사용
        if (browser.ipod || browser.iphone || browser.ipad || browser.android) {
        html5player(qua, lrn_time);
        height = '200';
        } else {
        //html5player(qua, lrn_time);
        flashplayer(<?php echo $auto_play; ?>, qua);
        }
        }
        var duration = 0;
        function flashplayer(auto, qua) {
        if (qua) { 
        vodname = vodname.replace('.mp4', qua + '.mp4');
        }
        console.log('rtmp://210.125.136.193 :1935/vod/mp4:'+ storage + '/' + vodname);
        jwplayer('mediaplayer').setup({
                'flashplayer': '<?php echo $CFG->wwwroot; ?>/local/repository/viewer/jwplayer/swf/jwplayer.flash.swf',
                'id': 'mediaplayer',
                'menu': 'false',
                'width': '100%',
                'height': height,
                'skin': '<?php echo $CFG->wwwroot; ?>/local/repository/viewer/jwplayer/skins/seven.css',
                 sources: [
                {
                    'file': 'rtmp://210.125.136.193:1935/vod/mp4:'+ storage + '/' + vodname,
                    "type": "rtmp",
                 },
                 {
                      'file': server + '/' + storage + '/' + vodname
                 }
                ],
                'image': server + '/' + storage + '/' + imagename,
                'primary': "flash",
    <?php if (preg_match('/xenoglobal/', $file->filepath)) { ?>
            'image':imagename,
    <?php } else { ?>
            'image': server + '/' + storage + '/' + imagename,
    <?php } ?>
        'plugins': {
        '<?php echo $CFG->wwwroot; ?>/local/repository/viewer/jwplayer/js/textoutput.js': {'text': ccmark},
                'captions-2': {
                files: '<?php echo $subtitles->path_ko; ?>,<?php echo $subtitles->path_en; ?>',
                                    labels: 'Korean,English',
                                    fontSize: '14',
                                    fontFamily: '나눔고딕, NanumGothic, ng, sans-serif'
                            }
                    },
                            events: {
                            onPlay: function (state) {
                            }
                            },
                            autostart: true
                    });
                    }

                    function html5player(qua, lrn_time) {
                    if (qua) {
                    vodname = vodname.replace('.mp4', qua + '.mp4');
                    }
                    var stream_url = server + ':1935/vod/uploads/mp4:' + storage + '/' + vodname +'/playlist.m3u8';
                    $('#vodquality').hide();
                    $('#vodimage').hide();
                    var api = $("#mediaplayer").flowplayer({
                    splash: false,
                            autoplay: true,
                            embed: false, // setup would need iframe embedding
                            ratio: 5 / 12,
                            // manual HLS level selection for Drive videos
                            hlsQualities: "drive",
                            speeds: [0.75, 1, 1.25, 1.5],
                            clip: {
                            sources: [
                            {type: "application/x-mpegurl", src: stream_url}
                            ]
                            }
                    });
                    }
    </script>
    <div id="player_area">
        <div id="mediaplayer">
            <div id="vodimage2">
                <?php
                if ($capture) {
                    if (preg_match('/xenoglobal/', $file->filepath)) {
                        echo '<img src="' . $capture . '"/>';
                    } else {
                        echo '<img src="' . MEDIA . '/uploads/' . $file->filepath . '/' . $capture . '"/>';
                    }
                }
                ?> 
            </div>
            <div id="vodquality">
                <?php
                if ($contents->con_type == 'video' && !preg_match('/xenoglobal/', $file->filepath)) {
                    if (!$file->fileoname) {
                        ?>
                        <a href="#" class="orange_btn full"><?php echo get_string('videotranscoding', 'local_repository'); ?></a>
                        <?php
                    } else {
                        ?>
                        <a href="#vod_play" class="orange_btn left" onclick="videoplayer('_sd',<?php echo $lrn_time; ?>);"><?php echo get_string('lowq', 'local_repository'); ?></a>
<!--                        <a href="#vod_play" class="blue_btn right" onclick="videoplayer('_hd',<?php echo $lrn_time; ?>);"><?php echo get_string('highq', 'local_repository'); ?></a>-->
                        <?php
                    }
                } else {
                    ?>
                    <a href="#vod_play" class="blue_btn full" onclick="videoplayer('',<?php echo $lrn_time; ?>);"><?php echo get_string('viewvideo', 'lcms'); ?></a>
    <?php } ?>
            </div>
        </div>
    </div>
    <?php
    /* 동영상 콘텐츠 보기
      $viewer_url = 'viewer/video_player.php?id='.$data->con_id;
      $attfile .= '<iframe id="vod_viewer" src="'.$viewer_url.'"></iframe>';
      $output_lcms .= html_writer::tag('div', $attfile, array('class' => ""));
     */
} else if ($data->con_type == 'embed') {

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
        } else if($contents->embed_type == 'vimeo'){

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
            } else {
                $output_lcms .= '<input type="button" class="btn_st01" onclick="window.open(\''.$contents->embed_code.'\',\'\',\'\')" value="콘텐츠 바로가기" />';
            }
        }
    }

    $output_lcms .= html_writer::end_tag('div');

//강의설명표시
    if (!empty($data->con_des)) {
        $output_lcms .= html_writer::tag('div', strip_tags($data->con_des), array('class' => 'detail-contents'));
    }

//자막파일 표시
    if (!empty($subtitles->list)) {
        $output_lcms .= html_writer::tag('div', $subtitles->list, array('class' => 'detail-contents caption'));
    }

    $output_lcms .= html_writer::end_tag('div');

    echo $output_lcms;
    ?>

<div class="table-footer-area">
    <div class="btn-area btn-area-left">
        <?php if (!$ref) { ?>
            <button class="btn_st01" onclick="location.href = 'write.php?mode=reply&id=<?php echo $id; ?>'"><?php echo get_string('referenceadd', 'local_repository'); ?></button>
        <?php } else { ?>
            <button class="btn_st01" onclick="location.href = 'detail.php?id=<?php echo $ref; ?>'"><?php echo get_string('back', 'local_repository'); ?></button>
        <?php } ?>
    </div>
    <div class="btn-area btn-area-right">
        <?php
        if ($data->con_type == 'html') {

            $ftp_server = get_config('local_repository', 'ftp_server');
            $ftp_user_name = get_config('local_repository', 'ftp_user');
            $ftp_user_pass = get_config('local_repository', 'ftp_pw');
            $conn_id = ftp_connect($ftp_server);

            $login_result = ftp_login($conn_id, $ftp_user_name, $ftp_user_pass);
            $buff = ftp_rawlist($conn_id, '.');
            ftp_pasv($conn_id, true);

            $files = ftp_nlist($conn_id, "/" . $data->con_id);
            $file = $files[0];

            ftp_close($conn_id);
            ?>
            <form enctype="multipart/form-data" onsubmit="return chk_form();" id="ftp_form" method="post" action="ftp_vod.php">
                <span>FTP 모바일 동영상 업로드</span>
                <input type="hidden" name="id" value="<?php echo $id; ?>">
                <input type="hidden" name="dir" value="<?php echo $data->con_id; ?>">
                <input type="file"  name="ftp_file" ><span style="color:red;"><?php if ($file) { ?><a href="mobile_html.php?id=<?php echo $data->con_id; ?>" target="_blank">[<?php echo $file; ?>]</a><?php } ?></span>
                <input type="submit" class="btn_st01" value="업로드" >
            </form>
            <script type="text/javascript">
                function chk_form(){
                var filename = $('input[name=ftp_file]').val();
                if (filename){
                var ext = /[^.]+$/.exec(filename)[0];
                } else {
                alert('파일을 등록해주세요.');
                $('input[name=ftp_file]').focus();
                return false;
                }
                if (ext.toLowerCase() == 'mp4'){
                return true;
                } else {
                alert('파일의 확장자가 mp4여야 합니다.');
                return false;
                }
                alert(ext);
                return false;
                }
            </script>
        <?php } 
            
        ?>
            <script>
                /**
                 * 좋아요 카운트 ajax
                 */
            function like_ajax() {
        $.ajax({
            url: 'like.ajax.php',
            method: 'POST',
            data: {
                'instance': '<?php echo $id; ?>',
            },
            success: function (data) {
                $("#like").val(data);
            },
            error: function (jqXHR, textStatus, errorThrown) {
                //console.log(jqXHR.responseText);
            }
        });
        
    }
            /**
            * 좋아요 스타일 교체
             */
            function changeimg() {
            if($('#like').css("background-image").match("like_b")=='like_b'){
                $("#like").css({"background":"#316ede url(./pix/like_w.png) no-repeat center left 17px","background-size":"17px",  "color":"white"});
            }else{
                $("#like").css({"background":" url(./pix/like_b.png) no-repeat center left 17px","background-size":"17px",  "color":"#000"});
            }

    }
            </script>
            <?php 
            //현재유저 좋아요 눌렀는지 확인
            $usql = "select likey from {lcms_like} where instance = :instance and userid = :userid ";
            $userlog = $DB->get_field_sql($usql,array('instance'=>$id,'userid'=>$USER->id));
            //전체 좋아요 수 value
            $sql = "select sum(likey) as sum from {lcms_like} where instance = :instance group by instance";
            $result = $DB->get_field_sql($sql,array('instance'=>$id));
            if($userlog==1){
                $color='white';
                $backcolor='#316ede';
                $imgurl = './pix/like_w.png';   
            }else{
                $color='black';
                $backcolor='"#fff"';
                $imgurl = './pix/like_b.png'; 
            }
            if(empty($result)){
                   $result = 0;
            }
            ?>
            <!-- 좋아요 버튼-->
            <input type="button" style="background-image: url('<?php echo $imgurl?>');background-repeat: no-repeat;vertical-align: middle;color:<?php echo $color;?>;background-size:17px;background-position: center left 17px;padding-left: 40px !important;font-weight: normal;background-color: <?php echo $backcolor?>;" onclick="like_ajax();changeimg();" class='like' id="like" name="like" value="<?php echo $result?>">
            
        <button class="btn_st01" onclick="location.href = 'write.php?mode=edit&id=<?php echo $id; ?>&ref=<?php echo $ref; ?>'"><?php echo get_string('detail:edit', 'local_repository'); ?></button>
        <button class="btn_st01" onclick="if (confirm('<?php echo get_string('delete_content', 'local_repository'); ?>')){ location.href = 'delete_contents.php?id=<?php echo $id; ?>&ref=<?php echo $ref; ?>' }"><?php echo get_string('detail:delete', 'local_repository'); ?></button>
        <button class="btn_st01" onclick="location.href = 'index.php?userid=<?php echo $userid; ?>'"><?php echo get_string('detail:list', 'local_repository'); ?></button>
    </div>
</div> 
<?php if (!$ref) { ?>
    <table cellpadding="0" cellspacing="0" class="generaltable">
        <caption><?php echo get_string('reference', 'local_repository'); ?></caption>
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
                    <td scope="col"><?php echo $num; ?></td>
                    <td scope="col"><a href="detail.php?id=<?php echo $file->id; ?>&ref=<?php echo $data->id; ?>"><?php echo $file->con_name; ?></a></td>
                    <td scope="col" class="number"><?php echo date('Y.m.d', $file->update_dt) ?></td>
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
<?php
if ($data->iscdms) {
    if ($data->status != 2 && is_siteadmin()) {
        echo '<input type="button" class="btn_st01" onclick="status_change(2,' . $data->id . ')" value="승인" />';
    }
    if ($data->status != 3 && is_siteadmin()) {
        echo '<input type="button" class="btn_st01" onclick="status_change(3,' . $data->id . ')" value="보류" />';
    }
    echo '<input type="button" class="btn_st01" onclick="location.href=\'cdms_list.php?status=' . $data->status . '\'" value="콘텐츠 공정으로 돌아가기" />';
}
?>

<script type="text/javascript" src="<?php echo $CFG->wwwroot; ?>/mod/lcms/player/js/jwplayer.js"></script>
<?php include_once 'loading.php'; ?>
<script type="text/javascript">

            function load_viewer_popup(id, qua){

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
                    success: function(data) {

                    $('body').css({'overflow':'hidden'});
                    tag.html(data).dialog({
                    title: '<?php echo $data->con_name; ?>',
                            modal: true,
                            width: width,
                            height: height,
                            close: function () {
                            //if($('video').length==0) jwplayer('mediaplayer').pause(); 
                            $(this).dialog('destroy').remove();
                            $('body').css({'overflow':'auto'});
                            }
                    }).dialog('open');
                    }

            });
            }

            function close_viewer_popup(){
            $('#viewer_popup').dialog('destroy').remove();
            $('body').css({'overflow':'auto'});
            }
            function status_change(status, id) {
            if (status == 3){
            var msg = prompt('보류사유');
            } else {
            var msg = '';
            }
            $.ajax({
            method: "POST",
                    url: "./cdms/status_change.ajax.php",
                    data: {id: id, status: status, msg:msg}
            })
                    .done(function (html) {
                    location.reload();
                    });
            }

            function caption_popup(id, name) {
            var tag = $("<div id='caption_popup'></div>");
            $.ajax({
            url: '<?php echo $CFG->wwwroot . '/local/repository/captionpopup.php' ?>',
                    data: {
                    id: id,
                            name: name
                    },
                    success: function(data) {
                    tag.html(data).dialog({
                    title: name,
                            modal: true,
                            width: 800,
                            resizable: false,
                            height: 400,
                            close: function () {
                            $('#frm_search_category').remove();
                            $(this).dialog('destroy').remove()
                            }
                    }).dialog('open');
                    }
            });
            }
</script>

<?php
echo $OUTPUT->footer();
?>







