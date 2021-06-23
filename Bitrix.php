<?php
	/**
	 * Created by PhpStorm.
	 * User: Luka
	 * Date: 16.06.2019
	 * Time: 17:31
	 */
	class Bitrix
	{
		var $url = "https://b24-c0l83n.bitrix24.ru/rest/1/i7khkzp9w07nwudx/crm.deal.add";
		
		function __construct()
		{
		}
		
		public function AddUser($phone)
		{
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $this->url);
			$field = array('fields' => array('TITLE' => $phone, "UF_CRM_PHONE" => $phone));
			curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($field));
		}
	}