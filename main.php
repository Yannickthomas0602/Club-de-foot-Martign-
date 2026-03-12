<main>
 

<div class="Image_accueil">
    <?php
    $sliderDir = __DIR__ . '/assets/img/Image_accueil/slider';
    $sliderImages = [];

    if (is_dir($sliderDir)) {
        $sliderImages = glob($sliderDir . '/*.{jpg,jpeg,png,webp,avif,JPG,JPEG,PNG,WEBP,AVIF}', GLOB_BRACE) ?: [];
        sort($sliderImages, SORT_NATURAL | SORT_FLAG_CASE);

        $projectRoot = str_replace('\\', '/', realpath(__DIR__) ?: __DIR__);

        $sliderImages = array_values(array_filter(array_map(static function ($absolutePath) use ($projectRoot) {
            $normalizedPath = str_replace('\\', '/', realpath($absolutePath) ?: $absolutePath);

            if (strpos($normalizedPath, $projectRoot . '/') === 0) {
                return substr($normalizedPath, strlen($projectRoot) + 1);
            }

            return null;
        }, $sliderImages)));
    }

    if (empty($sliderImages)) {
        $sliderImages = ['assets/img/Image_accueil/image U15.jpeg'];
    }
    ?>

    <?php foreach ($sliderImages as $index => $imagePath): ?>
        <img
            src="<?= htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8') ?>"
            alt="Image d'accueil du club <?= $index + 1 ?>"
            class="hero-slide <?= $index === 0 ? 'is-active' : '' ?>"
        >
    <?php endforeach; ?>

    <div class="hero-progress" id="heroProgress">
        <?php for ($bar = 0; $bar < count($sliderImages); $bar++): ?>
            <button class="hero-bar<?= $bar === 0 ? ' is-active' : '' ?>" type="button" aria-label="Aller à la photo <?= $bar + 1 ?>">
                <span class="hero-bar-fill"></span>
            </button>
        <?php endfor; ?>
    </div>
</div>


