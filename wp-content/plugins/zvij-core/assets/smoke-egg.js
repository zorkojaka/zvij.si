(function () {
  'use strict';
  var trigger = document.querySelector('.zv-smoke-egg-trigger');
  if (!trigger) { return; }
  var feedback = document.querySelector('.zv-smoke-egg-feedback');
  var queue = Promise.resolve();
  var clicks = 0;
  var feedbackTimer;
  function showReward(progress) {
    feedback.replaceChildren();
    var count = document.createElement('strong');
    count.textContent = '+' + progress + '/10';
    var gem = document.createElement('span');
    gem.className = 'zv-credit-reward__icon';
    gem.setAttribute('aria-hidden', 'true');
    feedback.append(count, gem);
    if (progress === 10) {
      var complete = document.createElement('span');
      complete.textContent = 'Zdej si se pa že uženu, dost maš... 😈';
      feedback.appendChild(complete);
      feedback.style.flexWrap = 'wrap';
      complete.style.flexBasis = '100%';
    }
    feedback.setAttribute('aria-label', 'Pridobil si 1 kristal. Napredek ' + progress + ' od 10.');
    window.clearTimeout(feedbackTimer);
    feedbackTimer = window.setTimeout(function () { feedback.replaceChildren(); feedback.removeAttribute('aria-label'); }, progress === 10 ? 8000 : 2300);
    var target = document.querySelector('.site-cart');
    var source = trigger.getBoundingClientRect();
    if (!target || window.matchMedia('(prefers-reduced-motion: reduce)').matches) { return; }
    var end = target.getBoundingClientRect();
    var crystal = document.createElement('span');
    crystal.className = 'zv-credit-reward__icon zv-egg-flying-crystal';
    crystal.setAttribute('aria-hidden', 'true');
    crystal.style.left = (source.left + source.width / 2 - 8) + 'px';
    crystal.style.top = (source.top - 8) + 'px';
    document.body.appendChild(crystal);
    var dx = end.left + end.width / 2 - source.left - source.width / 2;
    var dy = end.top + end.height / 2 - source.top;
    var flight = crystal.animate([
      {transform: 'translate(0, 0) scale(1)', opacity: 0},
      {transform: 'translate(-15px, -35px) scale(1.3)', opacity: 1, offset: 0.12},
      {transform: 'translate(' + (dx - 40) + 'px, ' + (dy * 0.55) + 'px) scale(1)', opacity: 1, offset: 0.6},
      {transform: 'translate(' + dx + 'px, ' + dy + 'px) scale(.3)', opacity: 0.2}
    ], {duration: 1150, easing: 'ease-in-out'});
    flight.finished.then(function () {
      crystal.remove();
      target.animate([{transform: 'scale(1)'}, {transform: 'scale(1.15)'}, {transform: 'scale(1)'}], {duration: 280});
    }).catch(function () { crystal.remove(); });
    window.setTimeout(function () { crystal.remove(); }, 1600);
  }
  trigger.addEventListener('click', function () {
    var logo = trigger.parentElement.querySelector('img').getBoundingClientRect();
    var ring = document.createElement('span');
    ring.className = 'zv-smoke-ring';
    ring.setAttribute('aria-hidden', 'true');
    ring.style.left = (logo.left + logo.width * 0.52) + 'px';
    ring.style.top = (logo.top + logo.height * 0.34) + 'px';
    ring.style.setProperty('--rise', '-' + (logo.top + 140) + 'px');
    document.body.appendChild(ring);
    window.setTimeout(function () { ring.remove(); }, 4100);
    if (!window.zvijSmokeEgg || !zvijSmokeEgg.loggedIn) { return; }
    clicks += 1;
    if (clicks > 10) { return; }
    queue = queue.then(function () {
      return fetch(zvijSmokeEgg.url, {
        method: 'POST', credentials: 'same-origin',
        body: new URLSearchParams({action: 'zvij_smoke_egg', nonce: zvijSmokeEgg.nonce})
      }).then(function (response) { return response.json(); }).then(function (result) {
        if (result.success && result.data.progress > 0) {
          var progress = document.querySelector('[data-smoke-quest-progress]');
          var reward = document.querySelector('[data-smoke-quest-reward]');
          var title = document.getElementById('zv-secret-quest-title');
          if (progress) { progress.textContent = result.data.progress + '/10'; }
          if (title) { title.textContent = 'Kjer je dim, je tudi ogenj'; }
          if (reward) { reward.textContent = result.data.progress === 10 ? 'Zdej si se pa že uženu, dost maš... 😈' : '+1 kristal za vsak odkrit klik'; }
        }
        if (result.success && result.data.awarded) {
          showReward(result.data.progress);
          var balance = document.querySelector('.site-member-balance');
          if (balance) {
            balance.textContent = result.data.balance_label;
            balance.setAttribute('aria-label', 'Prijavljen član. ' + result.data.balance_label + '. Odpri moj račun.');
          }
        }
      }).catch(function () { clicks -= 1; });
    });
  });
})();
