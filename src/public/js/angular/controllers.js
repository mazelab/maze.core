var controllers = angular.module("maze.controllers", []);

controllers.controller('clientListController', function($scope) {
    $scope.clients = [];

    var initBreadCrumb = function() {
        $('ul.breadcrumb').html('<li><a href="/">Dashboard</a><span class="divider">/</span></li><li class="active">Clients</li>');
    };

    initBreadCrumb();
});
controllers.controller('clientNewController', function($scope) {
    var initBreadCrumb = function() {
        $('ul.breadcrumb').html('<li><a href="/">Dashboard</a><span class="divider">/</span></li><li><a href="#/">Clients</a><span class="divider">/</span></li><li class="active">new</li>');
    };

    $scope.cancel = function() {
        window.location = '#/';
    }

    initBreadCrumb();
});
controllers.controller('domainModalRemoveService', function($scope, $modalInstance, service, domain, domainsService) {
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
controllers.controller('domainModalDelete', function($scope, $modalInstance, domainsService, domainId) {
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
controllers.controller('domainListController', function($scope) {
    $scope.domains = [];

    var initBreadCrumb = function() {
        $('ul.breadcrumb').html('<li><a href="/">Dashboard</a><span class="divider">/</span></li><li class="active">Domains</li>');
    };

    initBreadCrumb();
});
controllers.controller('domainEditController', function($scope, $filter, $modal, $q, domainsService, $routeParams, modulesService, logsService, nodesService) {
    $scope.domainId = $routeParams.domainId;

    var initBreadCrumb = function() {
        $('ul.breadcrumb').html('<li><a href="/">Dashboard</a><span class="divider">/</span></li><li><a href="#/">Domains</a><span class="divider">/</span></li><li class="active">' + $scope.domain.name + '</li>');
    };

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

    $scope.loadLogs = true;
    logsService.list({domain: $scope.domainId,limit: 10}).success(function(data) {
        $scope.logs = data;
        $scope.loadLogs = false;
    }).error(function(data) {
        $scope.loadLogs = false;
        $scope.logs = null;
    });

    $scope.loadNodes = true;
    nodesService.list({domain: $scope.domainId,limit: 10}).success(function(data) {
        $scope.nodes =  data;
        $scope.loadNodes = false;
    }).error(function(data) {
        $scope.loadNodes = false;
        $scope.nodes =  null;
    });

    $scope.updateAdditional = function (data) {
        return domainsService.update($scope.domainId, $.param(data)).success(function(data, code){
            if(code === 200 && data.domain && data.domain.additionalFields) {
                $scope.domain.additionalFields = data.domain.additionalFields;
            }
        });
    };

    $scope.modalDeleteDomain = function() {
        var modalInstance = $modal.open({
            templateUrl: '/partials/admin/domains/modal/delete.html',
            controller: 'domainModalDelete',
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
                templateUrl: '/partials/admin/domains/modal/removeService.html',
                controller: 'domainModalRemoveService',
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
controllers.controller('domainNewController', function($scope, $filter, domainsService, clientsService) {
    $scope.clients  = {};
    $scope.domain   = {};
    $scope.selected = {};

    $scope.changeclient = function(option){
        $scope.selected = $filter("filter")($scope.clients, {_id: (option)})[0]
    }

    clientsService.list().success(function(clients){
        $scope.clients = clients || {};
    });

    $scope.createDomain = function(){
        $scope.formErrors = [];
        domainsService.create($.param($scope.domain)).success(function(data, status, headers){
            if(headers('location')) {
                return location.href = headers('location');
            }

            return location.href = "#/";
        }).error(function(data) {
            if(data.formErrors) {
                $scope.formErrors = data.formErrors;
            }
        });
    };

    $scope.cancelCreation = function() {
        window.location = '#/';
    }

    var initBreadCrumb = function() {
        $('ul.breadcrumb').html('<li><a href="/">Dashboard</a><span class="divider">/</span></li><li><a href="#/">Domains</a><span class="divider">/</span></li><li class="active">new</li>');
    };

    initBreadCrumb();
});
controllers.controller('nodeEditController', function($scope, $filter, $modal, $q, $routeParams, nodesService, logsService, modulesService, clientsService, domainsService) {
    $scope.nodeId = $routeParams.nodeId;
    $scope.nodetypes = [
        {name:"Virtual Server", value: "virtual", image: "dummy_vm_200.png"},
        {name:"Cloud Server", value: "cloud", image: "dummy_cloud_200.png"},
        {name:"Dedicated Server‎", value: "dedicated", image: "dummy_server_200.png"}
    ];

    var initBreadCrumb = function() {
        $('ul.breadcrumb').html('<li><a href="/">Dashboard</a><span class="divider">/</span></li><li><a href="#/">Nodes</a><span class="divider">/</span></li><li class="active">' + $scope.node.name + '</li>');
    };

    $scope.updateAdditional = function (data) {
        return nodesService.update($scope.nodeId, $.param(data)).success(function(data, code){
            if(code === 200 && data.node && data.node.additionalFields) {
                $scope.node.additionalFields = data.node.additionalFields;
            }
        });
    };

    $scope.loadNode = true;
    nodesService.get($scope.nodeId).success(function(data) {
        $scope.node = data;
        $scope.loadNode = false;

        initBreadCrumb();
        initServices();
        initNodeTypeSelect();
    }).error(function(data, code) {
        if(code === 404) {
            window.location = '#/';
        }
        $scope.loadNode = false;
    });

    var initNodeTypeSelect = function() {
        $scope.$watch("node.nodetype", function(option){
            $scope.changeNodeType(option);
        });

        $scope.changeNodeType = function(option){
            option = (option || $scope.node.nodetype);
            select = $filter("filter")($scope.nodetypes, {value: (option)})[0];
            if (option && select && select.value) {
                $scope.selected = select;
            }
        };
    };

    $scope.loadLogs = true;
    logsService.list({node: $scope.nodeId,limit: 10}).success(function(data) {
        $scope.logs = data;
        $scope.loadLogs = false;
    }).error(function(data) {
        $scope.loadLogs = false;
        $scope.logs = null;
    });

    $scope.loadClients = true;
    clientsService.list({node: $scope.nodeId, limit: 10}).success(function(data) {
        $scope.clients =  data;
        $scope.loadClients = false;
    }).error(function(data) {
        $scope.clients =  null;
        $scope.loadClients = false;
    });

    $scope.loadDomains = true;
    domainsService.list({node: $scope.nodeId, limit: 10}).success(function(data) {
        $scope.domains =  data;
        $scope.loadDomains = false;
    }).error(function(data) {
        $scope.domains =  null;
        $scope.loadDomains = false;
    });

    $scope.modalDelete = function() {
        var modalInstance = $modal.open({
            templateUrl: '/partials/admin/nodes/modal/delete.html',
            controller: 'nodeModalDelete',
            resolve: {
                nodeId: function () {return $scope.nodeId}
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

        return nodesService.update($scope.nodeId, $.param(updateData)).catch(function(request) {
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

            nodesService.update($scope.node._id, $.param(updateData)).success(function(data) {
                $scope.node.services = data.node.services;
                $scope.services.selected = '';
                buildAvailableServices();

                setTimeout(function() {
                    $('#tabServices-' + serviceName).tab('show');
                }, 0);
            }).error(function() {
                $scope.errAddService.push('Failed');
            });
        };

        $scope.modalRemoveService = function(service) {
            if(!service) {
                return false;
            }

            var modalInstance = $modal.open({
                templateUrl: '/partials/admin/nodes/modal/removeService.html',
                controller: 'nodeModalRemoveService',
                resolve: {
                    service: function () {return service},
                    node: function () {return $scope.node}
                }
            });

            modalInstance.result.then(function (services) {
                $scope.node.services = services;
                $('#tabServicesList li a:first').tab('show');
                buildAvailableServices();
            });
        };

        var buildAvailableServices = function() {
            if(!$scope.node) {
                return false;
            }

            var availableServices = [];
            angular.forEach($scope.services.all, function(service) {
                if(service.name && (!$scope.node.services || !$scope.node.services[service.name])) {
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
controllers.controller('nodeListController', function($scope, nodesService) {
    $scope.nodes = [];

    $scope.loadUnregisteredNodes = true;
    nodesService.list({unregistered: 1}).success(function(data) {
        $scope.unregisteredNodes = data;
        $scope.loadUnregisteredNodes = false;
    }).error(function() {
        $scope.unregisteredNodes = null;
        $scope.loadUnregisteredNodes = false;
    });

    var initBreadCrumb = function() {
        $('ul.breadcrumb').html('<li><a href="/">Dashboard</a><span class="divider">/</span></li><li class="active">Nodes</li>');
    };

    initBreadCrumb();
});
controllers.controller('nodeRegisterController', function($scope, $filter, $routeParams, logsService, nodesService) {
    $scope.nodeName = $routeParams.nodeName;
    $scope.nodetypes = [
        {name:"Select a node type", value: ''},
        {name:"Virtual Server", value: "virtual", image: "dummy_vm_200.png"},
        {name:"Cloud Server", value: "cloud", image: "dummy_cloud_200.png"},
        {name:"Dedicated Server‎", value: "dedicated", image: "dummy_server_200.png"}
    ];

    $scope.$watch("node.nodetype", function(option){
        $scope.changetype(option);
    });

    $scope.cancelRegistration = function() {
        window.location = '#/';
    };

    $scope.register = function() {
        $scope.errors = [];
        nodesService.create($.param($scope.node)).success(function(response, status, headers){
            if(headers('location')) {
                return location.href = headers('location');
            }

            return location.href = "#/";
        }).error(function(data) {
            if(data.errors) {
                $scope.errors = data.errors;
            } else {
                $scope.errors[0] = 'Request failed!';
            }
        });
    }

    var initBreadCrumb = function() {
        $('ul.breadcrumb').html('<li><a href="/">Dashboard</a><span class="divider">/</span></li><li><a href="#/">Nodes</a><span class="divider">/</span></li><li class="active">register ' + $scope.nodeName + '</li>');
    };

    $scope.changetype = function(option){
        if (this.selected = $filter("filter")($scope.nodetypes, {value: (option || "")})[0]) {
            $scope.selected = this.selected;
        }
    };

    $scope.loadLog = true;
    logsService.list({context: $scope.nodeName, type:"conflict", action: "unregistered api"}).success(function(data) {
        if(data.length) {
            $scope.node = data[0].data;
            $scope.node.nodetype = '';
        } else {
            $scope.log = null;
        }

        $scope.loadLog = false;
    }).error(function() {
        $scope.loadLog = false;
        $scope.log = null;
    });

    initBreadCrumb();
});
controllers.controller('nodeModalDelete', function($scope, $modalInstance, nodesService, nodeId) {
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
controllers.controller('nodeModalRemoveService', function($scope, $modalInstance, service, node, nodesService) {
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


