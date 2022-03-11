<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sms {

	private $sms_gateway = null;
	private $sms_enabled = true;
	private $otp_template = null;

	public function __construct() {
		$this->sms_gateway = get_option('sms_gateway');
		$this->otp_template = get_option('otp_template');


		if(empty($this->sms_gateway)) {
			log_message('error', 'SMS gateway is not enabled');
			$this->sms_enabled = false;
		} else {
			include_once 'Smsgateways/'.$this->sms_gateway.'.php';

			try {
				$this->sms_gateway = new $this->sms_gateway;
			} catch (Exception $e) {
				log_message('error', 'Miscofiguration for ' . $this->sms_gateway);
				log_message('error', $e->getMessage());
				throw new Exception($e->getMessage());
			}
		}
		
	}

	public function send_otp($mobile_number, $otp) {
		if(!$this->sms_enabled) {
			return;
		}
		$this->otp_template = get_option('otp_template');


		$formatted_msg = str_replace("{{otp}}", $otp, $this->otp_template);


		try {
			$this->sms_gateway->send($mobile_number, $formatted_msg);
		} catch (Exception $e) {
			log_message('error', 'Unable to send otp for Infobip ' . $this->otp_template . ' mobile no: ' . $mobile_number);
			log_message('error', $e->getMessage());
			throw new Exception($e->getMessage());
		}
	}
}
