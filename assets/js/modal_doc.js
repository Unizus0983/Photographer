//AFFICHAGE DES IMAGES dans listes des fichiers chargés
function afficherImage(src) {
    const modal = document.getElementById('imageModal');
    const modalImg = document.getElementById('modalImage');

    modal.style.display = 'block';
    modalImg.src = src;
}

// Fermer la modal
document.querySelector('.close-modal').onclick = function () {
    document.getElementById('imageModal').style.display = 'none';
};

// Fermer en cliquant en dehors de l'image
document.getElementById('imageModal').onclick = function (event) {
    if (event.target === this) {
        this.style.display = 'none';
    }
};
