<?php
$page_title = "PEF - Programme Éducatif Fédéral";
require_once __DIR__ . "/fonctions.php";

// ── Lecture des articles ──────────────────────────────────────────────────
$pdo = getDB();
$articles = [];
$hasTable = false;

try {
  $check = $pdo->query("SELECT COUNT(*) FROM pef_articles");
  $hasTable = true;
  $articles = $pdo->query(
    "SELECT id, titre, theme, image_couverture_url,
                contenu_html, date_publication
         FROM pef_articles
         ORDER BY date_publication DESC"
  )->fetchAll();
} catch (Throwable $e) {
  // table absente → on affiche quand même la page
}

// Données JSON pour la modale JS
$articlesJson = json_encode($articles, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);

include __DIR__ . "/header.php";
?>

<!-- Tailwind CDN -->
<script src="https://cdn.tailwindcss.com"></script>
<script>
  tailwind.config = {
    theme: {
      extend: {
        colors: {
          rouge: { DEFAULT: '#d32f2f', dark: '#8A1D19', light: '#e57373' },
          noir: { DEFAULT: '#130201' },
          gris: { DEFAULT: '#f4f4f9' },
          marine: { DEFAULT: '#173f75' }
        },
        fontFamily: {
          montserrat: ['"Montserrat"', 'sans-serif'],
          roboto: ['"Roboto"', 'sans-serif'],
          bebas: ['"Bebas Neue"', 'cursive']
        }
      }
    }
  }
</script>

<style>
  :root {
    --pef-red: #d32f2f;
    --pef-red-dark: #8a1d19;
    --pef-ink: #130201;
    --pef-paper: #f4f4f9;
  }

  body {
    background-color: var(--pef-paper);
    font-family: 'Roboto', sans-serif;
  }

  h1, h2, h3, h4, h5, h6 {
    font-family: 'Montserrat', sans-serif;
  }

  /* ── Hero ── */
  .pef-hero-bg {
    background-color: #130201;
    background-image: linear-gradient(rgba(19, 2, 1, 0.8), rgba(19, 2, 1, 0.9)), url('https://images.unsplash.com/photo-1518370210600-6e92e4e8b0f3?w=1600&q=80');
    background-size: cover;
    background-position: center;
    border-bottom: 4px solid var(--pef-red);
  }

  .pef-hero-kicker {
    border: 1px solid rgba(255, 255, 255, 0.3);
  }

  /* ── Pilier cards ── */
  .pef-pilier-card {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
    border: 2px solid transparent;
  }

  .pef-pilier-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    border-color: var(--pef-red);
  }

  .pef-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 60px;
    height: 60px;
    border-radius: 50%;
    margin-bottom: 15px;
  }

  /* ── Blog ── */
  .pef-blog-card {
    cursor: pointer;
    background: #ffffff;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.05);
    transition: transform .2s ease, box-shadow .2s ease;
    border: 1px solid #e0e0e0;
  }

  .pef-blog-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 25px rgba(0,0,0,0.1);
    border-color: var(--pef-red);
  }

  /* ── Category badges ── */
  .pef-cat-badge {
    font-size: .75rem;
    font-weight: 600;
    text-transform: uppercase;
    font-family: 'Montserrat', sans-serif;
  }

  .cat-sante { background: #e8f5e9; color: #2e7d32; }
  .cat-citoyen { background: #e3f2fd; color: #1565c0; }
  .cat-environnement { background: #e0f2f1; color: #00695c; }
  .cat-fairplay { background: #fff8e1; color: #f57f17; }
  .cat-regles { background: #f3e5f5; color: #6a1b9a; }
  .cat-culture { background: #ffebee; color: #c62828; }

  /* ── Modal & Lightbox ── */
  #pef-modal {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 9999;
    background: rgba(0, 0, 0, .8);
    align-items: center;
    justify-content: center;
    padding: 16px;
  }

  #pef-modal.open {
    display: flex;
  }

  #pef-modal-inner {
    background: #fff;
    border-radius: 12px;
    max-width: 800px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 10px 30px rgba(0, 0, 0, .5);
  }

  #pef-modal-img {
    width: 100%;
    height: 300px;
    object-fit: cover;
    display: block;
  }

  #pef-modal-body {
    padding: 30px;
  }

  #pef-modal-body h2 {
    font-family: 'Montserrat', sans-serif;
    font-size: 2rem;
    font-weight: 700;
    color: #130201;
    margin: 10px 0 15px;
  }

  #pef-modal-content {
    font-family: 'Roboto', sans-serif;
    font-size: 1rem;
    line-height: 1.6;
    color: #444;
  }

  #pef-modal-content img {
    max-width: 100%;
    border-radius: 8px;
    margin: 15px 0;
    cursor: zoom-in;
  }

  /* Lightbox Image Zoom */
  #pef-lightbox {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 10000;
    background: rgba(0, 0, 0, .95);
    align-items: center;
    justify-content: center;
    cursor: zoom-out;
  }

  #pef-lightbox.open { display: flex; }

  #pef-lightbox img {
    max-width: 90%;
    max-height: 90%;
    object-fit: contain;
    border-radius: 4px;
  }

  #pef-lightbox-dl {
    position: absolute;
    bottom: 30px;
    left: 50%;
    transform: translateX(-50%);
    background: var(--pef-red);
    color: #fff;
    padding: 10px 20px;
    border-radius: 24px;
    text-decoration: none;
    font-family: 'Montserrat', sans-serif;
    font-weight: 600;
    font-size: 0.9rem;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: background .2s;
  }

  #pef-lightbox-dl:hover { background: var(--pef-red-dark); }

  #pef-modal-close {
    position: absolute;
    top: 20px;
    right: 20px;
    background: var(--pef-red);
    color: #fff;
    border: none;
    border-radius: 50%;
    width: 40px;
    height: 40px;
    font-size: 1.2rem;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background .2s;
    z-index: 10;
  }

  #pef-modal-close:hover { background: var(--pef-red-dark); }
  #pef-modal-inner-wrap { position: relative; width: 100%; max-width: 800px; }

