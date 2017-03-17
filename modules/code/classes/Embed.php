<?php

class Embed extends Model {
	public $_name = 'embed';
	public $_table = 'embed';
	protected $_fields = [ 'id', 'projectid', 'name', 'main_code', 'codes', 'views' ];
	protected $_relations = [ 'projectid' => [ 'table' => 'project', 'field' => 'id' ], 'main_code' => [ 'table' => 'code', 'field' => 'id' ] ];

	protected function saveFilter() {
		if ( isset( $this->name ) ) {
			$this->name = trim( strip_tags( str_replace( "'", '', $this->name ) ) );
		}
	}

	protected function validate() {
		if ( empty( $this->name ) ) {
			return LAN_CODE_ERR_EMBED_NAME;
		}

		if ( empty( $this->main_code ) ) {
			return LAN_CODE_ERR_EMBED_FILE;
		}

		if ( empty( $this->codes ) ) {
			return LAN_CODE_ERR_EMBED_FILES;
		}

		return true;
	}

	public function getCodes() {
		$codes = [];
		if ( empty( $this->codes ) ) {
			return $codes;
		}

		$codeids = explode( ',', $this->codes );
		$codeids = array_diff( $codeids, [ $this->main_code ] );
		array_unshift( $codeids, $this->main_code );

		foreach ( $codeids as $cid ) {
			$code = new Code( null, $cid );
			$codes[$code->properties['name']] = $code->properties;
		}

		return $codes;
	}

	public function getRunName() {
		if ( empty( $this->main_code ) ) {
			return '';
		}

		$code = new Code( null, $this->main_code );
		return $code->properties['name'];
	}
}