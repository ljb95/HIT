<?php
//  $resp = file_get_contents("https://www.udacity.com/public-api/v0/courses");
//  $json_response = json_decode($resp, true);
//  foreach ($json_response["courses"] as $course) {
//    echo $course["title"], "<br>";
//    echo $course["image"], "<br>";
//    echo $course["homepage"], "<br>";
//    echo "-------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------<br>";
//  }
//$resp = file_get_contents("http://api.ted.com/v1/speakers.json?api-key=YOURKEYHERE&filter=lastname:a*");
//  $json_response = json_decode($resp, true);
//  foreach ($json_response["courses"] as $course) {
//      $course["name"];
//  }

//$ch = curl_init();
//curl_setopt($ch, CURLOPT_URL, "https://learn.saylor.org/webservice/rest/server.php?wstoken=b0a1bed41af1f40af560d7c5b013cf67&wsfunction=local_wsfunc_get_visible_courses&moodlewsrestformat=json&cat=2");
//curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE); // 인증서 체크같은데 true 시 안되는 경우가 많다. // default 값이 true 이기때문에 이부분을 조심 (https 접속시에 필요)
//curl_setopt($ch, CURLOPT_SSLVERSION,3); // SSL 버젼 (https 접속시에 필요)
//curl_setopt($ch, CURLOPT_POST, 0); // Post Get 접속 여부 지금은 Get방식 1은 POST
//curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // 결과값을 받을것인지
//$output = curl_exec($ch);
//$output = json_decode($output);
//
//$datas = core_course_get_contents($output);
//
//print_object($datas);
//
//curl -X POST https://learn.saylor.org/webservice/rest/server.php --data
//"wstoken=b0a1bed41af1f40af560d7c5b013cf67&wsfunction=local_wsfunc_get_visible_courses&moodle
//wsrestformat=json&cat=2" -v
?>

<script>
    var xhr = new XMLHttpRequest();
xhr.open("GET", "https://www.codecademy.com/", false);
xhr.send();

console.log(xhr.status);
console.log(xhr.statusText);
</script>