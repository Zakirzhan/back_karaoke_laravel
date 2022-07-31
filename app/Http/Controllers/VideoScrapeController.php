<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class VideoScrapeController extends Controller
{
	// mix status - 
	   // 0 - nothing 
	   // 1 - downloading youtube 
	   // 2 - track uploaded 
	   // 3 - creating mix 
	   // 4 - ready
    public function getMixData(Request $request, $videoId){

    	$mix = DB::table('mixes')->where('yt_id', $videoId)->first(); 
    	if(isset($mix)) return $mix;
    	else return ["status"=>0, "error"=>"not found"];
    }

    public function downloadVideo(Request $request, $videoId){
 	  
    	$response = $this->sendYtVideo($videoId); 

    	$data = json_decode($response, true);

 		if(isset($data['song_id'])){
 			DB::table('mixes')->insert([
			    'yt_id' => $videoId,
			    'track_id' => $data['song_id'],
			    'task_id' => $data['fetch_task'],
	 		    'status' => 1,
			    'server_id' => 0,
			    'created_at' => date('Y-m-d H:i:s'),
			]); 
 		}  

		return $response;

    } 

    public function getDownloadingStatus(Request $request, $videoId)
    {

    	$mix = DB::table('mixes')->where('yt_id', $videoId)->first(); 

    	$url = 'https://first.karaoke.red/api/source-track/'.$mix->track_id.'/';

    	$response = Http::withOptions(['verify' => false])->get($url);
	   
 		$json = json_decode($response, true); 
 		
 		if($json['fetch_task_status'] == 'Done'){
 			$json['url'] = 'https://'.$this->getServerAddress($mix->server_id).$json['url'];

 			DB::table('mixes')
            ->where('yt_id', $videoId)
            ->update(['status' => 2]);  
 		}

 		return $json; 
    }
 	
    public function createMixFromTrack($videoId)
    {
    	$mix = DB::table('mixes')->where('yt_id', $videoId)->first(); 

    	$response = $this->sendTrackToMixing($mix->track_id);

    	$json = json_decode($response, true); 
    	
    	if(isset($json['id'])){

    		DB::table('mixes')
	            ->where('yt_id', $videoId)
	            ->update(['mix_id' => $json['id'], 'status' => 3]);  
	    }
   		return $response;
    }


    public function getMixingStatus($videoId)
    {	

    	$mix = DB::table('mixes')->where('yt_id', $videoId)->first(); 

    	$url = 'http://first.karaoke.red/api/mix/dynamic/'.$mix->mix_id.'/';

    	$response = Http::withOptions(['verify' => false])->get($url);
	   
 		$json = json_decode($response, true); 

 		if(isset($json['vocals_url']) && isset($json['accompaniment_url'])){
 			$json['vocals_url'] = 'https://'.$this->getServerAddress($mix->server_id).$json['vocals_url'];
 			$json['accompaniment_url'] = 'https://'.$this->getServerAddress($mix->server_id).$json['accompaniment_url'];
 			
 			DB::table('mixes')
            ->where('yt_id', $videoId)
            ->update([
            	'vocals_url' => $json['vocals_url'], 
            	'accompaniment_url' => $json['accompaniment_url'],
            	'status' => 4]);  
 		}

 		return $json;  
    }
    private function getServerAddress($server_id)
    {
    	$servers = ['first.karaoke.red','second.karaoke.red'];
    	return $servers[$server_id];
    }

    private function sendYtVideo($videoId){  

		$curl = curl_init();

		$json = '{"youtube_link":"https://www.youtube.com/watch?v='.$videoId.'","artist":"artist'.rand(0,1230).'","title":"song'.rand(0,1230).'"}';
		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'http://first.karaoke.red/api/source-track/youtube/',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS =>$json,
		  CURLOPT_HTTPHEADER => array(
		    'Connection: keep-alive',
		    'Accept: application/json, text/plain, */*',
		    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/97.0.4692.99 Safari/537.36',
		    'Content-Type: application/json',
		    'Origin: http://first.karaoke.red',
		    'Referer: http://first.karaoke.red/',
		    'Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7,kk;q=0.6,ky;q=0.5,fr;q=0.4,mt;q=0.3,zh-CN;q=0.2,zh;q=0.1',
		    'Cookie: csrftoken=3DgGmzhNhnhZQYkBVzTRSJa7ryPCvUcxOKHUizXiXEVG2MiiusxXGxUhg1gnX0Ty'
		  ),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		return $response;

    }

    private function sendTrackToMixing($trackId)
    {
    	$curl = curl_init();

		curl_setopt_array($curl, array(
		  CURLOPT_URL => 'http://first.karaoke.red/api/mix/dynamic/',
		  CURLOPT_RETURNTRANSFER => true,
		  CURLOPT_ENCODING => '',
		  CURLOPT_MAXREDIRS => 10,
		  CURLOPT_TIMEOUT => 0,
		  CURLOPT_FOLLOWLOCATION => true,
		  CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
		  CURLOPT_CUSTOMREQUEST => 'POST',
		  CURLOPT_POSTFIELDS =>'{"source_track":"'.$trackId.'","separator":"spleeter","separator_args":{"random_shifts":0,"iterations":1,"softmask":false,"alpha":1},"bitrate":256}',
		  CURLOPT_HTTPHEADER => array(
		    'Connection: keep-alive',
		    'Accept: application/json, text/plain, */*',
		    'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/97.0.4692.99 Safari/537.36',
		    'Content-Type: application/json',
		    'Origin: http://first.karaoke.red',
		    'Referer: http://first.karaoke.red/',
		    'Accept-Language: ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7,kk;q=0.6,ky;q=0.5,fr;q=0.4,mt;q=0.3,zh-CN;q=0.2,zh;q=0.1',
		    'Cookie: csrftoken=3DgGmzhNhnhZQYkBVzTRSJa7ryPCvUcxOKHUizXiXEVG2MiiusxXGxUhg1gnX0Ty'
		  ),
		));

		$response = curl_exec($curl);

		curl_close($curl);
		return $response;
    }
 
 }
