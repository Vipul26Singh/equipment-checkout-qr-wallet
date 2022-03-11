<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use Infobip as IBP;

class Infobip {

	private $api_key = null;
	private $base_url = '';
	public function __construct() {
		$this->api_key = get_option('infobip_api_key');
		$this->base_url = get_option('infobip_base_url');

		if(empty($this->api_key) || empty($this->base_url)) {
			throw new Exception("Infobip api keys does not exists");
		}
	}

	public function send($mobile_number, $msg) {

		$configuration = (new IBP\Configuration())
			->setHost($this->base_url)
			->setApiKeyPrefix('Authorization', 'App')
			->setApiKey('Authorization', $this->api_key);

		$client = new GuzzleHttp\Client();

		$sendSmsApi = new IBP\Api\SendSmsApi($client, $configuration);
		$destination = (new IBP\Model\SmsDestination())->setTo($mobile_number);
		$message = (new IBP\Model\SmsTextualMessage())
			->setFrom('InfoSMS')
			->setText($msg)
			->setDestinations([$destination]);
		$request = (new IBP\Model\SmsAdvancedTextualRequest())
			->setMessages([$message]);


		try {
			$smsResponse = $sendSmsApi->sendSmsMessage($request);
		} catch (Throwable $apiException) {
			throw new Exception($apiException->getMessage());
		}
	}
}
