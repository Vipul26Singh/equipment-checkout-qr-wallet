<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Model_api_blog extends MY_Model {

		private $primary_key 	= 'id';
	private $table_name 	= 'blog';
	private $field_search 	= ['id', 'title', 'content', 'image', 'category'];
	private $user_restriction = '';
	private $user_restriction_columns = '';
	private $has_relations = 1;
	private $default_select_field =  ['id', 'title', 'content', 'image', 'category'];



	public function __construct()
	{
		$config = array(
			'primary_key' 	=> $this->primary_key,
		 	'table_name' 	=> $this->table_name,
			'field_search' 	=> $this->field_search,
			'user_restriction' => $this->user_restriction,
			'user_restriction_columns' => $this->user_restriction_columns,
			'fetch_recursive_func' => array($this, 'fetch_recursive_relations'),
		 );

		$this->load->model('model_api_blog');

				$this->load->model('model_api_blog_category');
                
		parent::__construct($config);
	}

	public function fetch_recursive_relations($map_row)
	{
		
				if(is_object($map_row)) {
			$map_row->category_detail = $this->model_api_blog_category->get($map_row->category, 'category_id', 0, 0, [], true);
		} else {
			$map_row['category_detail'] = $this->model_api_blog_category->get($map_row['category'], 'category_id', 0, 0, [], true);
		}
		 
		return $map_row;
	}

	public function count_all($q = null, $field = null)
	{
		$iterasi = 1;
		$num = count($this->field_search);
		$where = NULL;
		$q = $this->scurity($q);
		$field = $this->scurity($field);

		if (empty($field)) {
			if(!empty($q)) {
				foreach ($this->field_search as $field) {
		    			if ($iterasi == 1) {
						$where .= $field . " = '" . $q . "' ";
		    			} else {
						$where .= "OR " . $field . " = '" . $q . "' ";
		    			}
		    			$iterasi++;
				}

				$where = '('.$where.')';
			}
		} else {
			$where .= "(" . $field . " = '" . $q . "' )";
		}

		if($this->apply_user_filter()) {
			if(!empty($where)) {
				$where .= " and ";
			}

			$where .= $this->get_user_filter_condition();
		}

		if(!empty($where)) {
			$this->db->where($where);
		}
		$query = $this->db->get($this->table_name);

		return $query->num_rows();
	}

	public function get($q = null, $field = null, $limit = 0, $offset = 0, $select_field = [], $use_default_select_field = false)
	{
		if($use_default_select_field) {
			$select_field = $this->default_select_field;
		}

		$iterasi = 1;
		$num = count($this->field_search);
		$where = NULL;
	
		$q = $this->scurity($q);
		$field = $this->scurity($field);

		if (empty($field)) {
			if(!empty($q)) {
				foreach ($this->field_search as $field) {
		    			if ($iterasi == 1) {
						$where .= $field . " = '" . $q . "' ";
		    			} else {
						$where .= "OR " . $field . " = '" . $q . "' ";
		    			}
		    			$iterasi++;
				}

				$where = '('.$where.')';
			}
		} else {
				$where .= "(" . $field . " = '" . $q . "' )";
		}

		if (is_array($select_field) AND count($select_field)) {
			$this->db->select($select_field);
		}

		if($this->apply_user_filter()) {
			if(!empty($where)) {
				$where .= " and ";
			}

			$where .= $this->get_user_filter_condition();
		}

		if ($where) {
			$this->db->where($where);
		}
	
		$this->db->limit($limit, $offset);
		$this->db->order_by($this->primary_key, "DESC");
		$query = $this->db->get($this->table_name);

		$fetched_result =  $query->result();


		if(!empty($this->has_relations) && !empty($fetched_result)) {
			$fetched_result = array_map(function ($map_row) {
				$map_row = $this->fetch_recursive_relations($map_row);
				return $map_row;
			}, $fetched_result);
		}


		return $fetched_result;
	}

	public function find($id = NULL, $select_field = [])
        {
                $fetched_result = parent::find($id, $select_field);
                if(!empty($this->has_relations) && !empty($fetched_result)) {
                                $fetched_result = $this->fetch_recursive_relations($fetched_result);
                }
                return $fetched_result;
        }

}

/* End of file Model_blog.php */
/* Location: ./application/models/Model_blog.php */
