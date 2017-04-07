<?php

namespace Phresto;
use Phresto\Controller;
use Phresto\View;
use Phresto\Exception\RequestException;

class ModelController extends Controller {

	const CLASSNAME = __CLASS__;

	protected $routeMapping = [ 'all' => [ 'id' => 0 ] ];

	protected $modelName;
	protected $contextModel;
	protected $methodName;

	public function __construct( $modelName, $reqType, $route, $body, $bodyRaw, $query, $headers, Model $contextModel = null ) {
		$this->modelName = $modelName;
		$this->contextModel = $contextModel;
		parent::__construct( $reqType, $route, $body, $bodyRaw, $query, $headers );
	}

	public function exec() {
		list( $method, $args ) = $this->getMethod();
		$this->methodName = $method->name;

		if ( $this->hasNextRoute() ) {
			$route = $this->getNextRoute();
			return $this->escalate( ( !empty( $this->route[0] ) ) ? $this->route[0] : 0, $route[0] );
		}

		$method->setAccessible( true );
		return $method->invokeArgs( $this, $args );
	}

	protected function auth() {
		$model = $this->modelName;
		return $model::auth( $reqType );
	}

	protected function hasNextRoute() {
		$routeMapping = $this->getRouteMapping( $this->methodName );
		return count( $routeMapping ) < count( $this->route );
	}

	protected function getNextRoute() {
		$routeMapping = $this->getRouteMapping( $this->methodName );
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
	* check if record exists
	* @param id record's index
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
	* get record
	* @param id record's index (all if empty)
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