import { $, $$ } from './utils.js';

export function initBannerCarousel() {
   const carousel = $("#bannerCarousel");
   if (!carousel) return;

   const track   = $(".banner-track",    carousel);
   const slides  = $$(".partner-banner", carousel);
   const btnPrev = carousel.querySelector(".carousel-btn--prev");
   const btnNext = carousel.querySelector(".carousel-btn--next");

   if (!track || slides.length === 0) return;

   const pageDots = document.getElementById("bannerPageDots");

   const isNarrow = () => window.innerWidth <= 700;
   const perPage  = () => isNarrow() ? 1 : 3;

   function pageCount() {
      return Math.ceil(slides.length / perPage());
   }

   let current = Math.floor(Math.random() * pageCount());

   function buildPageDots() {
      if (!pageDots) return;
      pageDots.innerHTML = "";
      const pages = pageCount();
      if (pages <= 1) {
         pageDots.classList.remove("is-visible");
         return;
      }
      pageDots.classList.add("is-visible");
      for (let i = 0; i < pages; i++) {
         const btn = document.createElement("button");
         btn.className = "carousel-pdot" + (i === current ? " is-active" : "");
         btn.setAttribute("aria-label", `Página ${i + 1}`);
         btn.addEventListener("click", () => goTo(i));
         pageDots.appendChild(btn);
      }
   }

   function updatePageDots() {
      if (!pageDots) return;
      pageDots.querySelectorAll(".carousel-pdot").forEach((d, i) => {
         d.classList.toggle("is-active", i === current);
      });
   }

   function goTo(index) {
      const pages = pageCount();
      current = ((index % pages) + pages) % pages;

      track.style.transform = `translateX(-${current * carousel.offsetWidth}px)`;

      const hasNav = pages > 1;
      if (btnPrev) btnPrev.style.display = hasNav ? "" : "none";
      if (btnNext) btnNext.style.display = hasNav ? "" : "none";

      const pp = perPage();
      slides.forEach((s, i) => {
         const visible = i >= current * pp && i < (current + 1) * pp;
         s.setAttribute("aria-hidden", String(!visible));
         s.setAttribute("tabindex", visible ? "0" : "-1");
      });

      updatePageDots();
   }

   buildPageDots();
   goTo(current);

   btnPrev && btnPrev.addEventListener("click", () => goTo(current - 1));
   btnNext && btnNext.addEventListener("click", () => goTo(current + 1));

   carousel.addEventListener("keydown", (e) => {
      if (e.key === "ArrowLeft")  goTo(current - 1);
      if (e.key === "ArrowRight") goTo(current + 1);
   });

   let touchStartX = 0;
   carousel.addEventListener("touchstart", (e) => {
      touchStartX = e.touches[0].clientX;
   }, { passive: true });
   carousel.addEventListener("touchend", (e) => {
      const dx = e.changedTouches[0].clientX - touchStartX;
      if (Math.abs(dx) > 40) goTo(current + (dx < 0 ? 1 : -1));
   }, { passive: true });

   window.addEventListener("resize", () => {
      current = Math.min(current, pageCount() - 1);
      buildPageDots();
      goTo(current);
   });
}
