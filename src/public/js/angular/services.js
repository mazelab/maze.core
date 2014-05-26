var services = angular.module("mazeServices", []);

/**
 * service for nodes
 */
services.service('nodesService', function($http) {
    this.list = function(params) {
        return $http.get('/api/nodes', {params: params});
    };
    this.get = function(id, params){
        return($http.get("/api/nodes/"+ id, {params: params}));
    };
    this.update = function(id, dataset){
        return($http.put("/api/nodes/"+ id, $.param(dataset)));
    };
    this.delete = function(id){
        return($http.delete("/api/nodes/"+ id));
    };
    this.create = function(dataset){
        return($http.post("/api/nodes/", $.param(dataset),
              {headers: {"Content-Type": "application/x-www-form-urlencoded"}}));
    };
});

/**
 * service for clients
 */
services.service('clientsService', function($http) {
    this.list = function(params) {
        return $http.get('/api/clients', {params: params});
    };
    this.get = function(id, params){
        return($http.get("/api/clients/"+ id, {params: params}));
    };
    this.update = function(id, dataset){
        return($http.put("/api/clients/"+ id, $.param(dataset)));
    };
    this.delete = function(id){
        return($http.delete("/api/clients/"+ id));
    };
});

/**
 * service for modules
 */
services.service('modulesService', function($http) {
    this.list = function(params) {
        return $http.get('/api/modules', {params: params});
    };
    this.get = function(name, params){
        return($http.get("/api/modules/"+ name, {params: params}));
    };
    this.update = function(name, dataset){
        return($http.put("/api/modules/"+ name, $.param(dataset)));
    };
});

/**
 * service for domain
 */
services.service('domainsService', function($http) {
    this.list = function(params) {
        return $http.get('/api/domains', {params: params});
    };
    this.get = function(name, params){
        return($http.get("/api/domains/"+ name, {params: params}));
    };
    this.update = function(name, dataset){
        return(
            $http.put("/api/domains/"+ name, $.param(dataset))
        );
    };
    this.delete = function(name){
        return($http.delete("/api/domains/"+ name));
    };
    this.create = function(dataset){
        return $http.post("/api/domains/", $.param(dataset),
               {headers: {"Content-Type": "application/x-www-form-urlencoded"}});
    };
});

/**
 * service for log
 */
services.service('logsService', function($http) {
    this.list = function(params) {
        return $http.get('/api/logs', {params: params});
    };
});
