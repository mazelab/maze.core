var services = angular.module("mazeServices", []);

/**
 * service for nodes
 */
services.service('nodesService', function($http) {
    this.list = function(params) {
        return $http.get('/api/nodes', {
            params: params
        });
    };
});

/**
 * service for clients
 */
services.service('clientService', function($http) {
    this.list = function(params) {
        return $http.get('/api/clients', {
            params: params
        });
    };
});

/**
 * service for domain
 */
services.service('domainService', function($http) {
    this.list = function(params) {
        return $http.get('/api/domains', {
            params: params
        });
    };
    this.get = function(name){
        return($http.get("/api/domains/"+ name));
    };
    this.update = function(name, dataset){
        return(
            $http.post("/api/domains/"+ name, $.param(dataset),
            {headers: {"Content-Type": "application/x-www-form-urlencoded"}})
        );
    };
    this.delete = function(name){
        return($http.delete("/api/domains/"+ name));
    };
    this.create = function(name, dataset){
        return $http.put("/api/domains/"+ name, $.param(dataset));
    };
});