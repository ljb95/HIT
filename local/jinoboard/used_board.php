<ul class="board-list">
<?php
$sql = "select count(*) from {jinoboard_contents} jc "
        . "join {jinoboard_used_board} ub on ub.contentid = jc.id" . $mlike
        . " where jc.board = :board " . $like . " and jc.isnotice = 0 order by ref DESC, step ASC";
$totalcount = $DB->count_records_sql($sql, array('board' => $board->id, 'search' => '%' . $search . '%'));
$total_pages = jinoboard_get_total_pages($totalcount, $perpage);
$num = $totalcount - $offset;

$sql = "select * from {jinoboard_contents} jc "
        . " join {jinoboard_used_board} ub on ub.contentid = jc.id" . $mlike
        . " where jc.board = :board " . $like . " and jc.isnotice = 0 order by ref DESC, step ASC";
//$sql = "select * from {jinoboard_contents} where board = :board " . $like . " and isnotice = 0 order by ref DESC, step ASC";
$contents = $DB->get_records_sql($sql, array('board' => $board->id, 'search' => '%' . $search . '%'), $offset, $perpage);
if ($board->allownotice == 1) {
    $sql = "select * from {jinoboard_contents} where board = :board " . $like . " and isnotice = 1 order by ref DESC, step ASC";
    $notices = $DB->get_records_sql($sql, array('board' => $board->id, 'search' => '%' . $search . '%'));
    foreach ($notices as $notice) {
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'local_jinoboard', 'attachment', $notice->id, 'timemodified', false);
        if (count($files) > 0) {
            $filecheck ='<img src="'.$CFG->wwwroot.'/local/jinoboard/pix/icon-attachment.png" alt="'.get_string('content:file', 'local_jinoboard').'">';
        }else {
            $filecheck ="";
        }

        echo "<li class='isnotice'>";
        echo "<span class='post-title'><a href='" . $CFG->wwwroot . "/local/jinoboard/detail.php?id=" . $notice->id . "&page=".$page."&perpage=".$perpage."'>" . $notice->title . "</a>&nbsp;".$filecheck."</span>";
        echo "<span class='post-date area-right'>" . date("Y-m-d", $notice->timemodified) . "</span>";
        //echo "<span class='post-viewcnt'>" . $notice->viewcnt . "</span>";
        echo "</li>";
    }
}
foreach ($contents as $content) {
    $completion = '';
        if($content->completion == 1){
            $completion = '['.get_string('completion','local_jinoboard').']';
        }
        $list_num++;
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'local_jinoboard', 'attachment', $content->contentid, 'timemodified', false);
        if (count($files) > 0) {
            $filecheck ='<img src="'.$CFG->wwwroot.'/local/jinoboard/pix/icon-attachment.png" alt="'.get_string('content:file', 'local_jinoboard').'">';
        }else {
            $filecheck ="";
        }
        if($content->lev){
            $step = $content->lev-1;
            $step_left_len = $step*15; 
            $date_left_len = ($step+1) * 20;
            $date_left = 'style="padding-left:' . $date_left_len . 'px;"';
            $step_left = 'style="margin-left:'.$step_left_len.'px;"';
            $step_icon = '<img src="'.$OUTPUT->pix_url('icon_reply', 'mod_jinotechboard').'" alt="" /> ';
        }else{
            $step_left = '';
            $step_icon = '';
        }
        
        $sql = "select count(*) from {jinoboard_comments} jc "
        . " where jc.board = ".$board->id." and jc.contentsid =".$content->contentid;
        $comments_count = $DB->count_records_sql($sql);
        
        $postuser = $DB->get_record('user', array('id' => $content->userid));
        $fullname = fullname($postuser);
        $first_string = mb_substr($fullname,0,1,'utf-8');
        $userdate = userdate($content->timecreated);   
        $by = new stdClass();
        $strcnt = mb_strlen($fullname,'utf-8');
        $star = '';
        if($board->type == 7){
            $masterid = 'yongreen';
        }
        for($i=1; $i<$strcnt; $i++)$star .= '*';
        $by->name = $first_string.$star;
        $by->date = $userdate;
        if($postuser->username == $masterid){
            $by->name = $fullname;
        }
        echo "<li><span class='post-title' ".$step_left.">".$step_icon;
        if ($content->issecret && $USER->id != $content->userid && !is_siteadmin()) {
            echo $content->title;
        } else {
            echo "<a href='" . $CFG->wwwroot . "/local/jinoboard/detail.php?id=" . $content->contentid . "&page=".$page."&perpage=".$perpage."&list_num=".$list_num."&search=".$search."&market=".$market."&type=".$type."&searchfield=".$searchfield."'>" .$completion. $content->title . "</a>";
        }
        echo "  ".$filecheck;
        if ($content->issecret) {
           echo "<img src='" . $CFG->wwwroot . "/local/jinoboard/pix/lock.png' width='15' height='15' alt='" . get_string('secreticon', 'local_jinoboard') . "' title='" . get_string('secreticon', 'local_jinoboard') . "'>";
        }

        echo '<br/><span class="post-date" '.$date_left.'>'.get_string("bynameondate", "local_jinoboard", $by).'</span>';
        echo "</span>";
        echo "<span class='post-viewinfo area-right reply_books'>" . $comments_count . "<br/><span>".get_string('reply:cnt', 'mod_jinotechboard')."</span></span>";
        echo "<span style='margin-right:15px;' class='post-viewinfo area-right'>" . $content->viewcnt . "<br/><span>".get_string('viewcount','local_jinoboard')."</span></span>";
        echo "</li>";
        $num--;
    }
    ?>
</ul>