</style>

<!-- ═══ SECTION 1 — HERO ═══════════════════════════════════════════════════ -->
<section class="pef-hero-bg relative pt-32 pb-24 px-4 text-center">
  <div class="max-w-4xl mx-auto relative z-10">
    <div class="flex justify-center mb-6">
      <span class="pef-hero-kicker inline-flex items-center gap-2 bg-black bg-opacity-40 text-white px-4 py-2 rounded-full text-xs font-semibold uppercase tracking-widest font-montserrat">
        <i class="fa-solid fa-star text-rouge"></i> Programme Officiel FFF
      </span>
    </div>

    <h1 class="font-montserrat font-bold text-5xl md:text-6xl text-white mb-6 uppercase">
      PEF <span class="text-rouge">Cadets</span>
    </h1>
    
    <p class="font-roboto text-lg md:text-xl text-gray-200 leading-relaxed max-w-2xl mx-auto mb-8">
      Le <strong class="text-white">Programme Éducatif Fédéral</strong> de la FFF accompagne nos jeunes joueurs bien au-delà du terrain : citoyenneté, respect, valeurs et ouverture sur le monde.
    </p>
    
    <div class="inline-flex items-center gap-4 bg-white px-6 py-4 rounded-xl shadow-lg text-noir font-roboto text-sm md:text-base font-medium max-w-lg mx-auto text-left">
      <div class="w-12 h-12 rounded-full bg-rouge bg-opacity-10 text-rouge flex items-center justify-center flex-shrink-0">
        <i class="fa-solid fa-calendar-check text-xl"></i>
      </div>
      <span>Chaque <strong>dernier mercredi du mois</strong>, une activité PEF spéciale est organisée !</span>
    </div>
  </div>
</section>

