var directives = angular.module("mazeDirectives", []);

/**
 * wraps content into dt/dl structure with label
 */
directives.directive('mazedlwrapper', function() {
    return {
        restrict: 'E',
        scope: {
            'label': '@',
            'dtClass' : '@',
            'ddClass': '@'
        },
        transclude: true,
        template: '<dl class="row-fluid">'+
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
        restrict: "AEM",
        scope: {
            fields: "="
        },
        template: '<div ng-repeat="(id, field) in fields.additionalFields" class="row-fluid" id="additional-{{id}}">' +
                    '<dt class="span3"><label>{{field.label}}</label></dt>' +
                    '<dd class="span5 cssEditable">' +
                        '<span name="{{field.label}}" editable-textarea="field.value" onbeforesave="_update($data);" e-ng-keydown="_keydown($event);" id="{{id}}" ng-click="_setId($event);_hide();" class="jsEditableAdditionalFields">{{field.value || "empty"}}</span>' +
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
            $scope._errors   = {};
            $scope._fields   = {};
            $scope._created  = {};
            $scope._activeId = null;
            $scope._infotext = angular.element("#additional-infotext");
            $scope._newfield = angular.element("#additional-newfield");

            /**
             * saves the hash (id) of opened additional field
             *
             * @private
             * @param {Event} event
             */
            $scope._setId = function(event){
                this.activeId = event.target.id;
            };

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
                        if (response.status === 200) {
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
             * @param {string} data
             */
            $scope._update = function(data){
                if (this.activeId && $attrs.update && $attrs.fields) {
                    this.model = angular.copy($scope.fields);
                    this.model.additionalFields[this.activeId].value = data;

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
