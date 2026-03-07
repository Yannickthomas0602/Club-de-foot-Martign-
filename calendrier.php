<?php 
$page_title = "Calendrier";
include 'header.php';
?>

<main>
    <section class="calendrier-section">
        <h1 class="calendrier-title">Calendrier des matchs</h1>
        <div class="calendrier-container">
            <iframe src="https://calendar.google.com/calendar/embed?src=ccm.calendrier%40gmail.com&ctz=Europe%2FParis" style="border: 0" width="800" height="600" frameborder="0" scrolling="no"></iframe>
        </div>
    </section>
</main>

<?php include 'footer.php'; ?>