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
 
}
