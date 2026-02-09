<?php
/**
 * Template part for displaying the header content
 *
 * @package LogicalTheme
 */
?>

<nav id="navbar-scroll" class="navbar navbar-scroll d-none d-xl-flex transition navbar navbar-expand-xl text-primary fixed-top z-3 w-100 bg-transparent">
<div class="container bg-white rounded-pill py-2 px-3 navbar-scroll-shell is-shadowed">
    <?php get_template_part('template-parts/header/inner-navbar'); ?>

  </div>
</nav>
<button class="btn mobile-offcanvas-toggle rounded-circle d-xl-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasNavbar" aria-controls="offcanvasNavbar" aria-label="<?php esc_attr_e('Apri menu', 'your-theme-textdomain'); ?>">
  <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" fill="currentColor" class="bi bi-list" viewBox="0 0 16 16" aria-hidden="true">
    <path fill-rule="evenodd" d="M2.5 12.5a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5m0-4a.5.5 0 0 1 .5-.5h10a.5.5 0 0 1 0 1H3a.5.5 0 0 1-.5-.5"/>
  </svg>
</button>
