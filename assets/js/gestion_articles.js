// CHARGEMENT DES ARTICLES POUR PAGE SITE

console.log('✅ JavaScript chargé - Début du chargement des articles');
function componentsElement(nameTag, content, className) {
    const element = document.createElement(nameTag);
    element.textContent = content;
    if (className) {
        element.className = className;
    }
    return element;
}

function addArticle(article) {
    const articleTag = componentsElement('article');
    articleTag.className = 'articleEdit';

    const divArticle = componentsElement('div');
    divArticle.className = 'headerArticle';
    articleTag.appendChild(divArticle);

    const titleArticle = componentsElement(
        'h1',
        article.titre,
        'article_title'
    );
    divArticle.appendChild(titleArticle);

    const dateArticle = componentsElement(
        'small',
        `publié le ${formatDate(article.date_publication)}`,
        'article_date'
    );
    divArticle.appendChild(dateArticle);

    // ⭐ AJOUTEZ L'IMAGE SI ELLE EXISTE
    if (article.image && article.image.trim() !== '') {
        const imageArticle = document.createElement('img');
        imageArticle.src = `./app/uploads/${article.image}`;
        imageArticle.alt = article.titre;
        imageArticle.className = 'article_image';
        divArticle.appendChild(imageArticle);
    }

    const contentArticle = componentsElement('div');
    contentArticle.className = 'content_article';
    articleTag.appendChild(contentArticle);

    // -----------------------------------------------------------------------
    // ⚠️ SÉCURITÉ XSS : Utilisation OBLIGATOIRE d'un sanitizer
    // Si DomPurify est chargé sur la page (recommandé)
    // -----------------------------------------------------------------------
    contentArticle.innerHTML = DOMPurify.sanitize(article.contenu, {
        ADD_ATTR: ['target', 'rel'],
        ALLOWED_ATTR: ['href', 'target', 'rel', 'class', 'style', 'id']
    });

    return articleTag;
}

function formatDate(dateString) {
    let date = new Date(dateString);
    return date.toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric'
    });
}
let currentPage = 1;
let totalPages = 1;

function loadingArticles(page = 1) {
    const container = document.getElementById('articles-container');
    // console.log('🔄 Chargement des articles...');

    fetch(`app/admin/api_articles.php?page=${page}`)
        .then((response) => response.json())
        .then((resultat) => {
            // Vide le container
            while (container.firstChild) {
                container.removeChild(container.firstChild);
            }
            // 🧩 Vérifie si l'API a renvoyé une erreur
            if (!resultat.success) {
                console.error('❌ Erreur API:', resultat.error);
                container.appendChild(
                    componentsElement(
                        'p',
                        `Erreur API : ${resultat.error}`,
                        'alert-message error'
                    )
                );
                return; // stoppe l'exécution ici
            }

            // Affiche les articles
            resultat.data.forEach((article) => {
                container.appendChild(addArticle(article));
            });

            // Met à jour la pagination
            currentPage = resultat.pagination.current_page;
            totalPages = resultat.pagination.total_page;

            // Ajoute les boutons
            addControlPagination();
        })
        .catch((erreur) => {
            console.error('❌ Erreur:', erreur);
            container.appendChild(
                componentsElement('p', 'Erreur de chargement', 'error')
            );
        });
}
function addControlPagination() {
    const container = document.getElementById('articles-container');

    if (!container) {
        console.error('❌ Élément #articles-container introuvable !');
        return;
    }
    // Supprime l’ancienne pagination (sinon elles s’empilent)
    const oldPagination = document.querySelector('.pagination');
    if (oldPagination) oldPagination.remove();

    const paginationDiv = componentsElement('div', '', 'pagination');

    //bouton précédent
    if (currentPage > 1) {
        const btnBefore = componentsElement(
            'button',
            '← Précédent',
            ' btn btn-pagination'
        );
        btnBefore.addEventListener('click', () =>
            loadingArticles(currentPage - 1)
        );
        paginationDiv.appendChild(btnBefore);
    }
    // span pour informer indicateur de page en cours/nbre de pages
    const infopage = componentsElement(
        'span',
        `Page ${currentPage}/${totalPages}`
    );
    paginationDiv.appendChild(infopage);

    //creation d'un bouton pour aller sur page suivante à partir de la page courante
    if (currentPage < totalPages) {
        const btnNext = componentsElement(
            'button',
            'Suivant →',
            ' btn btn-pagination'
        );

        btnNext.addEventListener('click', () =>
            loadingArticles(currentPage + 1)
        );
        paginationDiv.appendChild(btnNext);
    }
    container.after(paginationDiv);
}

document.addEventListener('DOMContentLoaded', function () {
    loadingArticles(1);
});
