var directives = angular.module("maze.directives", []);

/**
 * wraps content into dt/dl structure with label
 */
directives.directive('mazeDlWrapper', function() {
    return {
        restrict: 'E',
        scope: {
            'label': '@',
            'dlClass' : '@',
            'dtClass' : '@',
            'ddClass': '@'
        },
        transclude: true,
        template: '<dl class="{{dlClass}}">'+
            '<dt ng-if="label" class="{{dtClass}}">' +
            '<label>' +
            '{{label}}' +
            '</label>' +
            '</dt>' +
            '<dd ng-transclude class="{{ddClass}}"></dd>' +
            '</dl>'
    }
});
directives.directive('mazeAdditional', function() {
    return {
        restrict: "E",
        scope: {
            fields: "="
        },
        template: '<div ng-repeat="(id, field) in fields.additionalFields" class="row-fluid" id="additional-{{id}}">' +
                    '<dt class="span3"><label>{{field.label}}</label></dt>' +
                    '<dd class="span5 cssEditable">' +
                        '<span name="{{field.label}}" editable-textarea="field.value" onbeforesave="_update(id, $data);" e-ng-keydown="_keydown($event);" id="{{id}}" ng-click="_hide();" class="jsEditableAdditionalFields">{{field.value || "empty"}}</span>' +
                    '</dd>' +
                 '</div>' +
                 '<div class="row-fluid"><a id="additional-infotext" class="muted span12" ng-click="_open();">Add Info</a></div>' +
                 '<div id="additional-newfield" style="display:none;" class="cssAddItem">' +
                       '<div class="span3"><input type="text" placeholder="label" ng-model="_created.label;" class="span12"></div>' +
                       '<div class="span5">' +
                           '<textarea ng-model="_created.value;" placeholder="text" ng-keydown="_keydown($event);"></textarea>' +
                           '<button value="cancel" type="button" ng-click="_hide();" class="jsButton jsAdditionalCancel btn btn-info btn-mini"><i class="icon-remove icon-white"></i></button>' +
                           '<button value="save" type="submit" ng-click="_create();" class="jsButton jsAdditionalSave btn btn-info btn-mini"><i class="icon-ok icon-white"></i></button>' +
                           '<alert ng-repeat="alert in _errors.additionalValue" class="alert-danger" >{{alert}}</alert>' +
                       '</div>' +
                 '</div>',
        controller: ['$scope', '$attrs', '$parse', function($scope, $attrs, $parse){
            $scope._errors   = $scope._fields = $scope._created = {};
            $scope._activeId = null;
            $scope._infotext = angular.element("#additional-infotext");
            $scope._newfield = angular.element("#additional-newfield");

            /**
             * opens the container for a new field
             *
             * @private
             */
            $scope._open = function(){
                this._infotext.hide();
                this._newfield.show();
            };

            /**
             * closes the new container
             *
             * @private
             */
            $scope._hide = function(){
                this._infotext.show();
                this._newfield.hide();
                $scope._errors   = {};
            };

            /**
             * creates a new additional field
             *
             * @private
             */
            $scope._create = function(){
                if ($attrs.update && $attrs.fields && $scope.fields){
                    this.model = angular.copy($scope.fields);
                    this.model.additionalKey   = this._created.label;
                    this.model.additionalValue = this._created.value;

                    for (var id in this.model.additionalFields) {
                        if (this.model.additionalFields[id].label === this._created.label) {
                            $scope._errors.additionalValue = ["this label allready exists"];
                            return false;
                        }
                    }

                    $parse($attrs.update)($scope.$parent, {$data: this.model}).then(function(response){
                        $scope.response = response.data;
                        if (response.status === 202 || response.status === 204) {
                            $scope._hide();
                            $scope._created = {};
                        }
                    });
                }
            };

            /**
             * update an existing additional field
             *
             * @private
             * @param {string} id
             * @param {string} data
             */
            $scope._update = function(id, data){
                if (id && $attrs.update && $attrs.fields) {
                    this.model = angular.copy($scope.fields);
                    this.model.additionalFields[id].value = data;

                    return($parse($attrs.update)($scope.$parent, {$data: this.model}));
                }
            };

            /**
             * handle the keydown event
             *
             * @private
             * @param {Event} event
             */
            $scope._keydown = function(event) {
                // custom behavior on tab press
                if (9 === (event.keyCode || event.which) && event.target.nodeName.toLowerCase() === "textarea") {
                    var startPos = event.target.selectionStart;
                    var endPos   = event.target.selectionEnd;
                    event.target.value = event.target.value.substring(0, startPos) + "\t" + event.target.value.substring(endPos, event.target.value.length);
                    event.target.focus();
                    event.target.selectionStart = startPos + "\t".length;
                    event.target.selectionEnd = startPos + "\t".length;
                    event.preventDefault();
                }else if (event.which === 9) {
                    event.preventDefault();
                    // $.fn.mazeEditable.initTabKey(this);
                }
                return(event);
            };
        }]
    };
});
directives.directive('mazeSearch', function() {
    return {
        restrict: 'E',
        templateUrl: '/partials/admin/search.html',
        transclude: true,
        scope: {
            data: '=',
            limit: '@',
            page: '@',
            uri: '@'
        },
        compile: function(element, attrs){
            // set default values for pagination component
            if (!attrs.limit) { attrs.limit = 10; }
            if (!attrs.page) { attrs.page = 1; }
        },
        controller: function($scope, $http, $q) {
            $scope.search = $scope.first = $scope.last = $scope.total = '';
            if(!$scope.uri) {
                return false;
            }

            $scope.$watch('page + search + limit', function() {
                $scope.loadPager = true;
                $scope.errorMsg = [];

                var params = {
                    search: $scope.search || '',
                    page: $scope.page || 1,
                    limit: $scope.limit || 10
                }

                // cancel current request
                if($scope.currentRequest) {
                    $scope.currentRequest.resolve();
                }

                $scope.currentRequest = $q.defer();
                $http.get($scope.uri, {
                    timeout: $scope.currentRequest.promise,
                    params: params
                }).success(function(data) {
                    if(data.data) {
                        $scope.data = data.data;
                    } else {
                        $scope.data = [];
                    }

                    if(data.total) {
                        $scope.first = (params.limit * (params.page - 1)) + 1;
                        $scope.last = ($scope.first + $scope.data.length) - 1;
                        $scope.total = data.total;
                    } else {
                        $scope.total = 0;
                    }

                    $scope.loadPager = false;
                }).error(function(data, code) {
                    // ignore cancels
                    if(code === 0) {
                        return false;
                    }

                    $scope.data = null;
                    $scope.loadPager = false;

                    $scope.errorMsg = [ 'Request failed!' ];
                });

            });

        }
    }
});
