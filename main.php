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

    // Textes dynamiques pour le slider
    $sliderTexts = [
        [
            'title' => 'Cadets de Chelun Martigné',
            'subtitle' => 'Plus qu\'un club de football, une véritable famille.',
            'btn1_text' => 'Nos Équipes',
            'btn1_url' => 'equipes.php',
            'btn1_target' => '',
            'btn1_rel' => '',
            'btn2_text' => 'Découvrir le PEF',
            'btn2_url' => 'pef.php'
        ],
        [
            'title' => 'Rejoignez l\'Aventure',
            'subtitle' => 'La passion du jeu et l\'esprit d\'équipe à chaque instant.',
            'btn1_text' => 'Nous Contacter',
            'btn1_url' => 'mailto:CCMreseauxsociaux@gmail.com',
            'btn1_target' => '',
            'btn1_rel' => '',
            'btn2_text' => '',
            'btn2_url' => ''
        ],
        [
            'title' => 'Vivez la Passion',
            'subtitle' => 'Des tribunes au terrain, partageons ensemble les émotions du football.',
            'btn1_text' => 'Boutique',
            'btn1_url' => 'https://cadets-chelun-martigne.kalisport.com/',
            'btn1_target' => '_blank',
            'btn1_rel' => 'noopener noreferrer',
            'btn2_text' => '',
            'btn2_url' => ''
        ]
    ];
    ?>

    <?php foreach ($sliderImages as $index => $imagePath): ?>
        <img
            src="<?= htmlspecialchars($imagePath, ENT_QUOTES, 'UTF-8') ?>"
            alt="Image d'accueil du club <?= $index + 1 ?>"
            class="hero-slide <?= $index === 0 ? 'is-active' : '' ?>"
        >
    <?php endforeach; ?>

    <!-- OVERLAY TEXTUEL DU SLIDER (Dynamique) -->
    <div class="hero-overlay-content" id="heroOverlayContent">
        <h1 class="hero-title" id="heroTitle">Cadets de Chelun Martigné</h1>
        <p class="hero-subtitle" id="heroSubtitle">Plus qu'un club de football, une véritable famille.</p>
        <div class="hero-actions" id="heroActions">
            <a href="equipes.php" class="btn-red">Nos Équipes</a>
            <a href="pef.php" class="btn-outline-white">Découvrir le PEF</a>
        </div>
    </div>

    <div class="hero-progress" id="heroProgress">
        <?php for ($bar = 0; $bar < count($sliderImages); $bar++): ?>
            <button class="hero-bar<?= $bar === 0 ? ' is-active' : '' ?>" type="button" aria-label="Aller à la photo <?= $bar + 1 ?>">
                <span class="hero-bar-fill"></span>
            </button>
        <?php endfor; ?>
    </div>
</div>

<!-- NOUVELLE SECTION : HISTOIRE ET VALEURS (SEO) -->
<section class="club-story-section">
    <div class="story-container">
        <div class="story-text">
            <h2>Notre Histoire, Notre Passion</h2>
            <div class="title-separator"></div>
            <p>Bienvenue sur le site officiel des <strong>Cadets de Chelun Martigné</strong>. Depuis notre création, notre club incarne les valeurs fondamentales du football amateur : solidarité, respect, convivialité et dépassement de soi. Situé au cœur de notre commune, notre complexe sportif est le point de ralliement de toutes les générations de passionnés, chaque week-end.</p>
            <p>Que vous soyez jeune joueur débutant, compétiteur aguerri ou simple supporter fidèle, vous trouverez votre place au sein de notre grande famille sportive. Nous accordons une importance toute particulière à la formation de nos jeunes talents à travers le <strong>Programme Éducatif Fédéral (PEF)</strong>, afin de former d'excellents footballeurs, mais surtout de futurs citoyens responsables.</p>
            <div class="story-stats">
                <div class="stat-item">
                    <span class="stat-icon"><i class="fa-solid fa-users"></i></span>
                    <div class="stat-info">
                        <span class="stat-number">+150</span>
                        <span class="stat-label">Licenciés</span>
                    </div>
                </div>
                <div class="stat-item">
                    <span class="stat-icon"><i class="fa-solid fa-shield-halved"></i></span>
                    <div class="stat-info">
                        <span class="stat-number">PEF</span>
                        <span class="stat-label">Club Engagé</span>
                    </div>
                </div>
                <div class="stat-item">
                    <span class="stat-icon"><i class="fa-solid fa-heart"></i></span>
                    <div class="stat-info">
                        <span class="stat-number">100%</span>
                        <span class="stat-label">Passion</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="story-image-container">
            <div class="story-image-wrapper">
                <img src="assets/img/Image_accueil/image U15.jpeg" alt="Équipe des Cadets de Chelun Martigné en action">
                <div class="story-image-decoration"></div>
            </div>
        </div>
    </div>
</section>

