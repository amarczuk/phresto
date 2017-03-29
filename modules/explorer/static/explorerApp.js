angular.module('app', ['mm.foundation']).controller(
  'explorerAppController', 
  [ '$scope',
    '$document',
    '$rootScope',
    function($scope, $document, $rootScope) {
      $scope.routes = [];
      $scope.loading = false;

      $scope.makeRequest = function(controller, method) {
        var url = controller.endpoint;
        for(var idx in method.url) {
          url += '/' + method.url[idx];
        }
        if (!phresto[method.name]) return;
        $scope.loading = true;
        phresto[method.name](url, method.body)
          .then(function(response) {
            method.response = response;
            $scope.loading = false;
            $scope.$apply();
          })
          .catch(function(err) {
            method.response = err.message;
            $scope.loading = false;
            $scope.$apply();
          });
      }

      phresto.get('routes')
        .then(function(routes) {
          $scope.$apply(function() {
            $scope.routes = routes;
            console.log(routes);
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