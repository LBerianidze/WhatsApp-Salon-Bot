<?php	/**	 * Created by PhpStorm.	 * User: Лука	 * Date: 06.06.2019	 * Time: 19:31	 */	include_once "dbconfig.php";	include_once 'VkOAuth.php';	include_once 'Bitrix.php';	class whatsAppBot	{		var $APIurl = '';		var $token = '';				public function __construct($run)		{			if ($run)			{				$dbconfig = new DBConfig();				$json = file_get_contents('php://input');				$decoded = json_decode($json, true);				$texts = json_decode(file_get_contents('Texts.json'), true);				$ini = parse_ini_file('configs.ini');				$this->APIurl = $ini['service_url'];				$this->token = $ini['service_token'];				if (isset($decoded['messages']))				{					foreach ($decoded['messages'] as $message)					{						if (!$message['fromMe'])						{							$chatID = $message['chatId'];							$text = explode(' ', trim($message['body']));							if (!$dbconfig->UserExist($chatID))							{								$dbconfig->AddUser($chatID);								if (mb_strtolower($text[0], 'UTF-8') == 'салон1')								{									$this->sendMessage($chatID, str_replace("{MENU}", $texts['MenuItems'], $texts['GreetingForNewUsers']));								}								else								{									$this->sendMessage($chatID, str_replace("{MENU}", $texts['MenuItems'], $texts['Greeting']));								}								sleep(1);								$this->sendContact($chatID, $ini['greeting_number']);								$bitrix = new Bitrix();								$bitrix->AddUser($chatID);							}							else if (mb_strtolower($text[0], 'UTF-8') == 'привет' || mb_strtolower($text[0], 'UTF-8') == 'салон' || mb_strtolower($text[0], 'UTF-8') == 'салон1' || mb_strtolower(trim($message['body']), 'UTF-8') == 'хочу увидеть информацию про салон, цены, акции, работы, отзывы.')							{								$this->sendMessage($chatID, str_replace("{MENU}", $texts['MenuItems'], $texts['Greeting']));								sleep(1);								$this->sendContact($chatID, $ini['bot_phone']);							}							else							{								$user = $dbconfig->GetUser($chatID);								if ($user->Stage == 1)								{									if ($text[0] == 1)									{										$this->sendMessage($chatID, str_replace("{MENU}", $texts['MenuItems'], $texts['SignUp']));									}									else if ($text[0] == 2)									{										$this->file($chatID, $ini['discount_url'], 'discountcard.jpg', str_replace("{MENU}", $texts['MenuItems'], str_replace('{x2}', $user->PinCode, str_replace('{x1}', $user->CardNumber, $texts['VipCard']))));										$dbconfig->AddTask($chatID);									}									else if ($text[0] == 3)									{										$dbconfig->UpdateStage($chatID, 3);										$this->sendMessage($chatID, $texts['RequestReview']);									}									else if ($text[0] == 4)									{										$this->file($chatID, 'http://vh258879.eurodir.ru/WhatsApp%20Benedict%20Bot/Salon/prices.jpg', 'prices.jpg', str_replace("{MENU}", $texts['MenuItems'], $texts['PriceList']));										//$this->sendMessage($chatID, str_replace("{MENU}", $texts['MenuItems'], $texts['PriceList']));									}									else if ($text[0] == 5)									{										$dbconfig->UpdateStage($chatID, 7);										$this->sendMessage($chatID, str_replace('{x1}', $user->PersonalCode, $texts['BotFree']));										sleep(1);										$this->sendMessage($chatID, $texts['PriceLeaveReview']);									}									else if ($text[0] == 6)									{										$dbconfig->UpdateStage($chatID, 6);										$exploded = explode(',', $user->Options);										$sendmessage = $texts['Options'];										for ($i = 0; $i < count($exploded); $i++)										{											$sendmessage = str_replace("{x$i}", $exploded[$i] == '1' ? "Вкл" : "Выкл", $sendmessage);										}										$this->sendMessage($chatID, $sendmessage);									}									else if ($text[0] == 7)									{										//$this->geo($chatID);										//sleep(1);										$this->sendMessage($chatID, str_replace("{MENU}", $texts['MenuItems'], $texts['Contacts']));									}									else if ($text[0] == 8)									{										$this->sendMessage($chatID, str_replace("{MENU}", $texts['MenuItems'], $texts['OurWorks']));									}									else									{										$this->sendMessage($chatID, str_replace("{MENU}", $texts['MenuItems'], $texts['WrongMenuItem']));									}								}								else if ($user->Stage == 2)								{									if (ctype_digit(strval($text[0])))									{										if ($text[0] > 0 && $text[0] <= 5)										{											$dbconfig->AddReview($chatID, 1, $text[0], '');											//Add mark											if ($text[0] <= 3)											{												$this->sendMessage($chatID, $texts['Dislike']);											}											else											{												$this->sendMessage($chatID, $texts['Like']);											}											sleep(1);											$this->sendMessage($chatID, str_replace("{MENU}", $texts['MenuItems'], $texts['MainMenu']));											$dbconfig->UpdateStage($chatID, 1);										}										else										{											$this->sendMessage($chatID, str_replace("{MENU}", $texts['MenuItems'], $texts['MainMenu']));											$dbconfig->UpdateStage($chatID, 1);										}									}									else if ($text[0] == '0')									{										$this->sendMessage($chatID, str_replace("{MENU}", $texts['MenuItems'], $texts['MainMenu']));										$dbconfig->UpdateStage($chatID, 1);									}									else									{										$this->sendMessage($chatID, $texts['WrongCommand']);									}								}								else if ($user->Stage == 3)								{									if (strval($text[0]) === strval(intval($text[0])))									{										if ($text[0] > 0 && $text[0] <= 5)										{											if ($text[0] <= 3)											{												$this->sendMessage($chatID, $texts['Dislike']);											}											else											{												$this->sendMessage($chatID, $texts['Like']);											}											$dbconfig->UpdateStage($chatID, 5);										}										else if ($text[0] == -1 || $text[0] == 0)										{											$this->sendMessage($chatID, str_replace("{MENU}", $texts['MenuItems'], $texts['MainMenu']));											$dbconfig->UpdateStage($chatID, 1);										}										else										{											$this->sendMessage($chatID, $texts['WrongValue']);										}									}									else if ($text[0] == '0')									{										$this->sendMessage($chatID, str_replace("{MENU}", $texts['MenuItems'], $texts['MainMenu']));										$dbconfig->UpdateStage($chatID, 1);									}									else									{										$this->sendMessage($chatID, $texts['WrongCommand']);									}								}								else if ($user->Stage == 4)								{									if (strval($text[0]) === strval(intval($text[0])))									{										if ($text[0] == 0)										{											$dbconfig->UpdateStage($chatID, 1);											$this->sendMessage($chatID, str_replace("{MENU}", $texts['MenuItems'], $texts['MainMenu']));										}										else										{											$dbconfig->UpdateStage($chatID, 5);											$this->sendMessage($chatID, $texts['LeaveReview']);										}									}									else if ($text[0] == '0')									{										$this->sendMessage($chatID, str_replace("{MENU}", $texts['MenuItems'], $texts['MainMenu']));										$dbconfig->UpdateStage($chatID, 1);									}									else									{										$this->sendMessage($chatID, $texts['WrongCommand']);									}								}								else if ($user->Stage == 5)								{									if ($text[0] == '0')									{										$this->sendMessage($chatID, str_replace("{MENU}", $texts['MenuItems'], $texts['MainMenu']));										$dbconfig->UpdateStage($chatID, 1);									}									else if ($message['type'] == 'chat')									{										$vkcaller = new VkCaller();										$messagebody = $message['body'];										$messagebody .= $texts['VkPostAddBody'];										if ($user->ImageUrl == '')										{											$result_code = $vkcaller->wallPost($messagebody);										}										else										{											$result_code = $vkcaller->uploadPhotoAndwallPost($messagebody, $user->ImageUrl);										}										$group_id = $ini['vk_group_id'];										$this->sendMessage($chatID, str_replace('{x1}', "https://vk.com/wall-{$group_id}_{$result_code}", $texts['VkResult']));										$this->sendMessage($chatID, str_replace("{MENU}", $texts['MenuItems'], $texts['MainMenu']));										$dbconfig->UpdateStage($chatID, 1);										$dbconfig->UpdateImage($chatID, '');									}									else if ($message['type'] == 'image' && $message['caption'] != "")									{										$vkcaller = new VkCaller();										$messagebody = $message['caption'];										$messagebody .= $texts['VkPostAddBody'];										$result = $vkcaller->uploadPhotoAndwallPost($messagebody, $message['body']);										$group_id = $ini['vk_group_id'];										$this->sendMessage($chatID, str_replace('{x1}', "https://vk.com/wall-{$group_id}_{$result}", $texts['VkResult']));										$this->sendMessage($chatID, str_replace("{MENU}", $texts['MenuItems'], $texts['MainMenu']));										$dbconfig->UpdateStage($chatID, 1);										$dbconfig->UpdateImage($chatID, '');									}									else if ($message['type'] == 'image')									{										$dbconfig->UpdateImage($chatID, $message['body']);										$this->sendMessage($chatID, $texts['RequestTextAfterImage']);									}								}								else if ($user->Stage == 6)								{									if (strval($text[0]) === strval(intval($text[0])) && $text[0] >= 1 && $text[0] <= 4)									{										$exploded = explode(',', $user->Options);										$exploded[$text[0] - 1] = $exploded[$text[0] - 1] == "1" ? "0" : "1";										$dbconfig->UpdateStage($chatID, 6);										$sendmessage = $texts['Options'];										for ($i = 0; $i < count($exploded); $i++)										{											$sendmessage = str_replace("{x$i}", $exploded[$i] == '1' ? "Вкл" : "Выкл", $sendmessage);										}										$dbconfig->UpdateOptions($chatID, implode(',', $exploded));										$this->sendMessage($chatID, $sendmessage);;									}									else if ($text[0] == '0')									{										$this->sendMessage($chatID, str_replace("{MENU}", $texts['MenuItems'], $texts['MainMenu']));										$dbconfig->UpdateStage($chatID, 1);									}									else									{										$this->sendMessage($chatID, $texts['WrongCommand']);									}								}								else if ($user->Stage == 7)								{									if ($text[0] == '0')									{										$this->sendMessage($chatID, str_replace("{MENU}", $texts['MenuItems'], $texts['MainMenu']));										$dbconfig->UpdateStage($chatID, 1);									}									else if ($text[0] == '1')									{										$dbconfig->UpdateStage($chatID, 8);										$this->sendMessage($chatID, $texts['LeaveReview']);									}									else									{										$this->sendMessage($chatID, $texts['WrongCommand']);									}								}								else if ($user->Stage == 8)								{									if ($text[0] == '0')									{										$this->sendMessage($chatID, str_replace("{MENU}", $texts['MenuItems'], $texts['MainMenu']));										$dbconfig->UpdateStage($chatID, 1);									}									else if ($message['type'] == 'chat')									{										//публикуем пост										$vkcaller = new VkCaller();										$messagebody = $message['body'];										$messagebody .= $texts['VkPostAddBody'];										if ($user->ImageUrl == '')										{											$result_code = $vkcaller->wallPost($messagebody);										}										else										{											$result_code = $vkcaller->uploadPhotoAndwallPost($messagebody, $user->ImageUrl);										}										$group_id = $ini['vk_group_id'];										$this->sendMessage($chatID, str_replace('{x1}', "https://vk.com/wall-{$group_id}_{$result_code}", $texts['VkResult']));										$this->sendMessage($chatID, str_replace("{MENU}", $texts['MenuItems'], $texts['MainMenu']));										$dbconfig->UpdateStage($chatID, 1);										$dbconfig->UpdateImage($chatID, '');									}									else if ($message['type'] == 'image' && $message['caption'] != "")									{										$vkcaller = new VkCaller();										$messagebody = $message['caption'];										$messagebody .= $texts['VkPostAddBody'];										$result = $vkcaller->uploadPhotoAndwallPost($messagebody, $message['body']);										$group_id = $ini['vk_group_id'];										$this->sendMessage($chatID, str_replace('{x1}', "https://vk.com/wall-{$group_id}_{$result}", $texts['VkResult']));										$this->sendMessage($chatID, str_replace("{MENU}", $texts['MenuItems'], $texts['MainMenu']));										$dbconfig->UpdateStage($chatID, 1);										$dbconfig->UpdateImage($chatID, '');									}									else if ($message['type'] == 'image')									{										$dbconfig->UpdateImage($chatID, $message['body']);										$this->sendMessage($chatID, $texts['RequestTextAfterImage']);									}								}								else								{									$this->sendMessage($chatID, $texts['WrongCommand']);								}							}						}					}				}			}		}				public function file_get_contents_utf8($fn)		{			$content = file_get_contents($fn);			return mb_convert_encoding($content, 'UTF-8', mb_detect_encoding($content, 'UTF-8, ISO-8859-1', true));		}		//sends a file. it is called when the bot gets the command "file"		//@param $chatId [string] [required] - the ID of chat where we send a message		//@param $format [string] [required] - file format, from the params in the message body (text[1], etc)		public function file($chatId, $format, $filename, $text)		{			$data = array(				'chatId' => $chatId, 'body' => $format, 'filename' => $filename, 'caption' => $text			);			$this->sendRequest('sendFile', $data);		}				public function geo($chatId)		{			$data = array(				'lat' => 55.7917643, 'lng' => 37.5704959, 'address' => 'Нижняя Масловка,20', 'chatId' => $chatId			);			$this->sendRequest('sendLocation', $data);		}		//sends a location. it is called when the bot gets the command "geo"		//@param $chatId [string] [required] - the ID of chat where we send a message		public function sendContact($chatId, $contact)		{			$data = array(				'chatId' => $chatId, 'contactId' => $contact			);			$this->sendRequest('sendContact', $data);		}		//creates a group. it is called when the bot gets the command "group"		//@param chatId [string] [required] - the ID of chat where we send a message		//@param author [string] [required] - "author" property of the message		public function group($author)		{			$phone = str_replace('@c.us', '', $author);			$data = array(				'groupName' => 'Group with the bot PHP', 'phones' => array($phone), 'messageText' => 'It is your group. Enjoy'			);			$this->sendRequest('group', $data);		}				public function sendMessage($chatId, $text)		{			$data = array('chatId' => $chatId, 'body' => $text);			$this->sendRequest('message', $data);		}				public function sendRequest($method, $data)		{			$url = $this->APIurl . $method . '?token=' . $this->token;			if (is_array($data))			{				$data = json_encode($data);			}			$params = array();			$params['http'] = array();			$params['http']['method'] = "POST";			$params['http']['header'] = "Content-type: application/json";			$params['http']['content'] = $data;			file_get_contents($url, false, stream_context_create($params));		}	}//execute the class when this file is requested by the instance	new whatsAppBot(true);