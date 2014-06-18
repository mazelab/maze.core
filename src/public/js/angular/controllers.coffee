controllers = angular.module 'maze.controllers', []

controllers.controller 'clientListController', ['$scope', 'authService', ($scope, authService) ->
  $scope.client = []

  initBreadCrumb = () ->
    $('ul.breadcrumb').html('<li><a href="/">Dashboard</a><span class="divider">/</span></li><li class="active">Clients</li>')

  $scope.loginAsClient = (id) ->
    $scope.loadClientLogin = true;
    $scope.errors = {}
    authService.client id
    .success (data, code, headers) ->
        return window.location = headers('location') if headers('location')
        return location.href = "/";
        $scope.loadClientLogin = false;
    .error (data) ->
        $scope.errors[id] = ['Request failed!'];
        $scope.loadClientLogin = false;

  initBreadCrumb()
]

controllers.controller 'clientEditController', ['$scope', '$routeParams', '$q', '$modal', '$filter', '$timeout', 'clientsService', 'authService', 'modulesService', 'domainsService', 'logsService', 'nodesService', ($scope, $routeParams, $q, $modal, $filter, $timeout, clientsService, authService, modulesService, domainsService, logsService, nodesService) ->
  $scope.client = {}
  $scope.clientId = $routeParams.clientId

  initBreadCrumb = () ->
    $('ul.breadcrumb').html('<li><a href="/">Dashboard</a><span class="divider">/</span></li><li><a href="#/">Clients</a><span class="divider">/</span></li><li class="active"> ' + $scope.client.label + '</li>')

  $scope.activate = () ->
    $scope.changeState true

  $scope.deactivate = () ->
    $scope.changeState false

  $scope.passwordPrompt = false
  $scope.openPasswordPrompt = () ->
    $scope.passwordPrompt = true

  $scope.updateAdditional = (data) ->
    clientsService.update $scope.clientId, $.param(data)
    .success (data, code) ->
        $scope.client.additionalFields = data.client.additionalFields if code is 200 and data.client.additionalFields?

  $scope.closePasswordPrompt = () ->
    $scope.passwordPrompt = false
    $scope.accessFormErr = []
    $scope.accessData = {}
    $scope.accessSuccess = false

  $scope.changeState = (state) ->
    $scope.alerts = []
    clientsService.update $scope.clientId, $.param({'status': state})
    .success (data) ->
      $scope.client.status = state;
    .error () ->
      $scope.alerts = [ {msg: 'Request failed!', type: 'danger'}]

  $scope.modalDelete = () ->
    modalProperties =
      templateUrl: '/partials/admin/clients/modal/delete.html'
      controller: 'clientModalDelete'
      resolve: clientId: () -> $scope.clientId

    modalInstance = $modal.open modalProperties
    modalInstance.result.then (code) ->
      window.location = "#/" if code is 200

  $scope.loadLogs = true
  logsService.list {client: $scope.clientId, limit: 10}
  .success (data) ->
      $scope.logs = data
      $scope.loadLogs = false
  .error (data) ->
      $scope.logs = null
      $scope.loadLogs = false

  $scope.loadDomains = true
  domainsService.list {client: $scope.clientId, limit: 10}
  .success (data) ->
      $scope.domains =  data
      $scope.loadDomains = false
  .error () ->
      $scope.domains =  null;
      $scope.loadDomains = false;

  $scope.loadNodes = true
  nodesService.list {client: $scope.clientId, limit: 10}
  .success (data) ->
      $scope.nodes =  data
      $scope.loadNodes = false
  .error () ->
      $scope.nodes =  null;
      $scope.loadNodes = false;

  $scope.loadClient = true
  clientsService.get $scope.clientId
  .success (data) ->
      $scope.client = data
      $scope.loadClient = false

      initBreadCrumb()
      initServices()
  .error () ->
      $scope.client = null
      $scope.loadClient = false

  $scope.accessData = {}
  $scope.changeClientPassword = () ->
    return false if not $scope.accessData.password or not $scope.accessData.confirmPassword
    $scope.accessFormErr = []
    $scope.accessSuccess = false

    clientsService.update $scope.clientId, $.param $scope.accessData
    .success () ->
        $scope.accessSuccess = true
        $timeout () ->
          $scope.closePasswordPrompt()
        , 4000
    .error (data) ->
        $scope.accessFormErr = data.errForm if data.errForm?

  $scope.updateProperty = (property, data) ->
    return false if not (property or data)

    updateData = {}
    updateData[property] = data

    clientsService.update $scope.clientId, $.param(updateData)
    .catch (request) ->
        if request.data.errForm?[property]?
          messages = ""
          angular.forEach request.data.errForm[property], (value) ->
            messages = messages + ";" if messages
            messages = messages + value
        else
          messages = false

        $q.reject(messages)

  $scope.loginAsClient = () ->
    return false if not $scope.client._id
    $scope.loadClientLogin = true;
    $scope.errors = {}
    authService.client $scope.client._id
    .success (data, code, headers) ->
        return window.location = headers('location') if headers('location')
        return location.href = "/";
        $scope.loadClientLogin = false;
    .error (data) ->
        $scope.alerts = [ {msg: 'Request failed!', type: 'danger'}]
        $scope.loadClientLogin = false;

  initServices = () ->
    $scope.loadServices = true;
    $scope.services = {selected: '', all: []};

    modulesService.list()
    .success (data) ->
        $scope.services.all = data
        buildAvailableServices()
        $scope.loadServices = false
    .error () ->
        $scope.loadServices = false
        buildAvailableServices()
        $scope.errAddService = ['Failed to load services']

    $scope.addService = (serviceName) ->
      $scope.errAddService = []
      service = $filter('filter')($scope.services.available, {name: serviceName})[0]

      return false if not service?._id?

      updateData = {services: {}}
      updateData.services[serviceName] = true;

      clientsService.update $scope.client._id, $.param(updateData)
      .success (data) ->
          $scope.client.services = data.client.services
          $scope.services.selected = ''
          buildAvailableServices()

          setTimeout () ->
            $('#tabServices-' + serviceName).tab('show')
          , 0
      .error () ->
          $scope.errAddService.push('Failed')

    $scope.modalRemoveService = (service) ->
      return false if not service

      modalProperties =
        templateUrl: '/partials/admin/clients/modal/removeService.html'
        controller: 'clientModalRemoveService'
        resolve: {
          service: () ->
            service
          client: () ->
            $scope.client
        }

      modalInstance = $modal.open(modalProperties)
      modalInstance.result.then (services) ->
        $scope.client.services = services
        $('#tabServicesList li a:first').tab('show')
        buildAvailableServices()

    buildAvailableServices = () ->
      return false if not $scope.client?

      availableServices = []
      angular.forEach $scope.services.all, (service) ->
        availableServices.push(service) if service.name? and not $scope.client.services?[service.name]?

      if !availableServices.length
        $scope.services.available = [{label: 'No services available', name:''}]
      else
        $scope.services.available = [{label: 'Add new service', name:''}]

      $scope.services.available = $scope.services.available.concat(availableServices);
]
controllers.controller 'clientModalDelete', [ '$scope', '$modalInstance', 'clientsService', 'clientId', ($scope, $modalInstance, clientsService, clientId) ->
  $scope.ok = () ->
    $scope.errMessages = []

    clientsService.delete clientId
    .success (data, code) ->
        $modalInstance.close(code);
    .error () ->
        $scope.errMessages.push('Failed')

  $scope.cancel = () ->
    $modalInstance.dismiss()
]

