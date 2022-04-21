<?php

//우선, 유튜브 것으로 올려놓음. 향후 수정

require_once('../../config.php');

if (!empty($_REQUEST)) {
    foreach ($_REQUEST as $key => $val) {
        ${$key} = $val;
    }
}

$keyword = $search;
if(!$start) $start = 1;
if(!$max) $max = 20;

$feed_url = 'http://gdata.youtube.com/feeds/api/videos?q=' . urlencode($keyword) . '&start-index=' . $start . '&max-results=' .$max;
$feed = simplexml_load_file($feed_url);

echo '<style>
    .search_embed_list li{ padding: 5px 3px; list-style:none; clear: both; }
    .embed_thumbnail{ float:left; margin-right: 7px; };
    .embed_title{ float:left; display:block; }
    .embed_source{ font-size:80%;color:#777;}
    .embed_select{ float:left; }
    </style>';

echo '<form name="search_embed_youtube">';
echo '<div>
      <input type="text" name="search" value="'.$keyword.'"/>
      <a href="#search" onclick="search_embed();">'.get_string('stats_search1', 'local_lmsdata') .'</a>
      </div></form>';
echo '<ul class="search_embed_list">';

foreach ($feed->entry as $entry) {
    
    $media = $entry->children('http://search.yahoo.com/mrss/');
    $title = (string) $media->group->title;
    $description = (string) $media->group->description;
    if (empty($description)) {
        $description = $title;
    }
    $attrs = $media->group->thumbnail[2]->attributes();
    $thumbnail = $attrs['url'];
    $arr = explode('/', $entry->id);
    $id = $arr[count($arr) - 1];
    $source = 'http://www.youtube.com/v/' . $id;
    $thumbnail_width = $attrs['width'];
    $thumbnail_height = $attrs['height'];
    
    echo '<li>';
    echo '<div class="embed_thumbnail"><img src="'.$thumbnail.'" width="'.$thumbnail_width.'" height="'.$thumbnail_height.'" alt="'.$title.'" title="'.$title.'"/></div>';
    echo '<div class="embed_title">'.$title.'<br/><span class="embed_source">'.$source.'</span></div>';
    echo '<div class="embed_select"><a href="#select" onclick="select_embed(\''.$duration.'\',\''.$source.'\');">선택</a></div>';
    echo '</li>';
    
}

echo '</ul>';

?>

<script type="text/javascript">
    function select_embed(dur,sour){
        $('form[name=update_form]').find('input[name=emb_code]').val(sour);
        $('body').css({'overflow':'auto'});
        $('#load_form').remove();
    }
    
    function search_embed(){
        
    var dir = './html/search_embed_youtube.php';
    var search = $('input[name=search]').val();
       
    $.ajax({
        url: dir,
        data: {
            search: search
        },
        success: function(data) {    
            $('#load_form').html(data);
        }   
    });
}
</script>

      
