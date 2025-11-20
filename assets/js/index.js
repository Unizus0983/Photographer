// ✅ Version corrigée du slider
window.addEventListener('load', function () {
    // Petit délai pour laisser le navigateur finir le rendu
    setTimeout(function () {
        const track = document.querySelector('.slider-track');
        const slides = document.querySelectorAll('.slide');
        const dots = document.querySelectorAll('.slider-dot');
        const slider = document.querySelector('.slider-container');

        // Vérifier si tous les éléments du slider existent
        if (!track || !slides.length || !dots.length || !slider) {
            console.log('Slider elements not found on this page');
            return; // Arrêter l'exécution si le slider n'existe pas
        }

        let currentIndex = 0;
        const slideCount = slides.length;
        let interval;

        function updateSlider() {
            track.style.transform = `translateX(-${currentIndex * 100}%)`;
            dots.forEach((dot, index) => {
                dot.classList.toggle('active', index === currentIndex);
            });
        }

        function goToSlide(index) {
            currentIndex = index;
            updateSlider();
        }

        function startAutoSlide() {
            if (window.innerWidth < 768) return; // Mobile = pas d'auto-slide
            stopAutoSlide(); // Arrêter l'intervalle existant
            interval = setInterval(() => {
                currentIndex = (currentIndex + 1) % slideCount;
                updateSlider();
            }, 10000);
        }

        function stopAutoSlide() {
            if (interval) {
                clearInterval(interval);
                interval = null;
            }
        }

        // Événements
        dots.forEach((dot, index) => {
            dot.addEventListener('click', () => {
                stopAutoSlide();
                goToSlide(index);
                startAutoSlide();
            });
        });

        slider.addEventListener('mouseenter', stopAutoSlide);
        slider.addEventListener('mouseleave', startAutoSlide);

        startAutoSlide(); // Démarrage initial
    }, 100);
});

// VARIABLES
// variables menu
const burgerBtn = document.querySelector('.burger-bar');
const closeBtn = document.querySelector('.close');
const menuBurger = document.querySelector('.menu-burger');

// MENU BURGER
if (burgerBtn && closeBtn && menuBurger) {
    burgerBtn.addEventListener('click', () => {
        menuBurger.style.transform = 'translateX(0)';
    });

    closeBtn.addEventListener('click', () => {
        menuBurger.style.transform = 'translateX(-200%)';
    });
}

// BUTTON SCROLL - CORRIGÉ avec vérification
document.addEventListener('DOMContentLoaded', function () {
    const backToTopBtn = document.getElementById('backToTop');

    // Vérifier si le bouton existe avant d'ajouter l'événement
    if (backToTopBtn) {
        backToTopBtn.addEventListener('click', function () {
            const start = window.scrollY; // position de départ en px
            const end = 0; // position d'arrivée en px
            const duration = 2500; // durée de l'animation en ms (2.5s ici)
            const distance = start - end; // distance à parcourir en px
            let startTime = null; // temps de départ pour l'animation

            function scrollStep(timestamp) {
                if (!startTime) startTime = timestamp;
                const elapsed = timestamp - startTime;
                const progress = Math.min(elapsed / duration, 1); // progression linéaire

                window.scrollTo(0, start - distance * progress);

                if (progress < 1) {
                    requestAnimationFrame(scrollStep);
                }
            }

            requestAnimationFrame(scrollStep);
        });
    } else {
        console.log('Back to top button not found on this page');
    }
});
