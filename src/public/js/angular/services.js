var services = angular.module("mazeServices", []);

/**
 * service for nodes
 */
services.service('nodesService', function($http) {
    return {
        get: function() {
            return $http.get('/api/nodes', {
                params: {
                    service: 'mongodb'
                }
            });
        }
    }
});

/**
 * service for clients
 */
services.service('clientService', function($http) {
    return {
        get: function() {
            return $http.get('/api/clients', {
                params: {
                    service: 'mongodb'
                }
            });
        }
    }
});