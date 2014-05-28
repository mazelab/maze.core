angular.module('maze', ['ui.bootstrap', 'maze.directives', 'maze.services', 'maze.filters', 'maze.controllers', 'xeditable', 'angular-md5']).
run(function(editableOptions) {
    editableOptions.theme = 'bs2'; // bootstrap3 theme. Can be also 'bs2', 'default'
});