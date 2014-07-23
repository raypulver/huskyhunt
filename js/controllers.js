angular.module('huskyhunt.controllers', []).filter('strip', function () {
  return function(text) {
    var step1 = String(text).replace(/<[^>]+>/gm, '');
    var step2 = step1.replace(/&nbsp;/g, ' ');
    var step3 = step2.replace(/\&mdash;/g, '-');
    var step4 = step3.replace(/\&ndash;/g, '-');
    return step4.replace(/&rsquo;/g, '\''); 
  }
})
.controller('masterCtrl', function ($scope, $ionicLoading) {
  $scope.$on('$stateChangeStart', function (event, toState, toParams, fromState, fromParams) {
    if (toState.resolve) {
      $scope.loadingIndicator = $ionicLoading.show({
        content: 'Loading...'
      });
    }
  });
  $scope.$on('$stateChangeSuccess', function (event, toState, toParams, fromState, fromParams) {
    if (toState.resolve) {
      $scope.loadingIndicator.hide();
    }
  });
})
.controller('statusCtrl', function($scope, player, Badges, $state) {
  var user = player.data;
  $scope.netid = user.netid;
  $scope.score = user.score;
  $scope.badges = user.badges;
  $scope.goToBadges = function () {
    $state.go('game.badges');
  }
})

.controller('badgesCtrl', function ($scope, badges) {
  $scope.badges = badges.data;
})

.controller('levelsCtrl', function($scope, levels) {
  $scope.levels = levels.data;
})
.controller('mainLevelCtrl', function($scope, $stateParams, $ionicModal, $timeout, $ionicPopup, level, Quiz) {
  $scope.level = level.data;
  var wasLastQuestion = function (index) {
    return index == $scope.level.questions.length - 1;
  }
  $scope.selectedQuestion = 0;
  $scope.choices = {};
  $ionicModal.fromTemplateUrl('partials/modal/quiz.html', {
    scope: $scope,
    animation: 'slide-in-up'
  }).then(function (modal) {
    $scope.quizModal = modal;
  });
  $ionicModal.fromTemplateUrl('partials/modal/share.html', {
    scope: $scope,
    animation: 'slide-in-up'
  }).then(function (modal) {
    $scope.shareModal = modal;
  });
//  $scope.$on('$destroy', function () {
//    $scope.quizModal.remove();
//  });
  $scope.playLevel = function () {
    this.quizModal.show();
  }
  $scope.hideQuiz = function () {
    this.quizModal.hide();
  }
  $scope.done = function () {
    this.quizModal.remove()
    this.shareModal.remove();
    window.history.back();
  }
  $scope.attemptQuestion = function (answer) {
    Quiz.attempt($scope.level.questions[$scope.selectedQuestion].id, answer).then(function (res) {
      $scope.quizModal.hide();
      var showShare = false;
      if (res.data.winner) {
        $timeout(function () {
          $scope.level.questions[$scope.selectedQuestion].answers = [];
        }, 100);
        $timeout(function () {
          if (!wasLastQuestion($scope.selectedQuestion)) {
            $scope.selectedQuestion++;
          } else {
            showShare = true;
          }
        }, 200);
        $timeout(function () {
          if (showShare) {
            $scope.shareModal.show();
          } else {
            $scope.quizModal.show();
          }
        }, 500);
      } else {
        $timeout(function () {
          $ionicPopup.alert({
            title: res.data.feedback
          }).then(function (res) {
            $scope.quizModal.show();
          });
        }, 200);
      }
    });
  }
}).controller('quizCtrl', function ($scope, level) {
  $scope.submit = function () {
    var choice = $scope.choice + 1;
    var result = Answer.attempt($stateParams.levelId, choice)
    if (result < 0) {
      $ionicPopup.alert({
        title: 'Wrong! -10 points.'
      });
    } else {
      $ionicPopup.alert({
        title: 'Correct! +50 points.'
      }).then(function() { window.history.back() });
    }
    console.log("$scope.choice = " + $scope.choice);
  }
  $scope.level = level.data;
})
.controller('scoresCtrl', function($scope, scores) {
    $scope.scores = scores.data;
});
