<?php
defined('BASEPATH') OR exit('No direct script access allowed');


/**
*| --------------------------------------------------------------------------
*| Cc Options Controller
*| --------------------------------------------------------------------------
*| Cc Options site
*|
*/
class Cc_options extends Admin	
{
	
	public function __construct()
	{
		parent::__construct();

		$this->load->model('model_cc_options');
	}

	/**
	* show all Cc Optionss
	*
	* @var $offset String
	*/
	public function index($offset = 0)
	{
		$this->is_allowed('cc_options_list');

		$filter = $this->input->get('q');
		$field 	= $this->input->get('f');

		$this->data['cc_optionss'] = $this->model_cc_options->get( $filter, $field, $this->limit_page, $offset);
		$this->data['cc_options_counts'] = $this->model_cc_options->count_all($filter, $field);

		$config = [
			'base_url'     => 'administrator/cc_options/index/',
			'total_rows'   => $this->model_cc_options->count_all($filter, $field),
			'per_page'     => $this->limit_page,
			'uri_segment'  => 4,
		];

		$this->data['pagination'] = $this->pagination($config);

		$this->template->title('Configurations List');
		$this->render('backend/standart/administrator/cc_options/cc_options_list', $this->data);
	}
	
	
		/**
	* Update view Cc Optionss
	*
	* @var $id String
	*/
	public function edit($id)
	{
		$this->is_allowed('cc_options_update');

		$this->data['cc_options'] = $this->model_cc_options->find($id);

		$this->template->title('Configurations Update');
		$this->render('backend/standart/administrator/cc_options/cc_options_update', $this->data);
	}

	/**
	* Update Cc Optionss
	*
	* @var $id String
	*/
	public function edit_save($id)
	{
		if (!$this->is_allowed('cc_options_update', false)) {
			echo json_encode([
				'success' => false,
				'message' => cclang('sorry_you_do_not_have_permission_to_access')
				]);
			exit;
		}
		
		$this->form_validation->set_rules('user_friendly_name', 'User Friendly Name', 'trim|required|max_length[512]');
		$this->form_validation->set_rules('option_value', 'Option Value', 'trim|required');
		
		if ($this->form_validation->run()) {
		
			$save_data = [
					'user_friendly_name' => ($this->input->post('user_friendly_name') === '') ? NULL : $this->input->post('user_friendly_name'),
					'option_value' => ($this->input->post('option_value') === '') ? NULL : $this->input->post('option_value'),
			];

			

			$save_cc_options = $this->model_cc_options->change($id, $save_data);

			if ($save_cc_options) {
				if ($this->input->post('save_type') == 'stay') {
					$this->data['success'] = true;
					$this->data['id'] 	   = $id;
					$this->data['message'] = cclang('success_update_data_stay', [
						anchor('administrator/cc_options', ' Go back to list')
					]);
				} else {
					set_message(
						cclang('success_update_data_redirect', [
					]), 'success');

            		$this->data['success'] = true;
					$this->data['redirect'] = base_url('administrator/cc_options');
				}
			} else {
				if ($this->input->post('save_type') == 'stay') {
					$this->data['success'] = false;
					$this->data['message'] = cclang('data_not_change');
				} else {
            		$this->data['success'] = false;
            		$this->data['message'] = cclang('data_not_change');
					$this->data['redirect'] = base_url('administrator/cc_options');
				}
			}
		} else {
			$this->data['success'] = false;
			$this->data['message'] = validation_errors();
		}

		echo json_encode($this->data);
	}
	
	/**
	* delete Cc Optionss
	*
	* @var $id String
	*/
	public function delete($id = null)
	{
		$this->is_allowed('cc_options_delete');

		$this->load->helper('file');

		$arr_id = $this->input->get('id');
		$remove = false;

		if (!empty($id)) {
			$remove = $this->_remove($id);
		} elseif (count($arr_id) >0) {
			foreach ($arr_id as $id) {
				$remove = $this->_remove($id);
			}
		}

		if ($remove) {
            set_message(cclang('has_been_deleted', 'cc_options'), 'success');
        } else {
            set_message(cclang('error_delete', 'cc_options'), 'error');
        }

		redirect_back();
	}

		/**
	* View view Cc Optionss
	*
	* @var $id String
	*/
	public function view($id)
	{
		$this->is_allowed('cc_options_view');

		$this->data['cc_options'] = $this->model_cc_options->join_avaiable()->select_string()->find($id);

		$this->template->title('Configurations Detail');
		$this->render('backend/standart/administrator/cc_options/cc_options_view', $this->data);
	}
	
	/**
	* delete Cc Optionss
	*
	* @var $id String
	*/
	private function _remove($id)
	{
		$cc_options = $this->model_cc_options->find($id);

		
		
		return $this->model_cc_options->remove($id);
	}
	
	
	/**
	* Export to excel
	*
	* @return Files Excel .xls
	*/
	public function export()
	{
		$this->is_allowed('cc_options_export');

		$this->model_cc_options->export('cc_options', 'cc_options');
	}

	/**
	* Export to PDF
	*
	* @return Files PDF .pdf
	*/
	public function export_pdf()
	{
		$this->is_allowed('cc_options_export');

		$this->model_cc_options->pdf('cc_options', 'cc_options');
	}
}


/* End of file cc_options.php */
/* Location: ./application/controllers/administrator/Cc Options.php */
