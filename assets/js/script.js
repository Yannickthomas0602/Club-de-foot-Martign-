document.addEventListener("DOMContentLoaded", () => {
	const carousels = document.querySelectorAll(".feature-card-carousel");

	carousels.forEach((carousel) => {
		const slides = Array.from(carousel.querySelectorAll(".results-slide"));
		if (!slides.length) {
			return;
		}

		const previousButton = carousel.querySelector(".results-nav.prev");
		const nextButton = carousel.querySelector(".results-nav.next");
		const titleText = carousel.querySelector(".feature-title-text");
		const ctaLink = carousel.querySelector(".feature-card-link");

		const slideConfig = {
			resultats: {
				title: "Les Derniers Résultats",
				linkText: "Voir tous les résultats",
				href: "resultats.php",
				external: false,
			},
			boutique: {
				title: "La Boutique",
				linkText: "Voir la boutique",
				href: "https://cadets-chelun-martigne.kalisport.com/",
				external: true,
			},
			convocation: {
				title: "Convocation",
				linkText: "Voir les convocations",
				href: "convocation_admin.php",
				external: false,
			},
		};

		let activeIndex = slides.findIndex((slide) => slide.classList.contains("is-active"));
		if (activeIndex < 0) {
			activeIndex = 0;
		}

		const renderSlide = () => {
			slides.forEach((slide, index) => {
				slide.classList.toggle("is-active", index === activeIndex);
			});

			const currentKey = slides[activeIndex].dataset.slide;
			const currentConfig = slideConfig[currentKey] || slideConfig.resultats;

			if (titleText) {
				titleText.textContent = currentConfig.title;
			}

			if (ctaLink) {
				ctaLink.textContent = currentConfig.linkText;
				ctaLink.href = currentConfig.href;

				if (currentConfig.external) {
					ctaLink.target = "_blank";
					ctaLink.rel = "noopener noreferrer";
				} else {
					ctaLink.removeAttribute("target");
					ctaLink.removeAttribute("rel");
				}
			}
		};

		previousButton?.addEventListener("click", () => {
			activeIndex = (activeIndex - 1 + slides.length) % slides.length;
			renderSlide();
		});

		nextButton?.addEventListener("click", () => {
			activeIndex = (activeIndex + 1) % slides.length;
			renderSlide();
		});

		renderSlide();
	});
});
