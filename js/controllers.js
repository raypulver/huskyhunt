angular.module('huskyhunt.controllers', []).filter('strip', function () {
  return function(text) {
    var step1 = String(text).replace(/<[^>]+>/gm, '');
    var step2 = step1.replace(/&nbsp;/g, ' ');
    var step3 = step2.replace(/\&mdash;/g, '-');
    return step3.replace(/&rsquo;/g, '\''); 
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
.controller('statusCtrl', function($scope, Player, Badges, $state) {
  $scope.netid = Player.getNetID();
  $scope.score = Player.getScore();
  $scope.badges = Player.getBadges();
  $scope.goToBadges = function () {
    $state.go('game.badges');
  }
})

.controller('badgesCtrl', function ($scope, Player, Badges) {
  var badges = Player.getBadges();
  $scope.earned = Badges.getByArray(badges);
  $scope.unearned = Badges.getByArrayInverse(badges);
})

.controller('modulesCtrl', function($scope, modules) {
  $scope.modules = modules.data;
})
.controller('mainModuleCtrl', function($scope, $stateParams, $ionicModal, $timeout, $ionicPopup, module, Quiz) {
  $scope.module = module.data;
  var wasLastQuestion = function (index) {
    return index == $scope.module.questions.length - 1;
  }
/*
  $ionicModal.fromTemplateUrl('partials/result-modal.html', {
    scope: $scope,
    animation: 'slide-in-up',
    hardwareBackButtonClose: true,
    backdropClickToClose: true
  }).then(function (modal) {
    $scope.modal = modal;
  });
  $ionicModal.fromTemplateUrl('partials/correct-answer-modal.html', {
    scope: $scope,
    animation: 'slide-in-up',
    hardwareBackButtonClose: true,
    backdropClickToClose: true
  }).then(function (modal) {
    $scope.correctAnswerModal = modal;
  });
  $ionicModal.fromTemplateUrl('partials/wrong-answer-modal.html', {
    scope: $scope,
    animation: 'slide-in-up',
    hardwareBackButtonClose: true,
    backdropClickToClose: true
  }).then(function (modal) {
    $scope.wrongAnswerModal = modal;
  });
  $scope.modal = null; 
  $scope.$on('$destroy', function () {
    $scope.modal.remove();
  }); */
  $scope.selectedQuestion = 0;
  $scope.maxQuestion = $scope.module.question
  $ionicModal.fromTemplateUrl('partials/modal/quiz.html', {
    scope: $scope,
    animation: 'slide-in-up'
  }).then(function (modal) {
    $scope.quizModal = modal;
  });
//  $scope.$on('$destroy', function () {
//    $scope.quizModal.remove();
//  });
  $scope.playModule = function () {
    this.quizModal.show();
  }
  $scope.hideQuiz = function () {
    this.quizModal.hide();
  }
  $scope.attemptQuestion = function (answer) {
    Quiz.attempt($scope.module.questions[$scope.selectedQuestion].id, answer).then(function (res) {
      if (res.data.winner) {
        $scope.quizModal.hide();
        $timeout(function () {
          if (wasLastQuestion($scope.selectedQuestion)) {
            $ionicPopup.alert({
              title: 'Module Complete!',
              templateUrl: '<h2> Share! </h2><h4> Share what you have learned on the social network of your choice.</h4><h4> If you just scanned a QR code you have already earned the points for this module! </h4><hr><div class="row" style="text-align: center;"><blockquote>{{module.insight}}</blockquote><div class="col-md-5 col-md-offset-1"><img src="images/social_twitter_square.png"></div><div class="col-md-5 col-md-offset-1"><img src="images/social_facebook_square.png"></div></div><small>* Sharing is <em>not</em> required to be eligible for the grand prize.</small>'
            }).then(function (res) {
              window.history.back();
            });
          } else {
          $scope.selectedQuestion++;
          $scope.quizModal.show()
          }
        }, 500);
      }
    });
  }
}).controller('quizCtrl', function ($scope, module) {
  $scope.submit = function () {
    var choice = $scope.choice + 1;
    var result = Answer.attempt($stateParams.moduleId, choice)
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
  $scope.module = module.data;
})
.controller('scoresCtrl', function($scope, scores) {
    $scope.scores = scores.data;
});
