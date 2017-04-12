<?php

namespace Phresto;
use Phresto\Controller;
use Phresto\ModelController;
use Phresto\View;
use Phresto\Exception\RequestException;

class CustomModelController extends ModelController {

	const CLASSNAME = __CLASS__;
	const MODELCLASS = 'Phresto\\Module\\Model\\Name';

	public function __construct( $reqType, $route, $body, $bodyRaw, $query, $headers ) {
		$this->modelName = static::MODELCLASS;
		parent::__construct( static::MODELCLASS, $reqType, $route, $body, $bodyRaw, $query, $headers );
	}

	protected static function getParameters( $method, $className ) {
		return parent::getParameters( $method, static::MODELCLASS );
	}

	/**
	* prints model description
	* @return object
	*/
	protected function discover_get() {
		return View::jsonResponse( static::discover( $this->modelName ) );
	}

	/**
	* check if record exists
	* @param id record's index
	* @return 200 - OK, 404 - not found
	*/
	protected function head( $id = null ) {	
		if ( empty( $id ) ) {
			throw new RequestException( '404' );
		}

		$modelInstance = Container::{$this->modelName}();
		if ( empty( $this->contextModel ) ) {
			$modelInstance->setById( $id );
		} else {
			$modelInstance->setRelatedById( $this->contextModel, $id );
		}

		if ( empty( $modelInstance->getIndex() ) ) {
			throw new RequestException( '404' );
		}

		return null;
	}

	/**
	* get record
	* @param id record's index (all if empty)
	* @return object / array of objects
	*/
	protected function get( $id = null ) {
		if ( empty( $id ) && empty( $this->contextModel ) ) {
			$modelName = $this->modelName;
			return View::jsonResponse( $modelName::find( $this->query ) );
		}

		$modelInstance = Container::{$this->modelName}();
		if ( empty( $this->contextModel ) ) {
			$modelInstance->setById( $id );
		} else {
			if ( empty( $id ) ) {
				$modelName = $this->modelName;
				return View::jsonResponse( $modelName::findRelated( $this->contextModel, $this->query ) );
			}
			$modelInstance->setRelatedById( $this->contextModel, $id );
		}

		if ( empty( $modelInstance->getIndex() ) ) {
			throw new RequestException( '404' );
		}
		return View::jsonResponse( $modelInstance );
	}

	/**
	* create record
	* @param json model properties
	* @return object created record
	*/
	protected function post() {
		$modelInstance = Container::{$this->modelName}( $this->body );
		$modelInstance->save();
		return View::jsonResponse( $modelInstance );		
	}

	/**
	* update record
	* @param id id of record to update
	* @param json model properties
	* @return object updated record
	*/
	protected function patch( $id = null ) {
		if ( empty( $id ) ) {
			throw new RequestException( '404' );
		}

		if ( empty( $this->body ) ) {
			throw new RequestException( '204' );
		}

		$modelInstance = Container::{$this->modelName}( $id );
		if ( empty( $modelInstance->id ) ) {
			throw new RequestException( '404' );
		}
		$modelInstance->update( $this->body );
		$modelInstance->save();
		return View::jsonResponse( $modelInstance );
	}

	/**
	* upsert record
	* @param id (optional)
	* @param json model properties
	* @return object updated record
	*/
	protected function put( $id = null ) {
		if ( empty( $this->body ) ) {
			throw new RequestException( '204' );
		}

		$modelInstance = Container::{$this->modelName}( $id );
		$modelInstance->update( $this->body );
		$modelInstance->save();
		return View::jsonResponse( $modelInstance );
	}

	/**
	* delete record
	* @param id
	* @return object deleted record
	*/
	protected function delete( $id = null ) {
		if ( empty( $id ) ) {
			throw new RequestException( '404' );
		}

		$modelInstance = Container::{$this->modelName}( $id );
		
		if ( empty( $modelInstance->id ) ) {
			throw new RequestException( '404' );
		}

		$modelInstance->delete();
		return View::jsonResponse( $modelInstance );
	}
}