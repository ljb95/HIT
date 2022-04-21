<?php
require_once("../../config.php");
require_once("./lib.php");

$id = required_param('id', PARAM_INT);
$name = required_param('name', PARAM_RAW);

$context = context_system::instance();
$PAGE->set_context($context);

require_login();

//자막파일영역
$subtitles = get_subtitle_text($context, $id, $name);

if ($subtitles) {
    ?>
<div class="caption_wrap">
    <div class="hope"><?php $name; ?></div>
    <div class="moocs_choice">
        <?php
        foreach ($subtitles as $sub) {
            $starts = explode(',', $sub->starttime);
            echo '<ul>';
            echo '<li class="time on">' . $starts[0] . '</li>';
            echo '<li class="script">' . nl2br($sub->text) . '</li>';
            echo '</ul>';
        }
        ?>
    </div>
</div>
<?php } ?>
