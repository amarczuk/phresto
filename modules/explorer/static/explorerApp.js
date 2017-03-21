var explorerApp = angular.module('explorerApp', []);

explorerApp.controller(
  'explorerAppController', 
  function explorerAppController($scope, $document) {
  $scope.routes = [];
  $scope.error = null;

  phresto.get('routes')
    .then(function(routes) {
      $scope.$apply(function() {
        $scope.routes = routes;
        console.log(routes);
      });
    })
    .catch(function(err) {
      $scope.$apply(function() {
        $scope.error = err;
      });
    });

  angular.element(document).ready(function () {
    $document.foundation();
  });
});