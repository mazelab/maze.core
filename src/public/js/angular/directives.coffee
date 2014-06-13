directives = angular.module "maze.directives", []

directives.directive 'mazeDlWrapper', [ () ->
  {
    restrict: 'E'
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

directives.directive 'mazeHtmlPopover', [ () ->
  {
    restrict: 'E'
    scope: {
      label: '@',
      title: '@',
      placement: '@'
    }
    transclude: true,
    template: '<div><a href="" onclick="return false;">{{label || "popover"}}</a></div>'
    link: (scope, element, attrs, ctrl, transclude) ->
      $ element
      .find 'a'
      .popover {
        content: transclude()
        html: true
        trigger: 'click'
        placement: scope.placement
        title: scope.title
      }
  }
]

directives.directive 'mazeAdditional', [ () ->
  {
    restrict: "E"
    scope: {
      fields: "="
    }
    templateUrl: '/partials/admin/directives/additional.html'
    controller: [ '$scope', '$attrs', '$parse', ($scope, $attrs, $parse) ->
      $scope._errors   = $scope._fields = $scope._created = {}
      $scope._activeId = null
      $scope._infotext = angular.element("#additional-infotext")
      $scope._newfield = angular.element("#additional-newfield")

      $scope._open = () ->
        this._infotext.hide()
        this._newfield.show()

      $scope._hide = () ->
        this._infotext.show()
        this._newfield.hide()
        $scope._errors = {}

      $scope._create = () ->
        if $attrs.update and $attrs.fields and $scope.fields
          @model = angular.copy $scope.fields
          @model.additionalKey = @_created.label
          @model.additionalValue = @_created.value

          for id in @model.additionalFields
            if @model.additionalFields[id].label is @_created.label
              $scope._errors.additionalValue = ["this label allready exists"]
              return false

          $parse($attrs.update)($scope.$parent, {$data: this.model}).then () ->
            $scope._hide();
            $scope._created = {};

      $scope._update = (id, data) ->
        if id and $attrs.update and $attrs.fields
          update = { additionalFields: {} }
          update.additionalFields[id] = {value: data}

          $parse($attrs.update)($scope.$parent, {$data: update})

      $scope._keydown = (event) ->
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

directives.directive 'mazeSearch', [ () ->
  {
    restrict: 'E'
    templateUrl: '/partials/admin/directives/search.html'
    transclude: true
    scope: {
      data: '=',
      limit: '@',
      page: '@',
      uri: '@'
    }
    compile: (element, attrs) ->
      attrs.limit = 10 if not attrs.limit?
      attrs.page = 1 if not attrs.page?
    controller: ($scope, $http, $q) ->
      $scope.search = $scope.first = $scope.last = $scope.total = ''
      return false if not $scope.uri

      $scope.$watch 'page + search + limit', ()  ->
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
          $scope.errorMsg = [ 'Request failed!' ]
  }
]