controllers.controller 'clientModalRemoveService', ['$scope', '$modalInstance', 'service', 'client', 'clientsService', ($scope, $modalInstance, service, client, clientsService) ->
  $scope.service = service
  $scope.client = client
  $scope.errMessages = []

  $scope.ok = () ->
    $scope.errMessages = []

    updateData = {services: {}}
    updateData.services[service.name] = false

    clientsService.update client._id, $.param(updateData)
    .success (data) ->
        $modalInstance.close(data.client.services)
    .error () ->
        $scope.errMessages.push('Failed')

  $scope.cancel = () ->
    $modalInstance.dismiss()
]

controllers.controller 'clientNewController', ['$scope', 'clientsService', ($scope, clientsService) ->
  $scope.client = {}

  initBreadCrumb = () ->
    $('ul.breadcrumb').html('<li><a href="/">Dashboard</a><span class="divider">/</span></li><li><a href="#/">Clients</a><span class="divider">/</span></li><li class="active">new</li>')

  $scope.cancel = () ->
    window.location = '#/'

  $scope.createClient = () ->
    $scope.formErrors = []
    clientsService.create $.param($scope.client)
    .success (data, status, headers) ->
        return window.location = headers('location') if headers('location')
        return location.href = "#/";
    .error (data) ->
        $scope.formErrors = data.formErrors if data.formErrors?

  initBreadCrumb()
]

