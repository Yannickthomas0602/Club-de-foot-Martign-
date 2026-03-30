document.addEventListener("DOMContentLoaded", () => {
    const burgerBtn = document.getElementById("mobile-menu-btn");
    const closeBtn = document.getElementById("close-drawer-btn");
    const drawer = document.getElementById("mobile-drawer");
    const overlay = document.getElementById("drawer-overlay");

    const toggleMenu = () => {
        const isOpen = drawer.classList.toggle("open");
        overlay.classList.toggle("open");
        if(burgerBtn) burgerBtn.classList.toggle("open"); // Animation de croix pour le burger
        
        // Bloque le défilement de la page (body) quand le menu est ouvert
        document.body.style.overflow = isOpen ? "hidden" : "";
    };

    if (burgerBtn && closeBtn && overlay) {
        burgerBtn.addEventListener("click", toggleMenu);
        closeBtn.addEventListener("click", toggleMenu);
        overlay.addEventListener("click", toggleMenu);
    }
});