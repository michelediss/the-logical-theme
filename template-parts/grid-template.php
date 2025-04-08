<?php
$query = get_query_var('post_grid_query');
$args = get_query_var('post_grid_args');
?>

<div class="container my-4">

  <nav class="mb-4">
    <ul class="nav nav-pills justify-content-start">
      <li class="nav-item">
        <a class="nav-link border-primary border border-1 rounded-pill text-primary paragraph text-base me-3 <?php echo is_home() ? 'active bg-primary text-white' : ''; ?>" href="<?php echo home_url(); ?>/blog">Tutte</a>
      </li>
      <?php
      $categories = get_categories(['hide_empty' => 1]);
      foreach ($categories as $cat):
        ?>
        <li class="nav-item">
          <a class="nav-link border-primary border border-1 rounded-pill text-primary paragraph text-base me-3 <?php echo is_category($cat->term_id) ? 'active bg-primary text-white' : ''; ?>"
            href="<?php echo esc_url(get_category_link($cat->term_id)); ?>">
            <?php echo esc_html($cat->name); ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </nav>

  <?php if ($query->have_posts()): ?>
    <div class="row">
      <?php while ($query->have_posts()):
        $query->the_post(); ?>
        <div class="col-12 col-md-6 col-lg-3 mb-4">
          <div class="card h-100 border-0 shadow">
            <?php if (has_post_thumbnail()): ?>
              <a href="<?php the_permalink(); ?>">
                <?php the_post_thumbnail('medium', ['class' => 'card-img-top']); ?>
              </a>
            <?php endif; ?>

            <div class="card-body d-flex flex-wrap">
              <h5 class="card-title heading text-black text-xl">
                <a href="<?php the_permalink(); ?>" class="text-decoration-none text-dark">
                  <?php the_title(); ?>
                </a>
              </h5>
              <a href="<?php the_permalink(); ?>"
                class="btn rounded-pill px-4 bg-primary text-white align-self-end mt-3">Leggi</a>
            </div>

          </div>
        </div>
      <?php endwhile; ?>
    </div>
  <?php else: ?>
    <p>Nessun post trovato.</p>
  <?php endif; ?>
</div>

<?php if ($args['pagination']): ?>
  <nav aria-label="Pagination">
    <?php
    $pages = paginate_links([
      'total' => $query->max_num_pages,
      'prev_text' => '&laquo;',
      'next_text' => '&raquo;',
      'type' => 'array',
    ]);

    if (is_array($pages)): ?>
      <ul class="pagination justify-content-center mb-4">
        <?php foreach ($pages as $page):
          $active = strpos($page, 'current') !== false ? ' active' : '';
          // Replace WP “page-numbers” class with Bootstrap’s “page-link”
          $link = str_replace('page-numbers', 'page-link', $page);
          ?>
          <li class="paragraph text-primary page-item<?php echo $active; ?>">
            <?php echo $link; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    <?php endif; ?>
  </nav>
<?php endif; ?>