<footer class="site-footer">
    <div class="carousel-wrapper">
        <button class="prev-btn" id="prevBtn">❮</button>
        
        <div class="carousel-container" id="carouselContainer">
            <div class="carousel-track" id="carouselTrack">
                <?php
                include 'config/sponsors.php';
                
                // On affiche chaque sponsor avec son lien
                foreach ($sponsors as $logo => $url) {
                    if (file_exists($logo)) {
                        echo '<div class="slide"><a href="' . $url . '" target="_blank" title="Visiter le site"><img src="' . $logo . '" alt="Sponsor"></a></div>';
                    }
                }
                ?>
            </div>
        </div>

        <button class="next-btn" id="nextBtn">❯</button>
    </div>
</footer>