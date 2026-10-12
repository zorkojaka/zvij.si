(() => {
  const root = document.querySelector('.zv-invite');
  if (!root) return;
  const field = root.querySelector('#zv-invite-link');
  const feedback = root.querySelector('.zv-invite__feedback');
  root.querySelector('[data-invite-copy]').addEventListener('click', async () => {
    try {
      await navigator.clipboard.writeText(field.value);
      feedback.textContent = 'Povezava je kopirana. Pošlji jo prijatelju.';
    } catch {
      field.focus();
      field.select();
      feedback.textContent = 'Povezava je označena. Kopiraj jo in pošlji prijatelju.';
    }
  });
  const share = root.querySelector('[data-invite-share]');
  if (navigator.share) {
    share.hidden = false;
    share.addEventListener('click', async () => {
      try {
        await navigator.share({ title: 'Zvijače za zvijače.', text: 'Z nakupi nabiraj kristale in naslednjič plačaj manj.', url: share.dataset.url });
      } catch (error) {
        if (error.name !== 'AbortError') feedback.textContent = 'Povabilo lahko pošlješ tudi s kopirano povezavo.';
      }
    });
  }
})();
