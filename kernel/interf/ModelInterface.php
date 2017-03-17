<?php

namespace Phresto\Interf;

interface ModelInterface {

	public function __construct( $option = null );

	public static function find( $query );
	public static function findRelated( $model, $query = null );
	public static function auth( $reqType );

	public function save();
	public function delete();
	public function setIndex( $id );

	public static function getIndexField();
	public static function getFields();
	public function getIndexValue();

	public function __set( $name, $value );
	public function __get( $name );
	public function __isset ( $name );

}