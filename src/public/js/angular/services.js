// Generated by CoffeeScript 1.7.1
(function() {
  angular.module("maze.services", []).service('nodesService', [
    '$http', function($http) {
      return {
        list: function(params) {
          return $http.get('/api/nodes', {
            params: params
          });
        },
        get: function(id, params) {
          return $http.get("/api/nodes/" + id, {
            params: params
          });
        },
        update: function(id, data) {
          var httpProperties;
          httpProperties = {
            method: 'POST',
            url: '/api/nodes/' + id,
            data: data,
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
              'X-Requested-With': 'XMLHttpRequest'
            }
          };
          return $http(httpProperties);
        },
        "delete": function(id) {
          return $http["delete"]("/api/nodes/" + id);
        },
        create: function(data) {
          var httpProperties;
          httpProperties = {
            method: 'POST',
            url: '/api/nodes/',
            data: data,
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
              'X-Requested-With': 'XMLHttpRequest'
            }
          };
          return $http(httpProperties);
        }
      };
    }
  ]).service('clientsService', [
    '$http', function($http) {
      return {
        list: function(params) {
          return $http.get('/api/clients', {
            params: params
          });
        },
        get: function(id, params) {
          return $http.get("/api/clients/" + id, {
            params: params
          });
        },
        update: function(id, data) {
          var httpProperties;
          httpProperties = {
            method: 'POST',
            url: '/api/clients/' + id,
            data: data,
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
              'X-Requested-With': 'XMLHttpRequest'
            }
          };
          return $http(httpProperties);
        },
        create: function(data) {
          var httpProperties;
          httpProperties = {
            method: 'POST',
            url: '/api/clients',
            data: data,
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
              'X-Requested-With': 'XMLHttpRequest'
            }
          };
          return $http(httpProperties);
        },
        set: function(id, dataset) {
          return $http.put("/api/clients/" + id, $.param(dataset));
        },
        "delete": function(id) {
          return $http["delete"]("/api/clients/" + id);
        }
      };
    }
  ]).service('modulesService', [
    '$http', function($http) {
      return {
        list: function(params) {
          return $http.get('/api/modules', {
            params: params
          });
        },
        get: function(name, params) {
          return $http.get("/api/modules/" + name, {
            params: params
          });
        },
        set: function(name, dataset) {
          return $http.put("/api/modules/" + name, $.param(dataset));
        },
        update: function(name, data) {
          var httpProperties;
          httpProperties = {
            method: 'POST',
            url: '/api/modules/' + name,
            data: data,
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
              'X-Requested-With': 'XMLHttpRequest'
            }
          };
          return $http(httpProperties);
        }
      };
    }
  ]).service('domainsService', [
    '$http', function($http) {
      return {
        list: function(params) {
          return $http.get('/api/domains', {
            params: params
          });
        },
        get: function(id, params) {
          return $http.get("/api/domains/" + id, {
            params: params
          });
        },
        update: function(id, data) {
          var httpProperties;
          httpProperties = {
            method: 'POST',
            url: '/api/domains/' + id,
            data: data,
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
              'X-Requested-With': 'XMLHttpRequest'
            }
          };
          return $http(httpProperties);
        },
        "delete": function(id) {
          return $http["delete"]("/api/domains/" + id);
        },
        create: function(data) {
          var httpProperties;
          httpProperties = {
            method: 'POST',
            url: '/api/domains',
            data: data,
            headers: {
              'Content-Type': 'application/x-www-form-urlencoded',
              'X-Requested-With': 'XMLHttpRequest'
            }
          };
          return $http(httpProperties);
        }
      };
    }
  ]).service('logsService', [
    '$http', function($http) {
      return {
        list: function(params) {
          return $http.get('/api/logs', {
            params: params
          });
        }
      };
    }
  ]).service('authService', [
    '$http', function($http) {
      return {
        client: function(id) {
          return $http.post('/api/auth/' + id, {});
        }
      };
    }
  ]);

}).call(this);
