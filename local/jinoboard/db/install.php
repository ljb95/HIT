<?php

function xmldb_local_jinoboard_install() {
    global $DB,$USER;
    $maketime = time();
    $records = array(
        array_combine(array('type','allowrental', 'name','engname', 'maxbytes', 'maxattachments', 'allownotice', 'allowreply', 'allowcomment', 'allowupload', 'allowsecret', 'allowcategory', 'allowperiod','status','required','userid', 'timemodified'),
                array(1,0, '공지사항','Notice', 0, 1, 1, 0, 0, 1, 0, 0, 1, 1,1,$USER->id, $maketime)),
        array_combine(array('type','allowrental', 'name','engname', 'maxbytes', 'maxattachments', 'allownotice', 'allowreply', 'allowcomment', 'allowupload', 'allowsecret', 'allowcategory', 'allowperiod','status','required','userid', 'timemodified'), array(2,0, 'QnA','Qna'       , 0, 1, 0, 1, 0, 1, 1, 0, 0, 1,1,$USER->id, $maketime)),
        array_combine(array('type','allowrental', 'name','engname', 'maxbytes', 'maxattachments', 'allownotice', 'allowreply', 'allowcomment', 'allowupload', 'allowsecret', 'allowcategory', 'allowperiod','status','required','userid', 'timemodified'), array(3,0, 'FAQ','FAQ'       , 0, 1, 1, 0, 0, 1, 0, 1, 0, 1,1,$USER->id, $maketime)),
        array_combine(array('type','allowrental', 'name','engname', 'maxbytes', 'maxattachments', 'allownotice', 'allowreply', 'allowcomment', 'allowupload', 'allowsecret', 'allowcategory', 'allowperiod','status','required','userid', 'timemodified'), array(4,0, '도움말','Help'    , 0, 1, 1, 0, 0, 1, 0, 0, 0, 1,1,$USER->id, $maketime)),
        array_combine(array('type','allowrental', 'name','engname', 'maxbytes', 'maxattachments', 'allownotice', 'allowreply', 'allowcomment', 'allowupload', 'allowsecret', 'allowcategory', 'allowperiod','status','required','userid', 'timemodified'), array(5,0, '자료실','storage' , 0, 0, 0, 1, 1, 1, 0, 1, 0, 1,2,$USER->id, $maketime))
    );
    
    foreach ($records as $record) {
        $id = $DB->insert_record('jinoboard', $record);
        $allows = array(
            array_combine(
                    array('allowrole','allowdeletecomment','allowsecret', 'allowview', 'allowdetail', 'allowwrite', 'allowreply', 'allowcomment', 'allowmodify', 'allowdelete', 'allowupload', 'timemodified'), 
                    array('pr'  ,'true','true', 'true', 'true'  , 'true' , 'true' , 'true'   , 'true'  , 'true'  , 'true'  , $maketime)),
            array_combine(
                    array('allowrole','allowdeletecomment','allowsecret', 'allowview', 'allowdetail', 'allowwrite', 'allowreply', 'allowcomment', 'allowmodify', 'allowdelete', 'allowupload', 'timemodified'), 
                    array('ad','true','false', 'true', 'true', 'true', 'true', 'true', 'true', 'true', 'true', $maketime)),
            array_combine(
                    array('allowrole','allowdeletecomment','allowsecret', 'allowview', 'allowdetail', 'allowwrite', 'allowreply', 'allowcomment', 'allowmodify', 'allowdelete', 'allowupload', 'timemodified'), 
                    array('rs','false','false', 'true', 'true', 'false', 'false', 'true', 'false', 'false', 'false', $maketime)),
            array_combine(
                    array('allowrole','allowdeletecomment','allowsecret', 'allowview', 'allowdetail', 'allowwrite', 'allowreply', 'allowcomment', 'allowmodify', 'allowdelete', 'allowupload', 'timemodified'), 
                    array('gu','false','false', 'true', 'true', 'false', 'false', 'false', 'false', 'false', 'false', $maketime)),
            array_combine(
                    array('allowrole','allowdeletecomment','allowsecret', 'allowview', 'allowdetail', 'allowwrite', 'allowreply', 'allowcomment', 'allowmodify', 'allowdelete', 'allowupload', 'timemodified'), 
                    array('sa','true','true', 'true', 'true', 'true', 'true', 'true', 'true', 'true', 'true', $maketime)),
            array_combine(
                    array('allowrole','allowdeletecomment','allowsecret', 'allowview', 'allowdetail', 'allowwrite', 'allowreply', 'allowcomment', 'allowmodify', 'allowdelete', 'allowupload', 'timemodified'), 
                    array('ma','true','true', 'true', 'true', 'true', 'true', 'true', 'true', 'true', 'true', $maketime)),
        );
        foreach ($allows as $allow) {
             $allow['board'] = $id;
             $DB->insert_record('jinoboard_allowd', $allow , false);
        }
    }
}
