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

controllers.controller('domainListController', function($scope, domainsService) {
    $scope.domains = [];

    $scope.loadDomains = true;
    domainsService.list().success(function(data) {
        $scope.loadDomains = false;
        $scope.domains = data;
    }).error(function(){
        $scope.loadDomains = false;
    });

    var initBreadCrumb = function() {
        $('ul.breadcrumb').html('<li><a href="/">Dashboard</a><span class="divider">/</span></li><li class="active">Domains</li>');
    };

    initBreadCrumb();
});
controllers.controller('domainEditController', function($scope, $filter, $modal, $q, domainsService, $routeParams, modulesService) {
    $scope.domainId = $routeParams.domainId;

    $scope.loadDomain = true;
    domainsService.get($scope.domainId).success(function(data) {
        $scope.domain = data;
        $scope.loadDomain = false;

        initBreadCrumb();
        initServices();
    }).error(function(data, code) {
        if(code === 404) {
            window.location = '#/';
        }
        $scope.loadDomain = false;
    })

    $scope.modalDeleteDomain = function() {
        var modalInstance = $modal.open({
            templateUrl: 'modalDomainDelete.html',
            controller: 'modalDeleteDomain',
            resolve: {
                domainId: function () {return $scope.domainId}
            }
        });

        modalInstance.result.then(function (code) {
            if (code === 200) {
                return location.href = "#/";
            }
        });
    };

    $scope.updateProperty = function(property, data) {
        if(property === undefined || data === undefined) {
            return false;
        }
        var updateData = {};
        updateData[property] = data;

        return domainsService.update($scope.domainId, $.param(updateData)).catch(function(request) {
            var messages = '';
            if(request.data.errForm && request.data.errForm[property]) {
                angular.forEach(request.data.errForm[property], function(value, key){
                    if(messages !== '') {
                        messages = messages + ";";
                    }
                    messages = messages + value;
                });
            } else {
                messages = false
            }
            return $q.reject(messages);
        });
    };

    var initBreadCrumb = function() {
        $('ul.breadcrumb').html('<li><a href="/">Dashboard</a><span class="divider">/</span></li><li><a href="#/">Domains</a><span class="divider">/</span></li><li class="active">' + $scope.domain.name + '</li>');
    };

    var initServices = function() {
        $scope.loadServices = true;
        $scope.services = {selected: '', all: []};

        modulesService.list().success(function(data){
            $scope.services.all = data;
            buildAvailableServices();
            $scope.loadServices = false;
        }).error(function() {
            $scope.loadServices = false;
            buildAvailableServices();
            $scope.errAddService = ['Failed to load services'];
        });

        $scope.addService = function(serviceName) {
            $scope.errAddService = [];
            var service = $filter('filter')($scope.services.available, {name: serviceName})[0];
            if(!service || !service._id) {
                return false;
            }

            var updateData = {services: {}};
            updateData.services[serviceName] = true;

            domainsService.update($scope.domain._id, $.param(updateData)).success(function(data) {
                $scope.domain.services = data.domain.services;
                $scope.services.selected = '';
                buildAvailableServices();

                setTimeout(function() {
                    $('#tabServices-' + serviceName).tab('show');
                }, 0);
            }).error(function() {
                $scope.errAddService.push('Failed');
            });
        };

        $scope.modalRemoveDomainService = function(service) {
            if(!service) {
                return false;
            }

            var modalInstance = $modal.open({
                templateUrl: 'modalRemoveDomainService.html',
                controller: 'modalRemoveDomainService',
                resolve: {
                    service: function () {return service},
                    domain: function () {return $scope.domain}
                }
            });

            modalInstance.result.then(function (services) {
                $scope.domain.services = services;
                $('#tabServicesList li a:first').tab('show');
                buildAvailableServices();
            });
        };

        var buildAvailableServices = function() {
            if(!$scope.domain) {
                return false;
            }

            var availableServices = [];
            angular.forEach($scope.services.all, function(service) {
                if(service.name && (!$scope.domain.services || !$scope.domain.services[service.name])) {
                    availableServices.push(service);
                }
            });

            if(!availableServices.length) {
                $scope.services.available = [{label: 'No services available', name:''}];
            } else {
                $scope.services.available = [{label: 'Add new service', name:''}];
            }

            $scope.services.available = $scope.services.available.concat(availableServices);
        };
    };

});
controllers.factory('FormElements', function(){
    return {
        form: $("#addDomain"),
        disable: function(disable){
            angular.element(this.form).find("input, button").each(function(){
                $this = angular.element(this);
                $this.prop("disabled", disable);
                if (disable && $this.hasClass("btn") === true){
                    $this.addClass("disabled");
                } else {
                    $this.removeClass("disabled");
                }
            });

            return($this);
        }
    };
});
controllers.controller('domainNewController', function($scope, $filter, domainsService, clientsService, FormElements) {
    $scope.clients  = {};
    $scope.domain   = {};
    $scope.selected = {};

    $scope.changeclient = function(option){
        $scope.selected = $filter("filter")($scope.clients, {_id: (option)})[0]
    }

    clientsService.list().success(function(clients){
        $scope.clients = clients || {};
    });

    $scope.submit = function(event){
        FormElements.disable(true);
        domainsService.create($scope.domain).success(function(response, status){
            $scope.response = response;

            if (status === 201){
                return location.href = "#/domains";
            }

            FormElements.disable(false);
        });

        event.preventDefault();
    };


    var initBreadCrumb = function() {
        $('ul.breadcrumb').html('<li><a href="/">Dashboard</a><span class="divider">/</span></li><li><a href="#/">Domains</a><span class="divider">/</span></li><li class="active">new</li>');
    };

    initBreadCrumb();
});



