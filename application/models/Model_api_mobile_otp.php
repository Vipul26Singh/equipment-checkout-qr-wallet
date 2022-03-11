<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_api_mobile_otp extends MY_Model {

	private $primary_key 	= 'id';
	private $table_name 	= 'mobile_otp';
	private $field_search 	= [''];

	public function __construct()
	{
		$config = array(
			'primary_key' 	=> $this->primary_key,
			'table_name' 	=> $this->table_name,
			'field_search' 	=> $this->field_search,
		);

		parent::__construct($config);
	}

	public function count_all($q = null, $field = null)
	{
		$iterasi = 1;
		$num = count($this->field_search);
		$where = NULL;
		$q = $this->scurity($q);
		$field = $this->scurity($field);

		if (empty($field)) {
			foreach ($this->field_search as $field) {
				if ($iterasi == 1) {
					$where .= $field . " LIKE '%" . $q . "%' ";
				} else {
					$where .= "OR " . $field . " LIKE '%" . $q . "%' ";
				}
				$iterasi++;
			}

			$where = '('.$where.')';
		} else {
			$where .= "(" . $field . " LIKE '%" . $q . "%' )";
		}

		$this->db->where($where);
		$query = $this->db->get($this->table_name);

		return $query->num_rows();
	}

	public function get($q = null, $field = null, $limit = 0, $offset = 0, $select_field = [])
	{
		$iterasi = 1;
		$num = count($this->field_search);
		$where = NULL;
		$q = $this->scurity($q);
		$field = $this->scurity($field);

		if (empty($field)) {
			foreach ($this->field_search as $field) {
				if ($iterasi == 1) {
					$where .= $field . " LIKE '%" . $q . "%' ";
				} else {
					$where .= "OR " . $field . " LIKE '%" . $q . "%' ";
				}
				$iterasi++;
			}

			$where = '('.$where.')';
		} else {
			if (in_array($field, $select_field)) {
				$where .= "(" . $field . " LIKE '%" . $q . "%' )";
			}
		}

		if (is_array($select_field) AND count($select_field)) {
			$this->db->select($select_field);
		}

		if ($where) {
			$this->db->where($where);
		}
		$this->db->limit($limit, $offset);
		$this->db->order_by($this->primary_key, "DESC");
		$query = $this->db->get($this->table_name);

		return $query->result();
	}

	public function delete_expired($mobile_no = '') {
		$otp_maxlifetime = get_option('otp_maxlifetime');
		$this->db->delete($this->table_name, "`created_at` < (NOW() - INTERVAL {$otp_maxlifetime} MINUTE)");

		if(!empty($mobile_no)) {
			$this->db->delete($this->table_name, array('mobile_no' => $mobile_no));
		}
	}

	public function verify_otp($mobile_no, $otp) {
		$this->delete_expired();


		$this->db->where('mobile_no', $mobile_no);
		$this->db->where('otp', $otp);
		$query = $this->db->get($this->table_name);


		if ($query->num_rows() > 0) { 
			$this->delete_expired($mobile_no);
			return true;
		} else {
			return false;
		}
	}

}

/* End of file Model_mobile_otp.php */
/* Location: ./application/models/Model_mobile_otp.php */
