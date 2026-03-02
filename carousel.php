<link rel="stylesheet" href= "assets/css/carousel.css">
<link rel="stylesheet" href="assets/css/footer.css">


<div class="site-footer">
    <div class="carousel-wrapper">
        <button class="prev-btn" id="prevBtn">❮</button>
        
        <div class="carousel-container" id="carouselContainer">
            <div class="carousel-track" id="carouselTrack">
                <?php
                require_once __DIR__ . '/sponsors_store.php';
                $sponsors = loadSponsors();

                foreach ($sponsors as $sponsor) {
                    $logo = (string)($sponsor['image'] ?? '');
                    $url = (string)($sponsor['url'] ?? '');
                    if ($logo === '' || !file_exists(__DIR__ . '/' . ltrim($logo, '/'))) {
                        continue;
                    }

                    $logoSafe = htmlspecialchars($logo, ENT_QUOTES, 'UTF-8');
                    $urlSafe = htmlspecialchars($url !== '' ? $url : '#', ENT_QUOTES, 'UTF-8');
                    echo '<div class="slide"><a href="' . $urlSafe . '" target="_blank" title="Visiter le site" rel="noopener noreferrer"><img src="' . $logoSafe . '" alt="Sponsor"></a></div>';
                }
                ?>
            </div>
        </div>

        <button class="next-btn" id="nextBtn">❯</button>
    </div>
</div>