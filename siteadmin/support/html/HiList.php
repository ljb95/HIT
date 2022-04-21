<!--콘텐츠검색-->
<?php 

//콘텐츠 리스트 가져오기
$lists =  $DB->listContents($page);
//$lists = jason_decode($con_list);
$conListView = '';

for($i=0;$i<sizeof($lists['CON_SEQ']);$i++){

$seq = $lists['CON_SEQ'][$i];
//$clas_name = $lists['COURSE_NAME'][$i];
$name = $lists['CON_NAME'][$i];

//$name = stripslashes($name);

$serial = '&cseq=' . $seq . '&cname=' . $name;
$his_link = '?locat=history' . $serial;

$conListView .= '<tr>
    <td><a href="javascript:sideListView(\''.$locat.'\',\''.$seq.'\',\''.$name.'\');">'.stripslashes($name).'</a></td>
    </tr>';
}

//페이지 가져오기
$pagelink = $DB->getPageLink('LCMS_CONTENTS',$page);			

include_once('./html/CoSearch.php'); 

?>

<!--콘텐츠리스트-->
<section id="lc_contents_list">
    <h1 class="hx_hide"><?php echo get_string('contents_contentslist', 'local_lmsdata'); ?></h1>
    <table border="1" class="list_form">
        <caption>전체 콘텐츠 목록</caption>
        <thead>
            <tr>
                <th><?php echo get_string('contents_contentname', 'local_lmsdata'); ?></th>
            </tr>
        </thead>
        <tbody><?php echo $conListView; ?></tbody>
    </table>

    <div class="clearfix">
        <nav class="pageing clearfix">
            <h1 class="hx_hide">페이지링크</h1>
            <ul><?php echo $pagelink; ?></ul>
        </nav>
    </div>

</section>

<section id="lc_slist">
    <form method="post" name="hi_search_form">
        <fieldset>
            <select name="y" onchange="sideListView('<?php echo $locat;?>','<?php echo $seq;?>','<?php echo $name;?>');">
                <option value=""><?php echo get_string('stats_years', 'local_lmsdata'); ?></option>
                <?php 
                for($i=date('Y');$i>date('Y')-3;$i--){
                echo '<option value="'.$i.'">'.$i.'</option>';
                 }
                 ?>
            </select>
            <select name="m" onchange="sideListView('<?php echo $locat;?>','<?php echo $seq;?>','<?php echo $name;?>');">
                <option value=""><?php echo get_string('contents_month', 'local_lmsdata'); ?></option>
                <?php 
                for($i=1;$i<=12;$i++){
                $selected = '';
                //if($i==date('n')) $selected = 'selected';
                echo '<option value="'.$i.'" '.$selected.'>'.$i.'</option>';
                }
                ?>
            </select>
        </fieldset>
    </form>
    <form method="post" name="list_form">

        <input type="hidden" name="amode"/>
        <input type="hidden" name="chkarr"/>
        <input type="hidden" name="locat" value="<?php echo $locat ?>"/>
        <h1 class="hx_s02 grabox">콘텐츠를 선택해주세요.</h1>
        <table border="1" class="list_form">
            <caption>콘텐츠이력 목록</caption>
            <thead>
                <tr>
                    <th width="5%">순번</th>
                    <th width="15%">수정일자</th>
                    <th>내용</th>
                    <th width="15%">테이블명</th>
                    <th width="10%">변경형식</th>
                    <th width="10%"><?php echo get_string('board_viewdetails', 'local_lmsdata'); ?></th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>
    </form>
</section>
