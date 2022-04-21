<h4 class="page_sub_title">현재학기 설정</h4>
<form class="sync_area" id="frm_sync_config" method="POST">
    <label class="w_120 "><font color="#F00A0D" size="3px;"><strong>*</strong></font><?php echo get_string('year','local_lmsdata',' '); ?></label>
    <select title="year" class="w_90" onchange="#" name="year">
        <?php
        $years = siteadmin_get_years();
        foreach($years as $v=>$y) {
            $selected = '';
            if($v == $year) {
                $selected = ' selected';
            }
            echo '<option value="'.$v.'"'.$selected.'> '. get_string('year','local_lmsdata',$y) . '</option>';
        }
        ?>
    </select>

    <br>

    <label class="w_120 "><font color="#F00A0D" size="3px;"><strong>*</strong></font> <?php echo get_string('stats_terms','local_lmsdata'); ?></label>
    <select title="term" class="w_90" onchange="#" name="term">
        <?php 
        $terms = siteadmin_get_terms();
        foreach($terms as $v=>$t) {
            $selected = '';
            if($v == $term) {
                $selected = ' selected';
            }
            echo '<option value="'.$v.'"'.$selected.'> '.$t.'</option>';
        }
        ?>
    </select>
    
    <br>
    
    <label style="vertical-align: top;" class="w_120 "><font color="#F00A0D" size="3px;"><strong>*</strong></font> 자동 동기화 시간</label>
    <select multiple="" style="height:200px;" name="sync_time[]" class="w_90">
        <?php 
        for($i=1; $i<=24; $i++){ 
            $hour_date = $DB->get_record('haksa_auto_sync',array('year'=>$year,'term'=>$term,'hour'=>$i));
        ?>
        <option <?php if($hour_date){ echo 'selected="selected"'; } ?> value="<?php echo $i; ?>"><?php echo $i.'시'; ?></option>
        <?php } ?>
    </select>
</form><!--Sync Area End-->
<div>
     <p class="ps"><?php echo get_string('msg7','local_lmsdata'); ?></p>
</div>
<div id="btn_area">
    <input type="submit" class="blue_btn" style="float:left; margin-right: 10px;" value="<?php echo get_string('okay','local_lmsdata'); ?>" onclick="sync_set_config()"/> 
</div> 