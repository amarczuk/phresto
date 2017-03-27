angular.module('app', ['mm.foundation']).controller(
  'explorerAppController', 
  [ '$scope',
    '$document',
    '$rootScope',
    function($scope, $document, $rootScope) {
      $scope.routes = [];
      $scope.loading = false;

      phresto.get('routes')
        .then(function(routes) {
          $scope.$apply(function() {
            $scope.routes = routes;
            console.log(routes);
            $rootScope.$emit('addmessage', {type: 'info', message: 'loaded'});
          });
        })
        .catch(function(err) {
          $rootScope.$emit('addmessage', {type: 'alert', message: err.message});
        });

      angular.element(document).ready(function () {
        $document.foundation();
      });
    }
  ]);