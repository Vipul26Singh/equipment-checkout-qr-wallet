<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Sms {

	$sms_gateway = null;
	$sms_enabled = true;
	$otp_template = null;
	public function __construct() {
		$this->sms_gateway = get_option('sms_gateway');
		$this->otp_template = get_option('otp_template');


		if(empty($sms_gateway)) {
			log_message('error', 'SMS gateway is not enabled');
			$this->sms_enabled = false;
		} else {
			include_once 'Smsgateways/'.$this->sms_gateway.'.php';

			try {
				$this->sms_gateway = new $this->sms_gateway;
			} catch Exception($e) {
				log_message('error', 'Miscofiguration for ' . $this->sms_gateway);
				log_message('error', $e->getMessge());
				throw new Exception($e->getMessge());
			}
		}
		
	}

	public function send_otp($mobile_number, $otp) {
		if(!$this->sms_enabled) {
			return;
		}
		$this->otp_template = get_option('otp_template');

		str_replace("{{otp}}", $otp, $this->otp_template);

		try {
			$this->sms_gateway->send($mobile_number, $this->otp_template);
		} catch Exception($e) {
			log_message('error', 'Unable to send otp for ' . $this->sms_gateway);
			log_message('error', $e->getMessge());
			throw new Exception($e->getMessge());
		}
	}
}
