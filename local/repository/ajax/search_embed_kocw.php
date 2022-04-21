<?php
require_once('../../../config.php');

$kocw_key = '70aaa00tte60abr00aaa00ro00oa549a';

$keyword = required_param('search', PARAM_RAW);

$page = 1;
$perpage = 5;

$start_num =  (($page-1)*$perpage)+1;

$xml_url = 'http://www.riss.kr/openApi?key='.$kocw_key.'&version=1.0&type=K&keyword='.$keyword;

$response = file_get_contents($xml_url);
$object = simplexml_load_string($response);

$lists = $object->metadata;
?>
<form method="GET" style="float:left;">
      <input type="search" id="searchtxt" name="search" value="<?php echo $keyword ?>" placeholder="Enter Search Term"> 
      <input type="button" onclick="search_embed();" value="Search">
</form>
<table class="generaltable">
    <thead>
        <tr>
            <th>제목</th>
            <th>저자</th>
            <th>등록일</th>
            <!--th>설명</th-->
        </tr>
    </thead>
    <tbody>
<?php
foreach($lists as $list){
    $list = (array)$list;
    $title = $list['riss.title'];
    $author = $list['riss.author'];
    $date = $list['riss.pubdate'];
    $lanugage = $list['riss.lanugage'];
    $description = $list['riss.description'];
    $url = $list['url'];
?>
        <tr onclick="select_embed('<?php echo $url ?>');" style="cursor: pointer;">
        <td><?php echo $title ?></td>
            <td><?php echo $author; ?></td>
            <td><?php echo $date ?></td>
            <!--td><?php echo mb_strimwidth($description,0,50,'...','UTF-8'); ?></td-->
        </tr>
<?php
}
?>
<script type="text/javascript">
    function select_embed(sour) {
        $('input[name=emb_code]').val(sour);
        $('body').css({'overflow': 'auto'});
        $('#load_form').remove();
    }

    function search_embed() {
        var dir = '<?php echo $CFG->wwwroot.'/local/repository/ajax/search_embed_kocw.php'; ?>'
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
</script>
</tbody>
</table>