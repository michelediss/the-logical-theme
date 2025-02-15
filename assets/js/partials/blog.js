document.addEventListener("DOMContentLoaded", function () {
  // Verifica se il body ha entrambe le classi 'home' e 'page-id-310'
  if (
    !document.body.classList.contains('home') &&
    !document.body.classList.contains('page-id-314')
  ) {
    return; // Se una delle classi manca, non eseguire il codice
  }

  if (!window.blogGridConfig || !window.blogGridConfig.postsPerPage) {
    console.error("Errore: postsPerPage non configurato correttamente in PHP.");
    return;
  }

  // const apiBase = window.location.origin; 
  // riga per configurazione subfolder
  const apiBase = window.location.origin + '/acquapubblicabasilicata';
  //
  const postsPerPage = window.blogGridConfig.postsPerPage;
  let currentPage = 1;
  let currentCategory = null;
  let totalPagesForCurrentCategory = 1;

  const postsContainer = document.getElementById("posts-container");
  const filtersContainer = document.getElementById("filters-container");
  const loadMoreContainer = document.getElementById("load-more-container");

  // Riferimento allo spinner
  const spinner = document.getElementById("spinner");

  /**
   * Funzioni helper per mostrare/nascondere lo spinner
   */
  function showSpinner() {
    if (spinner) {
      spinner.style.display = "block";
    }
  }

  function hideSpinner() {
    if (spinner) {
      spinner.style.display = "none";
    }
  }

  function fetchCategories() {
    // Aggiunto hide_empty=true per mostrare solo le categorie con almeno 1 post
    const url = `${apiBase}/wp-json/wp/v2/categories?per_page=100&hide_empty=true&_fields=id,name`;
    return fetch(url).then((response) => {
      if (!response.ok) {
        throw new Error("Impossibile ottenere le categorie dall'API");
      }
      return response.json();
    });
  }

  function fetchPosts(page = 1, category = null) {
    let url = `${apiBase}/wp-json/wp/v2/posts?per_page=${postsPerPage}&page=${page}&_embed`;
    if (category) {
      url += `&categories=${category}`;
    }
    
    return fetch(url)
      .then((response) => {
        if (!response.ok) {
          throw new Error("Impossibile ottenere i post dall'API");
        }
        const totalPosts = response.headers.get("X-WP-Total");
        const totalPages = response.headers.get("X-WP-TotalPages");
        return response.json().then((data) => {
          return {
            posts: data,
            totalPosts: parseInt(totalPosts, 10),
            totalPages: parseInt(totalPages, 10),
          };
        });
      });
  }

  /**
   * Funzione per animare i post.
   * @param {NodeList|Array} elements - Elementi DOM da animare.
   * @param {string} direction - Direzione dell'animazione: 'in' o 'out'.
   * @returns {Promise} - Promessa che si risolve al termine dell'animazione.
   */
  function animatePosts(elements, direction = 'in') {
    if (direction === 'in') {
      // Animazione in entrata: da opacità 0 e scala ridotta a opacità 1 e scala 1
      return anime({
        targets: elements,
        opacity: [0, 1],
        scale: [0, 1],
        easing: "easeOutExpo",
        duration: (el, i) => 300 + i * 75,
        delay: (el, i) => i * 50,
      }).finished;
    } else {
      // Animazione in uscita: da opacità 1 e scala 1 a opacità 0 e scala ridotta
      return anime({
        targets: elements,
        opacity: [1, 0],
        scale: [1, 0],
        easing: "easeInExpo",
        duration: 300, // durata fissa o personalizzabile come in entrata
        delay: (el, i) => i * 50,
      }).finished;
    }
  }

  function renderCategoryFilters(categories) {
    function setActiveButton(clickedBtn) {
      const allButtons = filtersContainer.querySelectorAll("button");
      allButtons.forEach((btn) => btn.classList.remove("active"));
      clickedBtn.classList.add("active");
    }
  
    const allBtn = document.createElement("button");
    allBtn.textContent = "Tutti";
    allBtn.className =
      "nav-filter text-uppercase heading rounded-pill mx-2 px-3 py-2 active";
    allBtn.addEventListener("click", function () {
      if (currentCategory === null && currentPage === 1) return; // Evita ricariche inutili
      currentCategory = null;
      currentPage = 1;
      setActiveButton(allBtn);
  
      updatePostsWithExit();
    });
    filtersContainer.appendChild(allBtn);
  
    categories.forEach((cat) => {
      const btn = document.createElement("button");
      btn.textContent = cat.name;
      btn.className =
        "nav-filter text-uppercase heading rounded-pill me-2 px-3 py-2";
      btn.addEventListener("click", function () {
        if (currentCategory === cat.id && currentPage === 1) return; // Evita ricariche inutili
        currentCategory = cat.id;
        currentPage = 1;
        setActiveButton(btn);
  
        updatePostsWithExit();
      });
      filtersContainer.appendChild(btn);
    });
  }

  /**
   * Funzione per aggiornare i post con animazione di uscita.
   */
  function updatePostsWithExit() {
    // Imposta min-height uguale all'altezza corrente prima di eventuali modifiche
    const currentHeight = postsContainer.offsetHeight;
    postsContainer.style.minHeight = `${currentHeight}px`;

    // Recupero i post attualmente visibili
    const oldPosts = postsContainer.querySelectorAll(".col-6");
  
    // Eseguo l'animazione di uscita se ci sono post da rimuovere
    let exitAnimation = Promise.resolve();
    if (oldPosts.length > 0) {
      exitAnimation = animatePosts(oldPosts, 'out').then(() => {
        // Dopo l'animazione di uscita, svuoto il container
        postsContainer.innerHTML = "";
      });
    } else {
      // Se non ci sono post, posso semplicemente svuotare
      postsContainer.innerHTML = "";
    }
  
    exitAnimation.then(() => {
      // Carico i nuovi post
      updatePosts();
    });
  }

  function updatePosts() {
    // Mostra lo spinner prima di fare il fetch
    showSpinner();

    // Imposta min-height uguale all'altezza corrente prima di eventuali modifiche
    const currentHeight = postsContainer.offsetHeight;
    postsContainer.style.minHeight = `${currentHeight}px`;

    fetchPosts(currentPage, currentCategory)
      .then((data) => {
        totalPagesForCurrentCategory = data.totalPages;

        // Filtraggio ulteriore lato client
        if (currentCategory) {
          data.posts = data.posts.filter(
            (p) => p.categories && p.categories.includes(currentCategory)
          );
        }

        const newPostsElements = renderPosts(
          data.posts,
          "d-flex flex-column justify-content-between"
        );

        if (data.posts.length === 0 && currentPage === 1) {
          loadMoreContainer.style.display = "none";
          return;
        }

        requestAnimationFrame(() => {
          // Animazione in entrata sui nuovi post
          animatePosts(newPostsElements, 'in');
        });

        if (currentPage >= totalPagesForCurrentCategory) {
          loadMoreContainer.style.display = "none";
        } else {
          loadMoreContainer.style.display = "block";
        }
      })
      .catch((error) => {
        console.error("Errore durante il caricamento dei post:", error);
        loadMoreContainer.style.display = "none";
      })
      .finally(() => {
        // Nascondo lo spinner e rimuovo il min-height al termine del caricamento
        hideSpinner();
        postsContainer.style.minHeight = "";
      });
  }

  function renderPosts(posts, extraClasses = "") {
    const existingPostsCount = postsContainer.querySelectorAll(".col-6").length;
    const fragment = document.createDocumentFragment();

    posts.forEach((post) => {
      const col = document.createElement("div");
      col.className = "col-6 col-md-4 col-lg-3";

      const cardLink = document.createElement("a");
      cardLink.href = post.link;
      cardLink.className =
        "card h-100 border-0 text-decoration-none rounded-4 overflow-hidden";

      // Creazione featured image
      if (
        post._embedded &&
        post._embedded["wp:featuredmedia"] &&
        post._embedded["wp:featuredmedia"][0]
      ) {
        const ratioDiv = document.createElement("div");
        ratioDiv.className = "ratio ratio-16x9";
  
        const img = document.createElement("img");
        img.src = post._embedded["wp:featuredmedia"][0].source_url;
        img.alt = post.title.rendered;
        img.className = "featured object-fit-cover";
  
        ratioDiv.appendChild(img);
        cardLink.appendChild(ratioDiv);
      }

      const cardBody = document.createElement("div");
      cardBody.className = `card-body ${extraClasses}`.trim();

      const titleElement = document.createElement("h3");
      titleElement.className = "card-title heading text-lg text-lg-xl lh-sm mb-3 text-blue-800";
      titleElement.textContent = post.title.rendered;
      cardBody.appendChild(titleElement);

      // Mostra le categorie
      if (post.categories && post.categories.length > 0) {
        const categoriesContainer = document.createElement("div");
        categoriesContainer.className = "post-categories mt-2";

        post.categories.forEach((categoryId) => {
          const category = findCategoryById(categoryId);
          if (category) {
            const categoryBadge = document.createElement("span");
            categoryBadge.className =
              "rounded-pill paragraph text-xs text-md-sm text-uppercase cat-badge bg-blue-100 text-blue-800 px-3 py-1";
            categoryBadge.textContent = category.name;
            categoriesContainer.appendChild(categoryBadge);
          }
        });

        cardBody.appendChild(categoriesContainer);
      }

      cardLink.appendChild(cardBody);
      col.appendChild(cardLink);
      fragment.appendChild(col);
    });

    postsContainer.appendChild(fragment);

    const allPosts = postsContainer.querySelectorAll(".col-6");
    const newlyAddedPosts = Array.from(allPosts).slice(existingPostsCount);
    return newlyAddedPosts;
  }

  function findCategoryById(categoryId) {
    if (!window.loadedCategories) return null;
    return window.loadedCategories.find((cat) => cat.id === categoryId) || null;
  }

  function initLoadMoreButton() {
    if (!loadMoreContainer) {
      console.error("Errore: load-more-container non trovato nel DOM.");
      return;
    }

    const loadMoreBtn = document.createElement("button");
    loadMoreBtn.textContent = "Mostra Altro";
    loadMoreBtn.className = "button heading text-uppercase bg-blue-500 text-white border-0 rounded-pill px-4 py-2 lh-sm";
    loadMoreBtn.addEventListener("click", function () {
      currentPage++;
      updatePosts();
    });
    loadMoreContainer.appendChild(loadMoreBtn);
  }

  fetchCategories()
    .then((categories) => {
      window.loadedCategories = categories;
      renderCategoryFilters(categories);
      updatePosts();
    })
    .catch((error) => {
      console.error("Errore durante il caricamento delle categorie:", error);
    });

  initLoadMoreButton();
});
