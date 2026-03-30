<?php
$page_title = "PEF - Programme Éducatif Fédéral";
require_once __DIR__ . "/fonctions.php";

// ── Lecture des articles ──────────────────────────────────────────────────
$pdo      = getDB();
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
        rouge: { DEFAULT:'#d32f2f', dark:'#8A1D19', light:'#e57373' },
        noir:  { DEFAULT:'#130201' },
        gris:  { DEFAULT:'#f4f4f9' },
      },
      fontFamily: {
        bebas: ['"Bebas Neue"','cursive'],
        roboto:['"Roboto"','sans-serif'],
      }
    }
  }
}
</script>

<style>
/* ── Hero ── */
.pef-hero-bg { background:linear-gradient(135deg,#130201 0%,#1e0302 45%,#2a0403 75%,#130201 100%); }
.pef-gradient-text {
  background:linear-gradient(135deg,#d32f2f,#e57373,#d32f2f);
  -webkit-background-clip:text; -webkit-text-fill-color:transparent; background-clip:text;
}
.pef-hero-wave { position:absolute; bottom:-1px; left:0; width:100%; overflow:hidden; line-height:0; }

/* ── Pilier cards ── */
.pef-pilier-card { transition:transform .3s,box-shadow .3s,border-color .3s; }
.pef-pilier-card:hover { transform:translateY(-8px) scale(1.02); box-shadow:0 20px 40px rgba(211,47,47,.25); border-color:#d32f2f; }
.pef-pilier-card:hover .pef-icon { background:#d32f2f; transform:scale(1.15) rotate(-5deg); }
.pef-icon { transition:background .3s,transform .3s; }

/* ── Blog ── */
.pef-blog-card { cursor:pointer; transition:transform .3s,box-shadow .3s; }
.pef-blog-card:hover { transform:translateY(-6px); box-shadow:0 20px 40px rgba(0,0,0,.18); }

/* ── Category badges ── */
.pef-cat-badge { font-size:.72rem; font-weight:700; letter-spacing:.07em; text-transform:uppercase; }
.cat-sante        { background:#dcfce7; color:#166534; }
.cat-citoyen      { background:#dbeafe; color:#1e40af; }
.cat-environnement{ background:#d1fae5; color:#065f46; }
.cat-fairplay     { background:#fef9c3; color:#854d0e; }
.cat-regles       { background:#ede9fe; color:#4c1d95; }
.cat-culture      { background:#fee2e2; color:#991b1b; }

/* ── Fade-in ── */
.pef-appear { opacity:0; transform:translateY(24px); transition:opacity .6s,transform .6s; }
.pef-appear.is-visible { opacity:1; transform:none; }

/* ── Modal & Lightbox ── */
#pef-modal {
  display:none; position:fixed; inset:0; z-index:9999;
  background:rgba(0,0,0,.82); backdrop-filter:blur(4px);
  align-items:center; justify-content:center; padding:16px;
}
#pef-modal.open { display:flex; }
#pef-modal-inner {
  background:#fff; border-radius:20px; max-width:860px; width:100%;
  max-height:90vh; overflow-y:auto;
  box-shadow:0 30px 80px rgba(0,0,0,.5);
  animation:modalIn .25s ease;
}
@keyframes modalIn { from{opacity:0;transform:scale(.95)} to{opacity:1;transform:scale(1)} }
#pef-modal-img { width:100%; height:280px; object-fit:cover; border-radius:20px 20px 0 0; display:block; }
#pef-modal-body { padding:28px; }
#pef-modal-body h2 { font-family:"Bebas Neue",cursive; font-size:2rem; color:#130201; letter-spacing:.05em; margin:0 0 6px; }
#pef-modal-content { font-size:.97rem; line-height:1.75; color:#333; margin-top:16px; }
#pef-modal-content img { max-width:100%; border-radius:12px; margin:12px 0; cursor: zoom-in; transition: transform .2s; }
#pef-modal-content img:hover { transform: scale(1.01); }

/* Lightbox Image Zoom */
#pef-lightbox {
  display:none; position:fixed; inset:0; z-index:10000;
  background:rgba(0,0,0,.95); align-items:center; justify-content:center;
  cursor: zoom-out;
}
#pef-lightbox.open { display:flex; }
#pef-lightbox img { max-width:90%; max-height:90%; object-fit:contain; border-radius:8px; box-shadow:0 0 40px rgba(0,0,0,.5); }
#pef-lightbox-dl {
  position:absolute; bottom:30px; left:50%; transform:translateX(-50%);
  background:#d32f2f; color:#fff; padding:10px 20px; border-radius:30px;
  text-decoration:none; font-weight:bold; font-size:0.9rem;
  display:flex; align-items:center; gap:8px; transition: background .2s;
}
#pef-lightbox-dl:hover { background:#8A1D19; }

#pef-modal-close {
  position:absolute; top:16px; right:20px;
  background:#d32f2f; color:#fff; border:none; border-radius:50%;
  width:40px; height:40px; font-size:1.4rem; cursor:pointer;
  display:flex; align-items:center; justify-content:center;
  box-shadow:0 4px 12px rgba(211,47,47,.5);
  transition:background .2s;
  z-index:10;
}
#pef-modal-close:hover { background:#8A1D19; }
#pef-modal-inner-wrap { position:relative; }
</style>

<!-- ═══ SECTION 1 — HERO ═══════════════════════════════════════════════════ -->
<section class="pef-hero-bg relative pt-28 pb-24 px-4 overflow-hidden font-roboto">
  <div class="absolute top-0 left-0 w-80 h-80 rounded-full opacity-10 blur-3xl" style="background:#d32f2f;transform:translate(-40%,-40%)"></div>
  <div class="absolute bottom-0 right-0 w-96 h-96 rounded-full opacity-10 blur-3xl" style="background:#d32f2f;transform:translate(40%,40%)"></div>

  <div class="flex justify-center mb-6">
    <span class="inline-flex items-center gap-2 bg-white bg-opacity-10 border border-white border-opacity-20 text-white px-4 py-1.5 rounded-full text-sm font-semibold uppercase tracking-widest">
      <i class="fa-solid fa-star text-rouge"></i> Programme Officiel FFF
    </span>
  </div>

  <div class="max-w-4xl mx-auto text-center">
    <h1 class="font-bebas text-6xl md:text-8xl text-white tracking-wider mb-4">
      PEF <span class="pef-gradient-text">Cadets</span>
    </h1>
    <p class="text-lg md:text-xl text-gray-300 leading-relaxed max-w-2xl mx-auto mb-8">
      Le <strong class="text-white">Programme Éducatif Fédéral</strong> de la FFF accompagne nos jeunes joueurs bien au-delà du terrain&nbsp;: citoyenneté, respect, valeurs et ouverture sur le monde.
    </p>
    <div class="inline-flex items-center gap-3 bg-white bg-opacity-10 backdrop-blur border border-white border-opacity-20 rounded-2xl px-6 py-4 text-white text-sm md:text-base font-semibold">
      <div class="w-10 h-10 rounded-full bg-rouge flex items-center justify-center flex-shrink-0">
        <i class="fa-solid fa-calendar-check"></i>
      </div>
      <span>Chaque <strong>dernier mercredi du mois</strong>, une activité PEF spéciale est organisée&nbsp;!</span>
    </div>
  </div>

  <div class="pef-hero-wave">
    <svg viewBox="0 0 1440 60" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none">
      <path d="M0,30 C360,60 1080,0 1440,30 L1440,60 L0,60 Z" fill="#f4f4f9"/>
    </svg>
  </div>
</section>

<!-- ═══ SECTION 2 — 6 PILIERS ═══════════════════════════════════════════════ -->
<section class="bg-gris py-20 px-4 font-roboto">
  <div class="max-w-6xl mx-auto">
    <div class="text-center mb-14 pef-appear">
      <span class="text-rouge font-semibold uppercase tracking-widest text-sm">Les fondements du programme</span>
      <h2 class="font-bebas text-4xl md:text-5xl text-noir mt-2 tracking-wide">Les 6 Piliers du PEF</h2>
      <p class="text-gray-500 mt-3 max-w-xl mx-auto text-sm md:text-base">Chaque pilier guide nos joueurs dans leur développement personnel et collectif.</p>
    </div>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php
      $piliers = [
        ['Santé',              'fa-heart-pulse',        'bg-green-100',  'text-green-600',  'Promouvoir une hygiène de vie saine&nbsp;: nutrition, sommeil, activité physique et bien-être mental.'],
        ['Engagement Citoyen', 'fa-hand-holding-heart', 'bg-blue-100',   'text-blue-600',   'Développer la solidarité, la responsabilité collective et l\'implication dans la vie sociale locale.'],
        ['Environnement',      'fa-leaf',               'bg-emerald-100','text-emerald-600','Sensibiliser à l\'écologie, à la protection de la nature et aux éco-gestes quotidiens.'],
        ['Fair-Play',          'fa-handshake',          'bg-yellow-100', 'text-yellow-600', 'Respecter l\'adversaire, l\'arbitre et les règles du jeu&nbsp;: le fair-play est la marque des grands champions.'],
        ['Règles du Jeu',      'fa-book-open',          'bg-purple-100', 'text-purple-600', 'Comprendre et maîtriser les règles du football pour mieux jouer et mieux arbitrer.'],
        ['Culture Foot',       'fa-futbol',             'bg-red-100',    'text-rouge',      'Découvrir l\'histoire du football, ses grandes figures et son rôle dans la société mondiale.'],
      ];
      foreach ($piliers as $i => [$nom, $icon, $bg, $col, $desc]):
      ?>
      <div class="pef-pilier-card bg-white rounded-2xl shadow-md border-2 border-transparent p-6 cursor-default pef-appear" style="animation-delay:<?= $i * 0.06 ?>s">
        <div class="pef-icon w-14 h-14 rounded-xl <?= $bg ?> flex items-center justify-center mb-4">
          <i class="fa-solid <?= $icon ?> <?= $col ?> text-2xl"></i>
        </div>
        <h3 class="font-bebas text-2xl text-noir tracking-wide mb-2"><?= $nom ?></h3>
        <p class="text-gray-500 text-sm leading-relaxed"><?= $desc ?></p>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</section>

<!-- ═══ SECTION 3 — RAPPEL FFF ══════════════════════════════════════════════ -->
<section class="bg-white py-12 px-4 font-roboto border-y border-gray-100">
  <div class="max-w-3xl mx-auto flex flex-col sm:flex-row items-center gap-6 pef-appear">
    <div class="w-16 h-16 rounded-2xl bg-rouge flex items-center justify-center flex-shrink-0 shadow-lg">
      <i class="fa-solid fa-globe text-white text-3xl"></i>
    </div>
    <div>
      <h3 class="font-bebas text-2xl text-noir tracking-wide mb-1">Partagé chaque mois sur le site de la FFF</h3>
      <p class="text-gray-500 text-sm leading-relaxed">
        Chaque début de mois, nos actions PEF sont recensées et partagées sur le
        <a href="https://www.fff.fr/education/pef" target="_blank" rel="noopener noreferrer" class="text-rouge font-semibold hover:underline">
          site officiel du PEF de la FFF <i class="fa-solid fa-arrow-up-right-from-square text-xs"></i>
        </a>.
      </p>
    </div>
  </div>
</section>

<!-- ═══ SECTION 4 — BLOG ════════════════════════════════════════════════════ -->
<section class="bg-gris py-20 px-4 font-roboto">
  <div class="max-w-6xl mx-auto">
    <div class="mb-12 pef-appear">
      <span class="text-rouge font-semibold uppercase tracking-widest text-sm">Fil d'actualité</span>
      <h2 class="font-bebas text-4xl md:text-5xl text-noir mt-1 tracking-wide">Nos Actions en Images</h2>
    </div>

    <?php
    $catCss = [
      'Santé'              => ['cat-sante',         'fa-heart-pulse'],
      'Engagement Citoyen' => ['cat-citoyen',        'fa-hand-holding-heart'],
      'Environnement'      => ['cat-environnement',  'fa-leaf'],
      'Fair-Play'          => ['cat-fairplay',       'fa-handshake'],
      'Règles du Jeu'      => ['cat-regles',         'fa-book-open'],
      'Culture Foot'       => ['cat-culture',        'fa-futbol'],
    ];
    $defaultImgs = [
      'Santé'              => 'https://images.unsplash.com/photo-1571019613454-1cb2f99b2d8b?w=600&q=80',
      'Engagement Citoyen' => 'https://images.unsplash.com/photo-1529156069898-49953e39b3ac?w=600&q=80',
      'Environnement'      => 'https://images.unsplash.com/photo-1464692805480-a69dfaafdb0d?w=600&q=80',
      'Fair-Play'          => 'https://images.unsplash.com/photo-1547347298-4074ad3086f0?w=600&q=80',
      'Règles du Jeu'      => 'https://images.unsplash.com/photo-1431324155629-1a6debb1a764?w=600&q=80',
      'Culture Foot'       => 'https://images.unsplash.com/photo-1518370210600-6e92e4e8b0f3?w=600&q=80',
    ];

    if (!empty($articles)): ?>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8">
      <?php foreach ($articles as $i => $a):
        [$cls, $ico] = $catCss[$a['theme']] ?? ['cat-culture','fa-futbol'];
        $img  = !empty($a['image_couverture_url']) ? htmlspecialchars($a['image_couverture_url']) : ($defaultImgs[$a['theme']] ?? $defaultImgs['Culture Foot']);
        $date = (new DateTime($a['date_publication']))->format('d/m/Y');
        // Court résumé : on retire les tags HTML et on tronque
        $resume = mb_strimwidth(strip_tags($a['contenu_html']), 0, 140, '…');
      ?>
      <article
        class="pef-blog-card pef-appear bg-white rounded-2xl shadow-md overflow-hidden border border-gray-100"
        style="animation-delay:<?= $i * 0.07 ?>s"
        data-id="<?= (int)$a['id'] ?>"
        onclick="pefOpenModal(<?= (int)$a['id'] ?>)"
        role="button"
        tabindex="0"
        aria-label="Lire l'article : <?= htmlspecialchars($a['titre']) ?>">
        <div class="relative h-48 overflow-hidden">
          <img src="<?= $img ?>" alt="<?= htmlspecialchars($a['titre']) ?>" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
               onerror="this.src='<?= $defaultImgs['Culture Foot'] ?>'">
          <div class="absolute inset-0 bg-gradient-to-t from-black/40 to-transparent"></div>
        </div>
        <div class="p-5">
          <div class="flex items-center justify-between mb-3">
            <span class="pef-cat-badge inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full <?= $cls ?>">
              <i class="fa-solid <?= $ico ?> text-[0.65rem]"></i> <?= htmlspecialchars($a['theme']) ?>
            </span>
            <time class="text-gray-400 text-xs"><?= $date ?></time>
          </div>
          <h3 class="font-bebas text-xl text-noir tracking-wide mb-2 leading-tight"><?= htmlspecialchars($a['titre']) ?></h3>
          <p class="text-gray-500 text-sm leading-relaxed"><?= htmlspecialchars($resume) ?></p>
          <span class="mt-3 inline-flex items-center gap-1 text-rouge text-xs font-semibold">
            Lire la suite <i class="fa-solid fa-arrow-right text-[0.6rem]"></i>
          </span>
        </div>
      </article>
      <?php endforeach; ?>
    </div>

    <?php else: ?>
    <div class="text-center py-20 pef-appear">
      <div class="w-20 h-20 rounded-full bg-gray-100 flex items-center justify-center mx-auto mb-4">
        <i class="fa-solid fa-image text-gray-300 text-3xl"></i>
      </div>
      <p class="text-gray-400 font-semibold">Aucune action publiée pour le moment.</p>
      <p class="text-gray-300 text-sm mt-1">Les prochaines activités PEF apparaîtront ici.</p>
    </div>
    <?php endif; ?>

  </div>
</section>

<!-- ═══ MODALE ════════════════════════════════════════════════════════════════ -->
<div id="pef-modal" role="dialog" aria-modal="true" aria-labelledby="pef-modal-title" onclick="pefCloseOnOverlay(event)">
  <div id="pef-modal-inner-wrap">
    <button id="pef-modal-close" onclick="pefCloseModal()" aria-label="Fermer">&#10005;</button>
    <div id="pef-modal-inner">
      <img id="pef-modal-img" src="" alt="">
      <div id="pef-modal-body">
        <span id="pef-modal-badge" class="pef-cat-badge inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full mb-3"></span>
        <p id="pef-modal-date" class="text-gray-400 text-xs mb-2"></p>
        <h2 id="pef-modal-title"></h2>
        <hr class="border-gray-100 my-3">
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
// Articles PHP → JS (pour la modale)
const PEF_ARTICLES = <?= $articlesJson ?>;

const CAT_CSS = {
  'Santé':              'cat-sante',
  'Engagement Citoyen': 'cat-citoyen',
  'Environnement':      'cat-environnement',
  'Fair-Play':          'cat-fairplay',
  'Règles du Jeu':      'cat-regles',
  'Culture Foot':       'cat-culture',
};
const CAT_ICONS = {
  'Santé':              'fa-heart-pulse',
  'Engagement Citoyen': 'fa-hand-holding-heart',
  'Environnement':      'fa-leaf',
  'Fair-Play':          'fa-handshake',
  'Règles du Jeu':      'fa-book-open',
  'Culture Foot':       'fa-futbol',
};
const DEFAULT_IMG = 'https://images.unsplash.com/photo-1518370210600-6e92e4e8b0f3?w=800&q=80';

function pefOpenModal(id) {
  const art = PEF_ARTICLES.find(a => parseInt(a.id) === id);
  if (!art) return;

  const modal = document.getElementById('pef-modal');
  const img   = document.getElementById('pef-modal-img');
  const badge = document.getElementById('pef-modal-badge');
  const date  = document.getElementById('pef-modal-date');
  const title = document.getElementById('pef-modal-title');
  const cont  = document.getElementById('pef-modal-content');

  img.src = art.image_couverture_url || DEFAULT_IMG;
  img.alt = art.titre;

  const cls  = CAT_CSS[art.theme]  || 'cat-culture';
  const icon = CAT_ICONS[art.theme] || 'fa-futbol';
  badge.className = `pef-cat-badge inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full ${cls}`;
  badge.innerHTML = `<i class="fa-solid ${icon} text-[0.65rem]"></i> ${art.theme}`;

  const d = new Date(art.date_publication);
  date.textContent = d.toLocaleDateString('fr-FR', { day:'numeric', month:'long', year:'numeric' });
  title.textContent = art.titre;
  cont.innerHTML = art.contenu_html;

  // Ajout du clic sur les images pour le zoom
  cont.querySelectorAll('img').forEach(image => {
    image.addEventListener('click', (e) => {
      e.stopPropagation();
      pefOpenLightbox(image.src);
    });
  });

  modal.classList.add('open');
  document.body.style.overflow = 'hidden';
  document.getElementById('pef-modal-inner').scrollTop = 0;
}

function pefOpenLightbox(src) {
  const lb    = document.getElementById('pef-lightbox');
  const lbImg = document.getElementById('pef-lightbox-img');
  const lbDl  = document.getElementById('pef-lightbox-dl');
  
  lbImg.src = src;
  lbDl.href = src;
  lb.classList.add('open');
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

// Escape key
document.addEventListener('keydown', e => { 
  if (e.key === 'Escape') {
    if (document.getElementById('pef-lightbox').classList.contains('open')) pefCloseLightbox();
    else pefCloseModal();
  }
});

// Keyboard accessibility on cards
document.querySelectorAll('.pef-blog-card').forEach(card => {
  card.addEventListener('keydown', e => {
    if (e.key === 'Enter' || e.key === ' ') {
      e.preventDefault();
      card.click();
    }
  });
});

// Fade-in observer
const obs = new IntersectionObserver(entries => {
  entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('is-visible'); obs.unobserve(e.target); } });
}, { threshold: 0.1 });
document.querySelectorAll('.pef-appear').forEach(el => obs.observe(el));
</script>
