<?php
require_once '../../config.php';


$context = context_system::instance();
$PAGE->set_context($context);

$course = required_param('course', PARAM_INT);// 강좌 아이디
$forum = required_param('forum', PARAM_INT);//코스모듈 아이디
$discussion = required_param('discussion', PARAM_INT);//코스 아이디
$post = required_param('post', PARAM_INT);//코스 아이


// 현재유저가 좋아요를 한적이 있는지 확인
$usql = "select * from {forum_like} where forum = :forum and discussion = :discussion and post = :post and userid = :userid";
$userlog = $DB->get_record_sql($usql,array('forum'=>$forum,'discussion'=>$discussion,'post'=>$post,'userid'=>$USER->id));
$data = new stdClass();
if($userlog){
    //update
    if($userlog->likey==0){
        $data->id = $userlog->id;
        $data->likey = 1;
        $DB->update_record('forum_like', $data);
    }else{
        $data->id =$userlog->id;
        $data->likey = 0;
        $DB->update_record('forum_like', $data);
    }
    
}else{
    //insert
    $data = new stdClass();
    $data->userid = $USER->id;
    $data->course = $course;
    $data->forum = $forum;
    $data->discussion = $discussion;
    $data->post = $post;
    $data->likey = 1;
    
    $DB->insert_record('forum_like', $data);
}


//총 좋아요 수 data
$likesql = "select sum(likey) as sum from {forum_like} where forum = :forum and discussion = :discussion and post = :post";
$result = $DB->get_field_sql($likesql,array('forum'=>$forum,'discussion'=>$discussion,'post'=>$post));

echo $result;