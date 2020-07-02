<?php

namespace App\Lib;

use Exception;
use DB;
use Illuminate\Support\Facades\Log;

class FCM
{
    public function push($department_code, $course_code, $notify_data, $custom_data)
    {
		Log::info('FCM Push');
		Log::info($department_code);
		Log::info($course_code);
		Log::info(print_r($notify_data, true));
		Log::info(print_r($custom_data, true));
		
    	try {
			$server_key = "AAAAe26EEU8:APA91bFxEGh1ueFmaONtoFllP0uyVfZ0FIX0dk4Eaht-yJ0YL3_9CVvuu8u0_fVCeR_LfQ5zuqeEAMCl0JHA8qZUtO7qfERC_Qw5ObBY273wtW3cbHjlBHbIz3RQ79QhQ8Cr4nu8NpHO";
			$base_url = "https://fcm.googleapis.com/fcm/send";
			
			$targets = DB::table('FCMToken')
				->when(
					$department_code,
					function($q) use ($department_code){
						return $q->where('department_code', $department_code);
					}
				)
				->when(
					$course_code,
					function($q) use ($course_code){
						return $q->where('course_code', $course_code);
					}
				)
				->select('id', 'token')
				->get()
				;
				
			//Log::info(print_r($targets, true));
			
			foreach ($targets as $target) {
				//Log::info(print_r($target, true));
				$custom_data["department_code"] = $department_code;
				$custom_data["course_code"] = $course_code;
				
				$content = [
					"to" => $target->token,
					"priority" => "high",
					"notification" => $notify_data,
					"data" => $custom_data
				];
				
				$header = array(
				     "Content-Type:application/json"
				    ,"Authorization: key=".$server_key
				);
				
				$context = stream_context_create(array(
				    "http" => array(
				         'method' => 'POST'
				        ,'header' => implode("\r\n",$header)
				        ,'content'=> json_encode($content)
				    )
				));
				
				$ret = json_decode(file_get_contents($base_url, false, $context));
				//Log::info(print_r($ret, true));
				//Log::info(print_r($ret->failure, true));
				
				if (!!$ret->failure) {
					DB::table('FCMToken')
						->where('id', $target->id)
						->delete();
				}
			}
			
			return true;
		} catch (Exception $exception) {
			Log::error('FCM Push Error');
			Log::error(print_r($exception, true));
			return $exception;
		}
    }
}
