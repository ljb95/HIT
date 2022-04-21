<?php 
function okteam_get_paging_bar($url, $params, $total_pages, $current_page, $max_nav = 10) {
	$current_nav_page = (int) ($current_page / $max_nav);
	if (($current_page % $max_nav) > 0) {
		$current_nav_page += 1;
	}
	$page_start = ($current_nav_page - 1) * $max_nav + 1;
	$page_end = $current_nav_page * $max_nav;
	if ($page_end > $total_pages) {
		$page_end = $total_pages;
	}
        
	if (!empty($params)) {
		$tmp = array();
		foreach ($params as $key => $value) {
			$tmp[] = $key . '=' . $value;
		}
		$tmp[] = "page=";
		$url = $url . "?" . implode('&', $tmp);
	} else {
		$url = $url . "?page=";
	}
	echo html_writer::start_tag('div', array('class' => 'board-breadcrumbs'));
	if ($current_nav_page > 1) {
           // echo '<span class="board-nav-prev"><a class="prev" href="'.$url.(($current_nav_page - 2) * $max_nav + 1).'"><</a></span>';
	} else {
           // echo '<span class="board-nav-prev"><a class="prev" href="#"><</a></span>';
	}
	if ($current_page > 1) {
		echo '<span class="board-nav-prev"><a class="prev" href="'.$url.($current_page - 1).'"><</a></span>';
	} else {
		echo '<span class="board-nav-prev"><a class="prev" href="#"><</a></span>';
	}
        echo '<ul>';
	for ($i = $page_start; $i <= $page_end; $i++) {
		if ($i == $current_page) {
			echo '<li class="current"><a href="#">'.$i.'</a></li>';
		} else {
			echo '<li><a href="'.$url.''.$i.'&market='.$market.'">'.$i.'</a></li>';
		}
	}
        echo '</ul>';
	if ($current_page < $total_pages) {
		echo '<span class="board-nav-next"><a class="next" href="'.$url.($current_page + 1).'">></a></span>';
	} else {
		echo '<span class="board-nav-next"><a class="next" href="#">></a></span>';
	}
	if ($current_nav_page < $total_nav_pages) {
		//echo '<a class="next_" href="' . $url . ($current_nav_page * $max_nav + 1) . '"></a>';
	} else {
		//echo '<a class="next_" href="#"></a>';
	}
	echo html_writer::end_tag('div');
}