controllers.controller 'domainModalRemoveService', ['$scope', '$modalInstance', 'service', 'domain', 'domainsService', ($scope, $modalInstance, service, domain, domainsService) ->
  $scope.service = service
  $scope.domain = domain

  $scope.ok = () ->
    $scope.errMessages = []

    updateData = {services: {}}
    updateData.services[service.name] = false

    domainsService.update domain._id, $.param(updateData)
    .success (data) ->
      $modalInstance.close(data.domain.services)
    .error () ->
      $scope.errMessages.push 'Failed'

  $scope.cancel = () ->
    $modalInstance.dismiss()
]

controllers.controller 'domainModalDelete', ['$scope', '$modalInstance', 'domainsService', 'domainId', ($scope, $modalInstance, domainsService, domainId) ->
  $scope.ok = () ->
    $scope.errMessages = []

    domainsService.delete domainId
      .success (data, code) ->
        $modalInstance.close code
      .error () ->
        $scope.errMessages.push('Failed')

  $scope.cancel = () ->
    $modalInstance.dismiss()
]

controllers.controller 'domainListController', ['$scope', ($scope) ->
  $scope.domains = []

  initBreadCrumb = () ->
    $('ul.breadcrumb').html('<li><a href="/">Dashboard</a><span class="divider">/</span></li><li class="active">Domains</li>')

  initBreadCrumb()
]