<!-- ═══ SECTION 2 — 6 PILIERS ═══════════════════════════════════════════════ -->
<section class="py-16 px-4">
  <div class="max-w-6xl mx-auto">
    <div class="text-center mb-12">
      <span class="text-rouge font-montserrat font-semibold uppercase tracking-widest text-sm">Les fondements</span>
      <h2 class="font-montserrat font-bold text-3xl md:text-4xl text-noir mt-2">Les 6 Piliers du PEF</h2>
      <div class="w-16 h-1 bg-rouge mx-auto mt-4 mb-4"></div>
      <p class="text-gray-600 max-w-xl mx-auto font-roboto">Chaque pilier guide nos joueurs dans leur développement personnel et collectif.</p>
    </div>
    
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php
      $piliers = [
        ['Santé', 'fa-heart-pulse', 'bg-green-100', 'text-green-700', 'Promouvoir une hygiène de vie saine : nutrition, sommeil, activité physique et bien-être mental.'],
        ['Engagement Citoyen', 'fa-hand-holding-heart', 'bg-blue-100', 'text-blue-700', 'Développer la solidarité, la responsabilité collective et l\'implication dans la vie sociale locale.'],
        ['Environnement', 'fa-leaf', 'bg-teal-100', 'text-teal-700', 'Sensibiliser à l\'écologie, à la protection de la nature et aux éco-gestes quotidiens.'],
        ['Fair-Play', 'fa-handshake', 'bg-orange-100', 'text-orange-700', 'Respecter l\'adversaire, l\'arbitre et les règles du jeu : le fair-play est la marque des grands champions.'],
        ['Règles du Jeu', 'fa-book-open', 'bg-purple-100', 'text-purple-700', 'Comprendre et maîtriser les règles du football pour mieux jouer et mieux arbitrer.'],
        ['Culture Foot', 'fa-futbol', 'bg-red-100', 'text-red-700', 'Découvrir l\'histoire du football, ses grandes figures et son rôle dans la société mondiale.'],
      ];
      foreach ($piliers as [$nom, $icon, $bg, $col, $desc]):
        ?>
        <div class="pef-pilier-card p-6">
          <div class="pef-icon <?= $bg ?> <?= $col ?>">
            <i class="fa-solid <?= $icon ?> text-2xl"></i>
          </div>
          <h3 class="font-montserrat font-bold text-xl text-noir mb-3"><?= $nom ?></h3>
          <p class="text-gray-600 text-sm leading-relaxed font-roboto"><?= $desc ?></p>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ═══ SECTION 3 — RAPPEL FFF ══════════════════════════════════════════════ -->
<section class="bg-white py-10 px-4 border-y border-gray-200">
  <div class="max-w-4xl mx-auto flex flex-col md:flex-row items-center gap-6 justify-center text-center md:text-left">
    <div class="w-16 h-16 rounded-full bg-blue-50 flex items-center justify-center flex-shrink-0 border border-blue-100">
      <i class="fa-solid fa-globe text-blue-800 text-2xl"></i>
    </div>
    <div>
      <h3 class="font-montserrat font-bold text-xl text-noir mb-2">Partagé chaque mois sur le site de la FFF</h3>
      <p class="text-gray-600 font-roboto">
        Nos actions PEF sont régulièrement recensées et partagées sur le
        <a href="https://pef.fff.fr" target="_blank" rel="noopener noreferrer" class="text-rouge font-semibold hover:underline">
          site officiel du PEF de la FFF <i class="fa-solid fa-arrow-up-right-from-square text-xs ml-1"></i>
        </a>.
      </p>
    </div>
  </div>
</section>

