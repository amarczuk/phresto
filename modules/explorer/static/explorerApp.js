var explorerApp = angular.module('app', []);

explorerApp.controller(
  'explorerAppController', 
  function explorerAppController($scope, $document, $rootScope) {
  $scope.routes = [];
  $scope.loading = false;

  $scope.removeMessage = function(message) {
    var idx = $scope.messages.indexOf(message);
    if (idx < 0) return;
    $scope.messages.splice(idx, 1);
  }

  phresto.get('routes')
    .then(function(routes) {
      $scope.$apply(function() {
        $scope.routes = routes;
        console.log(routes);
      });
    })
    .catch(function(err) {
      $rootScope.emit('addmessage', {type: 'alert', message: err.message});
    });

  angular.element(document).ready(function () {
    $document.foundation();
  });
});