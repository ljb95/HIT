<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');

// Check for valid admin user - no guest autologin
require_login(0, false);
if (isguestuser()) {
    $SESSION->wantsurl = (string)new moodle_url('/siteadmin/manage/course_list.php');
    redirect(get_login_url());
}
$context = context_system::instance();
require_capability('moodle/site:config', $context);

require_once $CFG->dirroot . '/local/jinoboard/lib.php';
require_once dirname(dirname(__FILE__)) . '/lib/paging.php';

$type = optional_param('type', 5, PARAM_INT);
$page = optional_param('page', 1, PARAM_INT);
$search = optional_param('search', '', PARAM_RAW);
$perpage = optional_param('perpage', 10, PARAM_INT);

$board = $DB->get_record('jinoboard', array('type' => '5'));
$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_url('/local/jinoboard/index.php');

$like = '';
if (!empty($search)) {
    $like = "and " . $DB->sql_like('title', ':search', false);
}
$sql = "select count(id) from {jinoboard_contents} where board = :board " . $like . " and isnotice = 0 order by ref DESC, step ASC";
$totalcount = $DB->count_records_sql($sql, array('board' => $board->id, 'search' => '%' . $search . '%'));
$total_pages = jinoboard_get_total_pages($totalcount, $perpage);

$perpages = define_perpages();


?>
<?php include_once('../inc/header.php'); ?>
<div id="contents">
    <?php include_once('../inc/sidebar_support.php'); ?>
    <div id="content">
        <h3 class="page_title"><?php echo get_string('admin_question','local_lmsdata'); ?></h3>
        <div class="page_navbar"><a href="./notices.php"><?php echo get_string('site_management', 'local_lmsdata'); ?></a> > <a href="./qna.php"><?php echo get_string('admin_question','local_lmsdata'); ?></a> > <?php echo get_string('list','local_lmsdata'); ?></div>
        <form id="frm_qna_search" class="search_area">
            <input type="hidden" name="type" value="<?php echo $type; ?>">
            <select name="target" class="w_160">
                <option value="title"><?php echo get_string('title', 'local_lmsdata'); ?></option>
            </select>
            <input type="text" name="search" value="<?php echo $search; ?>" class="search-text" placeholder="<?php echo get_string('input', 'local_jinoboard'); ?>">
            <input type="submit" class="blue_btn" id="search" value="<?php echo get_string('search', 'local_jinoboard'); ?>">
            <select name="perpage" class="w_160" onchange="this.form.submit();" style="width:100px; float:right;">
                <?php foreach($perpages as $val) {
                     echo '<option value="'.$val.'" '.(($perpage==$val)?'selected':'').'>'.get_string('perpage','local_lmsdata',$val).'</option>';   
                }
                ?>
            </select>
        </form>
        <form action="./qna_del.php" method="post">
        <table cellspacing="0" cellpadding="0">
            <tr>
                <th style="width:5%;"><input type="checkbox" id="allcheck" style="margin: 0 !important;"/></th>
                <th style="width:5%;"><?php echo get_string('number', 'local_lmsdata'); ?></th>
                <th><?php echo get_string('title', 'local_lmsdata'); ?></th>
                <th style="width:10%;"><?php echo get_string('author','local_lmsdata'); ?></th>
                <th style="width:10%;"><?php echo get_string('datecreated', 'local_lmsdata'); ?></th>
            </tr>
            <?php
            $offset = 0;
            if ($page != 0) {
                $offset = ($page - 1) * $perpage;
            }
            $num = $totalcount - $offset;
            $sql = "select * from {jinoboard_contents} where board = :board " . $like . " and isnotice = 0 order by ref DESC, step ASC";
            $contents = $DB->get_records_sql($sql, array('board' => $board->id, 'search' => '%' . $search . '%'), $offset, $perpage);
            if ($num > 0) {
                foreach ($contents as $content) {
                        echo '<tr>';
                        $fs = get_file_storage();
                        $files = $fs->get_area_files($context->id, 'local_jinoboard', 'attachment', $content->id, 'timemodified', false);
                        if (count($files) > 0) {
                            $filecheck = '<img src="../img/icon-attachment.png" alt="' . get_string('content:file', 'local_jinoboard') . '"  class="icon-attachment">';
                        } else {
                            $filecheck = "";
                        }

                        if ($content->step) {
                            $step_left_len = $content->lev * 20;
                            $step_left = 'style="margin-left:' . $step_left_len . 'px;"';
                        } else {
                            $step_left = '';
                        }
                        
                         
                        $postuser = $DB->get_record('user', array('id' => $content->userid));
                        $fullname = fullname($postuser);
                        $userdate = userdate($content->timecreated);
                        
                        echo "<td><input type='checkbox' class='check_qna' name='check_list[]' value='".$content->id."' id='$content->id' ></td>";
                        echo "<td>" . $num . "</td>";
                        echo "<td style='text-align:left; padding-left:20px;'><a href='qna_view.php?id=" . $content->id . "' ".$step_left.">" . $content->title . "</a>".$filecheck."</td>";
                        echo '<td>' . $fullname . '</td>';
                        echo "<td>" . date("Y-m-d", $content->timecreated) . "</td>";
                        echo "</tr>";
                        $num--;
                    }
            } else {
                echo "<tr><td colspan='8' align='center'>".get_string('nodata','local_lmsdata')."</td></tr>";
            }
            ?>
        </table>

        <div class="btn_area">
            <input type="submit" onclick="del_check()" id="delete_qna" class="gray_btn" value="<?php echo get_string('delete', 'local_lmsdata'); ?>" style="float: left" />
            <!--<input type="button" value="<?php //echo get_string('writepost', 'local_jinoboard') ?>" onclick="location.href = 'qna_write.php?type=<?php //echo $board->type; ?>'" class="blue_btn" style="float:right;"/>-->
        </div>    
        </form>
            <?php
            $page_params = array();
            $page_params['type'] = $type;
            $page_params['perpage'] = $perpage;
            $page_params['search'] = $search;
            print_paging_navbar_notice($CFG->wwwroot . "/siteadmin/support/qna.php", $page_params, $total_pages, $page);
            ?>
            <!-- Breadcrumbs End -->
        </div> <!-- Table Footer Area End -->
    </div>
