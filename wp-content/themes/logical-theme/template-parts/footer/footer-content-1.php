<!-- Call to Action -->

<section id="contatti" class="cta-section text-start text-white bg-gradientY w-100">
    <div class="offset" style="height:80px"></div>
    <div class="container">
        <div class="row no-gsap">
            <div class="col-lg-10 col-xl-8 mx-auto py-5">
                <h2 class="heading text-lg">Restiamo in contatto!</h2>
                <p class="paragraph text-sm">Segui i nostri aggiornamenti e scopri come puoi sostenere il movimento
                    contro gli affitti brevi
                    incontrollati e la speculazione immobiliare. Chi ha rendite e ricchezze di famiglia è già
                    organizzato
                    e sta difendendo i suoi interessi. Noi, che in questa città abitiamo, paghiamo l’affitto e non
                    abbiamo
                    rendite per vivere senza lavorare, possiamo fare la differenza.</p>
                <p class="paragraph text-sm">Perché l’abuso immobiliare non è un disastro che subiamo individualmente,
                    ma un conflitto che
                    possiamo affrontare e superare, insieme.</p>
                <?php echo spacer(.5); ?>

                <div class="d-flex justify-content-start gap-3">
                    <?php get_template_part('template-parts/partials/social-links-buttons'); ?>
                </div>

            </div>
        </div>
    </div>

    <div class="separator bg-white w-100" style="height:1px"></div>


    <div class="container px-3 py-3">
        <div class="row no-gsap">
            <div class="col-lg-10 col-xl-8 mx-auto">
                <div class="row">
                    <div class="col-12 col-xxl-7 d-block d-xxl-flex justify-content-start flex-wrap align-items-center">
                        <p  class="d-block d-md-inline-block paragraph text-white text-xs m-0">© <?php echo date('Y'); ?> - Resta
                            Abitante
                        </p>
                        <span class="paragraph text-white text-xs d-none d-md-inline">&nbsp;-&nbsp;</span>
                        <div class="spacer pb-1 d-block d-md-none"></div>
                        <p  class="d-block d-md-inline-block paragraph text-white text-xs m-0">Quest'opera
                            è distribuita con licenza <a class="text-white paragraph bold" target="_blank"
                                href="https://creativecommons.org/licenses/by-nc-nd/4.0/">CC BY-NC-ND 4.0.</a>
                        </p>
                    </div>
                    <div class="col-12 col-xxl-5 d-block d-xxl-flex justify-content-start flex-wrap align-items-center">
                        <div class="spacer pb-1 d-block d-xxl-none"></div>
                        <p class="paragraph text-xs m-0">
                            <a href="#"
                                class="text-white text-uppercase text-decoration-none hover-secondary cookie-consent"
                                data-lcc-open-settings="1">Gestione cookie</a>
                                <span> | </span>
                            <a href="<?php echo esc_url(ltc_home_url('/privacy-policy/')); ?>"
                                class="text-white text-uppercase text-decoration-none hover-secondary">Privacy
                                policy</a>
                            <span> | </span>
                            <a href="<?php echo esc_url(ltc_home_url('/cookie-policy/')); ?>"
                                class="text-white text-uppercase text-decoration-none hover-secondary">Cookie policy</a>
                        </p>
                    </div>
                </div>
            </div>

        </div>
    </div>

</section>
