angular.module('huskyhunt', ['ionic', 'ionic.imagetitleview', 'ngSanitize', 'huskyhunt.controllers', 'huskyhunt.services', 'huskyhunt.service.badges', 'huskyhunt.service.scores', 'huskyhunt.service.levels', 'huskyhunt.service.player', 'huskyhunt.service.quiz'])

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

    .state('game.levels', {
      url: '/levels',
      views: {
        'level-board': {
          templateUrl: 'partials/levels.html',
          controller: 'levelsCtrl'
        }
      },
      resolve: {
        levels: function (Levels) {
          return Levels.get();
        }
      }
    })
    .state('game.play-level', {
      url: '/level/:levelId',
      views: {
        'level-board': {
          templateUrl: 'partials/level-main.html',
          controller: 'mainLevelCtrl'
        }
      },
      resolve: {
        level: function (Levels, $stateParams) {
          return Levels.get($stateParams.levelId);
        }
      }
    })
    .state('game.play-level.quiz', {
      url: '/quiz',
      views: {
        'level-board': {
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
