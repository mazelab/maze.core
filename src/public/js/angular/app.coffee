angular.module 'maze', ['ui.bootstrap', 'maze.directives', 'maze.services', 'maze.filters', 'maze.controllers', 'xeditable', 'ngRoute', 'angular-md5']
.run (editableOptions) ->
  editableOptions.theme = 'bs2';