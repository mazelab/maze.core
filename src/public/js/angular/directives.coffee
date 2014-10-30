angular.module "maze.directives", []

.directive 'mazeDlWrapper', [ () ->
  {
    restrict: 'A'
    scope: {
      'label': '@',
      'dlClass' : '@',
      'dtClass' : '@',
      'ddClass': '@'
    }
    transclude: true
    templateUrl: '/partials/admin/directives/dlWrapper.html'
  }
]

.directive 'mazeHtmlPopover', [ () ->
  {
    restrict: 'A'
    scope: {
      label: '@',
      title: '@',
      placement: '@'
      namespace: '@'
      single: '@'
    }
    transclude: true,
    template: '<div><a class="{{namespace}}" href="" onclick="return false;">{{label || "popover"}}</span></div>'
    compile: (element, attrs) ->
      attrs.namespace = 'aMazeHtmlPopover' if not attrs.namespace?
      attrs.single = true if attrs.single? and attrs.single and not attrs.single == 'false'

      {
        post: ($scope, element, attrs, ctrl, transclude) ->
          clickEvent = () ->
            if $scope.single == 'true'
              $ '.' + $scope.namespace
              .not(this)
              .popover 'hide'

#            $ this
#            .popover 'toggle'


          transclude $scope.$parent, (content) ->
            element
            .find 'a'
            .unbind 'click'
            .popover {
              content: content
              html: true
              trigger: 'click'
              placement: $scope.placement
              title: $scope.title
            }
            .click clickEvent
      }
  }
]

.directive 'mazeSearch', ['$filter', ($filter) ->
  {
    restrict: 'A'
    templateUrl: '/partials/admin/directives/search.html'
    transclude: true
    scope: {
      data: '=',
      limit: '@',
      page: '@',
      uri: '@',
      search: '='
    }
    compile: (element, attrs) ->
      attrs.limit = 10 if not attrs.limit?
      attrs.page = 1 if not attrs.page?
    controller: ($scope, $http, $q) ->
      $scope.first = $scope.last = $scope.total = ''
      return false if not $scope.uri

      $scope.$parent.$on 'mazeSearchReload', () ->
        load()

      $scope.$watch 'page + search + limit', ()  ->
        load()

      load = () ->
        $scope.loadPager = true
        $scope.errorMsg = []

        params = {
          search: $scope.search || ''
          page: $scope.page || 1
          limit: $scope.limit || 10
        }

        $scope.currentRequest.resolve() if $scope.currentRequest

        $scope.currentRequest = $q.defer()
        $http.get $scope.uri, {
          timeout: $scope.currentRequest.promise,
          params: params
        }
        .success (data) ->
          if data.data?
            $scope.data = data.data
          else
            $scope.data = []

          if data.total?
            $scope.first = (params.limit * (params.page - 1)) + 1
            $scope.last = ($scope.first + $scope.data.length) - 1
            $scope.total = data.total
          else
            $scope.total = 0

          $scope.loadPager = false
        .error (data, code) ->
          return false if code is 0

          $scope.data = null
          $scope.loadPager = false
          $scope.errorMsg = [$filter("translate")("CORE.MESSAGES.REQUEST_FAILED")]
  }
]

.directive 'mazeAdditional', ['$filter', ($filter) ->
  {
    restrict: "A"
    scope: {
      fields: "="
      update: "@"
    }
    templateUrl: '/partials/admin/directives/additional.html'
    controller: [ '$scope', '$parse', ($scope, $parse) ->
      $scope.errors   = $scope.fields = $scope.created = {}
      $scope.openNewForm = false;

      $scope.open = () ->
        $scope.openNewForm = true

      $scope.hide = () ->
        $scope.openNewForm = false
        $scope.errors = $scope.created = {}

      keyExists = (key) ->
        found = false
        angular.forEach $scope.fields, (additional) ->
          if additional.label is key
            found = true
        return found

      $scope.create = (key, value) ->
        return false if not key? or not value? or not $scope.update
        $scope.errors = {}

        if $scope.fields? and keyExists key
          $scope.errors.additionalValue = [$filter("translate")("CORE.DIRECTIVES.ADDITIONAL_LABEL_EXIST")]
          return false

        updateData = {
          additionalKey: key,
          additionalValue: value
        }

        $parse($scope.update)($scope.$parent, {$data: updateData}).then () ->
          $scope.hide();

      $scope.updateAdditional = (id, data) ->
        return false if not id or not $scope.update or not $scope.fields

        updateData =
          additionalFields: {}
        updateData.additionalFields[id] = {value: data};

        $parse($scope.update)($scope.$parent, {$data: updateData})

      $scope.keyDown = (event) ->
        if 9 is (event.keyCode || event.which) and event.target.nodeName.toLowerCase() is "textarea"
          startPos = event.target.selectionStart
          endPos   = event.target.selectionEnd
          event.target.value = event.target.value.substring(0, startPos) + "\t" + event.target.value.substring(endPos, event.target.value.length)
          event.target.focus()
          event.target.selectionStart = startPos + "\t".length
          event.target.selectionEnd = startPos + "\t".length
          event.preventDefault()
        else if event.which is 9
          event.preventDefault()

        return(event)
    ]
  }
]
.directive 'mazeAdminSearch', ['$filter', ($filter) ->
  {
    restrict: "A"
    templateUrl: '/partials/admin/directives/adminSearch.html'
    controller: ['$scope', '$http', '$q', '$location', '$filter', ($scope, $http, $q, $location, $filter) ->
      $scope.mazesearch = $location.search().search || ""
      $scope.categories =
        all     : {name: $filter("translate")("CORE.DIRECTIVES.SEARCH_All"), source: "/search"},
        clients : {name: $filter("translate")("CORE.LABELS.CLIENTS"), source: "/clients"},
        domains : {name: $filter("translate")("CORE.LABELS.DOMAINS"), source: "/domains"},
        nodes   : {name: $filter("translate")("CORE.LABELS.NODES"), source: "/nodes"}
      $scope.selection = $scope.categories.all

      $scope.submit = (event) ->
        $scope.search $scope.mazesearch; event.preventDefault() if event.keyCode == 13 && $scope.mazesearch

      $scope.sameOrigin = ->
        if $location.path().indexOf($scope.selection.source) == 0 then true else false

      $scope.search = (search) ->
        return $location.search({}) if not search
        return $location.search({search: search}) if search && $location.path() == $scope.selection.source
        $location.search({search: search}).path $scope.selection.source if search

      $scope.toggle = (category) ->
        $scope.selection = category
        $scope.mazesearch = $scope.mazesearch
        $scope.search $scope.mazesearch
        $location.path $scope.selection.source if not $scope.sameOrigin()

      $scope.dropdownSelection = ->
        for category of $scope.categories
          return $scope.selection = $scope.categories[category] if $location.path().indexOf($scope.categories[category].source) == 0
        $scope.selection = $scope.categories.all

      $scope.$on "$locationChangeSuccess", -> $scope.dropdownSelection()
      $scope.dropdownSelection()
      $scope.$watch "mazesearch", (search) ->
        $scope.search search
    ]
  }
]
