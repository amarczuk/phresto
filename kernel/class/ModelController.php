<?php

namespace Phresto;
use Phresto\Controller;
use Phresto\View;
use Phresto\Exception\RequestException;

class ModelController extends Controller {

	const CLASSNAME = __CLASS__;

	protected $routeMapping = [ 'id' => 0, 'model' => 1 ];

	protected $modelName;
	protected $contextModel;

	public function __construct( $modelName, $reqType, $route, $body, $bodyRaw, $query, $headers, Model $contextModel = null ) {
		$this->modelName = $modelName;
		$this->contextModel = $contextModel;
		parent::__construct( $reqType, $route, $body, $bodyRaw, $query, $headers );
	}

	protected function auth() {
		$model = $this->modelName;
		return $model::auth( $reqType );
	}

	protected function getNextRoute() {
		$route = ( is_array( $this->routeMapping[$this->reqType] ) ) ? $this->routeMapping[$this->reqType] : $this->routeMapping;
		array_shift( $route );
		array_shift( $route );
		return implode( '/', $route );
	}

	protected function escalate( $id, $model ) {
		$thisModel = Container::{$this->modelName}( $id );
		if ( !$thisModel->getIndexValue() ) {
			throw new RequestException( '404' );
		}
		$modelContr = new ModelController( $model, $this->reqType, $this->getNextRoute(), $this->body, $this->bodyRaw, $this->query, $this->headers, $thisModel );
		return $modelContr->exec();
	}

	protected function head( $id = null, Model $model = null ) {	
		if ( empty( $id ) ) {
			throw new RequestException( '404' );
		}

		$modelInstance = Container::{$this->modelName}( $id );
		if ( !isset( $modelInstance->id ) ) {
			throw new RequestException( '404' );
		}

		return null;
	}

	protected function get( $id = null, Model $model = null ) {
		if ( empty( $id ) ) {
			if ( empty( $this->contextModel ) ) {
				throw new RequestException( '404' );
			}
			$modelName = $this->modelName;
			return View::jsonResponse( $modelName::findRelated( $this->contextModel ) );
		}

		if ( !empty( $model ) ) {
			return $this->escalate( $id, $model );
		}

		$modelInstance = Container::{$this->modelName}( $id );
		if ( !isset( $modelInstance->id ) ) {
			throw new RequestException( '404' );
		}
		return View::jsonResponse( $modelInstance );
	}

	protected function post( $id = null, $model = null ) {
		if ( !empty( $id ) ) {
			if ( !empty( $model ) ) {
				return $this->escalate( $id, $model );
			}
			throw new RequestException( '409' );
		}

		$modelInstance = Container::{$this->modelName}( $this->body );
		$modelInstance->save();
		return View::jsonResponse( $modelInstance );		
	}

	protected function patch( $id = null, $model = null ) {
		if ( empty( $id ) ) {
			throw new RequestException( '404' );
		}

		if ( !empty( $model ) ) {
			return $this->escalate( $id, $model );
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

	protected function put( $id = null, $model = null ) {
		if ( !empty( $id ) && !empty( $model ) ) {
			return $this->escalate( $id, $model );
		}

		if ( empty( $this->body ) ) {
			throw new RequestException( '204' );
		}

		$modelInstance = Container::{$this->modelName}( $id );
		$modelInstance->update( $this->body );
		$modelInstance->save();
		return View::jsonResponse( $modelInstance );
	}

	protected function delete( $id = null, $model = null ) {
		if ( empty( $id ) ) {
			throw new RequestException( '404' );
		}

		if ( !empty( $model ) ) {
			return $this->escalate( $id, $model );
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