controllers.controller 'domainEditController', [ '$scope', '$filter', '$modal', '$q', 'domainsService', '$routeParams', 'modulesService', 'logsService', 'nodesService', ($scope, $filter, $modal, $q, domainsService, $routeParams, modulesService, logsService, nodesService) ->
  $scope.domainId = $routeParams.domainId

  initBreadCrumb = () ->
    $('ul.breadcrumb').html('<li><a href="/">Dashboard</a><span class="divider">/</span></li><li><a href="#/">Domains</a><span class="divider">/</span></li><li class="active">' + $scope.domain.name + '</li>')

  $scope.updateAdditional = (data) ->
    return domainsService.update $scope.domainId, $.param(data)
    .success (data) ->
        $scope.domain.additionalFields = data.domain.additionalFields if data.domain?.additionalFields?

  $scope.loadLogs = true
  logsService.list {domain: $scope.domainId, limit: 10}
  .success (data) ->
      $scope.logs = data
      $scope.loadLogs = false
  .error (data) ->
      $scope.logs = null
      $scope.loadLogs = false

  $scope.loadNodes = true
  nodesService.list {domain: $scope.domainId, limit: 10}
  .success (data) ->
      $scope.nodes =  data
      $scope.loadNodes = false
  .error () ->
      $scope.nodes =  null
      $scope.loadNodes = false

  $scope.loadDomain = true
  domainsService.get $scope.domainId
  .success (data) ->
      $scope.domain = data
      $scope.loadDomain = false;

      initBreadCrumb()
      initServices()
  .error (data, code) ->
      window.location = '#/' if code is 404
      $scope.loadDomain = false;

  $scope.modalDeleteDomain = () ->
    modalProperties =
      templateUrl: '/partials/admin/domains/modal/delete.html'
      controller: 'domainModalDelete'
      resolve: domainId: () -> $scope.domainId

    modalInstance = $modal.open modalProperties
    modalInstance.result.then (code) ->
      window.location = "#/" if code is 200

  $scope.updateProperty = (property, data) ->
    return false if not (property or data)

    updateData = {}
    updateData[property] = data

    domainsService.update $scope.domainId, $.param(updateData)
    .catch (request) ->
      if request.data.errForm?[property]?
        messages = ""
        angular.forEach request.data.errForm[property], (value, key) ->
          messages = messages + ";" if messages?
          messages = messages + value
      else
        messages = false

      $q.reject(messages)

  initServices = () ->
    $scope.loadServices = true;
    $scope.services = {selected: '', all: []};

    modulesService.list()
    .success (data) ->
      $scope.services.all = data
      buildAvailableServices()
      $scope.loadServices = false
    .error () ->
      $scope.loadServices = false
      buildAvailableServices()
      $scope.errAddService = ['Failed to load services']

    $scope.addService = (serviceName) ->
      $scope.errAddService = []
      service = $filter('filter')($scope.services.available, {name: serviceName})[0]

      return false if not service?._id?

      updateData = {services: {}}
      updateData.services[serviceName] = true;

      domainsService.update $scope.domain._id, $.param(updateData)
      .success (data) ->
        $scope.domain.services = data.domain.services
        $scope.services.selected = ''
        buildAvailableServices()

        setTimeout () ->
          $('#tabServices-' + serviceName).tab('show')
        , 0
      .error () ->
        $scope.errAddService.push('Failed')

    $scope.modalRemoveDomainService = (service) ->
      return false if not service

      modalProperties =
        templateUrl: '/partials/admin/domains/modal/removeService.html'
        controller: 'domainModalRemoveService'
        resolve: {
          service: () ->
            service
          domain: () ->
            $scope.domain
        }

      modalInstance = $modal.open(modalProperties)
      modalInstance.result.then (services) ->
        $scope.domain.services = services
        $('#tabServicesList li a:first').tab('show')
        buildAvailableServices()

    buildAvailableServices = () ->
      return false if not $scope.domain?

      availableServices = []
      angular.forEach $scope.services.all, (service) ->
        availableServices.push(service) if service.name? and not $scope.domain.services?[service.name]?

      if !availableServices.length
        $scope.services.available = [{label: 'No services available', name:''}]
      else
        $scope.services.available = [{label: 'Add new service', name:''}]

      $scope.services.available = $scope.services.available.concat(availableServices);
]

controllers.controller 'domainNewController', [ '$scope', '$filter', 'domainsService', 'clientsService', ($scope, $filter, domainsService, clientsService) ->
  $scope.clients  = $scope.domain = $scope.selected = {};

  $scope.changeclient = (option) ->
    $scope.selected = $filter("filter")($scope.clients, {_id: (option)})[0]

  clientsService.list()
  .success (clients) ->
    $scope.clients = clients || {}

  $scope.createDomain = () ->
    $scope.formErrors = []
    domainsService.create $.param($scope.domain)
    .success (data, status, headers) ->
      return window.location = headers('location') if headers('location')
      return location.href = "#/";
    .error (data) ->
      $scope.formErrors = data.formErrors if data.formErrors?

  $scope.cancelCreation = () ->
    window.location = '#/';

  initBreadCrumb = () ->
    $('ul.breadcrumb').html('<li><a href="/">Dashboard</a><span class="divider">/</span></li><li><a href="#/">Domains</a><span class="divider">/</span></li><li class="active">new</li>');

  initBreadCrumb()
]

