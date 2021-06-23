<?php
	/**
	 * Created by PhpStorm.
	 * User: Luka
	 * Date: 07.06.2019
	 * Time: 17:27
	 */
	include_once 'dbconfig.php';
	include_once 'Bot.php';
	$whatsappBot = new whatsAppBot(false);
	$dbconfig = new DBConfig();
	$tasks = $dbconfig->GetTasks();
	$texts = json_decode(file_get_contents('Texts.json'), true);
	foreach ($tasks as $task)
	{
		$dateTimeNow = new DateTime();
		$dateTimeCreate = new DateTime($task->CreateDate);
		$interval = $dateTimeNow->diff($dateTimeCreate);
		if(getTotalHours($interval)>1)
		{
			$whatsappBot->sendMessage($task->Phone,$texts['DiscountViewed']);
			$dbconfig->RemoveTasks($task->Phone);
			$dbconfig->UpdateStage($task->Phone,2);
		}
	}
	
	function getTotalHours(DateInterval $int){
		return ($int->d * 24) + $int->h + $int->i / 60;
	}