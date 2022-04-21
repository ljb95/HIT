<div id="accordion">
    <?php
    $sql = "select * from {jinoboard_contents} where board = :board " . $like . " and isnotice = 1 order by ref DESC, step ASC";
    $notices = $DB->get_records_sql($sql, array('board' => $board->id, 'search' => '%' . $search . '%'));
        foreach ($notices as $content) {

        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'local_jinoboard', 'attachment', $content->id, 'timemodified', false);
        $attachments = "";
        if (count($files) > 0) {
            //$attachments = '        <div class="attachments" style="text-align:right; padding-bottom:15px">   ';
            $type = '';
            $attfile = '';

            if ($CFG->enableportfolios)
                $canexport = $USER->id == $content->userid;
            if ($canexport) {
                require_once($CFG->libdir . '/portfoliolib.php');
            }
            foreach ($files as $file) {

                $filename = $file->get_filename();
                $mimetype = $file->get_mimetype();
                $iconimage = '<img src="' . $OUTPUT->pix_url(file_mimetype_icon($mimetype)) . '" class="icon" alt="' . $mimetype . '" />';
                $path = file_encode_url($CFG->wwwroot . '/pluginfile.php', '/' . $context->id . '/local_jinoboard/attachment/' . $content->id . '/' . $filename);


                if ($board->id == $CFG->DATAID) {
                    $attfile .= "<a href=\"javascript:alertDistribution('$path');\">$iconimage</a> ";
                    $attfile .= "<a href=\"javascript:alertDistribution('$path');\">" . s($filename) . "</a>";
                } else {
                    $attfile .= "<a href=\"$path\">$iconimage</a> ";
                    $attfile .= format_text("<a href=\"$path\">" . s($filename) . "</a>", FORMAT_HTML, array('context' => $context));
                }

                if ($canexport) {
                    $button = new portfolio_add_button();
                    $button->set_callback_options('jinotechboard_portfolio_caller', array('contentId' => $contentId, 'attachment' => $file->get_id()), '/mod/jinotechboard/locallib.php');

                    $button->set_format_by_file($file);
                    $attfile .= $button->to_html(PORTFOLIO_ADD_ICON_LINK);
                }
                $attfile .= '<br />';
            }

            $attachments .= $attfile;
        }
        ?>
    <ul style="clear:both; background:none; border:0px;" class="isnotice">
            <div class="left except title" style="width:73%; padding-left:15px;">
                <a id="accordion_title" href = '#'><?php echo $content->title; ?></a>
            </div>
            <div class="left tc" style="width:10%; text-align:center;"><?php echo date("Y-m-d", $content->timemodified); ?></div>
        </ul>
        <li style="clear:both; border:0px; padding-left:15px; margin-top:20px; width:100%;">
            <?php
            $content->contents = file_rewrite_pluginfile_urls($content->contents, 'pluginfile.php', $context->id, 'local_jinoboard', 'contents', $content->id);
            
            echo $attachments;
            echo $content->contents;
            ?>
        </li>
        <?php
    }
    foreach ($contents as $content) {

        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'local_jinoboard', 'attachment', $content->id, 'timemodified', false);
        $attachments = "";
        if (count($files) > 0) {
            //$attachments = '        <div class="attachments" style="text-align:right; padding-bottom:15px">   ';
            $type = '';
            $attfile = '';

            if ($CFG->enableportfolios)
                $canexport = $USER->id == $content->userid;
            if ($canexport) {
                require_once($CFG->libdir . '/portfoliolib.php');
            }
            foreach ($files as $file) {

                $filename = $file->get_filename();
                $mimetype = $file->get_mimetype();
                $iconimage = '<img src="' . $OUTPUT->pix_url(file_mimetype_icon($mimetype)) . '" class="icon" alt="' . $mimetype . '" />';
                $path = file_encode_url($CFG->wwwroot . '/pluginfile.php', '/' . $context->id . '/local_jinoboard/attachment/' . $content->id . '/' . $filename);


                if ($board->id == $CFG->DATAID) {
                    $attfile .= "<a href=\"javascript:alertDistribution('$path');\">$iconimage</a> ";
                    $attfile .= "<a href=\"javascript:alertDistribution('$path');\">" . s($filename) . "</a>";
                } else {
                    $attfile .= "<a href=\"$path\">$iconimage</a> ";
                    $attfile .= format_text("<a href=\"$path\">" . s($filename) . "</a>", FORMAT_HTML, array('context' => $context));
                }

                if ($canexport) {
                    $button = new portfolio_add_button();
                    $button->set_callback_options('jinotechboard_portfolio_caller', array('contentId' => $contentId, 'attachment' => $file->get_id()), '/mod/jinotechboard/locallib.php');

                    $button->set_format_by_file($file);
                    $attfile .= $button->to_html(PORTFOLIO_ADD_ICON_LINK);
                }
                $attfile .= '<br />';
            }

            $attachments .= $attfile;
        }
        ?>
        <ul style="clear:both; background:none; border:0px;">
            <div class="left except title" style="width:73%; padding-left:15px;">
                <a id="accordion_title" href = '#'><?php echo $content->title; ?></a>
            </div>
            <div class="left tc" style="width:10%; text-align:center;"><?php echo date("Y-m-d", $content->timemodified); ?></div>
        </ul>
        <li style="clear:both; border:0px; padding-left:15px; margin-top:20px; width:100%;">
            <?php
            $content->contents = file_rewrite_pluginfile_urls($content->contents, 'pluginfile.php', $context->id, 'local_jinoboard', 'contents', $content->id);
            
            echo $attachments;
            echo $content->contents;
            ?>
        </li>
        <?php
    }
    ?>
</div>