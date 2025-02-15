<div id="blog-grid-component" class="container my-4">
  <div class="row mb-4">
    <div class="col-12 px-0">
    <div class="navbar-filter-hover position-absolute h-100 end-0 z-2"></div>

      <div id="filters-container" class="d-flex overflow-x-scroll pe-5" role="group"></div>
    </div>
  </div>
  <div id="spinner" class="text-center my-5" style="display: none;">
    <!-- Qui puoi sostituire questa scritta con un'animazione CSS/JS a tuo piacimento -->
    <svg class="spinner" width="65px" height="65px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg">
      <circle class="path" fill="none" stroke-width="6" stroke-linecap="round" cx="33" cy="33" r="30"></circle>
    </svg>
  </div>
  <div id="posts-container" class="row g-4"></div>
  <div id="load-more-container" class="text-center mt-5"></div>
</div>



<?php render_blog_grid(8); ?>
