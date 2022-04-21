<?php
require_once '../../config.php';
require_once $CFG->libdir.'/completionlib.php';

require_once 'lib.php';

$id = required_param('id',PARAM_INT);


$PAGE->set_pagelayout('standard');
$PAGE->set_url('/local/repository/mobile_html.php', array('id'=>$id));

if ($id) {
    if (!$contents = $DB->get_record('lcms_contents', array('id' => $id))) {
        print_error('course module is incorrect');
    }
} else {
    print_error('missingparameter'); 
}

echo $OUTPUT->header();
?>
<link rel="stylesheet" href="<?php echo $CFG->wwwroot; ?>/local/repository/viewer/flowplayer/skin/skin.css">
<script src="<?php echo $CFG->wwwroot; ?>/local/repository/viewer/flowplayer/flowplayer.min.js"></script>
<script src="<?php echo $CFG->wwwroot; ?>/local/repository/viewer/flowplayer/flowplayer.min.js"></script>
<div id="package">
</div>
    <script>
    $(window).load(function() { 
                        var stream_url = '<?php echo $CFG->vodserver; ?>:1935/vod/uploads/mp4:<?php echo $contents->id; ?>/video.mp4/playlist.m3u8';
                        var api = $("#package").flowplayer({
                            splash: false,
                            autoplay: true,
                            embed: false, // setup would need iframe embedding
                            ratio: 5 / 12,
                            // manual HLS level selection for Drive videos
                            hlsQualities: "drive",
                            // manual VOD quality selection when hlsjs is not supported
                            //defaultQuality: "260p",
                            //qualities: ["160p", "260p", "530p", "800p"],

                            speeds: [0.75, 1, 1.25, 1.5],
                                // configuration common to all players in
                                // containers with class="player" goes here
                            clip: {
                                sources: [
                                    {type: "application/x-mpegurl",src: stream_url}
                                ]
                            }
                        });
    });
</script>

<?php
echo $OUTPUT->footer();