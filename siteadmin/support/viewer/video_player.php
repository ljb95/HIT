<?php
require_once '../../../config.php';
require_once($CFG->dirroot . "/lib/filelib.php");
require_once $CFG->dirroot.'/local/repository/lib.php';
require_once $CFG->dirroot.'/local/repository/config.php';

require_login();

$id = required_param('id', PARAM_INT);
$qua = optional_param('qua', 0, PARAM_INT);
$context = context_system::instance();

$PAGE->set_url('/local/repository/viewer/video_player.php', array('id' => $id));

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

$auto_play = 0;
$namearray = explode('.',$file->filename);
$n = count($namearray);
unset($namearray[$n-1]);
$capture = implode('',$namearray).'.png';
//자막파일영역
$subtitles = get_subtitle_list($context->id,$contents->id);

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
        <script type="text/javascript" src="<?php echo $CFG->wwwroot; ?>/local/repository/viewer/jwplayer/js/jwplayer.js"></script>
        <script type="text/javascript">
            var server = '<?php echo MEDIA;?>';
            var storage = 'uploads/<?php echo $file->filepath; ?>';
            var vodname = '<?php echo $file->filename; ?>'; 
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
                    flashplayer(<?php echo $auto_play;?>, qua);
                }
            }

            function flashplayer(auto, qua) {

                jwplayer('mediaplayer').setup({
                    'flashplayer': '<?php echo $CFG->wwwroot; ?>/local/repository/viewer/jwplayer/swf/player.swf',
                    'id': 'mediaplayer',
                    'menu': 'false',
                    'width': '100%',
                    'height': height,
                    'skin': '<?php echo $CFG->wwwroot; ?>/local/repository/viewer/jwplayer/skins/darkrv5.zip',
//                    'streamer': 'rtmp://' + server + '/vod/_definst_/',
//                    'type': 'rtmp',
                    'file': server + '/' + storage + '/' + vodname,
                    'image': server + '/' + storage + '/' + imagename,
                    'plugins': {
                        '<?php echo $CFG->wwwroot; ?>/local/repository/viewer/jwplaye/js/textoutput.js': {'text': ccmark},
                        'captions-2': {
                            files: '<?php echo $subtitles->path_ko; ?>,<?php echo $subtitles->path_en; ?>',
                            labels: 'Korean,English',
                            fontSize: '14',
                            fontFamily: '나눔고딕, NanumGothic, ng, sans-serif'
                         }
                     },
                    autostart: auto
                });                
            }

            function html5player(qua, lrn_time) {
                var stream_url = 'http://' + server + ':1935/vod/mp4:' + storage + '/' + vodname + '/playlist.m3u8';
                var videolist = '<video id="vod_player" preload controls src="' + stream_url + '">' +
                        '</video>';
                $('#mediaplayer').empty().append(videolist);
            }

        </script>
    </head>
    <body style="margin:0;overflow:hidden;">
        <div id="player_area">
            <div id="mediaplayer"><p>잠시만 기다려주세요. 로딩중입니다.</p></div>
        </div>
    </body>
</html>
<script>
    videoplayer('', 0);
</script>