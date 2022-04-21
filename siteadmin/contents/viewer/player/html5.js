(function(){
    //객체 초기화
    var _viewer = function(){
		
        this.id, this.event, this.st_time, this.lrn_time, this.duration, this.video, this.oldtime;
        
        //디버깅 설정(true: 디버그, false: 일반)
        this.debug = false;
        this.console;
                
    };
	
    //기능 정의
    _viewer.prototype = {
        //객체 값 셋팅
        call: function(id, event, lrn_time){
            
            if(this.debug){
                this.console = document.createElement('div');
                this.console.id = 'console';
                this.console.innerHTML = 'Console Window';
                //document.getElementsByTagName('body')[0].appendChild(this.console);
            }
            
            this.video.setEventListener();
            this.id = id, this.event = event, this.st_time = lrn_time, this.lrn_time = lrn_time, this.oldtime = lrn_time;
            
        },
      
        //영상을 스킵 시켰을 때 시간을 저장
        sendTime: function(){
            var fd = new FormData();
            fd.append('id', this.id);
            fd.append('event', this.event);
            fd.append('st_time', this.st_time);
            fd.append('lrn_time', this.lrn_time);
            fd.append('duration', parseInt(this.duration));
            this.req.open('post', 'playtime_ajax.php', true);//영상 시간을 저장할 주소
            this.req.send(fd);
        },
		
        log: function(msg){
            this.console.innerHTML = msg;
        }
              
    };
                        
    //비디오 객체에 이벤트 등록 함수 추가
    var _video = HTMLVideoElement.prototype;
    _video.setEventListener = function(){
		
        this.addEventListener('loadstart', function(e){
            /*
            if(!viewer.endTime) viewer.endTime = 0;
            
            if(viewer.startTime>0){
                if(!confirm('이전 학습부터 이어보시겠습니까?')){
                    viewer.isStart = false;
                }
            }
            */
            viewer.video.play();
            
        });
                
        this.addEventListener('timeupdate', function(e){
            viewer.duration = e.target.duration;
            if(e.target.currentTime>=viewer.st_time) viewer.lrn_time = e.target.currentTime;
            viewer.event = 5;
            //viewer.console.innerHTML = parseInt(viewer.startTime)+'<br/>'+parseInt(viewer.endTime)+'<br/>'+viewer.isPause;
            //5초에 한번씩 업데이트
            if(e.target.duration%5==0) viewer.sendTime();
        });
                               
        this.addEventListener('play', function(e){
            viewer.st_time = viewer.oldtime;
            viewer.lrn_time = viewer.video.currentTime;
            viewer.event = 1;
            viewer.sendTime();
        });
                
        this.addEventListener('pause', function(e){
            viewer.st_time = viewer.oldtime;
            viewer.lrn_time = viewer.video.currentTime;
            viewer.event = 3;
            viewer.sendTime();
            viewer.oldtime = viewer.lrn_time;
        });
        
        this.addEventListener('seeked', function(e){
            viewer.st_time = viewer.video.currentTime;
            viewer.lrn_time = viewer.video.currentTime;
            viewer.event = 4;
            viewer.sendTime();
        });
                
        this.addEventListener('ended', function(e){
            viewer.st_time = viewer.oldtime;;
            viewer.lrn_time = viewer.video.currentTime;
            viewer.event = 2;
            viewer.sendTime();
            viewer.oldtime = viewer.lrn_time;
        });
                
    };
	
    window.viewer = new _viewer();
   
	
})()