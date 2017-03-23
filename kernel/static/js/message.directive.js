angular.module('app').directive('message', [
  function () {
    return {
      replace: false,
      restrict: 'E',
      scope: {},
      controller: function ($scope, $rootScope) {
       
        $scope.messages = [];
        var off = $rootScope.on('addmessage', function(message) {
          //push into messages and delete after Xs
        });
        
      },
      template: "\
  <div id=\"messageContainer\">\
    <div ng-click=\"removeMessage(message)\" \
       data-alert \
       class=\"callout alert-box {{message.type}}\"\
       ng-repeat=\"message in messages\">\
       {{message.message}}\
    </div>\
  </div>"
    };
  }
]);
