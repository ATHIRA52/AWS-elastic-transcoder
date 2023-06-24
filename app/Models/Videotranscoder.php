<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Aws\ElasticTranscoder\ElasticTranscoderClient;
use Illuminate\Database\Eloquent\Model;
use Owenoj\LaravelGetId3\GetId3;
use Aws\S3\S3Client;


class Videotranscoder extends Model
{
    use HasFactory;

    public static function S3Client()
    {
      return  $s3client = new S3Client(['region' => env('AWS_REGION'),'version' =>  env('AWS_VERSION')]);
    }
    public static function checkPlayTime($file)
    {
        $track = new GetId3(request()->file('video_file'));
        $play_time_in_sec = $track->getPlaytimeSeconds();
        if ($play_time_in_sec > 60) 
            $mesg= "Video play time is greater than 60 second . ";
        else 
            $mesg= "Video play time is less than 60 second . ";
        return $mesg ; 
    }
    public static function uploadToBucket($s3client , $file)
    {
        $key = 'input/'. uniqid().'.'.$file->getClientOriginalExtension(); 
        $s3client->putObject([
            'Bucket' => env('AWS_BUCKET'),
            'Key' => $key,
            'SourceFile' =>  $file->getPathName(),
        ]);
        return $key ;
    }
    public static function transcoderClient()
    {
        return $transcoder = ElasticTranscoderClient::factory(array(
                                'credentials' => array(
                                    'key' => env('AWS_ACCESS_KEY_ID'),
                                    'secret' => env('AWS_SECRET_ACCESS_KEY'),
                                ),
                                'region' =>env('AWS_REGION'), 
                                'version' => env('AWS_VERSION'),
                            ));
     }
    public static function getPresetId($input)
    {
        $container = $input['video_file']->getClientOriginalExtension();
        $preset  = array(
            'mp4' => array(1080=>'1351620000001-000001' , 720=>'1351620000001-000001'), 
            'webm' => array(1080=>'1687636354429-8ykbly' , 720=>'1687636906506-gwcwm5'), 
        );
        return $preset[$container][$input['preset']] ; 
    }
     public static function transcode( $elasticTranscoder,$input_key,$preset)
    {
        $job = $elasticTranscoder->createJob(array(
                        'PipelineId' => env('AWS_PIPELINE'),
                        'OutputKeyPrefix' => 'output/'.uniqid(), 
                        'Input' => array(
                            'Key' => $input_key,
                            'FrameRate' => 'auto',
                            'Resolution' => 'auto',
                            'AspectRatio' => 'auto',
                            'Interlaced' => 'auto',
                            'Container' => 'auto',
                        ),

                        'Outputs' => array(
                            array(
                                'Key' => 'encoded-'.$input_key,
                                'Rotate' => 'auto',
                                'PresetId' =>$preset,
                            ),
                        ),
                    ));

      return  $jobData = $job->get('Job');

    }
    public static function checkJobStatus($elasticTranscoder,$job_id)
    {
        $count = 0;
         do {
            sleep(5);
            $jobStatusRequest = $elasticTranscoder->readJob(array('Id' => $job_id));
            $count++;
            $jobDetails = $jobStatusRequest['Job'] ;
        } while($jobDetails['Status'] == 'Submitted' || $count < 5);

        return $jobDetails ; 
    }
    public static function getTimeTaken($jobDetails)
    {
        if ($jobDetails['Status'] == 'Submitted') {
            return  "Encoding under progress ...";
        }
        elseif($jobDetails['Status'] == 'Complete'){
            return "Encoding complted ... time taken for transcoding : " . ($jobDetails['Timing']['FinishTimeMillis'] - $jobDetails['Timing']['StartTimeMillis'])/1000  .'Sec   . ' ;

        }

    }
    public static function getFileSize($job)
    {
        if ($job['Output']['FileSize'] < $job['Input']['DetectedProperties']['FileSize']/1000 ) 
            return  "out put file size ( " .$job['Output']['FileSize']/1000 .") KB   is less than input file (" . $job['Input']['DetectedProperties']['FileSize']/1000 . ") KB , compression logic works ...!  . ";
        else return  "out put file size ( " .$job['Output']['FileSize']/1000 .") KB   is greater than input file (" . $job['Input']['DetectedProperties']['FileSize']/1000 . ") KB , compression logic DOES NOT works ...!  . ";
        
    }
    public static function getEncodedUrl($s3client,$job)
    {
        $output_file =$job['OutputKeyPrefix'].$job['Output']['Key'] ; 
        $cmd = $s3client->getCommand('GetObject', [
            'Bucket' =>env('AWS_BUCKET'),
            'Key' => $output_file,
        ]);
        $request = $s3client->createPresignedRequest($cmd, '+20 minutes');
       return " Encoded file URL : " . $presignedUrl = (string)$request->getUri();
    }
}
