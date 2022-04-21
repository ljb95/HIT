if (!window['YT']) {var YT = {loading: 0,loaded: 0};}if (!window['YTConfig']) {var YTConfig = {'host': 'http://www.youtube.com'};}if (!YT.loading) {YT.loading = 1;(function(){var l = [];YT.ready = function(f) {if (YT.loaded) {f();} else {l.push(f);}};window.onYTReady = function() {YT.loaded = 1;for (var i = 0; i < l.length; i++) {try {l[i]();} catch (e) {}}};YT.setConfig = function(c) {for (var k in c) {if (c.hasOwnProperty(k)) {YTConfig[k] = c[k];}}};var a = document.createElement('script');a.type = 'text/javascript';a.id = 'www-widgetapi-script';a.src = 'https:' + '//s.ytimg.com/yts/jsbin/www-widgetapi-vflRxMsUX/www-widgetapi.js';a.async = true;var b = document.getElementsByTagName('script')[0];b.parentNode.insertBefore(a, b);})();}

var player;
var area_id;
var area_w;
var area_h;
var content_id;
var after_time;

function onYouTubePlayerAPIReady() {
	player = new YT.Player(area_id, {
	  height: area_h,
	  width: area_w,
	  videoId: content_id,
          playerVars: { 'autoplay': 0, 'controls': 1 },
	  events: {
              'onReady': onPlayerReady,
              'onStateChange': onPlayerStateChange           
	  }
	});
}
function onPlayerReady(event){
	if(after_time>0) C_Move(after_time);
}

function onPlayerStateChange(event){
    /*
    -1 –시작되지 않음
    0 – 종료
    1 – 재생 중
    2 – 일시중지
    3 – 버퍼링
    5 – 동영상 신호
    => 각 이벤트에 따른 저장방식 선택.. seek는??
    */
   alert(event.data);
}

function YouTube_Init(id,w,h,code,at){
	area_id=id;
	area_w=w;
	area_h=h;
	content_id=code;
	after_time=at;
}

function C_Play() {
  player.playVideo();
}

function C_Pause() {
  player.pauseVideo();
}

function C_Stop() {
  player.stopVideo();
}

function mute() {
  player.mute();
}

function unMute() {
  player.unMute();
}

function C_Muted(con) {
	if (con > 0)
	{
		mute()
	}else{
		unMute()
	}
}

function C_volume(newVolume) {
  player.setVolume(newVolume);
}

function C_duration() {
  return player.getDuration();
}

function C_Seek() {
  var currentTime = player.getCurrentTime();
  return roundNumber(currentTime, 3);
}

function C_Move(seconds) {
  player.seekTo(seconds, "true");
}

function C_URLData(URL_data,chktime){
	setInterval(function(){ 
		$.ajax({url:URL_data
			,dataType : "jsonp"
			,jsonpCallback : "myCallback"
			,success: function(data) {
				console.log(cur_pos)
			}
			})			
	}, chktime*1000);
}

function roundNumber(number, decimalPlaces) {
  decimalPlaces = (!decimalPlaces ? 2 : decimalPlaces);
  return Math.round(number * Math.pow(10, decimalPlaces)) /
      Math.pow(10, decimalPlaces);
}
