<?php
require(dirname(dirname(dirname(__FILE__))) . '/config.php');
$context = context_system::instance();

$PAGE->set_context($context);
$PAGE->set_pagelayout('standard');

$PAGE->set_url('/local/intro/manual.php');

$PAGE->navbar->add("메뉴얼");

echo $OUTPUT->header();
?>  
<div class="manual-box">
    <div class="manu-b stu">
        <h3>
            <span>학생</span> 매뉴얼
        </h3>
        <p>학생을 위한 메뉴얼 입니다.</p>
        <a href="#" class="manual-btn">메뉴얼 다운로드</a>
        <div class="img-area">
            이미지 영역
        </div>
    </div>
    <div class="manu-b pro">
        <h3>
            <span>교수</span> 매뉴얼
        </h3>
        <p>교수를 위한 메뉴얼 입니다.</p>
        <a href="#" class="manual-btn">메뉴얼 다운로드</a>
        <div class="img-area">
            이미지 영역
        </div>
    </div>
</div>
<?php
echo $OUTPUT->footer();
?>


