<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use \Firebase\JWT\JWT;

class Mobile_otp extends API
{

	public function __construct()
	{
		parent::__construct();
		$this->load->model('model_api_mobile_otp');
	}



	/**
	 * @api {post} /mobile_otp/add Add Mobile otp.
	 * @apiVersion 0.1.0
	 * @apiName AddMobile otp
	 * @apiGroup mobile_otp
	 * @apiHeader {String} X-Api-Key Mobile otps unique access-key.
	 * @apiPermission Mobile otp Cant be Accessed permission name : api_mobile_otp_add
	 *
	 * @apiParam {String} Mobile_no Mandatory mobile_no of Mobile otps. Input Mobile No Max Length : 20. 
	 *
	 * @apiSuccess {Boolean} Status status response api.
	 * @apiSuccess {String} Message message response api.
	 *
	 * @apiSuccessExample Success-Response:
	 *     HTTP/1.1 200 OK
	 *
	 * @apiError ValidationError Error validation.
	 *
	 * @apiErrorExample Error-Response:
	 *     HTTP/1.1 403 Not Acceptable
	 *
	 */
	public function add_post()
	{
		$this->is_allowed('api_mobile_otp_add', false);

		$this->form_validation->set_rules('mobile_no', 'Mobile No', 'trim|required|max_length[20]');

		if ($this->form_validation->run()) {
			$otp_length = get_option('otp_length');
			$otp_length = (int)$otp_length - 1;

			$min_otp = pow(10, $otp_length);
			$max_otp = $min_otp * 10 - 1;
			$otp = mt_rand($min_otp, $max_otp);

			$save_data = [
				'mobile_no' => $this->input->post('mobile_no'),
				'otp' => $otp
			];

			$this->model_api_mobile_otp->delete_expired($save_data['mobile_no']);

			$save_mobile_otp = $this->model_api_mobile_otp->store($save_data);

			if ($save_mobile_otp) {
				$this->load->library('sms');
				try {
					$this->sms->send_otp($save_data['mobile_no'], $otp);
				} catch (Exception $e) {
					log_message('error', 'OTP gateway error');
					log_message('error', $e->getMessage());
					$this->response([
						'status'        => false,
						'message'       => 'Unable to send OTP'
					], API::HTTP_NOT_ACCEPTABLE);
				}

				$this->response([
					'status' 	=> true,
					'message' 	=> 'Your data has been successfully stored into the database'
				], API::HTTP_OK);

			} else {
				$this->response([
					'status' 	=> false,
					'message' 	=> cclang('data_not_change')
				], API::HTTP_NOT_ACCEPTABLE);
			}

		} else {
			$this->response([
				'status' 	=> false,
				'message' 	=> validation_errors()
			], API::HTTP_NOT_ACCEPTABLE);
		}
	}

	/**
	 * @api {post} /mobile_otp/verify Verify Mobile otp.
	 * @apiVersion 0.1.0
	 * @apiName VerifyMobile otp
	 * @apiGroup mobile_otp
	 * @apiHeader {String} X-Api-Key Mobile otps unique access-key.
	 * @apiPermission Mobile otp Cant be Accessed permission name : api_mobile_otp_add
	 *
	 * @apiParam {String} Mobile_no Mandatory mobile_no of Mobile otps. Input Mobile No Max Length : 20.
	 * @apiParam {String} Otp Mandatory otp of Mobile otps. Input Mobile No Max Length : 20.
	 *
	 * @apiSuccess {Boolean} Status status response api.
	 * @apiSuccess {String} Message message response api.
	 *
	 * @apiSuccessExample Success-Response:
	 *     HTTP/1.1 200 OK
	 *
	 * @apiError ValidationError Error validation.
	 *
	 * @apiErrorExample Error-Response:
	 *     HTTP/1.1 403 Not Acceptable
	 *
	 */
	public function verify_post()
	{
		$this->is_allowed('api_mobile_otp_add', false);

		$this->form_validation->set_rules('mobile_no', 'Mobile No', 'trim|required|max_length[20]');
		$this->form_validation->set_rules('otp', 'Otp', 'trim|required|max_length[20]');

		if ($this->form_validation->run()) {

			$mobile_verified = $this->model_api_mobile_otp->verify_otp($this->input->post('mobile_no'), $this->input->post('otp'));


			if ($mobile_verified) {
				$this->load->library('sms');

				$this->response([
					'status' 	=> true,
					'message' 	=> 'Otp and mobile verified'
				], API::HTTP_OK);

			} else {
				$this->response([
					'status' 	=> false,
					'message' 	=> 'Mobile not verified'
				], API::HTTP_NOT_ACCEPTABLE);
			}

		} else {
			$this->response([
				'status' 	=> false,
				'message' 	=> validation_errors()
			], API::HTTP_NOT_ACCEPTABLE);
		}
	}
}

/* End of file Mobile otp.php */
/* Location: ./application/controllers/api/Mobile otp.php */
