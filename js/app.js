angular.module('huskyhunt', ['ionic', 'LocalStorageModule', 'ionic.imagetitleview', 'ngSanitize', 'huskyhunt.controllers', 'huskyhunt.services', 'huskyhunt.service.badges', 'huskyhunt.service.scores', 'huskyhunt.service.levels', 'huskyhunt.service.player', 'huskyhunt.service.quiz', 'huskyhunt.service.auth'])

.config(function($locationProvider, $stateProvider, $urlRouterProvider, $httpProvider) {
  $httpProvider.interceptors.push('AuthInterceptor'); 
  $stateProvider
    .state('auth', {
      url: '/auth',
      templateUrl: 'partials/auth.html',
      abstract: true,
      data: {
        access: 0
      }
    })
    .state('auth.login', {
      url: '/login',
      views: {
        'action': {
          templateUrl: 'partials/login.html',
          controller: 'loginCtrl'
        }
      },
      data: {
        access: 0
      }
    })
    .state('auth.register', {
      url: '/register',
      views: {
        'action': {
          templateUrl: 'partials/register.html',
          controller: 'registerCtrl'
        }
      },
      data: {
        access: 0
      }
    })
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
      },
      data: {
        access: 1
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
      },
      data: {
        access: 1
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
      },
      data: {
        access: 1
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
      },
      data: {
        access: 1
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
      },
      data: {
        access: 1
      }
    })

  // if none of the above states are matched, use this as the fallback
  $urlRouterProvider.otherwise('/auth/login');

});
