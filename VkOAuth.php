<?php
	/**
	 * Created by PhpStorm.
	 * User: Luka
	 * Date: 09.06.2019
	 * Time: 10:30
	 */
	class VkOAuth
	{
		var $login;
		var $password;
		
		public function __construct()
		{
			$cookies = array();
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, 'https://m.vk.com');
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4'));
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 1);
			$response = curl_exec($ch);
			preg_match('/form method="post" action="(.*)" novalidate>/', $response, $output_array);
			$actionUrl = $output_array[1];
			preg_match('/remixlhk=(.*?);/', $response, $output_array);
			$remixhlk = $output_array[1];
			$remixlang = 0;
			$cookies['remixhlk'] = $remixhlk;
			$cookies['remixlang'] = $remixlang;
			$cookies['remixmdevice'] = '1280/1024/1/!!-!!!!';
			curl_close($ch);
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $actionUrl);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_HEADER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, 'email=tst&pass=tst');
			curl_setopt($ch, REDIRECT, array(
				'Accept-Language: ru-RU,ru;q=0.8,en-US;q=0.6,en;q=0.4', 'Cookie: ' . $this->FormatCookies($cookies), 'AllowAutoRedirect: false'
			));
			$response = curl_exec($ch);
			var_dump($response);
		}
		
		function FormatCookies($cookies)
		{
			$cookieString = "";
			foreach ($cookies as $cookie => $value)
			{
				$cookieString .= $cookie . '=' . $value . ';';
			}
			return $cookieString;
		}
	}
	class VkCaller
	{
		public function wallPost($text)
		{
			$request_params = array(
				'owner_id' => '-170465987', 'message' => $text, 'v' => '5.95', 'access_token' => 'a83400ebd3e9a6afba9012a782e21ca9090e4340d119a65158449244d3756573350290b9788adb1bdca7bo'
			);
			$get_params = http_build_query($request_params);
			$result = json_decode(file_get_contents('https://api.vk.com/method/wall.post?' . $get_params));
			return $result->response->post_id;
		}
		public function wallPostWithImage($text,$image)
		{
			//$image='photo66748_265827614,https://images.unsplash.com/photo-1526319238109-524eecb9b913?ixlib=rb-1.2.1&ixid=eyJhcHBfaWQiOjEyMDd9&w=1000&q=80';
			$request_params = array(
				'owner_id' => '-170465987', 'message' => $text, 'v' => '5.95', 'attachments' =>$image, 'access_token' => 'a83400ebd3e9a6afba9012a782e21ca9090e4340d119a65158449244d3756573350290b9788adb1bdca7bo'
			);
			$get_params = http_build_query($request_params);
			$result = json_decode(file_get_contents('https://api.vk.com/method/wall.post?' . $get_params));
			return $result->response->post_id;
		}
		
		public function uploadPhotoAndwallPost($text,$url)
		{
			$request_params = array(
				'owner_id' => '-170465987', 'v' => '5.95', 'access_token' => 'a83400ebd3e9a6afba9012a782e21ca9090e4340d119a65158449244d3756573350290b9788adb1bdca7bo'
			);
			$get_params = http_build_query($request_params);
			$result = json_decode(file_get_contents('https://api.vk.com/method/photos.getWallUploadServer?' . $get_params), true);
			$uploadurl = $result['response']['upload_url'];
			$contentOrFalseOnFailure = file_get_contents($url);
			$uploaded = $this->UploadToUrl($uploadurl,$contentOrFalseOnFailure);
			$saved = $this->Save($uploaded);
			$id =  $saved->response[0]->id;
			$owner_id =  $saved->response[0]->owner_id;
			$photo_name = "photo$owner_id"."_$id";
			$this->wallPostWithImage($text,$photo_name);
		}
		
		function UploadToUrl($url, $data)
		{
			$id = uniqid();
			file_put_contents("$id.png",$data);
			$post_fields = array('photo' => new CURLFile(realpath("$id.png")));
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_URL, $url);
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
			curl_setopt($ch, CURLOPT_POST, 1);
			curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
			curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
			curl_setopt($ch, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 6.1; WOW64) AppleWebKit/537.17 (KHTML, like Gecko) Chrome/24.0.1312.57          Safari/537.17');
			$res = json_decode(curl_exec($ch));
			curl_close($ch);
			if(file_exists("$id.png")) unlink("$id.png");
			return $res;
		}
		function Save($params)
		{
			//json_decode( $params->photo,true)[0]['photo']
			//var_dump(json_decode( $params->photo,true));
			$request_params = array( 'v' => '5.95', 'access_token' => '33603cc6d59ad1e15fab13c04087604aed68f304909a8ac1f70808012e77299eb6458781b9269dca93103o',
				'photo'=>$params->photo,'server'=>$params->server,"hash"=>$params->hash,
			);
			$get_params = http_build_query($request_params);
			$result = json_decode(file_get_contents('https://api.vk.com/method/photos.saveWallPhoto?' . $get_params));
			return $result;
		}
	}
