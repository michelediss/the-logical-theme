export function initMenuReveal() {
  const navigation = document.querySelector('.wp-block-navigation');

  if (!navigation) {
    return;
  }

  const toggle = navigation.querySelector('button[aria-label], .wp-block-navigation__responsive-container-open');

  if (!toggle) {
    return;
  }

  toggle.addEventListener('click', () => {
    document.documentElement.classList.toggle('menu-is-open');
  });
}
