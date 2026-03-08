export function initPatternHooks() {
  const patternRoots = document.querySelectorAll('[data-pattern]');

  if (!patternRoots.length) {
    return;
  }

  patternRoots.forEach((root) => {
    root.dataset.patternReady = 'true';
  });
}
