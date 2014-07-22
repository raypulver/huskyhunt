angular.module('huskyhunt', ['ionic', 'ionic.imagetitleview', 'ngSanitize', 'huskyhunt.controllers', 'huskyhunt.services', 'huskyhunt.service.badges', 'huskyhunt.service.scores', 'huskyhunt.service.modules', 'huskyhunt.service.player', 'huskyhunt.service.quiz'])

.config(function($locationProvider, $stateProvider, $urlRouterProvider) {
  $stateProvider

    // setup an abstract state for the tabs directive
    .state('game', {
      url: "/game",
      abstract: true,
      templateUrl: "partials/index.html",
      controller: 'masterCtrl'
    })

    // Each tab has its own nav history stack:

    .state('game.status', {
      url: '/status',
      views: {
        'game-status': {
          templateUrl: 'partials/status.html',
          controller: 'statusCtrl'
        }
      },
      resolve: {
        player: function (Player) {
          return Player.getStatus();
        }
      }
    })

    .state('game.badges', {
      url: '/badges',
      views: {
        'game-status': {
          templateUrl: 'partials/badges.html',
          controller: 'badgesCtrl'
        }
      },
      resolve: {
        badges: function (Badges) {
          return Badges.get();
        }
      }
    })

    .state('game.modules', {
      url: '/modules',
      views: {
        'module-board': {
          templateUrl: 'partials/modules.html',
          controller: 'modulesCtrl'
        }
      },
      resolve: {
        modules: function (Modules) {
          return Modules.get();
        }
      }
    })
    .state('game.play-module', {
      url: '/module/:moduleId',
      views: {
        'module-board': {
          templateUrl: 'partials/module-main.html',
          controller: 'mainModuleCtrl'
        }
      },
      resolve: {
        module: function (Modules, $stateParams) {
          return Modules.get($stateParams.moduleId);
        }
      }
    })
    .state('game.play-module.quiz', {
      url: '/quiz',
      views: {
        'module-board': {
          templateUrl: 'partials/quiz.html',
          controller: 'quizCtrl'
        }
      }
    })
    .state('game.scores', {
      url: '/scores',
      views: {
        'scoreboard': {
          templateUrl: 'partials/scoreboard.html',
          controller: 'scoresCtrl'
        }
      },
      resolve: {
        scores: function (Scores) {
          return Scores.get();
        }
      }
    })

  // if none of the above states are matched, use this as the fallback
  $urlRouterProvider.otherwise('/game/status');

});