<!-- ═══ SECTION 4 — BLOG ════════════════════════════════════════════════════ -->
<section class="py-16 px-4">
  <div class="max-w-6xl mx-auto">
    <div class="mb-10">
      <span class="text-rouge font-montserrat font-semibold uppercase tracking-widest text-sm">Actualités</span>
      <h2 class="font-montserrat font-bold text-3xl md:text-4xl text-noir mt-2">Nos Actions en Images</h2>
      <div class="w-16 h-1 bg-rouge mt-4"></div>
    </div>

    <?php
    $catCss = [
      'Santé' => ['cat-sante', 'fa-heart-pulse'],
      'Engagement Citoyen' => ['cat-citoyen', 'fa-hand-holding-heart'],
      'Environnement' => ['cat-environnement', 'fa-leaf'],
      'Fair-Play' => ['cat-fairplay', 'fa-handshake'],
      'Règles du Jeu' => ['cat-regles', 'fa-book-open'],
      'Culture Foot' => ['cat-culture', 'fa-futbol'],
    ];
    $defaultImgs = [
      'Santé' => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=600&q=80',
      'Engagement Citoyen' => 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=600&q=80',
      'Environnement' => 'https://images.unsplash.com/photo-1464692805480-a69dfaafdb0d?w=600&q=80',
      'Fair-Play' => 'https://images.unsplash.com/photo-1547347298-4074ad3086f0?w=600&q=80',
      'Règles du Jeu' => 'https://images.unsplash.com/photo-1431324155629-1a6debb1a764?w=600&q=80',
      'Culture Foot' => 'https://images.unsplash.com/photo-1518370210600-6e92e4e8b0f3?w=600&q=80',
    ];

    if (!empty($articles)): ?>
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
        <?php foreach ($articles as $a):
          [$cls, $ico] = $catCss[$a['theme']] ?? ['cat-culture', 'fa-futbol'];
          $img = !empty($a['image_couverture_url']) ? htmlspecialchars($a['image_couverture_url']) : ($defaultImgs[$a['theme']] ?? $defaultImgs['Culture Foot']);
          $date = (new DateTime($a['date_publication']))->format('d/m/Y');
          $texte_brut = html_entity_decode(strip_tags($a['contenu_html']), ENT_QUOTES | ENT_HTML5, 'UTF-8');
          $resume = mb_strimwidth($texte_brut, 0, 120, '…');
          ?>
          <article class="pef-blog-card flex flex-col" onclick="pefOpenModal(<?= (int) $a['id'] ?>)">
            <div class="h-48 overflow-hidden bg-gray-100">
              <img src="<?= $img ?>" alt="<?= htmlspecialchars($a['titre']) ?>" class="w-full h-full object-cover" onerror="this.src='<?= $defaultImgs['Culture Foot'] ?>'">
            </div>
            <div class="p-5 flex flex-col flex-grow">
              <div class="flex items-center justify-between mb-3">
                <span class="pef-cat-badge px-2 py-1 rounded <?= $cls ?>">
                  <i class="fa-solid <?= $ico ?> mr-1"></i> <?= htmlspecialchars($a['theme']) ?>
                </span>
                <time class="text-gray-500 text-xs font-roboto"><?= $date ?></time>
              </div>
              <h3 class="font-montserrat font-bold text-lg text-noir mb-2"><?= htmlspecialchars($a['titre']) ?></h3>
              <p class="text-gray-600 text-sm flex-grow font-roboto"><?= htmlspecialchars($resume) ?></p>
              <div class="mt-4 text-rouge text-sm font-semibold font-montserrat uppercase flex items-center">
                Lire la suite <i class="fa-solid fa-chevron-right ml-1 text-xs"></i>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>

    <?php else: ?>
      <div class="text-center py-16 bg-white rounded-lg border border-gray-200">
        <i class="fa-regular fa-folder-open text-gray-300 text-5xl mb-4"></i>
        <p class="text-gray-500 font-montserrat font-medium text-lg">Aucune action publiée pour le moment.</p>
        <p class="text-gray-400 text-sm mt-1">Les prochaines activités PEF apparaîtront ici.</p>
      </div>
    <?php endif; ?>

  </div>
</section>

