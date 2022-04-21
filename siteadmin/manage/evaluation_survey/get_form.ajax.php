<?php
require_once dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php';
require_once dirname(dirname(dirname(__FILE__))) . '/lib/paging.php';
require_once dirname(dirname(dirname(__FILE__))) . '/lib.php';

header("Content-Type: text/html; charset=UTF-8");

$searchtext = optional_param('searchtext', '', PARAM_TEXT);


?>
<form id="form_get_form" method="post" onsubmit="return false;">
    <input type="text" name="searchtext" value="<?php echo $searchtext; ?>" placeholder="<?php echo get_string('search_placeholder','local_lmsdata'); ?>"  class="search-text"/>
    <input type="submit" id="form_searchbtn" class="blue_btn" value="<?php echo get_string('search','local_lmsdata'); ?>"/>
</form>
<table>
    <col width="10%">
    <col width="50%">
    <col width="30%">
    <col width="10%">
    <tr>
        <th><?php echo get_string('number', 'local_lmsdata'); ?></th>
        <th><?php echo get_string('sample_name','local_lmsdata'); ?></th>
        <th><?php echo get_string('select','local_lmsdata'); ?></th>
    </tr>
    <?php
    $where = '';
    if(!empty($searchtext)){
        $where = ' and title like :searchtext';
    }
    $sql = 'select * from {lmsdata_evaluation_forms} where type = :type '.$where;
    $forms = $DB->get_records_sql($sql, array('type' => 2,'searchtext'=>'%'.$searchtext.'%'));
    $cnt = 1;
    foreach ($forms as $form) {
        ?>
        <tr>
            <td><?php echo $cnt++; ?></td>
            <td class="text-left"><?php echo $form->title; ?></td>

            <td><input type="button" class="blue_btn" value="<?php echo get_string('select','local_lmsdata'); ?>" onclick="form_selete(<?php echo $form->id; ?>, '<?php echo $form->title; ?>')"></td>
        </tr>
    <?php 
    }
    if(!$forms){
        echo '<tr align="center"><td colspan="3">'. get_string('empty_sample','local_lmsdata').'<span><a style="color:red;" href="'.$CFG->wwwroot.'/siteadmin/manage/evaluation/survey_form.php">['.get_string('add','local_lmsdata').']</a></span></td></tr>';
    }
    ?>
</table><!--Table End-->
<script>
       $("#form_get_form").submit(function () {
        var postData = {
            searchtext:$("input[name=searchtext]").val()
        };
        $.ajax({
            url: "get_form.ajax.php",
            type:"POST",
            data:postData,
            success: function (result) {
                parent.$("#form_search_dialog").html(result);
            }
        });
        return false;
    });
</script>