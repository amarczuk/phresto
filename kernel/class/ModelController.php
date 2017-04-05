<?php

namespace Phresto;
use Phresto\Controller;
use Phresto\View;
use Phresto\Exception\RequestException;

class ModelController extends Controller {

	const CLASSNAME = __CLASS__;

	protected $routeMapping = [ 'id' => 0 ];

	protected $modelName;
	protected $contextModel;

	public function __construct( $modelName, $reqType, $route, $body, $bodyRaw, $query, $headers, Model $contextModel = null ) {
		$this->modelName = $modelName;
		$this->contextModel = $contextModel;
		parent::__construct( $reqType, $route, $body, $bodyRaw, $query, $headers );
	}

	public function exec() {
		if ( $this->hasNextRoute() ) {
			$route = $this->getNextRoute();
			return $this->escalate( ( !empty( $this->route[0] ) ) ? $this->route[0] : 0, $route[0] );
		}

		return parent::exec();
	}

	protected function auth() {
		$model = $this->modelName;
		return $model::auth( $reqType );
	}

	protected function hasNextRoute() {
		$routeMapping = ( is_array( $this->routeMapping[$this->reqType] ) ) ? $this->routeMapping[$this->reqType] : $this->routeMapping;
		return count( $routeMapping ) < count( $this->route );
	}

	protected function getNextRoute() {
		$routeMapping = ( is_array( $this->routeMapping[$this->reqType] ) ) ? $this->routeMapping[$this->reqType] : $this->routeMapping;
		$cnt = count( $routeMapping );
		$route = $this->route;
		for ( $i = 0; $i < $cnt; $i++ ) {
			array_shift( $route );
		}
		return $route;
	}

	protected function escalate( $id, $model ) {
		$thisModel = Container::{$this->modelName}( $id );
		$thisModelName = $this->modelName;
		if ( !$thisModel->getIndexValue() || !$thisModelName::isRelated( $model ) ) {
			throw new RequestException( 404 );
		}

		$modelClass = 'Phresto\\Modules\\Model\\' . $model;
		$modelController = 'Phresto\\ModelController';
		$modelContr = new Container::$modelController( $modelClass, $this->reqType, $this->getNextRoute(), $this->body, $this->bodyRaw, $this->query, $this->headers, $thisModel );
		return $modelContr->exec();
	}

	protected static function getParameters( $method, $className ) {
		$params = $method->getParameters();

		if ( in_array( $method->name, ['post', 'put', 'patch'] ) ) {
			$reflection = new \ReflectionClass( $className );
			$staticProps = $reflection->getStaticProperties(); 
			$params = array_merge( $params, $staticProps['_fields'] );
		}

		return $params;
	}

	/**
	* prints model description
	* @return object
	*/
	protected function discover_get() {
		return View::jsonResponse( static::discover( $this->modelName ) );
	}

	/**
	* check if element exists
	* @param id element's index
	* @return 200 - OK, 404 - not found
	*/
	protected function head( $id = null ) {	
		if ( empty( $id ) ) {
			throw new RequestException( '404' );
		}

		$modelInstance = Container::{$this->modelName}( $id );
		if ( !isset( $modelInstance->id ) ) {
			throw new RequestException( '404' );
		}

		return null;
	}

	/**
	* get element
	* @param id element's index (all if empty)
	* @return object / array of objects
	*/
	protected function get( $id = null ) {
		if ( empty( $id ) ) {
			$modelName = $this->modelName;
			return View::jsonResponse( $modelName::find() );
		}

		$modelInstance = Container::{$this->modelName}( $id );
		if ( !isset( $modelInstance->id ) ) {
			throw new RequestException( '404' );
		}
		return View::jsonResponse( $modelInstance );
	}

	/**
	* create element
	* @param model properties
	* @return object created element
	*/
	protected function post() {
		$modelInstance = Container::{$this->modelName}( $this->body );
		$modelInstance->save();
		return View::jsonResponse( $modelInstance );		
	}

	/**
	* update element
	* @param id of updated element
	* @param model properties
	* @return object updated element
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
	* upsert element
	* @param id (optional)
	* @return object updated element
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
	* delete element
	* @param id
	* @return object deleted element
	*/
	protected function delete( $id = null ) {
		if ( empty( $id ) ) {
			throw new RequestException( '404' );
		}

		$modelInstance = Container::{$this->modelName}( $id );
		if ( empty( $modelInstance->id ) ) {
			throw new RequestException( '404' );
		}
		$oldModel = View::jsonResponse( $modelInstance );
		$modelInstance->delete();
		return $oldModel;
	}
}