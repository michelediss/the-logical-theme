<?php get_header(); ?>

<!-- Sezione 1: Intestazione con immagine a tutto schermo e overlay -->
<section class="position-relative d-flex align-items-center justify-content-center" style="height: 60vh;">
  <img src="https://picsum.photos/1920/1080?random=1" alt="Hero Image" class="w-100 h-100 object-fit-cover">
  <div class="overlay position-absolute top-0 start-0 w-100 h-100" style="background: rgba(0, 0, 0, 0.65);">
  <div class="container position-relative d-flex justify-content-start align-items-end h-100 pb-4">
    <h1 class="heading text-5xl text-light"><?php the_title(); ?></h1>
  </div>
</section>

<!-- Paragraph Section + Image (2 Columns) -->
<?php echo spacer(3.5); ?>

<section class="container">
    <div class="row align-items-center">
        <div class="col-md-6">
            <h2 class="heading text-3xl">About Us</h2>
            <p class="paragraph text-base">Lorem ipsum dolor sit amet, consectetur adipiscing elit. Fusce vel risus nec
                leo interdum tincidunt. Sed eu magna ac eros malesuada consectetur.</p>
            <p class="paragraph text-base">Vivamus sit amet magna ac magna vehicula feugiat non sed lectus. Suspendisse
                vitae libero nec ligula fermentum facilisis et sit amet dolor.</p>
                <?php echo primary_button('Learn More', 'https://example.com', ' ', 'btn-primary btn-lg text-light rounded-pill px-4', true); ?>
        </div>
        <div class="col-md-6 mt-5 mt-md-0">
            <img src="https://picsum.photos/600/400?random=3" alt="About us image" class="img-fluid rounded">
        </div>
    </div>
</section>

<?php echo spacer(3.5); ?>

<!-- Sezione 3: Griglia con Cards 3x2 -->
<section class="container my-5">
  <div class="row row-cols-1 row-cols-md-3 g-4">
    <!-- Card 1 -->
    <div class="col">
      <div class="card h-100">
        <img src="https://picsum.photos/1920/1080?random=3" class="card-img-top" alt="Card Image 1">
        <div class="card-body">
          <h5 class="card-title text-xl">Titolo Card 1</h5>
          <p class="card-text text-base">Descrizione della card 1. Un breve testo per spiegare l'argomento di questa sezione.</p>
        </div>
      </div>
    </div>
    <!-- Card 2 -->
    <div class="col">
      <div class="card h-100">
        <img src="https://picsum.photos/1920/1080?random=4" class="card-img-top" alt="Card Image 2">
        <div class="card-body">
          <h5 class="card-title text-xl">Titolo Card 2</h5>
          <p class="card-text text-base">Descrizione della card 2. Un breve testo per spiegare l'argomento di questa sezione.</p>
        </div>
      </div>
    </div>
    <!-- Card 3 -->
    <div class="col">
      <div class="card h-100">
        <img src="https://picsum.photos/1920/1080?random=5" class="card-img-top" alt="Card Image 3">
        <div class="card-body">
          <h5 class="card-title text-xl">Titolo Card 3</h5>
          <p class="card-text text-base">Descrizione della card 3. Un breve testo per spiegare l'argomento di questa sezione.</p>
        </div>
      </div>
    </div>
    <!-- Card 4 -->
    <div class="col">
      <div class="card h-100">
        <img src="https://picsum.photos/1920/1080?random=6" class="card-img-top" alt="Card Image 4">
        <div class="card-body">
          <h5 class="card-title text-xl">Titolo Card 4</h5>
          <p class="card-text text-base">Descrizione della card 4. Un breve testo per spiegare l'argomento di questa sezione.</p>
        </div>
      </div>
    </div>
    <!-- Card 5 -->
    <div class="col">
      <div class="card h-100">
        <img src="https://picsum.photos/1920/1080?random=7" class="card-img-top" alt="Card Image 5">
        <div class="card-body">
          <h5 class="card-title text-xl">Titolo Card 5</h5>
          <p class="card-text text-base">Descrizione della card 5. Un breve testo per spiegare l'argomento di questa sezione.</p>
        </div>
      </div>
    </div>
    <!-- Card 6 -->
    <div class="col">
      <div class="card h-100">
        <img src="https://picsum.photos/1920/1080?random=8" class="card-img-top" alt="Card Image 6">
        <div class="card-body">
          <h5 class="card-title text-xl">Titolo Card 6</h5>
          <p class="card-text text-base">Descrizione della card 6. Un breve testo per spiegare l'argomento di questa sezione.</p>
        </div>
      </div>
    </div>
  </div>
</section>


<?php get_footer(); ?>
