// ✅ Version encore légèrement améliorée
window.addEventListener('load', function () {
    // Petit délai pour laisser le navigateur finir le rendu
    setTimeout(function () {
        const track = document.querySelector('.slider-track');
        const slides = document.querySelectorAll('.slide');
        const dots = document.querySelectorAll('.slider-dot');
        const slider = document.querySelector('.slider-container');
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

            interval = setInterval(() => {
                currentIndex = (currentIndex + 1) % slideCount;
                updateSlider();
            }, 10000);
        }

        function stopAutoSlide() {
            clearInterval(interval);
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

// BUTTON SCROLL
document.getElementById('backToTop').addEventListener('click', function () {
    const start = window.scrollY; //first position begenning in px
    const end = 0; //position in px
    const duration = 2500; // duration animation in ms (2s here)
    const distance = start - end; //distance to do in px to start to end with soustraction
    let startTime = null; //time distance for beginning

    //timestamp → automatically provided by requestAnimationFrame(method JS), this is the time in milliseconds since the page was opened

    function scrollStep(timestamp) {
        if (!startTime) startTime = timestamp;
        const elapsed = timestamp - startTime;
        const progress = Math.min(elapsed / duration, 1); // linear

        window.scrollTo(0, start - distance * progress);

        if (progress < 1) {
            requestAnimationFrame(scrollStep);
        }
    }

    requestAnimationFrame(scrollStep);
});