controllers.controller 'nodeEditController', ['$scope', '$filter', '$modal', '$q', '$routeParams', 'nodesService', 'logsService', 'modulesService', 'clientsService', 'domainsService' , ($scope, $filter, $modal, $q, $routeParams, nodesService, logsService, modulesService, clientsService, domainsService) ->
  $scope.nodeId = $routeParams.nodeId
  $scope.nodetypes = [
    {name:"Virtual Server", value: "virtual", image: "dummy_vm_200.png"}
    {name:"Cloud Server", value: "cloud", image: "dummy_cloud_200.png"}
    {name:"Dedicated Server‎", value: "dedicated", image: "dummy_server_200.png"}
  ]

  initBreadCrumb = () ->
    $('ul.breadcrumb').html('<li><a href="/">Dashboard</a><span class="divider">/</span></li><li><a href="#/">Nodes</a><span class="divider">/</span></li><li class="active">' + $scope.node.name + '</li>');

  $scope.updateAdditional = (data) ->
    nodesService.update $scope.nodeId, $.param(data)
    .success (data, code) ->
      $scope.node.additionalFields = data.node.additionalFields if code is 200 and data.node.additionalFields?

  $scope.loadNode = true
  nodesService.get $scope.nodeId
  .success (data) ->
    $scope.node = data
    $scope.loadNode = false

    initBreadCrumb()
    initServices()
    initNodeTypeSelect()
  .error (data, code) ->
    window.location = '#/' if code is 404
    $scope.loadNode = false;

  initNodeTypeSelect = () ->
    $scope.$watch "node.nodetype", (option) ->
      $scope.changeNodeType (option)

    $scope.changeNodeType = (option) ->
      option = (option || $scope.node.nodetype)
      select = $filter("filter")($scope.nodetypes, {value: (option)})[0]
      $scope.selected = select if option and select?.value?

  $scope.loadLogs = true
  logsService.list {node: $scope.nodeId,limit: 10}
  .success (data) ->
    $scope.logs = data
    $scope.loadLogs = false
  .error () ->
    $scope.loadLogs = false
    $scope.logs = null

  $scope.loadClients = true
  clientsService.list {node: $scope.nodeId, limit: 10}
  .success (data) ->
    $scope.clients =  data
    $scope.loadClients = false
  .error () ->
    $scope.clients =  null
    $scope.loadClients = false

  $scope.loadDomains = true
  domainsService.list {node: $scope.nodeId, limit: 10}
  .success (data) ->
    $scope.domains =  data
    $scope.loadDomains = false
  .error () ->
    $scope.domains =  null;
    $scope.loadDomains = false;

  $scope.modalDelete = () ->
    modalProperties =
      templateUrl: '/partials/admin/nodes/modal/delete.html'
      controller: 'nodeModalDelete'
      resolve: {
        nodeId: () ->
          $scope.nodeId
       }

    modalInstance = $modal.open modalProperties
    modalInstance.result.then (code) ->
      location.href = "#/" if code is 200

  $scope.updateProperty = (property, data) ->
    return false if not (property or data)

    updateData = {}
    updateData[property] = data

    nodesService.update $scope.nodeId, $.param(updateData)
    .catch (request) ->
      if request.data.errForm?[property]?
        messages = ""
        angular.forEach request.data.errForm[property], (value, key) ->
          messages = messages + ";" if messages?
          messages = messages + value
      else
        messages = false

      $q.reject(messages)

  initServices = () ->
    $scope.loadServices = true;
    $scope.services = {selected: '', all: []};

    modulesService.list()
    .success (data) ->
        $scope.services.all = data
        buildAvailableServices()
        $scope.loadServices = false
    .error () ->
        $scope.loadServices = false
        buildAvailableServices()
        $scope.errAddService = ['Failed to load services']

    $scope.addService = (serviceName) ->
      $scope.errAddService = []
      service = $filter('filter')($scope.services.available, {name: serviceName})[0]

      return false if not service?._id?

      updateData = {services: {}}
      updateData.services[serviceName] = true;

      nodesService.update $scope.node._id, $.param(updateData)
      .success (data) ->
        $scope.node.services = data.node.services
        $scope.services.selected = ''
        buildAvailableServices()

        setTimeout () ->
          $('#tabServices-' + serviceName).tab('show')
        , 0
      .error () ->
        $scope.errAddService.push('Failed')

    $scope.modalRemoveService = (service) ->
      return false if not service

      modalProperties =
        templateUrl: '/partials/admin/nodes/modal/removeService.html'
        controller: 'nodeModalRemoveService'
        resolve: {
          service: () ->
            service
          node: () ->
            $scope.node
        }

      modalInstance = $modal.open(modalProperties)
      modalInstance.result.then (services) ->
        $scope.node.services = services
        $('#tabServicesList li a:first').tab('show')
        buildAvailableServices()

    buildAvailableServices = () ->
      return false if not $scope.node?

      availableServices = []
      angular.forEach $scope.services.all, (service) ->
        availableServices.push(service) if service.name? and not $scope.node.services?[service.name]?

      if !availableServices.length
        $scope.services.available = [{label: 'No services available', name:''}]
      else
        $scope.services.available = [{label: 'Add new service', name:''}]

      $scope.services.available = $scope.services.available.concat(availableServices);
]

