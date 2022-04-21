//유효성 체크 (이것으로 공통적으로 적용함)
function validate_required_fields(frm) {
    
    var msgcount = 0; 
    var blank_pattern = /^\s+|\s+$/g;
    $('p.required_field_msg').remove();
    frm.find(':required').filter(':visible').each(function (i, field) {
        if ($(field).val() == '' || $(field).val().replace(blank_pattern, '') == '') {
            $(field).before('<p class="required_field_msg" style="padding:5px 0;color:red;">필수항목입니다.</p>');
            msgcount++;
        }
    });
    
    if (msgcount > 0) return false;
    
}


