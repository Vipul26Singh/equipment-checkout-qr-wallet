<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use \Firebase\JWT\JWT;

class User_wallet extends API
{
	
	public function __construct()
	{
		parent::__construct();
		$this->load->model('model_api_user_wallet');
	}

	
	/**
	 * @api {get} /user_wallet/all Get all user_wallets.
	 * @apiVersion 0.1.0
	 * @apiName AllUserwallet 
	 * @apiGroup user_wallet
	 * @apiHeader {String} X-Api-Key User wallets unique access-key.
	 * @apiHeader {String} X-Token User wallets unique token.
	 * @apiPermission User wallet Cant be Accessed permission name : api_user_wallet_all
	 *
	 * @apiParam {String} [Filter=null] Optional filter of User wallets.
	 * @apiParam {String} [Field="All Field"] Optional field of User wallets : id, wallet_code, wallet_qrcode, user_id.
	 * @apiParam {String} [Start=0] Optional start index of User wallets.
	 * @apiParam {String} [Limit=10] Optional limit data of User wallets.
	 *
	 *
	 * @apiSuccess {Boolean} Status status response api.
	 * @apiSuccess {String} Message message response api.
	 * @apiSuccess {Array} Data data of user_wallet.
	 *
	 * @apiSuccessExample Success-Response:
	 *     HTTP/1.1 200 OK
	 *
	 * @apiError NoDataUser wallet User wallet data is nothing.
	 *
	 * @apiErrorExample Error-Response:
	 *     HTTP/1.1 403 Not Acceptable
	 *
	 */
	public function all_get()
	{
		$this->is_allowed('api_user_wallet_all');

		$filter = $this->get('filter');
		$field = $this->get('field');
		$limit = $this->get('limit') ? $this->get('limit') : $this->limit_page;
		$start = $this->get('start');

		$select_field = ['id', 'wallet_code', 'wallet_qrcode', 'user_id'];
		$user_wallets = $this->model_api_user_wallet->get($filter, $field, $limit, $start, $select_field);
		$total = $this->model_api_user_wallet->count_all($filter, $field);

		$data['user_wallet'] = $user_wallets;
				
		$this->response([
			'status' 	=> true,
			'message' 	=> 'Data User wallet',
			'data'	 	=> $data,
			'total' 	=> $total
		], API::HTTP_OK);
	}

	
	/**
	 * @api {get} /user_wallet/detail Detail User wallet.
	 * @apiVersion 0.1.0
	 * @apiName DetailUser wallet
	 * @apiGroup user_wallet
	 * @apiHeader {String} X-Api-Key User wallets unique access-key.
	 * @apiHeader {String} X-Token User wallets unique token.
	 * @apiPermission User wallet Cant be Accessed permission name : api_user_wallet_detail
	 *
	 * @apiParam {Integer} Id Mandatory id of User wallets.
	 *
	 * @apiSuccess {Boolean} Status status response api.
	 * @apiSuccess {String} Message message response api.
	 * @apiSuccess {Array} Data data of user_wallet.
	 *
	 * @apiSuccessExample Success-Response:
	 *     HTTP/1.1 200 OK
	 *
	 * @apiError User walletNotFound User wallet data is not found.
	 *
	 * @apiErrorExample Error-Response:
	 *     HTTP/1.1 403 Not Acceptable
	 *
	 */
	public function detail_get()
	{
		$this->is_allowed('api_user_wallet_detail');

		$this->requiredInput(['id']);

		$id = $this->get('id');

		$select_field = ['id', 'wallet_code', 'wallet_qrcode', 'user_id'];
		$data['user_wallet'] = $this->model_api_user_wallet->find($id, $select_field);

		if ($data['user_wallet']) {
			
			$this->response([
				'status' 	=> true,
				'message' 	=> 'Detail User wallet',
				'data'	 	=> $data
			], API::HTTP_OK);
		} else {
			$this->response([
				'status' 	=> true,
				'message' 	=> 'User wallet not found'
			], API::HTTP_NOT_ACCEPTABLE);
		}
	}
	
		
	/**
	 * @api {post} /user_wallet/add Add User wallet.
	 * @apiVersion 0.1.0
	 * @apiName AddUser wallet
	 * @apiGroup user_wallet
	 * @apiHeader {String} X-Api-Key User wallets unique access-key.
	 * @apiHeader {String} X-Token User wallets unique token.
	 * @apiPermission User wallet Cant be Accessed permission name : api_user_wallet_add
	 *
 	 * @apiParam {String} [Wallet_code] Optional wallet_code of User wallets.  
	 * @apiParam {String} [Wallet_qrcode] Optional wallet_qrcode of User wallets.  
	 * @apiParam {String} [User_id] Optional user_id of User wallets.  
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
		$this->is_allowed('api_user_wallet_add');

		
		if ($this->form_validation->run()) {

			$save_data = [
				'wallet_code' => $this->input->post('wallet_code'),
				'wallet_qrcode' => $this->input->post('wallet_qrcode'),
				'user_id' => $this->input->post('user_id'),
			];
			
			$save_user_wallet = $this->model_api_user_wallet->store($save_data);

			if ($save_user_wallet) {
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
	 * @api {post} /user_wallet/delete Delete User wallet. 
	 * @apiVersion 0.1.0
	 * @apiName DeleteUser wallet
	 * @apiGroup user_wallet
	 * @apiHeader {String} X-Api-Key User wallets unique access-key.
	 * @apiHeader {String} X-Token User wallets unique token.
	 	 * @apiPermission User wallet Cant be Accessed permission name : api_user_wallet_delete
	 *
	 * @apiParam {Integer} Id Mandatory id of User wallets .
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
		$this->is_allowed('api_user_wallet_delete');

		$user_wallet = $this->model_api_user_wallet->find($this->post('id'));

		if (!$user_wallet) {
			$this->response([
				'status' 	=> false,
				'message' 	=> 'User wallet not found'
			], API::HTTP_NOT_ACCEPTABLE);
		} else {
			$delete = $this->model_api_user_wallet->remove($this->post('id'));

			}
		
		if ($delete) {
			$this->response([
				'status' 	=> true,
				'message' 	=> 'User wallet deleted',
			], API::HTTP_OK);
		} else {
			$this->response([
				'status' 	=> false,
				'message' 	=> 'User wallet not delete'
			], API::HTTP_NOT_ACCEPTABLE);
		}
	}

	
}

/* End of file User wallet.php */
/* Location: ./application/controllers/api/User wallet.php */
