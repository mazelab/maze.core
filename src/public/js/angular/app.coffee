angular.module 'maze', ['ui.bootstrap', 'maze.directives', 'maze.services', 'maze.filters', 'maze.controllers', 'xeditable', 'ngRoute', 'angular-md5', 'pascalprecht.translate']
.run (editableOptions) ->
  editableOptions.theme = 'bs2';

.config ($translateProvider, $routeProvider) ->
  $translateProvider.useStaticFilesLoader [] =
    prefix: "/js/angular/locale/"
    suffix: ".json"
  $translateProvider.preferredLanguage("en_US");

  $routeProvider
  .when '/nodes/',
      templateUrl: '/partials/admin/nodes/list.html'
      controller: 'nodeListController'
      reloadOnSearch: false
  .when '/clients/',
      templateUrl: '/partials/admin/clients/list.html'
      controller: 'clientListController'
      reloadOnSearch: false
  .when '/domains/',
      templateUrl: '/partials/admin/domains/list.html'
      controller: 'domainListController'
      reloadOnSearch: false
  .when '/search/',
      templateUrl: '/partials/admin/search/list.html'
      controller: 'searchListController'
      reloadOnSearch: false

