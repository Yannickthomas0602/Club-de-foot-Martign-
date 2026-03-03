<?php
if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (isset($_SESSION['role_slug']) && $_SESSION['role_slug'] === 'admin') {
    return;
}

require_once __DIR__ . '/fonctions.php';

try {
    $pdoPopup = getDB();
    $stmtPopup = $pdoPopup->query(
        "SELECT id, titre, contenu
         FROM annonces_popup
         WHERE actif = 1
           AND date_fin > NOW()
         ORDER BY id DESC"
    );
    $annoncesPopup = $stmtPopup->fetchAll();
} catch (Throwable $e) {
    $annoncesPopup = [];
}

if (!$annoncesPopup) {
    return;
}
?>

<div id="popupOverlay" class="popup-overlay" aria-hidden="true">
    <div class="popup-modal" role="dialog" aria-modal="true" aria-labelledby="popupTitle">
        <button type="button" id="popupClose" class="popup-close" aria-label="Fermer">×</button>
        <h2 id="popupTitle">Annonces du club</h2>
        <div id="popupContent" class="popup-content">
            <?php foreach ($annoncesPopup as $annonce): ?>
                <article class="popup-item">
                    <h3 class="popup-item-title"><?= htmlspecialchars($annonce['titre'], ENT_QUOTES, 'UTF-8') ?></h3>
                    <div class="popup-item-body"><?= $annonce['contenu'] ?></div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<style>
.popup-overlay {
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.65);
    display: none;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    padding: 16px;
}

.popup-modal {
    width: 100%;
    max-width: 640px;
    max-height: 86vh;
    overflow-y: auto;
    background: #fff;
    border-radius: 10px;
    padding: 24px;
    position: relative;
    box-sizing: border-box;
    font-family: 'Roboto', sans-serif;
}

#popupTitle {
    margin: 0;
    font-size: 1.5rem;
    line-height: 1.25;
    padding-right: 28px;
}

.popup-close {
    position: absolute;
    top: 8px;
    right: 12px;
    border: 0;
    background: transparent;
    font-size: 28px;
    cursor: pointer;
    line-height: 1;
}

.popup-content {
    margin-top: 12px;
}

.popup-item + .popup-item {
    border-top: 1px solid #e5e5e5;
    margin-top: 14px;
    padding-top: 14px;
}

.popup-item-title {
    margin: 0 0 8px;
    font-size: 1.15rem;
    line-height: 1.3;
}

.popup-item-body {
    font-size: 1rem;
    line-height: 1.5;
    overflow-wrap: anywhere;
}

.popup-item-body img,
.popup-item-body iframe,
.popup-item-body video,
.popup-item-body table {
    max-width: 100%;
}

@media (max-width: 768px) {
    .popup-overlay {
        padding: 10px;
    }

    .popup-modal {
        max-width: 100%;
        max-height: 90vh;
        padding: 16px;
        border-radius: 8px;
    }

    #popupTitle {
        font-size: 1.25rem;
    }

    .popup-item-title {
        font-size: 1.05rem;
    }

    .popup-item-body {
        font-size: 0.95rem;
    }

    .popup-close {
        top: 6px;
        right: 8px;
        font-size: 26px;
    }
}

@media (max-width: 480px) {
    .popup-overlay {
        padding: 8px;
    }

    .popup-modal {
        max-height: 92vh;
        padding: 14px;
    }

    #popupTitle {
        font-size: 1.12rem;
    }

    .popup-content {
        margin-top: 10px;
    }
}
</style>

<script>
(function () {
    const overlay = document.getElementById('popupOverlay');
    const closeBtn = document.getElementById('popupClose');
    const autoOpenKey = 'club_popup_opened_once';

    if (!overlay || !closeBtn) {
        return;
    }

    function closePopup() {
        overlay.style.display = 'none';
        overlay.setAttribute('aria-hidden', 'true');
    }

    function openPopup() {
        overlay.style.display = 'flex';
        overlay.setAttribute('aria-hidden', 'false');
    }

    window.openClubPopup = openPopup;

    if (!sessionStorage.getItem(autoOpenKey)) {
        openPopup();
        sessionStorage.setItem(autoOpenKey, '1');
    }

    closeBtn.addEventListener('click', closePopup);

    overlay.addEventListener('click', function (event) {
        if (event.target === overlay) {
            closePopup();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && overlay.style.display === 'flex') {
            closePopup();
        }
    });
})();
</script>
