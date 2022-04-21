<?php
require_once '../../../config.php';
require_once $CFG->dirroot.'/local/repository/config.php';
require_once '../lib.php';

$id = required_param('id', PARAM_INT);
$qua = optional_param('qua', 0, PARAM_INT);

if ($id) {
    if (!$contents = $DB->get_record('lcms_contents', array('id' => $id))) {
        print_error('lcms contents is incorrect');
    }
    if (!$file = $DB->get_record('lcms_contents_file', array('con_seq' => $id))) {
        print_error('lcms contents file is incorrect');
    }
} else {
    print_error('missingparameter');
}

$file->filename = str_replace('.mp4', '', $file->filename);
if ($file->filepath == 'media') {
    $file->filedir = $file->filename;
} else {
    $file->filedir = $file->filepath . '/' . $file->filename;
    //$storage = ($contents->share_yn=='N')? 'media':'contents'; 
    $img_nm = 'http://' . TRANS_THUMB . '/'.$file->user_no.'/' . $file->filename . '.jpg'; //이미지경로
}

//if (file_exists($img_nm)) {
    $capture = $img_nm;
//}

$cid = $file->filedir; //기존미디어일경우 경로 잡아야함..
$lrn_time = 0; //이어보기여부
$auto_play = 1; //자동재생여부: 1이면 자동재생
$interval_num = ''; //재생정보를 저장하는 간격
$status_num = '02'; //01:로그쌓기, 02:복습
$return_url = ''; //30초마다 되돌릴 url
$param1 = $id; //id값을 넘김
$param2 = '2';
$param3 = '3';
$opened = ($contents->share_yn == 'N') ? 1 : 0;
if ($opened == 1) {
    $current = date('m/d/Y');
    $userid = $USER->id;
    $key = $userid . '/' . $current;
    $key_code = sha1($key);
}
?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width,  target-densityDpi=device-dpi, initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <link rel="stylesheet" type="text/css" href="jquery-ui-1.10.3.custom.css" />
        <link rel="stylesheet" href="../styles.css" />
        <script type="text/javascript" src="jquery-1.10.2.min.js"></script>
        <script type="text/javascript" src="jquery-ui-1.10.3.custom.min.js"></script>
        <script type="text/javascript" src="player/flash.js"></script>
    </head>
    <body style="margin:0; padding: 0;">

        <div id="vodplayer"></div>
        <div id="vodimage">
            <?php
            if ($capture) {
                echo '<img src="' . $capture . '"/>';
            }
            ?> 
        </div>
        <div id="vodquality">
            <?php
            if ($contents->con_type == 'video' && $file->filepath != 'media') {
                if (!$file->fileoname) {
                    ?>
                    <a href="#" class="orange_btn full"><?php echo get_string('videotranscoding', 'local_repository'); ?></a>
                    <?php
                } else {
                    ?>
                    <a href="#vod_play" class="orange_btn left" onclick="videoplayer('_l',<?php echo $lrn_time; ?>);"><?php echo get_string('lowq','local_repository'); ?></a>
                    <a href="#vod_play" class="blue_btn right" onclick="videoplayer('_h',<?php echo $lrn_time; ?>);"><?php echo get_string('highq','local_repository'); ?></a>
                    <?php
                }
            } else {
                ?>
                <a href="#vod_play" class="blue_btn full" onclick="videoplayer('',<?php echo $lrn_time; ?>);"><?php echo get_string('viewvideo', 'lcms'); ?></a>

            <?php } ?>
        </div>
        <script type="text/javascript">

            function videoplayer(qua, lrn_time) {

                $('#vodimage').hide();
                $('#vodquality').hide();

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
                    html5player(qua, lrn_time, '<?php echo $opened; ?>');
                } else {
                    flashplayer('<?php echo $auto_play; ?>', qua);
                }
            }

            function flashplayer(auto, qua) {
                var ff = mf("player/vod_player_lms.swf?cid=<?php echo $cid; ?>" + qua + "&auto_play=" + auto + "&lrn_time=<?php echo $lrn_time; ?>&interval_num=<?php echo $interval_num; ?>&status_num=<?php echo $status_num; ?>&img_nm=<?php echo $img_nm; ?>&param1=<?php echo $param1; ?>&param2=<?php echo $param2; ?>&param3=<?php echo $param3; ?>&return_url=<?php echo $return_url; ?>&opened=<?php echo $opened ?>&key_code=<?php echo $key_code; ?>", "player", "100%", 500, "none");
                setcode(document.getElementById('vodplayer'), ff);
            }

            function html5player(qua, lrn_time, opened) {
                var stream_url = '';
                if (opened == '0')
                    stream_url = 'http://<?php echo MEDIA; ?>:1935/public/_definst_/mp4:<?php echo $cid; ?>' + qua + '.mp4/playlist.m3u8';
                else
                    stream_url = 'http://<?php echo MEDIA; ?>:1935/media/_definst_/mp4:md1/<?php echo $cid; ?>' + qua + '.mp4/playlist.m3u8?key_code=<?php echo $key_code; ?>';
                var ff = '<video id="vod_player" preload controls src="' + stream_url + '">' +
                        '</video>';
                setcode(document.getElementById('vodplayer'), ff);
            }

        </script>

    </body>
</html>