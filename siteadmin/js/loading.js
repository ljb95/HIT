$(document).on({
    ajaxStart: function() {
        $(".loading_modal").show();
    },
    ajaxStop: function() {
        $(".loading_modal").hide();
    }
});