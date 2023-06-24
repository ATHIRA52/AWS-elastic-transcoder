# AWS-elastic-transcoder

API parameters are 
 
1.video_file(FILE) - video file can be accepted
2.preset(TEXT)  - form factor (144p, 240p, 360p, 480p, 720p and 1080p) 



#tasks completed

1.Checks video duration is less than 60 seconds or not 

2.Videos will be upload to AWS bucket

3.Can be transcoded using aws elastic transcoder 

  a.Return the file size along with the URLs
  
  b.Check and make sure the end file sizes are lower than the uploaded file size, and handle the compression logic if not.
  
  c.Returns time requred to transcode the video

#preset
Created custom preset for webm file and  saved  preset ID as array .

  $preset  = array(
            'mp4' => array(1080=>'1351620000001-000001' , 720=>'1351620000001-000001'), 
            'webm' => array(1080=>'1687636354429-8ykbly' , 720=>'1687636906506-gwcwm5'), 
        );


        


#Example Response : 

{

    "status": true,
    
    "message": "Video play time is less than 60 second . Video has been upload to aws bucket with key as input/64975aa187567.mp4 . Video has been encoded .. Job ID : 1687640742242-c73lue . Encoding complted ... time taken for transcoding : 3.703Sec   . out put file size ( 2055.077) KB   is greater than input file (4848.208) KB , compression logic  works ...!  .  Encoded file URL : https://test-bucket-athira.s3.ap-south-1.amazonaws.com/output/64975aa5b7ef1encoded-input/64975aa187567.mp4?X-Amz-Content-Sha256=UNSIGNED-PAYLOAD&X-Amz-Algorithm=AWS4-HMAC-SHA256&X-Amz-Credential=AKIAWXFAR2G6MUD3XD4C%2F20230624%2Fap-south-1%2Fs3%2Faws4_request&X-Amz-Date=20230624T210608Z&X-Amz-SignedHeaders=host&X-Amz-Expires=1200&X-Amz-Signature=fc05a8bc404fe1619bdd2c806b60efc38b436b349cf0b47937c87ca690ae05e2"
    
}
