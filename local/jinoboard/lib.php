<?php

/**
 * 
 * @global type $DB
 * @global type $USER
 * @param type $data
 * @param type $type
 * @param type $context
 */
function add_new_content($data, $type, $context, $mode, $parent) {
	global $DB, $USER;
	
	$content = new stdClass();
	$board = $DB->get_record('jinoboard',array('type'=>$type));
	$content->board = $board->id;
	$content->category = 0;
	if ($type == 3) {
		$content->category = $data->category;
	}
	$content->title = $data->name;
	$content->contents = $data->contents;
	$content->userid = $USER->id;
	$content->ref = 0;
	$content->step = 0;
	$content->lev = 0;
	if ($mode == "reply") {
		$content->ref = $parent->ref;
		$content->step = $parent->step + 1;
		$content->lev = $parent->lev + 1;
                $query = 'update {jinoboard_contents} set step = step + 1 where ref = :ref and step > :step';
		$DB->execute($query, array('ref'=>$parent->ref, 'step'=>$parent->step));
	}
        $data->isnotice = isset($data->isnotice)?$data->isnotice:0;
        $data->issecret = isset($data->issecret)?$data->issecret:0;
        $content->isnotice = ($board->allownotice && $data->isnotice) ? $data->isnotice : 0;
	$content->issecret = ($board->allowsecret && $data->issecret) ? $data->issecret : 0;

	$content->viewcnt = 0;
	$content->itemid = $content->contents['itemid'];
	$content->timecreated = time();
	$content->timemodified = time();
        if($data->availablefromenabled) $content->timeend =  $data->timeend+86399;
	if ($newid = $DB->insert_record('jinoboard_contents', $content)) {
            if($board->type == 7){
                $mcontent = new stdClass();
                $mcontent->board = $board->id;
                $mcontent->contentid = $newid;
                $mcontent->completion = 0;
                $mcontent->status = $data->purpose;
                $mcontent->timecreated = time();
                $mcontent->timemodified = time();
                $newid2 = $DB->insert_record('jinoboard_used_board', $mcontent);
            }
		$content->id = $newid;
		if ($mode != "reply") {
			$DB->set_field_select('jinoboard_contents', "ref", $newid, "id=$newid");
		}
		$content->contents = file_save_draft_area_files($content->itemid, $context->id, 'local_jinoboard', 'contents', $content->id, jinoboard_editor_options($context, $content->id), $content->contents['text']);
		$DB->set_field('jinoboard_contents', 'contents', $content->contents, array('id' => $content->id));
		
		$draftitemid = file_get_submitted_draft_itemid('attachments');
		file_save_draft_area_files($draftitemid, $context->id, 'local_jinoboard', 'attachment', $content->id);
	}
}
function add_new_uncorecontent($data, $bid, $context, $mode, $parent) {
	global $DB, $USER;
	
	$content = new stdClass();
	$board = $DB->get_record('jinoboard',array('id'=>$bid));
	$content->board = $board->id;
	$content->category = 0;
	if (!empty($content->category)) {
            $content->category = $data->category;
	}
	$content->title = $data->name;
	$content->contents = $data->contents;
	$content->userid = $USER->id;
	$content->ref = 0;
	$content->step = 0;
	$content->lev = 0;
	if ($mode == "reply") {
		$content->ref = $parent->ref;
		$content->step = $parent->step + 1;
		$content->lev = $parent->lev + 1;
                $query = 'update {jinoboard_contents} set step = step + 1 where ref = :ref and step > :step';
		$DB->execute($query, array('ref'=>$parent->ref, 'step'=>$parent->step));
	}
        $data->isnotice = isset($data->isnotice)?$data->isnotice:0;
        $data->issecret = isset($data->issecret)?$data->issecret:0;
        $content->isnotice = ($board->allownotice && $data->isnotice) ? $data->isnotice : 0;
	$content->issecret = ($board->allowsecret && $data->issecret) ? $data->issecret : 0;

	$content->viewcnt = 0;
	$content->itemid = $content->contents['itemid'];
	$content->timecreated = time();
	$content->timemodified = time();
        if($data->availablefromenabled) $content->timeend =  $data->timeend+86399;
	if ($newid = $DB->insert_record('jinoboard_contents', $content)) {
		$content->id = $newid;
		if ($mode != "reply") {
			$DB->set_field_select('jinoboard_contents', "ref", $newid, "id=$newid");
		}
		$content->contents = file_save_draft_area_files($content->itemid, $context->id, 'local_jinoboard', 'contents', $content->id, jinoboard_editor_options($context, $content->id), $content->contents['text']);
		$DB->set_field('jinoboard_contents', 'contents', $content->contents, array('id' => $content->id));
		
		$draftitemid = file_get_submitted_draft_itemid('attachments');
		file_save_draft_area_files($draftitemid, $context->id, 'local_jinoboard', 'attachment', $content->id);
	}
}

