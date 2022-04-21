<?php
require_once dirname(dirname(dirname (__FILE__))).'/config.php';
require_once dirname(dirname (__FILE__)).'/lib.php';
//require_once $CFG->dirroot.'/local/courselist/lib.php';
require_once $CFG->dirroot.'/lib/coursecatlib.php';


?>
<?php include_once (dirname(dirname (__FILE__)).'/inc/header.php'); ?>
<div id="contents">
    <?php include_once  (dirname(dirname (__FILE__)).'/inc/sidebar_manage.php');?>

    <div id="content">
        <h3 class="page_title"><?php echo get_string('add_course_excell','local_lmsdata'); ?></h3>
        <div class="page_navbar"><a href="./category_list.php"><?php echo get_string('course_management', 'local_lmsdata'); ?></a> > <a href="<?php echo $CFG->wwwroot.'/siteadmin/manage/course_list.php'; ?>"><?php echo get_string('opencourse', 'local_lmsdata'); ?></a> > <?php echo get_string('add_excell','local_lmsdata'); ?></div>
        <form name="frm_course_excel" id="frm_course_excel" class="search_area" action="<?php echo $CFG->wwwroot.'/siteadmin/manage/course_list_excel.execute.php'; ?>" method="post" enctype="multipart/form-data">
            <input type="file" name="course_excel" class="btn-margin" size="50"/>
            <input type="button" class="gray_btn" style="margin-right: 10px;" value="<?php echo get_string('add3','local_lmsdata'); ?>" onclick="course_create_excel_submit()"/>
            <br/>
            ><?php echo get_string('excell_string','local_lmsdata'); ?> <a href="<?php echo $CFG->wwwroot;?>/siteadmin/manage/course_sample.xlsx" >[<?php echo get_string('download_sample','local_lmsdata'); ?>]</a>
        </form>
        <input type="button" class="normal_btn" style="float:right;margin-right: 10px;" value="<?php echo get_string('msg6','local_lmsdata'); ?>" onclick="location.href='<?php echo $CFG->wwwroot.'/siteadmin/manage/course_list.php'?>'"/>
    </div><!--Content End-->
</div> <!--Contents End-->

<?php include_once ('../inc/footer.php');?>

<script type="text/javascript">
    function course_create_excel_submit() {
        if($.trim($("input[name='course_excel']").val()) != '') {
             var filename = $.trim($("input[name='course_excel']").val());
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
                $("#frm_course_excel").submit();
             }
        }
    }
    
</script>    
