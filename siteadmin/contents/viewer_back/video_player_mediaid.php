<?php
require_once '../../../config.php';
require_once $CFG->dirroot.'/local/repository/config.php';
require_once '../lib.php';

$id = required_param('id', PARAM_INT);
$qua = optional_param('qua', 0, PARAM_INT);

$PAGE->set_url('/local/repository/viewer/video_player_mediaid.php', array('id' => $id));

if ($id) {
    if (!$contents = $DB->get_record('lcms_contents', array('id' => $id))) {
        print_error('lcms contents is incorrect');
    }
} else {
    print_error('missingparameter');
}

require_login();

$mdata = mediainfo($contents->embed_code); 
$filename = str_replace('.mp4', '', $mdata[0]->o_file);
$filepath = ($mdata[0]->path)? $mdata[0]->path.'/':'';
$capture = 'http://'.TRANS_THUMB.'/'.$filepath.$filename.'.png';
$cid = $filepath.$filename; //기존미디어일경우 경로 잡아야함..
$lrn_time = 0; //이어보기여부
$auto_play = 1; //자동재생여부: 1이면 자동재생
$interval_num = ''; //재생정보를 저장하는 간격
$status_num = '02'; //01:로그쌓기, 02:복습
$return_url = ''; //30초마다 되돌릴 url
$param1 = $id; //id값을 넘김
$param2 = '2';
$param3 = '3';
$opened = $mdata[0]->opened;
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
    <body style="margin:0;">

        <div id="vodplayer"></div>
        <div id="vodimage">
            <?php
            if ($capture) {
                echo '<img src="' . $capture . '"/>';
            }
            ?> 
        </div>
        <div id="vodquality">
            <a href="#vod_play" class="blue_btn full" onclick="videoplayer('',<?php echo $lrn_time; ?>);"><?php echo get_string('viewvideo', 'lcms'); ?></a>
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