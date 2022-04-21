<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
//require_once (dirname(dirname(dirname(__FILE__))) . '/lib/adminlib.php');
require_once(dirname(dirname(__FILE__)) . '/lib.php');
//require_once (dirname(dirname(dirname(__FILE__))) . '/slib/form/editor.php');


$PAGE->set_pagelayout('admin');
$strplural = get_string('popup_manage', 'local_lmsdata');
$PAGE->navbar->add($strplural);
$PAGE->set_url('/siteadmin/support/popup_view.php');
$id = optional_param('id', 0, PARAM_INT);

//$context = context_system::instance();
//require_capability('local/popup:managepopup', $context);

$PAGE->set_context($context);

$data = $DB->get_record('popup', array('id' => $id));
?>
<script  LANGUAGE="JavaScript">
    function deletepopup(id) {
        if (confirm("<?php echo get_string('suredeleteselectedcontents', 'local_popup'); ?>")) {
            document.deleteform.id.value = id;
            document.deleteform.action = '<?php echo($CFG->wwwroot); ?>/local/popup/delete.php';
            document.deleteform.submit();
        }
    }
</script>
<?php include_once (dirname(dirname(__FILE__)) . '/inc/header.php'); ?>
<div id="contents">
    <?php include_once ('../inc/sidebar_support.php'); ?>

    <div id="content">
        <h3 class="page_title"><?php echo get_string('popup_manage', 'local_lmsdata'); ?></h3>
        <p class="page_sub_title"> <?php echo get_string('popup_des', 'local_lmsdata'); ?></p>

        <div class="frm_popup">
            <table cellspadding="0" cellspacing="0" class="detail">
                <tr>
                    <td class="field_title"><font color="#F00A0D"  size="3px;"><strong>*</strong></font><?php echo get_string('popup', 'local_lmsdata'); ?></td>
                    <td class="field_value"><?php echo $data->title; ?></td>
                </tr>
                <tr>
                    <td class="field_title"><font color="#F00A0D"  size="3px;"><strong>*</strong></font><?php echo get_string('post_period', 'local_lmsdata'); ?></td>
                    <td class="field_value"><?php echo date("Y-m-d", $data->timeavailable); ?> ~ <?php echo date("Y-m-d", $data->timedue); ?></td>
                </tr>
                <tr>
                    <td class="field_title"><font color="#F00A0D"  size="3px;"><strong>*</strong></font><?php echo get_string('position', 'local_lmsdata'); ?></td>
                    <td class="field_value"><?php if ($data->type == 1) echo  get_string('login');
    else echo get_string('support_mylmsdata', 'local_lmsdata'); ?></td>
                </tr>
                <tr>
                    <td class="field_title"><font color="#F00A0D"  size="3px;"><strong>*</strong></font><?php echo get_string('post_visible', 'local_lmsdata'); ?></td>
                    <td class="field_value">
                        <?php
                        $now = date("Y-m-d");
                        if ($now >= date("Y-m-d", $data->timeavailable) && $now <= date("Y-m-d", $data->timedue)) {
                            echo get_string('active', 'local_lmsdata');
                        } else if ($now < date("Y-m-d", $data->timeavailable)) {
                            echo get_string('notaperiod', 'local_lmsdata');
                        } else {
                            echo get_string('status3', 'local_lmsdata');
                        }
                        ?>
                    </td>
                </tr>
                <tr>
                    <td class="field_title"><font color="#F00A0D"  size="3px;"><strong>*</strong></font><?php echo get_string('contents', 'local_lmsdata'); ?></td>
                    <td class="field_value" ><?php echo $data->description; ?></td>
                </tr>
                <tr>
                    <td class="field_title"><?php echo get_string('xsize', 'local_lmsdata'); ?></td>
                    <td class="field_value" colspan="3"><?php echo $data->popupwidth; ?> px</td>
                </tr>
                <tr>
                    <td class="field_title"><?php echo get_string('ysize', 'local_lmsdata'); ?></td>
                    <td class="field_value" colspan="3"><?php echo $data->popupheight; ?> px</td>
                </tr>
                <tr>
                    <td class="field_title"><?php echo get_string('xposition', 'local_lmsdata'); ?></td>
                    <td class="field_value" colspan="3"><?php echo $data->popupx; ?> px</td>
                </tr>
                <tr>
                    <td class="field_title"><?php echo get_string('yposition', 'local_lmsdata'); ?></td>
                    <td class="field_value" colspan="3"><?php echo $data->popupy; ?> px</td>
                </tr>
                <tr>
                    <td class="field_title"><?php echo get_string('viewscroll', 'local_lmsdata'); ?></td>
                    <td class="field_value" colspan="3"><?php if ($data->availablescroll == 1) {
                            echo "표시";
                        } else {
                            echo "표시안함";
                        } ?></td>
                </tr>
            </table>
            <div id="btn_area">
                <input type="button" id="popup_write"  class="blue_btn" value="<?php echo get_string('edit', 'local_lmsdata'); ?>" style="float:right" />
                <input type="button" id="popup_list" class="normal_btn" value="<?php echo get_string('list2', 'local_lmsdata'); ?>" style="float:left;" />
            </div><!--Btn Area End-->
            </form>
        </div><!--Form Popup End-->
    </div><!--Content End-->
</div> <!--Contents End-->

<?php include_once ('../inc/footer.php'); ?>

<script type="text/javascript">
    $(document).ready(function () {
        $('#popup_list').click(function () {
            location.href = "./popup.php";
        });
        $('#popup_write').click(function () {
            location.href = '<?php echo($CFG->wwwroot); ?>/siteadmin/support/popup_write.php?mode=edit&id=<?php echo($data->id); ?>';
                    });
                });
</script>
