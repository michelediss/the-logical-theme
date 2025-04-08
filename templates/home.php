<?php get_header(); ?>

<?php echo spacer(6); ?>

<div class="container-fluid mb-5">
  <div class="container">
    <div class="row">
      <div class="col-12 bg-primary-white rounded-5 p-4">
        <h1 class="text text-3xl heading text-black mb-3">Blog</h1>
        <p class="paragraph text-lg text-black">Il blog del Comitato per l'acqua pubblica in Basilicata "Peppe Di
          Bello". Qui troverai aggiornamenti costanti sulle nostre iniziative, analisi dei problemi legati alla gestione
          delle risorse idriche, storie di impegno civile e proposte per garantire che l’acqua rimanga un bene comune,
          libero da logiche di profitto.</p>
      </div>
    </div>
  </div>
</div>

<?php

render_post_grid([
  'pagination' => true,
]);
?>


<?php echo spacer(2); ?>




<?php get_footer(); ?>