function edit_uncore_content($data, $bid, $context) {
	global $DB, $USER;
        $board = $DB->get_record('jinoboard',array('id'=>$bid));
	$content = new stdClass();

	$content->id = $data->id;

	$content->category = 0;
	if (!empty($content->category)) {
            $content->category = $data->category;
	}
	$content->title = $data->name;
	$content->itemid = $data->contents['itemid'];
	$content->contents = file_save_draft_area_files($content->itemid, $context->id, 'local_jinoboard', 'contents', $content->id, jinoboard_editor_options($context, $content->id), $data->contents['text']);
	$content->timemodified = time();
        if($data->availablefromenabled) $content->timeend = $data->timeend+86399;
        $data->isnotice = isset($data->isnotice)?$data->isnotice:0;
        $data->issecret = isset($data->issecret)?$data->issecret:0;
        $content->isnotice = ($board->allownotice && $data->isnotice) ? $data->isnotice : 0;
	$content->issecret = ($board->allowsecret && $data->issecret) ? $data->issecret : 0;
        
	$newid = $DB->update_record('jinoboard_contents', $content);
	
	$draftitemid = file_get_submitted_draft_itemid('attachments');
    file_save_draft_area_files($draftitemid, $context->id, 'local_jinoboard', 'attachment', $content->id);
}
function edit_content($data, $type, $context) {
	global $DB, $USER;
        $board = $DB->get_record('jinoboard',array('type'=>$type));
	$content = new stdClass();

	$content->id = $data->id;

	$content->category = 0;
	if ($type == 3) {
		$content->category = $data->category;
	}
	$content->title = $data->name;
	$content->itemid = $data->contents['itemid'];
	$content->contents = file_save_draft_area_files($content->itemid, $context->id, 'local_jinoboard', 'contents', $content->id, jinoboard_editor_options($context, $content->id), $data->contents['text']);
	$content->timemodified = time();
        if($data->availablefromenabled) $content->timeend = $data->timeend+86399;
        $data->isnotice = isset($data->isnotice)?$data->isnotice:0;
        $data->issecret = isset($data->issecret)?$data->issecret:0;
        $content->isnotice = ($board->allownotice && $data->isnotice) ? $data->isnotice : 0;
	$content->issecret = ($board->allowsecret && $data->issecret) ? $data->issecret : 0;
        
	$newid = $DB->update_record('jinoboard_contents', $content);
	
	$draftitemid = file_get_submitted_draft_itemid('attachments');
    file_save_draft_area_files($draftitemid, $context->id, 'local_jinoboard', 'attachment', $content->id);
}

function jinoboard_editor_options($context, $contentid) {
	global $COURSE, $PAGE, $CFG;
// TODO: add max files and max size support
	$maxbytes = get_user_max_upload_file_size($PAGE->context, $CFG->maxbytes, $COURSE->maxbytes);
	return array(
		'maxfiles' => EDITOR_UNLIMITED_FILES,
		'maxbytes' => $maxbytes,
		'trusttext' => true,
		'return_types' => FILE_INTERNAL | FILE_EXTERNAL,
		'subdirs' => file_area_contains_subdirs($context, 'local_jinoboard', 'contents', $contentid)
	);
}

