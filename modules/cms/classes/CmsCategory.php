<?php

class CmsCategory extends Model {
	public $_name = 'cms_category';
	public $_table = 'cms_category';
	protected $_fields = [ 'id', 'url', 'title', 'status' ];
	protected $_relations = [ 'id' => [ 'table' => 'cms', 'field' => 'categoryid' ] ];

	protected function saveFilter() {
		
	}

	public function getAllActive() {
		$cats = $this->search( [ 'where' => [ 'status' => 1 ] ] );

		return $cats['properties'];
	}

	protected function validate() {
		
		return true;
	}

}