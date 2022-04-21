if (!window['YT']) {var YT = {loading: 0,loaded: 0};}if (!window['YTConfig']) {var YTConfig = {'host': 'http://www.youtube.com'};}if (!YT.loading) {YT.loading = 1;(function(){var l = [];YT.ready = function(f) {if (YT.loaded) {f();} else {l.push(f);}};window.onYTReady = function() {YT.loaded = 1;for (var i = 0; i < l.length; i++) {try {l[i]();} catch (e) {}}};YT.setConfig = function(c) {for (var k in c) {if (c.hasOwnProperty(k)) {YTConfig[k] = c[k];}}};var a = document.createElement('script');a.type = 'text/javascript';a.id = 'www-widgetapi-script';a.src = 'https:' + '//s.ytimg.com/yts/jsbin/www-widgetapi-vflRxMsUX/www-widgetapi.js';a.async = true;var b = document.getElementsByTagName('script')[0];b.parentNode.insertBefore(a, b);})();}

var player;
var area_id;
var area_w;
var area_h;
var content_id;
var starttime;;
var mode;

var positionfrom = 0, positionto = 0, duration = 0;
var satiscompleted = $('#satiscompleted').val();
var intervalID;
/*
var video = document.getElementById('contents_viewer');
var controls = document.getElementById('controls');
var playbtn = document.getElementById('playpausebtn');
var seekslider = document.getElementById('seekslider');
var totaltime = document.getElementById('totaltime');
*/
function onYouTubeIframeAPIReady() {
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
    duration = player.getDuration();
}

function onPlayerStateChange(event){
    var state = player.getPlayerState();
    switch(state) {
        case YT.PlayerState.ENDED:
            playtime_update(positionfrom, positionto, 1, duration, mode);
            window.clearInterval(intervalID);
            
            break;
        case YT.PlayerState.PLAYING:
            positionfrom = Math.ceil(player.getCurrentTime());
            intervalID = setInterval(YouTube_updateCurrentTime, 1000);
            break;
        case YT.PlayerState.PAUSED:
            if (positionfrom < positionto) {
                playtime_update(positionfrom, positionto, 1, duration, mode);
            }
            window.clearInterval(intervalID);
            break;
        case YT.PlayerState.BUFFERING:
            break;
        case YT.PlayerState.CUED:
            break;
        default:
            break;
    }
}

function YouTube_Init(id,w,h,code,m){
    area_id=id;
    area_w=w;
    area_h=h;
    content_id=code;
    mode=m;
}

function YouTube_updateCurrentTime() {
    positionto = Math.ceil(player.getCurrentTime());
}

function roundNumber(number, decimalPlaces) {
    decimalPlaces = (!decimalPlaces ? 2 : decimalPlaces);
    return Math.round(number * Math.pow(10, decimalPlaces)) /
        Math.pow(10, decimalPlaces);
}
