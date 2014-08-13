angular.module 'maze', ['ui.bootstrap', 'maze.directives', 'maze.services', 'maze.filters', 'maze.controllers', 'xeditable', 'ngRoute', 'angular-md5', 'pascalprecht.translate']
.run (editableOptions) ->
  editableOptions.theme = 'bs2';
.config ($translateProvider) ->
  $translateProvider.useStaticFilesLoader [] =
    prefix: "js/angular/locale/"
    suffix: ".json"
