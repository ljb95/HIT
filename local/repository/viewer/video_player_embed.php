<?php
require_once '../../../config.php';
require_once '../config.php';
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
        <?php if ($contents->embed_type == 'youtube') { ?>
            <script type="text/javascript" src="player/youtube.js"></script>
        <?php } ?>
        <script type="text/javascript">
            var height = '800';
            
            function videoplayer(type) {
                

                //디바이스별 플레이어 사용
                if(type == 'youtube'){
                     YouTube_Init("mediaplayer","100%","400","<?php echo $emb_code;?>","0");
                }else{
                    embedplayer();
                }
            }

             function embedplayer(){
                var videolist = '<iframe src="https://vimeo.com/video/<?php echo $cid;?>" width="100%" height="300" frameborder="0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>';
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