<?php
function admin_println($message) {
    echo '<div style="width:500px">';
    echo $message;
    echo '</div><br/>';
    admin_flushdata();
}

function admin_flushdata() {
    while (ob_get_level() > 0) {
        ob_end_flush();
    }

    flush();

    ob_start();
}
