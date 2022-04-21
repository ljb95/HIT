function goto_page(page, frm) {
    $('[name=page]').val(page);
    $('form[name='+frm+']').submit();
}

function change_perpage(perpage, frm) {
    $('[name=perpage]').val(perpage);
    $('form[name='+frm+']').submit();
}