</div>

<script>
//    $(function () {
//        $("#accordion").accordion({
//            collapsible: true,
//            heightStyle: "content",
//            header: "h3",
//            active: false
//        });
//        $("#accordion").accordion("option", "icons", null);
//    });
//	$('#accordion input[type="checkbox"]').click(function(e) {
//		e.stopPropagation();
//	});
function del_check(){
    if(confirm("삭제 하시겠습니까") == false){
        return false;
    }
}

 $(document).ready(function () {
    $('#allcheck').click(function() {
           if($('#allcheck').is(":checked")){
               $(".check_qna").each(function(){
                this.checked = true;   
               });
           }else{
                $(".check_qna").each(function(){
                this.checked = false;   
               });
           }
    });
//    $('#delete_qna').click(function() {
//        if(confirm("<?php echo get_string('delete', 'local_lmsdata'); ?> 하시겠습니까")){  
//            var del_list =[];
//            $(".check_qna").each(function(index, element){
//                if($(this).is(":checked")){
//                    del_list.push(this.id) ;
//                }
//            })
//            console.log(del_list);
//              $.ajax({
//                  url : "./qna_del.php",
//                  type: "post",
//                  data : {
//                      data : del_list                            
//                  },
//                  async: false,
//                  success: function(data){
//                     location.href = "./qna.php";
//                  },
//                  error:function(e){
//                      console.log(e.responseText);
//                  }
//              }); 
//          }
//      });

    $('#search').click(function() {
       var searchfield = $('#searchfield').val();
       var searchval = $('#searchval').val();
       var timestart = $('#timestart').val();
       var timeend = $('#timeend').val();

       location.href = "./notice.php?searchfield="+searchfield+"&searchvalue="+searchval+"&timestart="+timestart+"&timeend="+timeend;
    });
});
</script>
<?php include_once('../inc/footer.php'); ?>