<section class="container-stats">
    <div class="card">
        <h3 class="card-title"><i class="fa-solid fa-camera"></i> Les Dernières photos</h3>
        <div class="card-content image-slider">
            <img src="assets\img\Image_accueil\image U15.jpeg" alt="Match action">
            <button class="prev results-nav" type="button" aria-label="Photo précédente">❮</button>
            <button class="next results-nav" type="button" aria-label="Photo suivante">❯</button>
        </div>
        <a href="photos.php" class="btn-red">Voir toutes les photos</a>
    </div>

    <div class="card" id="homeFeatureCard">
        <h3 class="card-title"><i class="fa-solid fa-bullhorn"></i> <span id="homeFeatureTitle">Actualités du club</span></h3>
        <div class="card-content results-box">
            <button class="results-nav prev" type="button" id="homeFeaturePrev" aria-label="Carte précédente">❮</button>
            <div class="results-slides" id="homeFeatureSlides">
                <div class="results-slide is-active" data-slide="annonces">
                    <div class="feature-placeholder actualites-preview-box">
                        <div id="actualitesPreview" class="actualites-preview"></div>
                    </div>
                </div>

                <div class="results-slide" data-slide="boutique">
                    <div class="feature-placeholder boutique-placeholder">
                        <div class="boutique-image-wrap">
                            <img src="assets\img\Image_accueil\boutique.png" alt="image boutique">
                        </div>
                    </div>
                </div>
            </div>
            <button class="results-nav next" type="button" id="homeFeatureNext" aria-label="Carte suivante">❯</button>
        </div>
        <button type="button" class="btn-red" id="homeFeatureActionBtn">Voir les actualités</button>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const heroSlides = Array.from(document.querySelectorAll('.Image_accueil .hero-slide'));
    const heroBars   = Array.from(document.querySelectorAll('#heroProgress .hero-bar'));
    const heroDuration = 5000; // ms par slide
    let heroIndex = 0;
    let heroRaf = null;
    let heroStart = null;

    function showHeroSlide(index) {
        heroSlides.forEach(function (slide, i) {
            slide.classList.toggle('is-active', i === index);
        });
        heroBars.forEach(function (bar, i) {
            bar.classList.remove('is-active', 'is-done');
            if (i < index)  { bar.classList.add('is-done'); }
            if (i === index) { bar.classList.add('is-active'); }
            bar.querySelector('.hero-bar-fill').style.transform = i < index ? 'scaleX(1)' : 'scaleX(0)';
        });
    }

    function tick(timestamp) {
        if (!heroStart) { heroStart = timestamp; }
        const elapsed = timestamp - heroStart;
        const progress = Math.min(elapsed / heroDuration, 1);

        if (heroBars[heroIndex]) {
            heroBars[heroIndex].querySelector('.hero-bar-fill').style.transform = 'scaleX(' + progress + ')';
        }

        if (progress < 1) {
            heroRaf = requestAnimationFrame(tick);
        } else {
            heroIndex = (heroIndex + 1) % heroSlides.length;
            showHeroSlide(heroIndex);
            heroStart = null;
            heroRaf = requestAnimationFrame(tick);
        }
    }

    function goToSlide(index) {
        cancelAnimationFrame(heroRaf);
        heroIndex = index;
        showHeroSlide(heroIndex);
        heroStart = null;
        heroRaf = requestAnimationFrame(tick);
    }

    if (heroSlides.length <= 1) {
        const progressEl = document.getElementById('heroProgress');
        if (progressEl) { progressEl.style.display = 'none'; }
    } else {
        heroBars.forEach(function (bar, i) {
            bar.addEventListener('click', function () { goToSlide(i); });
        });
        showHeroSlide(heroIndex);
        heroRaf = requestAnimationFrame(tick);
    }

    const slides = Array.from(document.querySelectorAll('#homeFeatureSlides .results-slide'));
    const previousButton = document.getElementById('homeFeaturePrev');
    const nextButton = document.getElementById('homeFeatureNext');
    const actionButton = document.getElementById('homeFeatureActionBtn');
    const featureTitle = document.getElementById('homeFeatureTitle');
    const previewContainer = document.getElementById('actualitesPreview');

    if (!slides.length || !previousButton || !nextButton || !actionButton || !featureTitle || !previewContainer) {
        return;
    }

    const popupFirstItem = document.querySelector('#popupContent .popup-item');
    if (popupFirstItem) {
        const popupTitle = popupFirstItem.querySelector('.popup-item-title')?.textContent?.trim() || 'Annonce du club';
        const popupBodyText = popupFirstItem.querySelector('.popup-item-body')?.textContent?.trim() || '';
        const previewText = popupBodyText.length > 180 ? popupBodyText.slice(0, 180) + '…' : popupBodyText;

        previewContainer.innerHTML = `
            <article class="actualites-mini-card">
                <h4>${popupTitle}</h4>
                <p>${previewText || 'Clique sur le bouton pour voir les annonces en détail.'}</p>
            </article>
        `;
    } else {
        previewContainer.innerHTML = '<p class="actualites-empty">Aucune annonce active pour le moment.</p>';
    }

    let currentIndex = 0;

    function renderSlide() {
        slides.forEach(function (slide, index) {
            slide.classList.toggle('is-active', index === currentIndex);
        });

        const key = slides[currentIndex].dataset.slide;
        if (key === 'boutique') {
            featureTitle.textContent = 'La Boutique';
            actionButton.textContent = 'Voir la boutique';
        } else {
            featureTitle.textContent = 'Actualités du club';
            actionButton.textContent = 'Voir les actualités';
        }
    }

    previousButton.addEventListener('click', function () {
        currentIndex = (currentIndex - 1 + slides.length) % slides.length;
        renderSlide();
    });

    nextButton.addEventListener('click', function () {
        currentIndex = (currentIndex + 1) % slides.length;
        renderSlide();
    });

    actionButton.addEventListener('click', function () {
        const key = slides[currentIndex].dataset.slide;
        if (key === 'boutique') {
            window.open('https://cadets-chelun-martigne.kalisport.com/', '_blank', 'noopener,noreferrer');
            return;
        }

        if (typeof window.openClubPopup === 'function') {
            window.openClubPopup();
        }
    });

    renderSlide();
});
</script>

    
</main>