<!-- ═══ MODALE ════════════════════════════════════════════════════════════════ -->
<div id="pef-modal" onclick="pefCloseOnOverlay(event)">
  <div id="pef-modal-inner-wrap">
    <button id="pef-modal-close" onclick="pefCloseModal()">
      <i class="fa-solid fa-xmark"></i>
    </button>
    <div id="pef-modal-inner">
      <img id="pef-modal-img" src="" alt="">
      <div id="pef-modal-body">
        <span id="pef-modal-badge" class="pef-cat-badge px-2 py-1 rounded inline-block mb-3"></span>
        <div id="pef-modal-date" class="text-gray-500 text-sm mb-2 font-roboto"></div>
        <h2 id="pef-modal-title"></h2>
        <div class="w-12 h-1 bg-rouge mb-4"></div>
        <div id="pef-modal-content"></div>
      </div>
    </div>
  </div>
</div>

<!-- ═══ LIGHTBOX (ZOOM) ══════════════════════════════════════════════════════ -->
<div id="pef-lightbox" onclick="pefCloseLightbox()">
  <img id="pef-lightbox-img" src="" alt="">
  <a id="pef-lightbox-dl" href="" download onclick="event.stopPropagation()">
    <i class="fa-solid fa-download"></i> Télécharger l'image
  </a>
</div>

<?php include __DIR__ . "/footer.php"; ?>

<script>
  const PEF_ARTICLES = <?= $articlesJson ?>;
  const CAT_CSS = {
    'Santé': 'cat-sante',
    'Engagement Citoyen': 'cat-citoyen',
    'Environnement': 'cat-environnement',
    'Fair-Play': 'cat-fairplay',
    'Règles du Jeu': 'cat-regles',
    'Culture Foot': 'cat-culture',
  };
  const CAT_ICONS = {
    'Santé': 'fa-heart-pulse',
    'Engagement Citoyen': 'fa-hand-holding-heart',
    'Environnement': 'fa-leaf',
    'Fair-Play': 'fa-handshake',
    'Règles du Jeu': 'fa-book-open',
    'Culture Foot': 'fa-futbol',
  };
  const DEFAULT_IMG = 'https://images.unsplash.com/photo-1518370210600-6e92e4e8b0f3?w=800&q=80';

  function pefOpenModal(id) {
    const art = PEF_ARTICLES.find(a => parseInt(a.id) === id);
    if (!art) return;

    document.getElementById('pef-modal-img').src = art.image_couverture_url || DEFAULT_IMG;
    
    const cls = CAT_CSS[art.theme] || 'cat-culture';
    const icon = CAT_ICONS[art.theme] || 'fa-futbol';
    const badge = document.getElementById('pef-modal-badge');
    badge.className = `pef-cat-badge px-2 py-1 rounded inline-block mb-3 ${cls}`;
    badge.innerHTML = `<i class="fa-solid ${icon} mr-1"></i> ${art.theme}`;

    const d = new Date(art.date_publication);
    document.getElementById('pef-modal-date').textContent = d.toLocaleDateString('fr-FR', { day: 'numeric', month: 'long', year: 'numeric' });
    document.getElementById('pef-modal-title').textContent = art.titre;
    
    const cont = document.getElementById('pef-modal-content');
    cont.innerHTML = art.contenu_html;

    cont.querySelectorAll('img').forEach(image => {
      image.addEventListener('click', (e) => {
        e.stopPropagation();
        pefOpenLightbox(image.src);
      });
    });

    document.getElementById('pef-modal').classList.add('open');
    document.body.style.overflow = 'hidden';
    document.getElementById('pef-modal-inner').scrollTop = 0;
  }

  function pefOpenLightbox(src) {
    document.getElementById('pef-lightbox-img').src = src;
    document.getElementById('pef-lightbox-dl').href = src;
    document.getElementById('pef-lightbox').classList.add('open');
  }

  function pefCloseLightbox() {
    document.getElementById('pef-lightbox').classList.remove('open');
  }

  function pefCloseModal() {
    document.getElementById('pef-modal').classList.remove('open');
    document.body.style.overflow = '';
  }

  function pefCloseOnOverlay(e) {
    if (e.target === document.getElementById('pef-modal')) pefCloseModal();
  }

  document.addEventListener('keydown', e => {
    if (e.key === 'Escape') {
      if (document.getElementById('pef-lightbox').classList.contains('open')) pefCloseLightbox();
      else pefCloseModal();
    }
  });
</script>