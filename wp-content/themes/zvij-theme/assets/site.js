(() => {
  const header = document.querySelector('.site-header');
  if (header) {
    const updateOffset = () => {
      const top = parseFloat(getComputedStyle(header).top) || 0;
      document.documentElement.style.setProperty('--zvij-sticky-offset', `${header.offsetHeight + top + 16}px`);
    };
    updateOffset();
    if (window.ResizeObserver) new ResizeObserver(updateOffset).observe(header);
    window.addEventListener('resize', updateOffset);
  }
  const excluded = 'script, style, textarea, input, [contenteditable]';
  function normalize(node) {
    if (node.nodeType === Node.TEXT_NODE) {
      if (!node.parentElement || node.parentElement.closest(excluded)) return;
      const next = node.nodeValue.replace(/[\u2013\u2014]/g, '-');
      if (next !== node.nodeValue) node.nodeValue = next;
      return;
    }
    if (node.nodeType !== Node.ELEMENT_NODE || node.closest(excluded)) return;
    const walker = document.createTreeWalker(node, NodeFilter.SHOW_TEXT);
    while (walker.nextNode()) normalize(walker.currentNode);
  }
  // Also handle copy inserted by variation selectors and AJAX notices.
  normalize(document.body);
  new MutationObserver(records => {
    for (const record of records) {
      if (record.type === 'characterData') normalize(record.target);
      else record.addedNodes.forEach(normalize);
    }
  }).observe(document.body, { childList: true, characterData: true, subtree: true });
})();
