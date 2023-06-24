<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use Aws\S3\Exception\S3Exception;
use App\Models\Videotranscoder as Transcoder ;

class Videotranscoder extends Controller
{
    
    public function uploadVideo(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'video_file' => 'required|mimetypes:video/mp4,video/webm',
            'preset' => 'required',
        ]);
 
        if ($validator->fails()) 
            return response()->json(['status' => false, 'message' =>$validator->messages()], 400);
            
        try {
            if($request->has('video_file')){
                $mesg= Transcoder::checkPlayTime($request->video_file);
                $s3client =Transcoder::S3Client();
                $input_key =Transcoder::uploadToBucket($s3client,$request->video_file);
                $mesg=$mesg . "Video has been upload to aws bucket with key as ". $input_key ." . ";
                $elTranscoder =Transcoder::transcoderClient();
                $preset_Id =Transcoder::getPresetId($request->all());
                $job =Transcoder::transcode($elTranscoder,$input_key,$preset_Id);
                $mesg=$mesg . "Video has been encoded .. Job ID : ". $job['Id'] ." . ";
                $job=Transcoder::checkJobStatus($elTranscoder,$job['Id']);
                $mesg=$mesg .Transcoder::getTimeTaken($job);
                $mesg=$mesg .Transcoder::getFileSize($job);
                $mesg=$mesg .Transcoder::getEncodedUrl($s3client,$job);
                return response()->json(['status' => true, 'message' =>  $mesg], 200);

            }
            else  return response()->json(['status' => false, 'message' => 'Pleae add Video'], 400);
        }catch(S3Exception $e){
            echo $e->getMessage() . PHP_EOL;

        }

    }
    public function uploads()
    {

// dd('fff');
//$s3 = App::make('aws')->createClient('s3');
//         $s3 = AWS::createClient('s3');
// $res= $s3->putObject(array(
//     'Bucket'     => 'test-bucket-athira',
//     'Key'        => 'YOUR_OBJECT_KEY',
//     'SourceFile' => 'C:\Users\HP\Downloads\CV_TEMPLATE_0017.docx',
// ));

// dd($res);
        $region = 'ap-south-1';
$version = 'latest';

$s3client = new S3Client([
    'region' => $region,
    'version' => $version
]);


//dd($s3client);

 //$bucket_name = 'new-buck-byathi' ;
 $bucket_name = 'test-bucket-athira' ;
// $bucket_name = 'input-bucket-athira-1' ;
// try {
//     $s3client->createBucket([
//         'Bucket' => $bucket_name,
//         'CreateBucketConfiguration' => ['LocationConstraint' => $region],
//     ]);
//     echo "Created bucket named: $bucket_name \n";
// } catch (Exception $exception) {
//     echo "Failed to create bucket $bucket_name with error: " . $exception->getMessage();
//     exit("Please fix error with bucket creation before continuing.");
// }



// $file_name = "input-vid" . uniqid();
// try {
//     $s3client->putObject([
//         'Bucket' => $bucket_name,
//         'Key' => 'sampletwo-5s.mp4',
//         //'SourceFile' => 'C:\Users\HP\Downloads\pexels-pressmaster-3195394-3840x2160-25fps.mp4'
//         'SourceFile' => 'C:\Users\HP\Downloads\sample-5s.mp4',
 //    'ACL'    => 'public-read',

//     ]);
//     echo "Uploaded $file_name to $bucket_name.\n";
// } catch (Exception $exception) {
//     echo "Failed to upload $file_name with error: " . $exception->getMessage();
//     exit("Please fix error with file upload before continuing.");
// }


// dd("file uploaded");


$elasticTranscoder = ElasticTranscoderClient::factory(array(
    'credentials' => array(
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
    ),
    'region' => 'ap-south-1', // dont forget to set the region
     'version' => 'latest',

));


  $result = $elasticTranscoder -> listPresets([]);

  dd($result);


// $job = $elasticTranscoder->createJob(array(

//     'PipelineId' => '1687342954094-9si0mx',

//     'OutputKeyPrefix' => 'three/-xxc',  //unique

//     'Input' => array(
//        // 'Key' => 'pexels-pressmaster-3209828-3840x2160-25fps.mp4',
//         'Key' => 'sample-5s.mp4',
//         'FrameRate' => 'auto',
//         'Resolution' => 'auto',
//         'AspectRatio' => 'auto',
//         'Interlaced' => 'auto',
//         'Container' => 'auto',
//     ),

//     'Outputs' => array(
//         array(
//             'Key' => 'vid6496675d09f4e.mp4',
//             'Rotate' => 'auto',
//             'PresetId' => '1351620000001-000020',
//         ),
//     ),
// ));
// // get the job data as array
// $jobData = $job->get('Job');


// dd($jobData );
// dd( $jobData['Timing']);
// $jobId1 = $jobData['Timing']['StartTimeMillis'];
// $jobId1 = $jobData['Timing']['FinishTimeMillis'];
// dd( $jobData['Timing']['FinishTimeMillis'] - $jobData['Timing']['StartTimeMillis'] );

//$jobStatusRequest = $elasticTranscoder->readJob(array('Id' => $jobData['Id']));
 $jobStatusRequest = $elasticTranscoder->readJob(array('Id' => '1687583948824-2w88i2'));
 $jobDetails = $jobStatusRequest['Job'] ;
 //dd($jobDetails);
 // echo "OutPut File size : " .$jobDetails['Output']['FileSize']/1000 .'KB' ;
 // echo "Input File size : " .$jobDetails['Input']['DetectedProperties']['FileSize']/1000 .'KB' ;

 if ($jobDetails['Output']['FileSize'] < $jobDetails['Input']['DetectedProperties']['FileSize']) {
    echo "out put file size  is less than input file , compression logic works";
 }
// if ($jobDetails['Output']['FileSize']) {
//     // code...
// }
 // dd( $jobDetails['Output']['FrameRate']);
// // dd($jobDetails['Job']);
// if ($jobDetails['Status'] == 'Submitted') {
//     echo "Encoding under progress ...";
// }
// elseif($jobDetails['Status'] == 'Complete'){
//       echo "time taken for transcoding : " . ($jobDetails['Timing']['FinishTimeMillis'] - $jobDetails['Timing']['StartTimeMillis'])/1000 ;

// }



//  $result = $s3->getObject([
//         'Bucket' => $bucket,
//         'Key'    => $keyname
//     ]);

//     // Display the object in the browser.
//     header("Content-Type: {$result['ContentType']}");
//     echo $result['Body'];
// } catch (S3Exception $e) {
//     echo $e->getMessage() . PHP_EOL;
// }



   


//     $cmd = $s3client->getCommand('GetObject', [
//     'Bucket' => $bucket_name,
//     'Key' => 'three/-xxcvid6496675d09f4e.mp4',
// ]);

// $request = $s3client->createPresignedRequest($cmd, '+20 minutes');
// // Get the actual presigned-url
// $presignedUrl = (string)$request->getUri();





dd($file);



    }














     public function uploadVideox(Request $request)
    {

        if($request->has('video_file')){
            // $track = new GetId3(request()->file('video_file'));
            // $play_time_in_sec = $track->getPlaytimeSeconds();
            // if ($play_time_in_sec > 60) 
            //     echo "Video play time is greater than 60 second \n";
            // else 
            //     echo "Video play time is less than 60 second \n";




$elasticTranscoder = ElasticTranscoderClient::factory(array(
    'credentials' => array(
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
    ),
    'region' => env('AWS_REGION'), // dont forget to set the region
     'version' => 'latest',

));


$job = $elasticTranscoder->createJob(array(

    'PipelineId' => '1687342954094-9si0mx',

    'OutputKeyPrefix' => 'eco/ded',

    'Input' => array(
        'Key' => 'pexels-pressmaster-3209828-3840x2160-25fps.mp4',
        'FrameRate' => 'auto',
        'Resolution' => 'auto',
        'AspectRatio' => 'auto',
        'Interlaced' => 'auto',
        'Container' => 'auto',
    ),

    'Outputs' => array(
        array(
            'Key' => 'myOutputnew.mp4',
            'Rotate' => 'auto',
            'PresetId' => '1351620000001-000020',
        ),
    ),
));
dd($job);


                 
 
           
           
        }

    }
}
