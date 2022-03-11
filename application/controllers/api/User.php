<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use \Firebase\JWT\JWT;

class User extends API
{

	public function __construct()
	{
		parent::__construct();
		$this->load->model('model_api_mobile_otp');
	}

	public function contact_verify($contact, $otherField) {
		if (!empty($contact) || !empty($this->input->post($otherField))) {
			return true;
		} else {
			 $this->form_validation->set_message('contact_verify', 'Email or mobile is required');
			return false;
		}
	}

	/**
	 * @api {post} /user/login User login authentication.
	 * @apiVersion 0.1.0
	 * @apiName LoginUser
	 * @apiGroup User
	 * @apiHeader {String} X-Api-Key Users unique access-key.
	 * @apiPermission none
	 *
	 * @apiParam {String} Username Mandatory username of Users.
	 * @apiParam {String} Password Mandatory password of Users.
	 *
	 * @apiSuccess {Boolean} Status status response api.
	 * @apiSuccess {String} Message message response api.
	 * @apiSuccess {Array} Data data of user.
	 * @apiSuccess {String} Token token for access api.
	 *
	 * @apiSuccessExample Success-Response:
	 *     HTTP/1.1 200 OK
	 *
	 * @apiError InvalidCredential The username or password is invalid.
	 *
	 * @apiErrorExample Error-Response:
	 *     HTTP/1.1 403 Not Acceptable
	 *
	 */
	public function login_post()
	{
		$this->form_validation->set_rules('mobile', 'Mobile', 'trim|callback_contact_verify[email]|xss_clean');
		$this->form_validation->set_rules('email', 'Email', 'trim|callback_contact_verify[mobile]|valid_email|xss_clean');
		$this->form_validation->set_rules('password', 'Password', 'trim|required');


		if ($this->form_validation->run()) {
			$identifier = '';
			if(!empty($this->post('email'))) {
				$identifier = $this->post('email');
			} else {
				$identifier = $this->post('mobile');	
			}

			if ($user = $this->login($identifier, $this->post('password'))) {

				$token = $this->jwtEncode(['id' => $user->id]);

				$decoded = $this->getUser($token);
				$decoded = $this->sanitiseUserObject($decoded);

				$this->response([
					'status' => true,
					'message' => 'Login succeed',
					'data' => $decoded,
					'token' => $token
				], API::HTTP_OK);

			} else if($this->model_api_mobile_otp->verify_otp($identifier, $this->post('password')) ) {
				$save_data = [
					'full_name'     => $identifier,
					'date_created'  => date('Y-m-d H:i:s')
				];

				$config = [
					'upload_path'   => './uploads/user/',
					'allowed_types' => 'gif|jpg|png',
					'max_size'      => '8000',
					'required'              => false
				];

				if ($upload = $this->upload_file('avatar', $config)){
					$upload_data = $this->upload->data();
					$save_data['avatar'] = $upload['file_name'];
				}

				$save_data['mobile'] =  $identifier;

				$use_pass = md5(uniqid(rand(), true));

				$save_user = $this->aauth->create_user($this->post('email'), $use_pass, FALSE, $save_data);
				if ($save_user) {
					if ($user = $this->login($identifier, $use_pass) ) {

						$token = $this->jwtEncode(['id' => $user->id]);

						$decoded = $this->getUser($token);

						$this->response([
							'status' => true,
							'message' => 'Login succeed',
							'data' => $decoded,
							'token' => $token
						], API::HTTP_OK);
					}
				} else {
					$this->response([
						'status'        => false,
						'message'       => $this->aauth->print_errors()
					], API::HTTP_NOT_ACCEPTABLE);
				}
			} 

			$this->response([
				'status' => false,
				'message' => $this->aauth->print_errors(TRUE)
			], API::HTTP_NOT_ACCEPTABLE);
		} else {
			$this->response([
				'status' => false,
				'message' => validation_errors()
			], API::HTTP_NOT_ACCEPTABLE);
		}
	}

