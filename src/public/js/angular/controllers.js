var controllers = angular.module("maze.controllers", []);

controllers.controller('modalRemoveClientService', function($scope, $modalInstance, service, client, clientsService) {
    $scope.service = service;
    $scope.client = client;
    $scope.errMessages = [];

    $scope.ok = function(){
        $scope.errMessages = [];
        var updateData = {services: {}};
        updateData.services[service.name] = false;

        clientsService.update(client._id, $.param(updateData)).success(function(data) {
            $modalInstance.close(data.client.services);
        }).error(function() {
            $scope.errMessages.push('Failed');
        });
    };
    $scope.cancel = function() {
        $modalInstance.dismiss();
    };
});
