<?php

namespace Phresto;
use Phresto\Controller;
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
		$route = $this->routeMapping;
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
		
		return ;
	}

	protected function get( $id = null, Model $model = null ) {
		if ( empty( $id ) ) {
			if ( empty( $this->contextModel ) ) {
				throw new RequestException( '404' );
			}
			$modelName = $this->modelName;
			return json_encode( $modelName::findRelated( $this->contextModel ) );
		}

		if ( !empty( $model ) ) {
			return $this->escalate( $id, $model );
		}

		return json_encode( Container::{$this->modelName}( $id ) );
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
		return json_encode( $modelInstance );		
	}

	protected function put( $id = null, $model = null ) {
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
		$modelInstance->setIndex( $id );
		$modelInstance->save();
		return json_encode( $modelInstance );
	}

	protected function patch( $id = null, $model = null ) {
		if ( !empty( $id ) && !empty( $model ) ) {
			return $this->escalate( $id, $model );
		}

		$modelInstance = Container::{$this->modelName}( $this->body );
		$modelInstance->id = $id;
		$modelInstance->save();
		return json_encode( $modelInstance );
	}

	protected function delete( $id = null, $model = null ) {
		if ( empty( $id ) ) {
			throw new RequestException( '404' );
		}

		if ( !empty( $model ) ) {
			return $this->escalate( $id, $model );
		}

		$modelInstance = Container::{$this->modelName}( $id );
		$modelInstance->delete();
		return json_encode( $modelInstance );
	}
}