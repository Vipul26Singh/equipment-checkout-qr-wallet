<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Qrcode {

	private $enable_wallet = false;
	private $CI = null;

	public function __construct() {
		$this->enable_wallet = get_option('enable_wallet');
		$this->CI = &get_instance();

		if( empty($this->enable_wallet) ) {
			$this->enable_wallet = false;
		}
	}

	public function generate_code($data) {
		$qr_code = (new QRCode)->render($data);
		echo $qr_code;
		return $qr_code;
	}	

}
