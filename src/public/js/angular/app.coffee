angular.module 'maze', ['ui.bootstrap', 'maze.directives', 'maze.services', 'maze.filters', 'maze.controllers', 'xeditable', 'ngRoute', 'angular-md5', 'pascalprecht.translate']
.run (editableOptions) ->
  editableOptions.theme = 'bs2';

.config ($translateProvider, $routeProvider) ->
  $translateProvider.useStaticFilesLoader [] =
    prefix: "/js/angular/locale/"
    suffix: ".json"
  $translateProvider.preferredLanguage("en_US");

  ##
  # Core Routes
  ##

  $routeProvider

  .when "/",
    templateUrl: "/dashboardadmin",
    controller: "dashboardController"
  .when "/clients",
    templateUrl: "/partials/admin/clients/list.html"
    controller: "clientListController"
    reloadOnSearch: false
  .when "/clients/edit/:clientId",
    templateUrl: "/partials/admin/clients/edit.html"
    controller: "clientEditController"
  .when "/clients/new",
    templateUrl: "/partials/admin/clients/new.html"
    controller: "clientNewController"
  .when "/clients/login/:clientId",
    templateUrl: "/dashboardadmin"
    controller: "clientSwitchToController"
  .when "/nodes",
    templateUrl: "/partials/admin/nodes/list.html"
    controller: "nodeListController"
    reloadOnSearch: false
  .when "/nodes/edit/:nodeId",
    templateUrl: "/partials/admin/nodes/edit.html"
    controller: "nodeEditController"
  .when "/nodes/register/:nodeName",
    templateUrl: "/partials/admin/nodes/register.html"
    controller: "nodeRegisterController"
  .when "/domains",
    templateUrl: "/partials/admin/domains/list.html"
    controller: "domainListController"
    reloadOnSearch: false
  .when "/domains/edit/:domainId",
    templateUrl: "/partials/admin/domains/edit.html"
    controller: "domainEditController"
  .when "/domains/new",
    templateUrl: "/partials/admin/domains/new.html"
    controller: "domainNewController"
  .when "/profile",
    templateUrl: "/profile"
    controller: "profileController"
  .when "/profile/access",
    templateUrl: "/profile/access"
    controller: "profileAccessController"
  .when "/system",
    templateUrl: "/system"
    controller: "systemController"
  .when "/system/admin/new",
    templateUrl: "/system/addadmin"
    controller: "systemAddAdminController"
  .when "/news",
    templateUrl: "/news"
    controller: "newsListController"
  .when "/news/edit/:id/:title",
    templateUrl: (param) -> "/news/detail/#{param.id}"
    controller: "newsEditController"
  .when "/news/add",
    templateUrl: "/news/add"
    controller: "newsAddController"
  .when "/modules",
    templateUrl: "/module/"
    controller: "moduleListController"
  .when "/modules/detail/:moduleName",
    templateUrl: (param) -> "/modules/detail/#{param.moduleName}"
    controller: "moduleDetailController"
  .when "/search",
    templateUrl: "/partials/admin/search/list.html"
    controller: "searchListController"
    reloadOnSearch: false
