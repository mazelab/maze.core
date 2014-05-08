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