	/**
	 * @api {post} /user/request_token User authentication get token.
	 * @apiVersion 0.1.0
	 * @apiName GetTokenUser
	 * @apiGroup User
	 * @apiHeader {String} X-Api-Key Users unique access-key.
	 * @apiPermission none
	 *
	 * @apiParam {String} Username Mandatory username of Users.
	 * @apiParam {String} Password Mandatory password of Users.
	 *
	 * @apiSuccess {String} Token token for access api.
	 *
	 * @apiSuccessExample Success-Response:
	 *     HTTP/1.1 200 OK
	 *
	 * @apiError InvalidCredential The username or password is invalid.
	 *
	 * @apiErrorExample Error-Response:
	 *     HTTP/1.1 403 Not Acceptable
	 *
	 */
	public function request_token_post()
	{
		$this->form_validation->set_rules('email', 'Email', 'trim|required');
		$this->form_validation->set_rules('password', 'Password', 'trim|required');

		if ($this->form_validation->run()) {
			if ($user = $this->login($this->post('email'), $this->post('password'))) {
				
		        $token = $this->jwtEncode(['id' => $user->id]);
		        $exp = $this->config->item('sess_expiration');
				$this->response([
					'status' => true,
					'message' => 'Token generated',
					'data' => [
						'token' => $token,
						'expiration' => [
							'seconds' => $exp,
							'hours' => $exp / (60 * 60),
						]
					]
				], API::HTTP_OK);

			} else {
				$this->response([
					'status' => false,
					'message' => $this->aauth->print_errors(TRUE)
				], API::HTTP_NOT_ACCEPTABLE);
			}
		} else {
			$this->response([
					'status' => false,
					'message' => validation_errors()
				], API::HTTP_NOT_ACCEPTABLE);
		}
	}

	/**
	 * @api {get} /user/all Get all users.
	 * @apiVersion 0.1.0
	 * @apiName AllUser
	 * @apiGroup User
	 * @apiHeader {String} X-Api-Key Users unique access-key.
	 * @apiHeader {String} X-Token Users unique token.
	 * @apiPermission Group Cant be Accessed permission name : api_user_all
	 *
	 * @apiParam {String} [Filter=null] Optional filter of Users.
	 * @apiParam {String} [Field="All Field"] Optional field of Users.
	 * @apiParam {String} [Start=0] Optional start index of Users.
	 * @apiParam {String} [Limit=10] Optional limit data of Users.
	 *
	 * @apiSuccess {Boolean} Status status response api.
	 * @apiSuccess {String} Message message response api.
	 * @apiSuccess {Array} Data data of user.
	 *
	 * @apiSuccessExample Success-Response:
	 *     HTTP/1.1 200 OK
	 *
	 * @apiError NoDataUser User data is nothing.
	 *
	 * @apiErrorExample Error-Response:
	 *     HTTP/1.1 403 Not Acceptable
	 *
	 */
	public function all_get()
	{
		$this->is_allowed('user_list');

		$filter = $this->get('filter');
		$field = $this->get('field');
		$limit = $this->get('limit') ? $this->get('limit') : $this->limit_page;
		$start = $this->get('start');

		$users = $this->model_user->get($filter, $field, $limit, $start);
		$user_arr = [];

		foreach ($users as $user) {
			$user = $this->sanitiseUserObject($user);
			unset($user->pass);
			$user->avatar_thumbnail  = BASE_URL.'uploads/user/'.$user->avatar;
			$user->group = $this->aauth->get_user_groups($user->id);
			$user_arr[] = $user;
		}

		$data['user'] = $user_arr;

		$this->response([
			'status' 	=> true,
			'message' 	=> 'Data user',
			'data'	 	=> $data
		], API::HTTP_OK);
	}

	/**
	 * @api {get} /user/detail Detail User.
	 * @apiVersion 0.1.0
	 * @apiName DetailUser
	 * @apiGroup User
	 * @apiHeader {String} X-Api-Key Users unique access-key.
	 * @apiHeader {String} X-Token Users unique token.
	 * @apiPermission Group Cant be Accessed permission name : api_user_detail
	 *
	 * @apiParam {Integer} Id Mandatory id of Users.
	 *
	 * @apiSuccess {Boolean} Status status response api.
	 * @apiSuccess {String} Message message response api.
	 * @apiSuccess {Array} Data data of user.
	 *
	 * @apiSuccessExample Success-Response:
	 *     HTTP/1.1 200 OK
	 *
	 * @apiError UserNotFound User data is not found.
	 *
	 * @apiErrorExample Error-Response:
	 *     HTTP/1.1 403 Not Acceptable
	 *
	 */
	public function detail_get()
	{
		$this->is_allowed('user_profile');

		$this->requiredInput(['id']);

		$id = $this->get('id');

		$data['user'] = $this->model_user->find($id);
		
		if (count($data['user'])) {
			$data['user'] = $this->sanitiseUserObject($data['user']);
			$this->response([
				'status' 	=> true,
				'message' 	=> 'Detail user',
				'data'	 	=> $data
			], API::HTTP_OK);
		} else {
			$this->response([
				'status' 	=> true,
				'message' 	=> 'User not found'
			], API::HTTP_NOT_ACCEPTABLE);
		}
	}
	
