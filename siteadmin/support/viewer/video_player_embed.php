<?php
require_once '../../../config.php';
require_once $CFG->dirroot.'/local/repository/config.php';
require_once '../lib.php';

$id = required_param('id', PARAM_INT);
$context = context_system::instance();

$PAGE->set_url('/local/repository/viewer/video_player_embed.php', array('id' => $id));

if ($id) {
    if (!$contents = $DB->get_record('lcms_contents', array('id' => $id))) {
        print_error('lcms contents is incorrect');
    }
} else {
    print_error('missingparameter');
}

require_login();

if ($contents->embed_type == 'youtube') {

    $embs = explode('/', $contents->embed_code);
    $emb_code = trim(str_replace("watch?v=", "", $embs[sizeof($embs) - 1]));
    $emb1 = explode('&', $emb_code);
    $cid = trim($emb1[0]);
    $img_nm = 'http://img.youtube.com/vi/' . $cid . '/mqdefault.jpg';
}

if ($contents->embed_type == 'vimeo') {

    $embs = explode('/', $contents->embed_code);
    $cid = trim($embs[sizeof($embs) - 1]);
    $embinfo = vimeoinfo($cid);
    $img_nm = $embinfo->thumbnail;
}

$auto_play = 0; //자동재생여부: 1이면 자동재생

//자막파일영역
$subtitles = get_subtitle_list($context->id,$contents->id);

?>
<!DOCTYPE html>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="viewport" content="width=device-width,   initial-scale=1.0, minimum-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <link rel="stylesheet" type="text/css" href="jquery-ui-1.10.3.custom.css" />
        <link rel="stylesheet" href="../styles.css" />
        <script type="text/javascript" src="jquery-1.10.2.min.js"></script>
        <script type="text/javascript" src="jquery-ui-1.10.3.custom.min.js"></script>
        <script type="text/javascript" src="jwplayer/js/jwplayer.js"></script>
        <?php if ($contents->embed_type == 'youtube') { ?>
            <script type="text/javascript" src="player/youtube.js"></script>
        <?php } else if ($contents->embed_type == 'vimeo') { ?>
            <script src="https://f.vimeocdn.com/js/froogaloop2.min.js"></script>
        <?php } ?>
        <script type="text/javascript">
            var height = '500';
            
            function videoplayer(type) {
                
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
                if(type == 'youtube'){
                    if (browser.ipod || browser.iphone || browser.ipad || browser.android) {
                        html5player();
                        height = '200';
                    } else {
                        flashplayer('<?php echo $auto_play;?>');
                    }
                }else{
                    embedplayer();
                }
            }

            function flashplayer(auto) {
                var ccmark = '';
                jwplayer('mediaplayer').setup({
                    'flashplayer': '<?php echo $CFG->wwwroot; ?>/local/repository/viewer/jwplayer/swf/player.swf',
                    'id': 'mediaplayer',
                    'menu': 'false',
                    'width': '100%',
                    'height': height,
                    'skin': '<?php echo $CFG->wwwroot; ?>/local/repository/viewer/jwplayer/skins/darkrv5.zip',
                    'file': 'http://www.youtube.com/watch?v=<?php echo $cid;?>',
                    'plugins': {
                        '<?php echo $CFG->wwwroot; ?>/local/repository/viewer/jwplaye/js/textoutput.js': {'text': ccmark},
                        'captions-2': {
                            files: '<?php echo $subtitles->path_ko; ?>,<?php echo $subtitles->path_en; ?>',
                            labels: 'Korean,English',
                            fontSize: '14',
                            fontFamily: '나눔고딕, NanumGothic, ng, sans-serif'
                         }
                     },
                     'autostart': auto
                 });
             }

             function html5player() {
                 var status_num = '<?php echo $status_num; ?>';
                 YouTube_Init("vodplayer", "100%", "200", "<?php echo $cid; ?>", 0);
             }
             
             function embedplayer(){
                var videolist = '<iframe src="//player.vimeo.com/video/<?php echo $cid;?>" width="100%" height="300" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
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

<script>videoplayer('<?php echo $contents->embed_type; ?>');</script>