<!-- SECTION ACTUALITES ET PHOTOS -->
<section class="container-stats modern-stats bg-light-gray">
    <div class="section-heading text-center">
        <h2>La Vie du Club</h2>
        <div class="title-separator center"></div>
        <p class="section-subtitle">Suivez nos derniers matchs, événements et actualités.</p>
    </div>

    <div class="cards-wrapper">
        <div class="card modern-card">
            <h3 class="card-title"><i class="fa-solid fa-camera text-rouge"></i> Dernières photos</h3>
            <div class="card-content image-slider">
                <img src="assets/img/Image_accueil/image U15.jpeg" alt="Match action">
                <button class="prev results-nav" type="button" aria-label="Photo précédente">❮</button>
                <button class="next results-nav" type="button" aria-label="Photo suivante">❯</button>
            </div>
            <a href="photos.php" class="btn-red w-full">Voir la galerie complète</a>
        </div>

        <div class="card modern-card" id="homeFeatureCard">
            <h3 class="card-title"><i class="fa-solid fa-bullhorn text-rouge"></i> Actualités du club</h3>
            <div class="card-content results-box">
                <div class="feature-placeholder actualites-preview-box w-full" style="border: none; background: transparent;">
                    <div id="actualitesPreview" class="actualites-preview"></div>
                </div>
            </div>
            <button type="button" class="btn-red w-full" id="homeFeatureActionBtn">Voir les actualités</button>
        </div>
    </div>
</section>

<!-- BANNIERE D'APPEL A L'ACTION (CTA) -->
<section class="cta-banner">
    <div class="cta-container">
        <h2>Prêt à enfiler les crampons ?</h2>
        <p>Rejoignez les Cadets de Chelun Martigné pour cette saison. Que vous souhaitiez jouer, encadrer, arbitrer ou devenir bénévole, notre équipe est à votre écoute !</p>
        <div class="cta-buttons">
            <a href="mailto:CCMreseauxsociaux@gmail.com" class="btn-red btn-large">Nous Contacter</a>
            <a href="https://cadets-chelun-martigne.kalisport.com/" target="_blank" rel="noopener noreferrer" class="btn-outline-white btn-large">Visiter la Boutique</a>
        </div>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // ---- SLIDER HERO ----
    const heroSlides = Array.from(document.querySelectorAll('.Image_accueil .hero-slide'));
    const heroBars   = Array.from(document.querySelectorAll('#heroProgress .hero-bar'));
    const heroDuration = 6000;
    let heroIndex = 0;
    let heroRaf = null;
    let heroStart = null;

    const SLIDER_TEXTS = <?= json_encode($sliderTexts) ?>;
    const overlayContent = document.getElementById('heroOverlayContent');
    const heroTitle = document.getElementById('heroTitle');
    const heroSubtitle = document.getElementById('heroSubtitle');
    const heroActions = document.getElementById('heroActions');
    let isFirstLoad = true;

    function updateSliderText(index) {
        if (isFirstLoad) {
            isFirstLoad = false;
            return; // Pas d'animation au chargement initial
        }

        const textData = SLIDER_TEXTS[index % SLIDER_TEXTS.length];
        
        if (overlayContent) overlayContent.classList.add('is-hidden');
        
        setTimeout(() => {
            if (heroTitle) heroTitle.textContent = textData.title;
            if (heroSubtitle) heroSubtitle.textContent = textData.subtitle;
            
            if (heroActions) {
                let btnsHtml = '';
                if(textData.btn1_text) {
                    const btn1Target = textData.btn1_target ? ` target="${textData.btn1_target}"` : '';
                    const btn1Rel = textData.btn1_rel ? ` rel="${textData.btn1_rel}"` : '';
                    btnsHtml += `<a href="${textData.btn1_url}"${btn1Target}${btn1Rel} class="btn-red">${textData.btn1_text}</a>`;
                }
                if(textData.btn2_text) {
                    btnsHtml += `<a href="${textData.btn2_url}" class="btn-outline-white">${textData.btn2_text}</a>`;
                }
                heroActions.innerHTML = btnsHtml;
            }
            
            if (overlayContent) overlayContent.classList.remove('is-hidden');
        }, 300); // Doit correspondre à la transition CSS
    }

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

        updateSliderText(index);
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
        if (index === heroIndex) return;
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

    // ---- ACTUALITES WIDGET (Simplifié) ----
    const actionButton = document.getElementById('homeFeatureActionBtn');
    const previewContainer = document.getElementById('actualitesPreview');

    if (previewContainer) {
        const popupFirstItem = document.querySelector('#popupContent .popup-item');
        if (popupFirstItem) {
            const popupTitle = popupFirstItem.querySelector('.popup-item-title')?.textContent?.trim() || 'Annonce du club';
            const popupBodyText = popupFirstItem.querySelector('.popup-item-body')?.textContent?.trim() || '';
            const previewText = popupBodyText.length > 150 ? popupBodyText.slice(0, 150) + '…' : popupBodyText;

            previewContainer.innerHTML = `
                <article class="actualites-mini-card" style="background: rgba(255,255,255,0.05); border-color: rgba(255,255,255,0.1); color: white;">
                    <h4 style="color: white;">${popupTitle}</h4>
                    <p style="color: #e2e8f0;">${previewText || 'Clique sur le bouton pour voir les annonces en détail.'}</p>
                </article>
            `;
        } else {
            previewContainer.innerHTML = '<p class="actualites-empty" style="color: #e2e8f0;">Aucune annonce active pour le moment.</p>';
        }
    }

    if (actionButton) {
        actionButton.addEventListener('click', function () {
            if (typeof window.openClubPopup === 'function') {
                window.openClubPopup();
            }
        });
    }
});
</script>
</main>
