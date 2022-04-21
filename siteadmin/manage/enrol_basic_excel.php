<?php
require_once dirname(dirname(dirname (__FILE__))).'/config.php';
require_once dirname(dirname (__FILE__)).'/lib.php';
require_once $CFG->dirroot.'/lib/coursecatlib.php';

$year = optional_param('year', '', PARAM_INT);
$term = optional_param('term', '', PARAM_INT);

if(empty($year)) {
    $year = get_config('moodle', 'haxa_year');
}

if(empty($term)) {
    $term = get_config('moodle', 'haxa_term');
}

?>
<?php include_once (dirname(dirname (__FILE__)).'/inc/header.php'); ?>
<div id="contents">
    <?php include_once  (dirname(dirname (__FILE__)).'/inc/sidebar_manage.php');?>

    <div id="content">
        <h3 class="page_title">수강생 <?php echo get_string('add_excell','local_lmsdata'); ?></h3>
        <div class="page_navbar"><a href="./enrol_basic_course.php">수강생</a> > <a href="./enrol_basic_course.php">기본의학 및 특과</a> > <?php echo get_string('add_excell','local_lmsdata'); ?></div>
        <form name="frm_enrol_excel" id="frm_enrol_excel" class="search_area" action="<?php echo $CFG->wwwroot.'/siteadmin/manage/enrol_basic_excel.execute.php'; ?>" method="post" enctype="multipart/form-data">
            <select name="year" class="w_160">
                <option value="0"  <?php echo $year == 0 ? 'selected' : ''?>><?php echo get_string('all','local_lmsdata'); ?></option>
                <?php
                    $year_arr = lmsdata_get_years();
                    foreach($year_arr as $tg_year) {
                        $selected = "";
                        if($tg_year == $year) {
                           $selected = "selected";
                        } 
                        echo '<option value="'.$tg_year.'"  '.$selected.'>'. get_string('year','local_lmsdata',$tg_year) . '</option>';
                    }
                ?>
            </select>
            <select name="term" class="w_160">
                <option value="00" <?php echo (($term == '00') ? 'selected' : ''); ?> >전학기</option>
                <option value="10" <?php echo (($term == '10') ? 'selected' : ''); ?> >1<?php echo get_string('stats_terms','local_lmsdata'); ?></option>
                <option value="20" <?php echo (($term == '20') ? 'selected' : ''); ?> >2<?php echo get_string('stats_terms','local_lmsdata'); ?></option>
            </select>
            <input type="file" name="enrol_excel" size="50"/>
            <input type="button" class="blue_btn" style="margin-right: 10px;" value="<?php echo get_string('add3','local_lmsdata'); ?>" onclick="enrol_basic_excel_submit()"/>
            <br/>
            ><?php echo get_string('excell_string','local_lmsdata'); ?> <a href="<?php echo $CFG->wwwroot;?>/siteadmin/manage/enrol_sample.xlsx" >[<?php echo get_string('download_sample','local_lmsdata'); ?>]</a>
        </form>
        <input type="button" class="blue_btn" style="float:right;margin-right: 10px;" value="<?php echo get_string('msg6','local_lmsdata'); ?>" onclick="location.href='<?php echo $CFG->wwwroot.'/siteadmin/manage/enrol_basic_course.php';?>'"/>
    </div><!--Content End-->
</div> <!--Contents End-->

<?php include_once ('../inc/footer.php');?>

<script type="text/javascript">
    function enrol_basic_excel_submit() {
        if($.trim($("input[name='enrol_excel']").val()) != '') {
             var filename = $.trim($("input[name='enrol_excel']").val());
             var extension = filename.replace(/^.*\./, ''); 
             if(extension == filename) {
                 extension = "";
             } else {
                 extension = extension.toLowerCase();
             }
             
             if($.inArray( extension, [ "xls", "xlsx" ] ) == -1) {
                 alert("<?php echo get_string('onlyexcell','local_lmsdata'); ?>");
                 return false;
             } else {
                $("#frm_enrol_excel").submit();
             }
        }
    }
    
</script>    