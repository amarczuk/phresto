<?php

class Cms extends Model {
	public $_name = 'cms';
	public $_table = 'cms';
	protected $_fields = [ 'id', 'url', 'title', 'content', 'short', 'lang', 'status', 'categoryid' ];
	protected $_relations = [ 'categoryid' => [ 'table' => 'cms_category', 'field' => 'id' ] ];

	protected function saveFilter() {
		
	}

	public function getFromCategory( $id, $active = true ) {
		$search = [ 'where' => [ 'categoryid' => $id ] ];
		if ( $active ) {
			$search['where']['status'] = 1;
		}
		$art = $this->search( $search );

		return $art['properties'];
	}

	protected function validate() {
		
		return true;
	}

}