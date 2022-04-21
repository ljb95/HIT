<?php

require(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
set_include_path('google-api-php-client-master/src/');
require_once 'Google/autoload.php'; // or wherever autoload.php is located
require_once 'Google/Client.php';
require_once 'Google/Service/YouTube.php';

if (!empty($_REQUEST)) {
    foreach ($_REQUEST as $key => $val) {
        ${$key} = $val;
    }
}

$DEVELOPER_KEY = 'AIzaSyCaeZ2Fbx61Ov1_I8OVLj1rO4EtPQDbDnM';
$keyword = $search;
 
    $client = new Google_Client();
    $client->setDeveloperKey($DEVELOPER_KEY);
    $youtube = new Google_Service_YouTube($client);
    $param = array( 'q' => $keyword,
        'maxResults' => 6,
        'order'=>'viewCount',
        'type'=>'video'
    );
    
    $searchResponse = $youtube->search->listSearch('id,snippet',$param);
    if($nexttoken){
        $param['pageToken'] = $nexttoken;
    }
    if($prevtoken){
        $param['pageToken'] = $prevtoken;
    }
    $searchResponse = $youtube->search->listSearch('id,snippet',$param);
    $videos = '';
    $channels = '';
    $playlists = '';
echo <<<HEA
        <!doctype html>
<html>
    <head>
        <title>YouTube Search</title>
<style>
    .search_embed_list li{ padding: 5px 3px; list-style:none; clear: both; }
    .embed_thumbnail{ float:left; margin-right: 7px; };
    .embed_title{ float:left; display:block; }
    .embed_source{ font-size:80%;color:#777;}
    .embed_select{ float:left; }
</style>
    <script src="//code.jquery.com/jquery-1.11.3.js"></script>
    <script src="//code.jquery.com/ui/1.11.2/jquery-ui.js"></script>
    </head>
    <body>
HEA;
$nexttoken = $searchResponse['nextPageToken'];
$prevtoken = $searchResponse['prevPageToken'];

echo <<<SCRIPT

<script type="text/javascript">
    function select_embed(sour) {
        $('input[name=emb_code]').val(sour);
        $('body').css({'overflow': 'auto'});
        $('#load_form').remove();
    }

    function search_embed() {
        var dir = '$CFG->wwwroot/contents/html/search_embed_youtube.php';
        var search = $('input[id=searchtxt]').val();
  
        $.ajax({
            url: dir,
            data: {
                search: search
            },
            success: function (data) {
                $('#load_form').html(data);
            }
        });
    }
    function prev_embed() {
        var dir = '$CFG->wwwroot/contents/html/search_embed_youtube.php';
        var search = $('input[name=search]').val();
  
        $.ajax({
            url: dir,
            data: {
                search: '$keyword',
                prevtoken :  '$prevtoken' 
            },
            success: function (data) {
                $('#load_form').html(data);
            }
        });
    }
    function next_embed() {

       var dir = '$CFG->wwwroot/contents/html/search_embed_youtube.php';
        var search = $('input[name=search]').val();
  
        $.ajax({
            url: dir,
            data: {
                search: '$keyword',
                nexttoken :  '$nexttoken' 
            },
            success: function (data) {
                $('#load_form').html(data);
            }
        });
    }
</script>
SCRIPT;
echo <<<END
 <div>
<form method="GET" style="float:left;">
      <input type="search" id="searchtxt" name="search" value="$keyword" placeholder="Enter Search Term"> 
      <input type="button" onclick="search_embed();" value="Search">
</form>
END;
if($prevtoken){
    echo <<<END2
<form method="GET" style="float:left;">
          <input type="hidden" name="search" value="$keyword">
          <input type="hidden" name="prevtoken" value="$prevtoken">
          &nbsp;<input type="button" onclick="prev_embed();" value="Prev">
</form> 

END2;
}
if($nexttoken){
echo <<<END3
<form method="GET" style="float:left;">
          <input type="hidden" name="search" value="$keyword">
          <input type="hidden" name="nexttoken" value="$nexttoken">
          &nbsp;<input type="button" onclick="next_embed();" value="Next">
</form> 
  </div>
  

<br />
<br />
END3;
}
    foreach ($searchResponse['items'] as $searchResult) {
                $thuimg = $searchResult['snippet']['thumbnails']['modelData']['medium']['url'];
                $title = $searchResult['snippet']['title'];
                $videoId = $searchResult['id']['videoId'];
                $source = 'http://www.youtube.com/v/' . $videoId;
                $select = 'Select';
                echo <<<VID
            <div style="width:480px; float:left;">
                <div class="embed_thumbnail"><img src="$thuimg" alt="$title" width="172" width="99" title="$title"/></div>
                <div class="embed_title">$title<br/><span class="embed_source">$source</span></div>
                <div class="embed_select"><input type="button" onclick="select_embed('$source');" value="$select"></div>         
            </div>
VID;

    }
          if(isset($searchResponse['nextPageToken'])){
              // return to our function and loop again
            return youtube_search($query, $max_results, $searchResponse['nextPageToken']);
        }

?>


</body>
</html>