(function () {
  'use strict';
  var trigger = document.querySelector('.zv-smoke-egg-trigger');
  if (!trigger) { return; }
  var feedback = document.querySelector('.zv-smoke-egg-feedback');
  var queue = Promise.resolve();
  var clicks = 0;
  var feedbackTimer;
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
        if (result.success && result.data.awarded) {
          window.clearTimeout(feedbackTimer);
          feedback.textContent = '+1 kristal · Kjer je dim, je tudi ogenj';
          feedbackTimer = window.setTimeout(function () { feedback.textContent = ''; }, 2500);
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
