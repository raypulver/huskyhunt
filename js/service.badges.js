angular.module('huskyhunt.service.badges', []).factory('Badges', function () {
  var achievements = [
    {
      index: 0,
      name: 'Tier 1',
      unearnedDescription: 'Score 100 points to earn this badge.',
      earnedDescription: 'Scored over 100 points.',
      image: 'images/badges/100pts.png'
    },
    {
      index: 1,
      name: 'Scavenger',
      unearnedDescription: 'Answer 7 scavenger hunt questions to earn this badge.',
      earnedDescription: 'Answered 7 scavenger hunt questions.',
      image: 'images/badges/scavenger.png'
    },
    {
      index: 2,
      name: 'Security Expert',
      image: 'images/badges/security.png',
      unearnedDescription: 'Answer 7 internet security questions to earn this badge.',
      earnedDescription: 'Answered 7 internet security questions.'
    },
    {
      index: 3,
      name: 'Husky Hunter',
      image: 'images/badges/top25.png',
      unearnedDescription: 'Successfully rank #25 or better on the scoreboard to become a Husky Hunter.',
      earnedDescription: 'Ranked in the Top 25.'
    },
    {
      index: 4,
      name: 'Husky Champion',
      image: 'images/badges/top10.png',
      unearnedDescription: 'Place in the Top Ten to earn the Husky Champion badge.',
      earnedDescription: 'You\'re the best, and people know it!'
    }
  ];
  return {
   // This function takes a spcae delimited list of achievement names and returns an array of those achievements.
    getByName: function (str) {
      var i = 0, ret = [];
      var split = str.split(' ');
      while (i < achievements.length) {
        angular.forEach(split, function (v) {
          if (achievements[i] == v.name && ret.indexOf(v.name) == -1) {
            ret.push(v);
            if (ret.length == split.length) {
              return ret;
            }
          }
        });
      }
      return ret;
    },
    getByIndex: function (index) {
      return achievements[index];
    },
    getByArray: function (arr) {
      var ret = [];
      angular.forEach(arr, function (v) {
        ret.push(achievements[v]);
      });
      return ret;
    },
    getByArrayInverse: function (arr) {
      var ret = [];
      angular.forEach(achievements, function (u) {
        var tagged = false;
        angular.forEach(arr, function (v) {
          if (achievements.indexOf(u) == v) {
            console.log('This never gets called');
            tagged = true;
          }
        });
        if (!tagged) {
          console.log(u.index + ' passes.');
          ret.push(u);
        }
      });
      return ret;
    }
  }
});          