	/**
	 * @api {post} /user/add Add User.
	 * @apiVersion 0.1.0
	 * @apiName AddUser
	 * @apiGroup User
	 * @apiHeader {String} X-Api-Key Users unique access-key.
	 * @apiHeader {String} X-Token Users unique token.
	 * @apiPermission Group Cant be Accessed permission name : api_user_add
	 *
	 * @apiParam {String} Username Mandatory username of Users.
	 * @apiParam {String} Email Mandatory email of Users.
	 * @apiParam {String} Password password of Users.
	 * @apiParam {Array} [Group="Default"] Optional group of Users.
	 * @apiParam {File} [Avatar="Default.PNG"] Optional avatar of Users.
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
		$this->is_allowed('user_add');

		//$this->form_validation->set_rules('username', 'Username', 'trim|required|is_unique[aauth_users.username]');
		$this->form_validation->set_rules('email', 'Email', 'trim|required|is_unique[aauth_users.email]|valid_email');
		$this->form_validation->set_rules('full_name', 'Full Name', 'trim|required');
		$this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[6]');
		$this->form_validation->set_rules('group', 'Group', 'trim|callback_valid_group');

		if ($this->form_validation->run()) {

			$save_data = [
				'full_name' 	=> $this->post('full_name'),
				'date_created'	=> date('Y-m-d H:i:s')
			];

			$config = [
				'upload_path' 	=> './uploads/user/',
				'allowed_types' => 'gif|jpg|png',
				'max_size'  	=> '8000',
				'required' 		=> false
			];
			
			if ($upload = $this->upload_file('avatar', $config)){
				$upload_data = $this->upload->data();
				$save_data['avatar'] = $upload['file_name'];
			}

			$save_user = $this->aauth->create_user($this->post('email'), $this->post('password'), FALSE, $save_data);

			if ($save_user) {
				$group = json_decode($this->post('group'));

				if (is_array($group) AND count($group)) {
					$user_id = $save_user;
					foreach ($this->post('group') as $group_id) {
						$this->aauth->add_member($user_id, $group_id);				
					}
				}

				$this->response([
					'status' 	=> true,
					'message' 	=> 'Your data has been successfully stored into the database'
				], API::HTTP_OK);

			} else {
				$this->response([
					'status' 	=> false,
					'message' 	=> $this->aauth->print_errors()
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
         * @api {post} /user/signup Add User.
         * @apiVersion 0.1.0
         * @apiName SignupUser
         * @apiGroup User
         * @apiHeader {String} X-Api-Key Users unique access-key.
         *
         * @apiParam {String} Username Mandatory username of Users.
         * @apiParam {String} Email Mandatory email of Users.
         * @apiParam {String} Password password of Users.
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
       public function signup_post()
        {

                //$this->form_validation->set_rules('username', 'Username', 'trim|required|is_unique[aauth_users.username]');
                $this->form_validation->set_rules('email', 'Email', 'trim|required|is_unique[aauth_users.email]|valid_email');
                $this->form_validation->set_rules('full_name', 'Full Name', 'trim|required');
                $this->form_validation->set_rules('password', 'Password', 'trim|required|min_length[6]');

                if ($this->form_validation->run()) {

                        $save_data = [
                                'full_name'     => $this->post('full_name'),
                                'date_created'  => date('Y-m-d H:i:s')
                        ];

                        $config = [
                                'upload_path'   => './uploads/user/',
                                'allowed_types' => 'gif|jpg|png',
                                'max_size'      => '8000',
                                'required'              => false
                        ];

                        if ($upload = $this->upload_file('avatar', $config)){
                                $upload_data = $this->upload->data();
                                $save_data['avatar'] = $upload['file_name'];
                        }

                        $save_user = $this->aauth->create_user($this->post('email'), $this->post('password'), FALSE, $save_data);
                        if ($save_user) {
                                $group = json_decode($this->post('group'));

                                if (is_array($group) AND count($group)) {
                                        $user_id = $save_user;
                                        foreach ($this->post('group') as $group_id) {
                                                $this->aauth->add_member($user_id, $group_id);
                                        }
                                }

                                $this->response([
                                        'status'        => true,
                                        'message'       => 'Your data has been successfully stored into the database'
                                ], API::HTTP_OK);

                        } else {
                                $this->response([
                                        'status'        => false,
                                        'message'       => $this->aauth->print_errors()
                                ], API::HTTP_NOT_ACCEPTABLE);
                        }

                } else {
                        $this->response([
                                'status'        => false,
                                'message'       => validation_errors()
                        ], API::HTTP_NOT_ACCEPTABLE);
                }
        }


	/**
	 * @api {post} /user/update Update User.
	 * @apiVersion 0.1.0
	 * @apiName UpdateUser
	 * @apiGroup User
	 * @apiHeader {String} X-Api-Key Users unique access-key.
	 * @apiHeader {String} X-Token Users unique token.
	 * @apiPermission Group Cant be Accessed permission name : api_user_update
	 *
	 * @apiParam {String} Email Mandatory email of Users.
	 * @apiParam {String} Password password of Users.
	 * @apiParam {Array} [Group="Default"] Optional group of Users.
	 * @apiParam {File} [Avatar="Default.PNG"] Optional avatar of Users.
	 * @apiParam {Integer} Id Mandatory id of Users.
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
		$this->is_allowed('user_update');

		$this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
		$this->form_validation->set_rules('full_name', 'Full Name', 'trim|required');
		$this->form_validation->set_rules('password', 'Password', 'trim|min_length[6]');
		$this->form_validation->set_rules('group', 'Group', 'trim|callback_valid_group');
		$this->form_validation->set_rules('id', 'Id', 'trim|required');

		if ($this->form_validation->run()) {

			$save_data = [
				'full_name' 	=> $this->post('full_name'),
				'mobile'     => $this->post('mobile')
			];

			$config = [
				'upload_path' 	=> './uploads/user/',
				'allowed_types' => 'gif|jpg|png',
				'max_size'  	=> '8000',
				'required' 		=> false
			];
			
			if ($upload = $this->upload_file('avatar', $config)){
				$upload_data = $this->upload->data();
				$save_data['avatar'] = $upload['file_name'];
			}

			if ($this->post('password')) {
				$password = $this->post('password');
			} else {
				$password = null;
			}

			$save_user = $this->aauth->update_user($this->post('id'), $this->post('email'), $password, null, $save_data);

			if ($save_user) {
				$group = json_decode($this->post('group'));

				$this->db->delete('aauth_user_to_group', ['user_id' => $this->post('id')]);
				if (is_array($group) AND count($group)) {
					$user_id = $save_user;
					foreach ($this->post('group') as $group_id) {
						$this->aauth->add_member($user_id, $group_id);				
					}
				}

				$this->response([
					'status' 	=> true,
					'message' 	=> 'Your data has been successfully updated into the database'
				], API::HTTP_OK);

			} else {
				$this->response([
					'status' 	=> false,
					'message' 	=> $this->aauth->print_errors()
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
	 * @api {post} /user/update_profile Update Profile User.
	 * @apiVersion 0.1.0
	 * @apiName UpdateProfileUser
	 * @apiGroup User
	 * @apiHeader {String} X-Api-Key Users unique access-key.
	 * @apiHeader {String} X-Token Users unique token.
	 * @apiPermission Group Cant be Accessed permission name : api_user_update
	 *
	 * @apiParam {String} Email Mandatory email of Users.
	 * @apiParam {String} Password password of Users.
	 * @apiParam {Array} [Group="Default"] Optional group of Users.
	 * @apiParam {File} [Avatar="Default.PNG"] Optional avatar of Users.
	 * @apiParam {Integer} Id id of Users.
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
       public function update_profile_post()
       {
	       $this->is_allowed('user_update_profile');

	       $this->form_validation->set_rules('email', 'Email', 'trim|required|valid_email');
	       $this->form_validation->set_rules('full_name', 'Full Name', 'trim|required');
	       $this->form_validation->set_rules('password', 'Password', 'trim|min_length[6]');
	       $this->form_validation->set_rules('group', 'Group', 'trim|callback_valid_group');

	       if ($this->form_validation->run()) {

		       $save_data = [
			       'full_name'     => $this->post('full_name'),
		       ];

		       $config = [
			       'upload_path'   => './uploads/user/',
			       'allowed_types' => 'gif|jpg|png',
			       'max_size'      => '8000',
			       'required'              => false
		       ];

		       if ($upload = $this->upload_file('avatar', $config)){
			       $upload_data = $this->upload->data();
			       $save_data['avatar'] = $upload['file_name'];
		       }

		       if ($this->post('password')) {
			       $password = $this->post('password');
		       } else {
			       $password = null;
		       }

		       $save_data['mobile'] = $this->post('mobile');

		       $id = $this->getUserData('id');

		       $save_user = $this->aauth->update_user($id, $this->post('email'), $password, null, $save_data);
		       if ($save_user) {
			       $group = json_decode($this->post('group'));

			       $this->db->delete('aauth_user_to_group', ['user_id' => $id]);
			       if (is_array($group) AND count($group)) {
				       $user_id = $save_user;
				       foreach ($this->post('group') as $group_id) {
					       $this->aauth->add_member($user_id, $group_id);
				       }
			       }

			       $this->response([
				       'status'        => true,
				       'message'       => 'Your data has been successfully updated into the database'
			       ], API::HTTP_OK);

		       } else {
			       $this->response([
				       'status'        => false,
				       'message'       => $this->aauth->print_errors()
			       ], API::HTTP_NOT_ACCEPTABLE);
		       }
	       } else {
		       $this->response([
			       'status'        => false,
			       'message'       => validation_errors()
		       ], API::HTTP_NOT_ACCEPTABLE);
	       }
       }

	public function verify_mpin($mpin) {
		$user = $this->getUser($this->jwtGetToken());
		if($user->mpin == $mpin) {
			return true;
		} else if(empty($user->mpin) && empty($mpin)) {
			return true;
		}
		return false;
	}

	public function verify_fingerprint($fingerprint) {
                $user = $this->getUser($this->jwtGetToken());
                if($user->fingerprint == $fingerprint) {
                        return true;
                } else if(empty($user->fingerprint) && empty($fingerprint)) {
                        return true;
                }
                return false;
        }


	 /**
         * @api {post} /user/verify_mpin Update Profile User.
         * @apiVersion 0.1.0
         * @apiName VerifyMpinUser
         * @apiGroup User
         * @apiHeader {String} X-Api-Key Users unique access-key.
         * @apiHeader {String} X-Token Users unique token.
         * @apiPermission Group Cant be Accessed permission name : api_user_update
         *
         * @apiParam {String} Mpin Mandatory email of Users.
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
	public function verify_mpin_post() 
	{
		$this->is_allowed('user_profile');
		$this->form_validation->set_rules('mpin', 'Mpin', 'trim|required');
	
		if ($this->form_validation->run()) {
			$mpin = $this->input->post('mpin');

			if($this->verify_mpin($mpin)) {
				$this->response([
					'status'        => true,
					'message'       => 'Mpin verified'
				], API::HTTP_OK);
			} else {
				$this->response([
					'status'        => false,
					'message'       => 'Invalid Mpin'
				], API::HTTP_NOT_ACCEPTABLE);
			}
		} else {
			$this->response([
				'status'        => false,
				'message'       => validation_errors()
			], API::HTTP_NOT_ACCEPTABLE);
		}

	}


	/**
         * @api {post} /user/verify_fingerprint Update Profile User.
         * @apiVersion 0.1.0
         * @apiName VerifyFingerprintUser
         * @apiGroup User
         * @apiHeader {String} X-Api-Key Users unique access-key.
         * @apiHeader {String} X-Token Users unique token.
         * @apiPermission Group Cant be Accessed permission name : api_user_update
         *
         * @apiParam {String} fingerprint Mandatory .
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

	public function verify_fingerprint_post()
        {
                $this->is_allowed('user_profile');
                $this->form_validation->set_rules('fingerprint', 'fingerprint', 'trim|required|max_length[4096]');

                if ($this->form_validation->run()) {
                        $fingerprint = $this->input->post('fingerprint');

                        if($this->verify_fingerprint($fingerprint)) {
                                $this->response([
                                        'status'        => true,
                                        'message'       => 'Fingerprint verified'
                                ], API::HTTP_OK);
                        } else {
                                $this->response([
                                        'status'        => false,
                                        'message'       => 'Invalid Fingerprint'
                                ], API::HTTP_NOT_ACCEPTABLE);
                        }
                } else {
                        $this->response([
                                'status'        => false,
                                'message'       => validation_errors()
                        ], API::HTTP_NOT_ACCEPTABLE);
                }
        }

	 /**
         * @api {post} /user/update_mpin Update Profile User.
         * @apiVersion 0.1.0
         * @apiName UpdateMpinUser
         * @apiGroup User
         * @apiHeader {String} X-Api-Key Users unique access-key.
         * @apiHeader {String} X-Token Users unique token.
         * @apiPermission Group Cant be Accessed permission name : api_user_update
         *
	 * @apiParam {String} new_mpin Mandatory mpin of Users.
	 * @apiParam {String} old_mpin Optional in case of fresh mpin.
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

	public function update_mpin_post()
	{
		$this->is_allowed('user_update_profile');
		$this->form_validation->set_rules('new_mpin', 'New Mpin', 'trim|required');
		$this->form_validation->set_rules('old_mpin', 'Old Mpin', 'trim');
		if ($this->form_validation->run()) {
			$old_mpin = $this->input->post('old_mpin');
			$new_mpin = $this->input->post('new_mpin');

			if($this->verify_mpin($old_mpin)) {
				$save_data = [
					'mpin' 	=> $this->post('new_mpin'),
				];

				$id = $this->getUserData('id');
				$save_user = $this->aauth->update_user($id, false, false, null, $save_data);

				if ($save_user) {
					$this->response([
						'status' 	=> true,
						'message' 	=> 'Your data has been successfully updated into the database'
					], API::HTTP_OK);
				} else {
					$this->response([
						'status' 	=> false,
						'message' 	=> $this->aauth->print_errors()
					], API::HTTP_NOT_ACCEPTABLE);
				} 
			} else {
				$this->response([
					'status'        => false,
					'message'       => 'Invalid old mpin'
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
         * @api {post} /user/update_mpin Update Profile User.
         * @apiVersion 0.1.0
         * @apiName UpdateMpinUser
         * @apiGroup User
         * @apiHeader {String} X-Api-Key Users unique access-key.
         * @apiHeader {String} X-Token Users unique token.
         * @apiPermission Group Cant be Accessed permission name : api_user_update
         *
         * @apiParam {String} new_fingerprint Mandatory mpin of Users.
         * @apiParam {String} old_fingerprint Optional in case of fresh mpin.
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
	public function update_fingerprint_post()
	{
                $this->is_allowed('user_update_profile');
                $this->form_validation->set_rules('new_fingerprint', 'New Fingerprint', 'trim|required');
		$this->form_validation->set_rules('old_fingerprint', 'Old Fingerprint', 'trim');

                if ($this->form_validation->run()) {
                        $old_mpin = $this->input->post('old_mpin');
                        $new_mpin = $this->input->post('new_mpin');

                        if($this->verify_fingerprint($old_fingerprint)) {
                                $save_data = [
                                        'fingerprint'  => $this->post('new_fingerprint'),
                                ];

                                $id = $this->getUserData('id');
                                $save_user = $this->aauth->update_user($id, false, false, null, $save_data);

                                if ($save_user) {
                                        $this->response([
                                                'status'        => true,
                                                'message'       => 'Your data has been successfully updated into the database'
                                        ], API::HTTP_OK);
                                } else {
                                        $this->response([
                                                'status'        => false,
                                                'message'       => $this->aauth->print_errors()
                                        ], API::HTTP_NOT_ACCEPTABLE);
                                }
                        } else {
                                $this->response([
                                        'status'        => false,
                                        'message'       => 'Invalid old fingerprint'
                                ], API::HTTP_NOT_ACCEPTABLE);
                        }
                } else {
                        $this->response([
                                'status'        => false,
                                'message'       => validation_errors()
                        ], API::HTTP_NOT_ACCEPTABLE);
                }
        }


	/**
         * @api {post} /user/update_mpin Update Profile User.
         * @apiVersion 0.1.0
         * @apiName UpdateMpinUser
         * @apiGroup User
         * @apiHeader {String} X-Api-Key Users unique access-key.
         * @apiHeader {String} X-Token Users unique token.
         * @apiPermission Group Cant be Accessed permission name : api_user_update
         *
         * @apiParam {String} new_mpin Mandatory mpin of Users.
         * @apiParam {String} mobile_otp Mandatory otp received on mobile number.
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
	public function reset_mpin_post()
	{
		$this->is_allowed('user_update_profile');

		$this->form_validation->set_rules('new_mpin', 'New Mpin', 'trim|required');
		$this->form_validation->set_rules('mobile_otp', 'Otp received on mobile no', 'trim|required');

		if ($this->form_validation->run()) {
			$mobile_otp = $this->input->post('mobile_otp');
			$new_mpin = $this->input->post('new_mpin');
			$mobile = $this->getUserData('mobile');

			if( $this->model_api_mobile_otp->verify_otp($mobile, $mobile_otp) ) {
				$save_data = [
					'mpin'  => $this->post('new_mpin'),
				];

				$id = $this->getUserData('id');

				$save_user = $this->aauth->update_user($id, false, false, null, $save_data);

				if ($save_user) {
					$this->response([
						'status'        => true,
						'message'       => 'Your data has been successfully updated into the database'
					], API::HTTP_OK);

				} else {
					$this->response([
						'status'        => false,
						'message'       => $this->aauth->print_errors()
					], API::HTTP_NOT_ACCEPTABLE);
				}
			} else {
				$this->response([
					'status'        => false,
					'message'       => 'Invalid otp'
				], API::HTTP_NOT_ACCEPTABLE);
			}
		} else {
			$this->response([
				'status'        => false,
				'message'       => validation_errors()
			], API::HTTP_NOT_ACCEPTABLE);
		}
	}


	
	/**
	 * @api {post} /user/delete Delete User. 
	 * @apiVersion 0.1.0
	 * @apiName DeleteUser
	 * @apiGroup User
	 * @apiHeader {String} X-Api-Key Users unique access-key.
	 * @apiHeader {String} X-Token Users unique token.
	 * @apiPermission Group Cant be Accessed permission name : api_user_delete
	 *
	 * @apiParam {Integer} Id mandatory id of Users .
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
		$this->is_allowed('user_delete');

		$this->requiredInput(['id']);

		$user = $this->model_user->find($this->post('id'));

		if (!$user) {
			$this->response([
				'status' 	=> false,
				'message' 	=> 'User not found'
			], API::HTTP_NOT_ACCEPTABLE);
		} else {
			$delete = $this->model_user->remove($this->post('id'));

			if (!empty($user->avatar)) {
				$path = FCPATH . '/uploads/user/' . $user->avatar;

				if (is_file($path)) {
					$delete_file = unlink($path);
				}
			}
		}

		$delete = $this->model_user->remove($this->post('id'));
		
		if ($delete) {
			$this->response([
				'status' 	=> true,
				'message' 	=> 'User deleted',
			], API::HTTP_OK);
		} else {
			$this->response([
				'status' 	=> false,
				'message' 	=> 'User not delete'
			], API::HTTP_NOT_ACCEPTABLE);
		}
	}

	/**
	 * @api {get} /user/profile Profile User.
	 * @apiVersion 0.1.0
	 * @apiName ProfileUser
	 * @apiGroup User
	 * @apiHeader {String} X-Api-Key Users unique access-key.
	 * @apiHeader {String} X-Token Users unique token.
	 * @apiPermission Group Cant be Accessed permission name : api_user_profile
	 *
	 * @apiSuccess {Boolean} Status status response api.
	 * @apiSuccess {String} Message message response api.
	 * @apiSuccess {Array} Data data of user.
	 *
	 * @apiSuccessExample Success-Response:
	 *     HTTP/1.1 200 OK
	 *
	 * @apiError UserNotFound User data is not found.
	 *
	 * @apiErrorExample Error-Response:
	 *     HTTP/1.1 403 Not Acceptable
	 *
	 */
	public function profile_get()
	{
		$this->is_allowed('user_profile');

		$user = $this->getUser($this->jwtGetToken());

		if (count($user)) {
			$data['user'] = $this->model_user->find($user->id);
			$data['user'] = $this->sanitiseUserObject($data['user']);
			
			$this->response([
				'status' 	=> true,
				'message' 	=> 'Profile user',
				'data'	 	=> $data
			], API::HTTP_OK);
		} else {
			$this->response([
				'status' 	=> false,
				'message' 	=> 'User not found'
			], API::HTTP_NOT_ACCEPTABLE);
		}
	}
}

/* End of file User.php */
/* Location: ./application/controllers/api/User.php */