function local_jinoboard_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload) {
	global $CFG, $DB;

	$fileareas = array('attachment', 'contents');
	if (!in_array($filearea, $fileareas)) {
		return false;
	}


	$fs = get_file_storage();
	$relativepath = implode('/', $args);

	$fullpath = "/$context->id/local_jinoboard/$filearea/$relativepath";
	if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
		return false;
	}


	// finally send the file
	send_stored_file($file, 0, 0, true); // download MUST be forced - security!
}

function jinoboard_get_total_pages($rows, $limit = 10) {
	if ($rows == 0) {
		return 1;
	}

	$total_pages = (int) ($rows / $limit);

	if (($rows % $limit) > 0) {
		$total_pages += 1;
	}

	return $total_pages;
}

function jinoboard_get_paging_bar($url, $params, $total_pages, $current_page, $market, $max_nav = 10) {
	$total_nav_pages = jinoboard_get_total_pages($total_pages, $max_nav);
	$current_nav_page = (int) ($current_page / $max_nav);
	if (($current_page % $max_nav) > 0) {
		$current_nav_page += 1;
	}
	$page_start = ($current_nav_page - 1) * $max_nav + 1;
	$page_end = $current_nav_page * $max_nav;
	if ($page_end > $total_pages) {
		$page_end = $total_pages;
	}
        
	if (!empty($params)) {
		$tmp = array();
		foreach ($params as $key => $value) {
			$tmp[] = $key . '=' . $value;
		}
		$tmp[] = "page=";
		$url = $url . "?" . implode('&', $tmp);
	} else {
		$url = $url . "?page=";
	}
	echo html_writer::start_tag('div', array('class' => 'board-breadcrumbs'));
	if ($current_nav_page > 1) {
           // echo '<span class="board-nav-prev"><a class="prev" href="'.$url.(($current_nav_page - 2) * $max_nav + 1).'"><</a></span>';
	} else {
           // echo '<span class="board-nav-prev"><a class="prev" href="#"><</a></span>';
	}
	if ($current_page > 1) {
		echo '<span class="board-nav-prev"><a class="prev" href="'.$url.($current_page - 1).'&market='.$market.'"><</a></span>';
	} else {
		echo '<span class="board-nav-prev"><a class="prev" href="#"><</a></span>';
	}
        echo '<ul>';
	for ($i = $page_start; $i <= $page_end; $i++) {
		if ($i == $current_page) {
			echo '<li class="current"><a href="#">'.$i.'</a></li>';
		} else {
			echo '<li><a href="'.$url.''.$i.'&market='.$market.'">'.$i.'</a></li>';
		}
	}
        echo '</ul>';
	if ($current_page < $total_pages) {
		echo '<span class="board-nav-next"><a class="next" href="'.$url.($current_page + 1).'&market='.$market.'">></a></span>';
	} else {
		echo '<span class="board-nav-next"><a class="next" href="#">></a></span>';
	}
	if ($current_nav_page < $total_nav_pages) {
		//echo '<a class="next_" href="' . $url . ($current_nav_page * $max_nav + 1) . '"></a>';
	} else {
		//echo '<a class="next_" href="#"></a>';
	}
	echo html_writer::end_tag('div');
}

function define_targets() {
    $targets = array('sa'=>'학생전체','m1'=>'1학년','m2'=>'2학년','m3'=>'3학년','m4'=>'4학년','교수'=>'교수');
    return $targets;
}

function define_targets_check() {
    $targets = array('m1'=>'1학년','m2'=>'2학년','m3'=>'3학년','m4'=>'4학년','교수'=>'교수');
    return $targets;
}

function define_perpages() {
    $perpages = array('10','20','30','50');
    return $perpages;
}