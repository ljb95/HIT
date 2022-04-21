<?php
require_once '../../../config.php';
require_once $CFG->dirroot.'/local/repository/config.php';
require_once '../lib.php';

$id = required_param('id', PARAM_INT);

$PAGE->set_url('/local/repository/viewer/video_player_embed.php', array('id' => $id));

if ($id) {
    if (!$contents = $DB->get_record('lcms_contents', array('id' => $id))) {
        print_error('lcms contents is incorrect');
    }
} else {
    print_error('missingparameter');
}

// require_login();

if ($contents->embed_type == 'youtube') {

        $embs = explode('/', $contents->embed_code);
        $emb_code = trim(str_replace("watch?v=", "", $embs[sizeof($embs) - 1]));
        $emb1 = explode('&', $emb_code);
        $cid = trim($emb1[0]);
        $img_nm = 'http://img.youtube.com/vi/'.$cid.'/0.jpg';
}

if ($contents->embed_type == 'vimeo') {

        $embs = explode('/', $contents->embed_code);
        $cid = trim($embs[sizeof($embs) - 1]);
        $embinfo = vimeoinfo($cid);
        $img_nm = $embinfo->thumbnail;
}

$auto_play = 1; //자동재생여부: 1이면 자동재생
$lrn_time = 0; //이어보기여부
$interval_num = ''; //재생정보를 저장하는 간격
$status_num = '02'; //01:로그쌓기, 02:복습
$return_url = ''; //30초마다 되돌릴 url
$param1 = $id; //id값을 넘김
$param2 = '2';
$param3 = '3';

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
        <?php if ($contents->embed_type == 'youtube') {?>
        <script type="text/javascript" src="player/youtube.js"></script>
        <?php }else if($contents->embed_type == 'vimeo') {?>
        <script src="https://f.vimeocdn.com/js/froogaloop2.min.js"></script>
        <?php }?>
    </head>
    <body style="margin:0;">
        <?php if ($contents->embed_type == 'youtube') {?>
        <div id="vodplayer"></div>
        <?php }else if($contents->embed_type == 'vimeo') {?>
        <iframe id="player1" src="https://player.vimeo.com/video/76979871?api=1&player_id=player1" width="100%" height="100%" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>
        <?php }?>

        <script type="text/javascript">
            
            videoplayer('<?php echo $contents->embed_type;?>','<?php echo $lrn_time;?>');

            function videoplayer(type,lrn_time) {

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
                    html5player(type,lrn_time);
                } else {
                    flashplayer(0,type);
                }
            }

            function flashplayer(auto, type) {
                if(type=='youtube'){
                    var ff = mf("player/youtube_player_lms.swf?cid=<?php echo $cid; ?>&auto_play=" + auto + "&lrn_time=<?php echo $lrn_time; ?>&interval_num=<?php echo $interval_num; ?>&status_num=<?php echo $status_num; ?>&img_nm=<?php echo $img_nm; ?>&param1=<?php echo $param1; ?>&param2=<?php echo $param2; ?>&param3=<?php echo $param3; ?>&return_url=<?php echo $return_url; ?>", "player", "100%", 500, "none");
                    setcode(document.getElementById('vodplayer'), ff);
                }
            }

            function html5player(type, lrn_time) {
                var status_num = '<?php echo $status_num;?>';
                if(type=='youtube') YouTube_Init("vodplayer","100%","200","<?php echo $cid;?>",lrn_time);
            }

        </script>

    </body>
</html>