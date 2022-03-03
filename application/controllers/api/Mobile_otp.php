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
	 * @api {get} /mobile_otp/all Get all mobile_otps.
	 * @apiVersion 0.1.0
	 * @apiName AllMobileotp 
	 * @apiGroup mobile_otp
	 * @apiHeader {String} X-Api-Key Mobile otps unique access-key.
	 * @apiPermission Mobile otp Cant be Accessed permission name : api_mobile_otp_all
	 *
	 * @apiParam {String} [Filter=null] Optional filter of Mobile otps.
	 * @apiParam {String} [Field="All Field"] Optional field of Mobile otps : .
	 * @apiParam {String} [Start=0] Optional start index of Mobile otps.
	 * @apiParam {String} [Limit=10] Optional limit data of Mobile otps.
	 *
	 *
	 * @apiSuccess {Boolean} Status status response api.
	 * @apiSuccess {String} Message message response api.
	 * @apiSuccess {Array} Data data of mobile_otp.
	 *
	 * @apiSuccessExample Success-Response:
	 *     HTTP/1.1 200 OK
	 *
	 * @apiError NoDataMobile otp Mobile otp data is nothing.
	 *
	 * @apiErrorExample Error-Response:
	 *     HTTP/1.1 403 Not Acceptable
	 *
	 */
	public function all_get()
	{
		$this->is_allowed('api_mobile_otp_all', false);

		$filter = $this->get('filter');
		$field = $this->get('field');
		$limit = $this->get('limit') ? $this->get('limit') : $this->limit_page;
		$start = $this->get('start');

		$select_field = [''];
		$mobile_otps = $this->model_api_mobile_otp->get($filter, $field, $limit, $start, $select_field);
		$total = $this->model_api_mobile_otp->count_all($filter, $field);

		$data['mobile_otp'] = $mobile_otps;
				
		$this->response([
			'status' 	=> true,
			'message' 	=> 'Data Mobile otp',
			'data'	 	=> $data,
			'total' 	=> $total
		], API::HTTP_OK);
	}

	
	/**
	 * @api {get} /mobile_otp/detail Detail Mobile otp.
	 * @apiVersion 0.1.0
	 * @apiName DetailMobile otp
	 * @apiGroup mobile_otp
	 * @apiHeader {String} X-Api-Key Mobile otps unique access-key.
	 * @apiPermission Mobile otp Cant be Accessed permission name : api_mobile_otp_detail
	 *
	 * @apiParam {Integer} Id Mandatory id of Mobile otps.
	 *
	 * @apiSuccess {Boolean} Status status response api.
	 * @apiSuccess {String} Message message response api.
	 * @apiSuccess {Array} Data data of mobile_otp.
	 *
	 * @apiSuccessExample Success-Response:
	 *     HTTP/1.1 200 OK
	 *
	 * @apiError Mobile otpNotFound Mobile otp data is not found.
	 *
	 * @apiErrorExample Error-Response:
	 *     HTTP/1.1 403 Not Acceptable
	 *
	 */
	public function detail_get()
	{
		$this->is_allowed('api_mobile_otp_detail', false);

		$this->requiredInput(['id']);

		$id = $this->get('id');

		$select_field = [''];
		$data['mobile_otp'] = $this->model_api_mobile_otp->find($id, $select_field);

		if ($data['mobile_otp']) {
			
			$this->response([
				'status' 	=> true,
				'message' 	=> 'Detail Mobile otp',
				'data'	 	=> $data
			], API::HTTP_OK);
		} else {
			$this->response([
				'status' 	=> true,
				'message' 	=> 'Mobile otp not found'
			], API::HTTP_NOT_ACCEPTABLE);
		}
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

			$save_data = [
				'mobile_no' => $this->input->post('mobile_no'),
			];
			
			$save_mobile_otp = $this->model_api_mobile_otp->store($save_data);

			if ($save_mobile_otp) {
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
	 * @api {post} /mobile_otp/update Update Mobile otp.
	 * @apiVersion 0.1.0
	 * @apiName UpdateMobile otp
	 * @apiGroup mobile_otp
	 * @apiHeader {String} X-Api-Key Mobile otps unique access-key.
	 * @apiPermission Mobile otp Cant be Accessed permission name : api_mobile_otp_update
	 *
	 * @apiParam {Integer} id Mandatory id of Mobile Otp.
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
	public function update_post()
	{
		$this->is_allowed('api_mobile_otp_update', false);

		
		
		if ($this->form_validation->run()) {

			$save_data = [
			];
			
			$save_mobile_otp = $this->model_api_mobile_otp->change($this->post('id'), $save_data);

			if ($save_mobile_otp) {
				$this->response([
					'status' 	=> true,
					'message' 	=> 'Your data has been successfully updated into the database'
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
	 * @api {post} /mobile_otp/delete Delete Mobile otp. 
	 * @apiVersion 0.1.0
	 * @apiName DeleteMobile otp
	 * @apiGroup mobile_otp
	 * @apiHeader {String} X-Api-Key Mobile otps unique access-key.
	 	 * @apiPermission Mobile otp Cant be Accessed permission name : api_mobile_otp_delete
	 *
	 * @apiParam {Integer} Id Mandatory id of Mobile otps .
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
	public function delete_post()
	{
		$this->is_allowed('api_mobile_otp_delete', false);

		$mobile_otp = $this->model_api_mobile_otp->find($this->post('id'));

		if (!$mobile_otp) {
			$this->response([
				'status' 	=> false,
				'message' 	=> 'Mobile otp not found'
			], API::HTTP_NOT_ACCEPTABLE);
		} else {
			$delete = $this->model_api_mobile_otp->remove($this->post('id'));

			}
		
		if ($delete) {
			$this->response([
				'status' 	=> true,
				'message' 	=> 'Mobile otp deleted',
			], API::HTTP_OK);
		} else {
			$this->response([
				'status' 	=> false,
				'message' 	=> 'Mobile otp not delete'
			], API::HTTP_NOT_ACCEPTABLE);
		}
	}

}

/* End of file Mobile otp.php */
/* Location: ./application/controllers/api/Mobile otp.php */
