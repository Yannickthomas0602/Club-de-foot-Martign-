const track = document.getElementById('carouselTrack');
const container = document.getElementById('carouselContainer');
const nextBtn = document.getElementById('nextBtn');
const prevBtn = document.getElementById('prevBtn');
const slides = Array.from(track.children);

// Cloner les slides pour créer l'effet infini
const clonedSlides = slides.map(slide => slide.cloneNode(true));
clonedSlides.forEach(slide => track.appendChild(slide));

let currentPosition = 0;
let isAnimating = false;
let autoScrollInterval;
let pauseTimeout; // Pour gérer le timeout après un clic

// Fonction pour obtenir la largeur d'une slide en fonction de l'écran
function getSlideWidth() {
    if (window.innerWidth <= 480) {
        return 112; // 100px + 12px gap
    } else if (window.innerWidth <= 768) {
        return 128; // 120px + 8px gap
    } else {
        return 170; // 150px + 20px gap
    }
}

let SLIDE_WIDTH = getSlideWidth();
const AUTO_SCROLL_SPEED = 1; // pixels par pas (très lent)
const AUTO_SCROLL_INTERVAL = 50; // ms entre chaque pas
const PAUSE_DURATION = 3000; // 3 secondes de pause quand on clique

// Fonction pour mettre à jour la position
function updatePosition() {
    track.style.transform = `translateX(-${currentPosition}px)`;
}

// Défilement automatique continu
function autoScroll() {
    currentPosition += AUTO_SCROLL_SPEED;
    
    // Si on a atteint la fin (slides originales * largeur), revenir au début
    const totalWidth = slides.length * SLIDE_WIDTH;
    if (currentPosition >= totalWidth) {
        currentPosition = 0;
    }
    
    updatePosition();
}

// Démarrer l'auto-scroll continu
function startAutoScroll() {
    autoScrollInterval = setInterval(autoScroll, AUTO_SCROLL_INTERVAL);
}

// Arrêter l'auto-scroll
function stopAutoScroll() {
    clearInterval(autoScrollInterval);
    clearTimeout(pauseTimeout); // Annuler le timeout en attente
}

// Événements des flèches
nextBtn.addEventListener('click', () => {
    stopAutoScroll();
    currentPosition += SLIDE_WIDTH;
    
    // Gestion de la boucle infinie
    const totalWidth = slides.length * SLIDE_WIDTH;
    if (currentPosition >= totalWidth) {
        currentPosition = 0;
    }
    
    updatePosition();
    
    // Reprendre l'auto-scroll après 3 secondes (sauf si on survole)
    pauseTimeout = setTimeout(startAutoScroll, PAUSE_DURATION);
});

prevBtn.addEventListener('click', () => {
    stopAutoScroll();
    currentPosition -= SLIDE_WIDTH;
    
    // Gestion de la boucle infinie en arrière
    const totalWidth = slides.length * SLIDE_WIDTH;
    if (currentPosition < 0) {
        currentPosition = totalWidth - SLIDE_WIDTH;
    }
    
    updatePosition();
    
    // Reprendre l'auto-scroll après 3 secondes (sauf si on survole)
    pauseTimeout = setTimeout(startAutoScroll, PAUSE_DURATION);
});

// Arrêter au survol, reprendre au départ
container.addEventListener('mouseenter', stopAutoScroll);
container.addEventListener('mouseleave', () => {
    clearTimeout(pauseTimeout); // Annuler le timeout en attente
    startAutoScroll();
});

// Adapter au redimensionnement de l'écran
window.addEventListener('resize', () => {
    SLIDE_WIDTH = getSlideWidth();
});

// Initialiser
startAutoScroll();