angular.module "maze.services", []

.service 'nodesService',[ '$http', ($http) ->
  {
    list: (params) ->
      $http.get('/api/nodes', {params: params})

    get: (id, params) ->
      $http.get("/api/nodes/"+ id, {params: params})

    update: (id, data) ->
      httpProperties = {
        method: 'POST'
        url: '/api/nodes/' + id
        data: data
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
          'X-Requested-With': 'XMLHttpRequest'
        }
      }
      $http httpProperties

    delete: (id) ->
      $http.delete("/api/nodes/"+ id)

    create: (data) ->
      httpProperties = {
        method: 'POST'
        url: '/api/nodes/'
        data: data
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded'
          'X-Requested-With': 'XMLHttpRequest'
        }
      }
      $http httpProperties
  }
]

.service 'clientsService', [ '$http', ($http) ->
  {
    list: (params) ->
      $http.get('/api/clients', {params: params})

    get: (id, params) ->
      $http.get("/api/clients/"+ id, {params: params})

    update: (id, data) ->
      httpProperties = {
        method: 'POST',
        url: '/api/clients/' + id,
        data: data,
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest'
        }
      }
      $http httpProperties

    create: (data) ->
      httpProperties = {
        method: 'POST',
        url: '/api/clients',
        data: data,
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest'
        }
      }
      $http httpProperties

    set: (id, dataset) ->
      $http.put("/api/clients/"+ id, $.param(dataset))

    delete: (id) ->
      $http.delete("/api/clients/"+ id)
  }
]

.service 'modulesService', ['$http', ($http) ->
  {
    list: (params) ->
      $http.get('/api/modules', {params: params})

    get: (name, params) ->
      $http.get("/api/modules/"+ name, {params: params})

    set: (name, dataset) ->
      $http.put("/api/modules/"+ name, $.param(dataset))

    update: (name, data) ->
      httpProperties = {
        method: 'POST',
        url: '/api/modules/' + name,
        data: data,
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest'
        }
      }
      $http httpProperties
  }
]

.service 'domainsService', [ '$http', ($http) ->
  {
    list: (params) ->
      $http.get('/api/domains', {params: params})

    get: (id, params) ->
      $http.get("/api/domains/"+ id, {params: params})

    update: (id, data) ->
      httpProperties = {
        method: 'POST',
        url: '/api/domains/' + id,
        data: data,
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest'
        }
      }
      $http httpProperties

    delete: (id) ->
     $http.delete("/api/domains/"+ id)

    create: (data) ->
      httpProperties = {
        method: 'POST',
        url: '/api/domains',
        data: data,
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
          'X-Requested-With': 'XMLHttpRequest'
        }
      }
      $http httpProperties
  }
]

.service 'logsService', [ '$http', ($http) ->
  {
    list: (params) ->
      $http.get('/api/logs', {params: params})
  }
]

.service 'authService', [ '$http', ($http) ->
  {
    client: (id) ->
      $http.post('/api/auth/' + id, {})
  }
]