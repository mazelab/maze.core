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
controllers.controller('modalRemoveNodeService', function($scope, $modalInstance, service, node, nodesService) {
    $scope.service = service;
    $scope.node = node;

    $scope.ok = function(){
        $scope.errMessages = [];
        var updateData = {services: {}};
        updateData.services[service.name] = false;

        nodesService.update(node._id, $.param(updateData)).success(function(data) {
            $modalInstance.close(data.node.services);
        }).error(function() {
            $scope.errMessages.push('Failed');
        });
    };
    $scope.cancel = function() {
        $modalInstance.dismiss();
    };
});
controllers.controller('modalRemoveDomainService', function($scope, $modalInstance, service, domain, domainsService) {
    $scope.service = service;
    $scope.domain = domain;

    $scope.ok = function(){
        $scope.errMessages = [];
        var updateData = {services: {}};
        updateData.services[service.name] = false;

        domainsService.update(domain._id, $.param(updateData)).success(function(data) {
            $modalInstance.close(data.domain.services);
        }).error(function() {
            $scope.errMessages.push('Failed');
        });
    };
    $scope.cancel = function() {
        $modalInstance.dismiss();
    };
});
controllers.controller('modalDeleteClient', function($scope, $modalInstance, clientsService, clientId) {
    $scope.ok = function(){
        $scope.errMessages = [];
        clientsService.delete(clientId).success(function(data, code) {
            $modalInstance.close(code);
        }).error(function() {
            $scope.errMessages.push('Failed');
        });
    }
    $scope.cancel = function() {
        $modalInstance.dismiss();
    }
});
controllers.controller('modalDeleteNode', function($scope, $modalInstance, nodesService, nodeId) {
    $scope.ok = function(){
        $scope.errMessages = [];
        nodesService.delete(nodeId).success(function(data, code) {
            $modalInstance.close(code);
        }).error(function() {
            $scope.errMessages.push('Failed');
        });
    }
    $scope.cancel = function() {
        $modalInstance.dismiss();
    }
});
controllers.controller('modalDeleteDomain', function($scope, $modalInstance, domainsService, domainId) {
    $scope.ok = function(){
        $scope.errMessages = [];
        domainsService.delete(domainId).success(function(data, code) {
            $modalInstance.close(code);
        }).error(function() {
            $scope.errMessages.push('Failed');
        });
    }
    $scope.cancel = function() {
        $modalInstance.dismiss();
    }
});