controllers.controller 'nodeListController', ['$scope', 'nodesService', ($scope, nodesService) ->
  $scope.nodes = []

  $scope.loadUnregisteredNodes = true
  nodesService.list ({unregistered: 1})
  .success (data) ->
    $scope.unregisteredNodes = data
    $scope.loadUnregisteredNodes = false
  .error () ->
    $scope.unregisteredNodes = null
    $scope.loadUnregisteredNodes = false

  initBreadCrumb = () ->
    $('ul.breadcrumb').html('<li><a href="/">Dashboard</a><span class="divider">/</span></li><li class="active">Nodes</li>');

  initBreadCrumb()
]

controllers.controller 'nodeRegisterController', [ '$scope', '$filter', '$routeParams', 'logsService', 'nodesService', ($scope, $filter, $routeParams, logsService, nodesService) ->
  $scope.nodeName = $routeParams.nodeName
  $scope.nodetypes = [
    {name:"Select a node type", value: ''}
    {name:"Virtual Server", value: "virtual", image: "dummy_vm_200.png"}
    {name:"Cloud Server", value: "cloud", image: "dummy_cloud_200.png"}
    {name:"Dedicated Server‎", value: "dedicated", image: "dummy_server_200.png"}
  ]

  $scope.cancelRegistration = () ->
    window.location = '#/';

  initBreadCrumb = () ->
    $('ul.breadcrumb').html('<li><a href="/">Dashboard</a><span class="divider">/</span></li><li><a href="#/">Nodes</a><span class="divider">/</span></li><li class="active">register ' + $scope.nodeName + '</li>');

  $scope.changetype = (option) ->
    if (@selected = $filter("filter")($scope.nodetypes, {value: (option || "")})[0])
      $scope.selected = @selected

  $scope.$watch "node.nodetype", (option) ->
    $scope.changetype option

  $scope.register = () ->
    $scope.errors = []
    nodesService.create $.param($scope.node)
    .success (response, status, headers) ->
      return window.location = headers('location') if headers('location')?
      window.location = "#/"
    .error (data) ->
      if data.errors
        $scope.errors = data.errors;
      else
        $scope.errors[0] = 'Request failed!';

  $scope.loadLog = true
  logsService.list {context: $scope.nodeName, type:"conflict", action: "unregistered api"}
  .success (data) ->
    if data.length
      $scope.node = data[0].data
      $scope.node.nodetype = ''
    else
      $scope.log = null
    $scope.loadLog = false;
  .error () ->
    $scope.log = null;
    $scope.loadLog = false;

  initBreadCrumb();
]

controllers.controller 'nodeModalDelete', [ '$scope', '$modalInstance', 'nodesService', 'nodeId', ($scope, $modalInstance, nodesService, nodeId) ->
  $scope.ok = () ->
    $scope.errMessages = [];

    nodesService.delete nodeId
    .success (data, code) ->
      $modalInstance.close(code)
    .error () ->
      $scope.errMessages.push('Failed');

  $scope.cancel = () ->
    $modalInstance.dismiss()
]

controllers.controller 'nodeModalRemoveService', [ '$scope', '$modalInstance', 'service', 'node', 'nodesService', ($scope, $modalInstance, service, node, nodesService) ->
  $scope.service = service
  $scope.node = node

  $scope.ok = () ->
    $scope.errMessages = []

    updateData = {services: {}}
    updateData.services[service.name] = false

    nodesService.update node._id, $.param(updateData)
    .success (data) ->
      $modalInstance.close(data.node.services)
    .error () ->
      $scope.errMessages.push('Failed')

  $scope.cancel = () ->
    $modalInstance.dismiss()
]

