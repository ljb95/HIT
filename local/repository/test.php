<html>
<head>
<title>On-Demand Player</title>
</head>
<body>
<div id='player'>&nbsp;</div>
<script type='text/javascript' src='http://jwplayer.mediaserve.com/jwplayer.js'></script>
<script type='text/javascript'>
  jwplayer('player').setup({
    'id': 'playerID',
    'width': '720',
    'height': '480',
    'provider': 'rtmp',
    'streamer': 'rtmp://210.181.136.120:1935/vod',
    'file': 'mp4:sample_sample.mp4', 
    'modes': [
      {type: 'flash', src: 'http://jwplayer.mediaserve.com/player.swf'},
      {
        type: 'html5',
        config: {
          'file': 'http://210.181.136.120:1935/vod/mp4:sample_sample.mp4/playlist.m3u8',
          'provider': 'mp4:sample.mp4'
        }
      },
      {
        type: 'download',
        config: {
          'file': 'rtsp://210.181.136.120:1935/vod/sample_sample.mp4',
          'provider': 'video'
        }
      }
    ]
  });
</script>
</body>
</html>

