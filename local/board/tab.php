<?php
    if (empty($currenttab)) {
        $currenttab = 'courseNotice';
    }

    $rows = array();
    $row = array();

    $row[] = new tabobject('courseNotice', "$CFG->wwwroot/local/board/notice.php", get_string('course:notice', 'local_board'));
    $row[] = new tabobject('qna', "$CFG->wwwroot/local/board/qna.php", get_string('qna', 'local_board'));
    $rows[] = $row;

    print_tabs($rows, $currenttab);
    
    echo '<br/>';