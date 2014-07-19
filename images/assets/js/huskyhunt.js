angular.module("huskyhunt", ['ngRoute', 'ngTouch', 'ngAnimate', 'mobile-angular-ui']).config(function ($locationProvider, $routeProvider) {
  $locationProvider.html5Mode(false).hashPrefix('!');
  $routeProvider.when('/', { templateUrl: "partials/splash.html" });
  $routeProvider.when('/module/:moduleNum', {
    templateUrl: "partials/single-module.html",
    controller: "moduleCtrl"
  });
}).controller("mainCtrl", function ($rootScope, $scope) {
    $rootScope.$on("$routeChangeStart", function () {
      $rootScope.loading = true;
    });
    $rootScope.$on("$routeChangeSuccess", function () {
      $rootScope.loading = false;
    });
    $scope.modules = [ { name: 'Module 1' }, { name: 'Another Module' }, { name: 'Module 2' }, { name: 'Why wasn\'t the last one 3?' } ];
}).controller("moduleCtrl", function ($scope, $http) {
  $scope.data = 5;
});

